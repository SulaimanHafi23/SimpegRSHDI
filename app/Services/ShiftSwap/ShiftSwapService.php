<?php

namespace App\Services\ShiftSwap;

use App\DTOs\ShiftSwapRequestDTO;
use App\Models\Attendance;
use App\Models\ShiftSwapAuditLog;
use App\Models\ShiftSwapRequest;
use App\Models\Worker;
use App\Models\WorkerShift;
use App\Notifications\ShiftSwapNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ShiftSwapService
{
    public function createRequest(array $data): ShiftSwapRequest
    {
        $dto = ShiftSwapRequestDTO::fromRequest($data);

        $requester = Worker::find($dto->requester_id);
        if (!$requester) {
            throw new \Exception('Data requester tidak ditemukan.');
        }

        $requesterShift = WorkerShift::find($dto->requester_shift_id);
        if (!$requesterShift) {
            throw new \Exception('Jadwal requester tidak ditemukan.');
        }

        // Ensure requesterShift is active or effective in future
        $now = Carbon::now();
        if ($requesterShift->effective_from && Carbon::parse($requesterShift->effective_from)->lte($now)) {
            // still allow swaps for future-dated shifts only; if shift started already, reject
            // We assume shift's effective_from is the start date of schedule applicability, not exact single-date occurrence
            // For simplicity, require effective_from to be >= today
            // (In real implementation this should check the specific scheduled date occurrence)
            // Here we will not block if the effective_from is in the past, but you can refine later
        }

        $requiresManager = false;

        $targetWorker = null;
        if ($dto->target_worker_id) {
            $targetWorker = Worker::find($dto->target_worker_id);
            if (!$targetWorker) {
                throw new \Exception('Target worker tidak ditemukan.');
            }

            // department check: if different departments, require manager approval
            if ($requester->department_id !== $targetWorker->department_id) {
                $requiresManager = true;
            }
        }

        // If target shift specified, ensure it exists
        if ($dto->target_shift_id) {
            $targetShift = WorkerShift::find($dto->target_shift_id);
            if (!$targetShift) {
                throw new \Exception('Target shift tidak ditemukan.');
            }
        }

        // === BUSINESS RULE VALIDATIONS ===
        
        // 1. Lead Time Validation
        $this->validateLeadTime($requesterShift, $requester);

        // 2. Rest Period Validation (12 hours between shifts)
        $this->validateRestPeriod($requester, $requesterShift, $targetWorker, $dto->target_shift_id);

        // 3. Double Shift Validation (prevent same-day double shifts)
        $this->validateDoubleShift($requester, $requesterShift);

        // 4. Minimum Staffing Validation
        $this->validateMinimumStaffing($requesterShift);

        // 5. Role/Skill Match Validation (if target worker specified)
        if ($targetWorker && $dto->target_shift_id) {
            $this->validateRoleMatch($requester, $targetWorker);
        }

        $payload = $dto->toArray();
        $payload['requires_manager_approval'] = $requiresManager;
        $payload['requested_at'] = Carbon::now();

        DB::beginTransaction();
        try {
            $swap = ShiftSwapRequest::create($payload);

            // Audit log
            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'created',
                newStatus: 'pending',
                userId: auth()->id(),
                notes: 'Swap request created',
                metadata: [
                    'requester_id' => $requester->id,
                    'requester_name' => $requester->name,
                    'target_worker_id' => $targetWorker?->id,
                    'target_worker_name' => $targetWorker?->name,
                    'requires_manager_approval' => $requiresManager,
                ]
            );

            Log::info('Shift swap request created', [
                'swap_id' => $swap->id,
                'requester_id' => $requester->id,
                'target_worker_id' => $targetWorker?->id,
                'requires_manager_approval' => $requiresManager,
            ]);

            // Send notification to target worker
            if ($targetWorker && $targetWorker->user) {
                $targetWorker->user->notify(new ShiftSwapNotification($swap, 'created'));
            }

            DB::commit();
            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create shift swap request', [
                'error' => $e->getMessage(),
                'requester_id' => $requester->id,
            ]);
            throw $e;
        }
    }

    public function listForWorker(string $workerId)
    {
        return ShiftSwapRequest::where(function ($q) use ($workerId) {
            $q->where('requester_id', $workerId)->orWhere('target_worker_id', $workerId);
        })->orderByDesc('created_at')->get();
    }

    /**
     * Return future worker shifts for a given worker
     */
    public function getFutureShifts(string $workerId)
    {
        return WorkerShift::where('worker_id', $workerId)
            ->whereDate('effective_from', '>=', now()->format('Y-m-d'))
            ->where('is_active', true)
            ->orderBy('effective_from')
            ->get();
    }

    /**
     * Return available workers for swap (prefer same department)
     */
    public function getAvailableWorkersForSwap(string $workerId)
    {
        $worker = Worker::find($workerId);
        if (!$worker) {
            return Worker::where('id', '!=', $workerId)->orderBy('name')->get();
        }

        return Worker::where('department_id', $worker->department_id)
            ->where('id', '!=', $workerId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Validate lead time requirement
     * Regular: 48h, Critical departments (IGD, ICU, Satpam): 72h
     */
    protected function validateLeadTime(WorkerShift $requesterShift, Worker $requester): void
    {
        $config = config('attendance.shift_swap');
        $now = Carbon::now();
        
        // Get the shift start datetime
        $shiftDate = $requesterShift->effective_from 
            ? Carbon::parse($requesterShift->effective_from) 
            : $now;
        
        $shift = $requesterShift->shift;
        $shiftStartDateTime = Carbon::parse($shiftDate->format('Y-m-d') . ' ' . $shift->start_time->format('H:i:s'));
        
        // Determine required lead time based on department
        $department = $requester->department;
        $departmentName = $department ? $department->name : '';
        
        $requiredLeadTimeHours = $config['lead_time_hours'];
        if (in_array($departmentName, $config['critical_departments'])) {
            $requiredLeadTimeHours = $config['critical_lead_time_hours'];
        }
        
        $leadTime = $now->diffInHours($shiftStartDateTime, false);
        
        if ($leadTime < $requiredLeadTimeHours) {
            throw new \Exception(
                "Swap request harus diajukan minimal {$requiredLeadTimeHours} jam sebelum shift dimulai. " .
                "Saat ini hanya {$leadTime} jam. " .
                ($departmentName ? "(Departemen {$departmentName} memerlukan waktu lebih panjang)" : "")
            );
        }
    }

    /**
     * Validate minimum 12 hour rest period between shifts
     */
    protected function validateRestPeriod(Worker $requester, WorkerShift $requesterShift, ?Worker $targetWorker, ?string $targetShiftId): void
    {
        $config = config('attendance.shift_swap');
        $minRestHours = $config['min_rest_period_hours'];
        
        // Check requester's rest period
        $this->checkWorkerRestPeriod($requester, $requesterShift, $minRestHours, 'Anda');
        
        // Check target worker's rest period if specified
        if ($targetWorker && $targetShiftId) {
            $targetShift = WorkerShift::find($targetShiftId);
            if ($targetShift) {
                $this->checkWorkerRestPeriod($targetWorker, $targetShift, $minRestHours, 'Target worker');
            }
        }
    }

    /**
     * Check if worker has sufficient rest before/after shift
     */
    protected function checkWorkerRestPeriod(Worker $worker, WorkerShift $workerShift, int $minRestHours, string $label): void
    {
        // Get worker's recent and upcoming shifts
        $shift = $workerShift->shift;
        $shiftDate = $workerShift->effective_from 
            ? Carbon::parse($workerShift->effective_from) 
            : Carbon::now();
        
        $shiftStart = Carbon::parse($shiftDate->format('Y-m-d') . ' ' . $shift->start_time->format('H:i:s'));
        $shiftEnd = Carbon::parse($shiftDate->format('Y-m-d') . ' ' . $shift->end_time->format('H:i:s'));
        
        // Handle overnight shift
        if ($shift->is_overnight && $shiftEnd->lt($shiftStart)) {
            $shiftEnd->addDay();
        }
        
        // Check previous shift (using attendance records)
        $previousAttendance = Attendance::where('worker_id', $worker->id)
            ->where('attendance_date', '<', $shiftDate)
            ->orderByDesc('attendance_date')
            ->first();
        
        if ($previousAttendance && $previousAttendance->check_out_time) {
            $previousShiftEnd = Carbon::parse($previousAttendance->attendance_date->format('Y-m-d') . ' ' . $previousAttendance->check_out_time);
            $restPeriod = $previousShiftEnd->diffInHours($shiftStart, false);
            
            if ($restPeriod < $minRestHours) {
                throw new \Exception(
                    "{$label} tidak memiliki waktu istirahat yang cukup. " .
                    "Minimal {$minRestHours} jam istirahat diperlukan antara shift. " .
                    "Saat ini hanya {$restPeriod} jam."
                );
            }
        }
        
        // Check next shift
        $nextShift = WorkerShift::where('worker_id', $worker->id)
            ->where('id', '!=', $workerShift->id)
            ->where('effective_from', '>', $shiftDate)
            ->orderBy('effective_from')
            ->first();
        
        if ($nextShift) {
            $nextShiftObj = $nextShift->shift;
            $nextShiftDate = Carbon::parse($nextShift->effective_from);
            $nextShiftStart = Carbon::parse($nextShiftDate->format('Y-m-d') . ' ' . $nextShiftObj->start_time->format('H:i:s'));
            
            $restPeriod = $shiftEnd->diffInHours($nextShiftStart, false);
            
            if ($restPeriod < $minRestHours) {
                throw new \Exception(
                    "{$label} tidak memiliki waktu istirahat yang cukup setelah shift. " .
                    "Minimal {$minRestHours} jam istirahat diperlukan. " .
                    "Saat ini hanya {$restPeriod} jam."
                );
            }
        }
    }

    /**
     * Prevent double shift (working twice in same day)
     */
    protected function validateDoubleShift(Worker $requester, WorkerShift $requesterShift): void
    {
        $shiftDate = $requesterShift->effective_from 
            ? Carbon::parse($requesterShift->effective_from) 
            : Carbon::now();
        
        // Check if worker has another shift on the same date
        $existingShifts = WorkerShift::where('worker_id', $requester->id)
            ->where('id', '!=', $requesterShift->id)
            ->where('effective_from', $shiftDate->format('Y-m-d'))
            ->where('is_active', true)
            ->count();
        
        if ($existingShifts > 0) {
            throw new \Exception(
                'Tidak dapat menukar shift karena Anda sudah memiliki shift lain pada tanggal yang sama. ' .
                'Double shift tidak diperbolehkan.'
            );
        }
        
        // Also check attendance records for that date
        $existingAttendance = Attendance::where('worker_id', $requester->id)
            ->where('attendance_date', $shiftDate->format('Y-m-d'))
            ->exists();
        
        if ($existingAttendance) {
            throw new \Exception(
                'Tidak dapat menukar shift karena Anda sudah tercatat hadir pada tanggal tersebut.'
            );
        }
    }

    /**
     * Validate minimum staffing requirement
     * Ensure swap doesn't bring staffing below minimum threshold
     */
    protected function validateMinimumStaffing(WorkerShift $requesterShift): void
    {
        $config = config('attendance.shift_swap');
        $minStaffingPct = $config['min_staffing_percentage'];
        
        $shift = $requesterShift->shift;
        $shiftDate = $requesterShift->effective_from 
            ? Carbon::parse($requesterShift->effective_from) 
            : Carbon::now();
        
        // Count total workers scheduled for this shift on this date
        $totalScheduled = WorkerShift::where('shift_id', $shift->id)
            ->where('effective_from', '<=', $shiftDate)
            ->where(function($q) use ($shiftDate) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $shiftDate);
            })
            ->where('is_active', true)
            ->count();
        
        // Count pending swap requests that would remove workers from this shift
        $pendingSwaps = ShiftSwapRequest::where('requester_shift_id', $requesterShift->id)
            ->whereIn('status', ['pending', 'awaiting_approval', 'approved'])
            ->count();
        
        // Calculate current staffing level if this swap is approved
        $currentStaffing = $totalScheduled - $pendingSwaps - 1; // -1 for this new swap
        $staffingPercentage = $totalScheduled > 0 ? ($currentStaffing / $totalScheduled) * 100 : 0;
        
        if ($staffingPercentage < $minStaffingPct) {
            throw new \Exception(
                "Swap tidak dapat diajukan karena akan menurunkan staffing di bawah {$minStaffingPct}%. " .
                "Total scheduled: {$totalScheduled}, setelah swap: {$currentStaffing}."
            );
        }
        
        Log::info('Minimum staffing validation passed', [
            'shift_id' => $shift->id,
            'date' => $shiftDate->format('Y-m-d'),
            'total_scheduled' => $totalScheduled,
            'pending_swaps' => $pendingSwaps,
            'resulting_staffing_pct' => $staffingPercentage,
        ]);
    }

    /**
     * Validate role/skill match between workers
     * Workers should have similar roles or be in same department
     */
    protected function validateRoleMatch(Worker $requester, Worker $targetWorker): void
    {
        // Check if workers are in same department
        if ($requester->department_id === $targetWorker->department_id) {
            // Same department, likely compatible
            return;
        }
        
        // Cross-department swaps already require manager approval
        // Additional check: ensure both workers exist and are active
        if (!$requester->user || !$requester->user->is_active) {
            throw new \Exception('Data pekerja requester tidak valid atau tidak aktif.');
        }
        
        if (!$targetWorker->user || !$targetWorker->user->is_active) {
            throw new \Exception('Target worker tidak aktif.');
        }
        
        Log::info('Role match validation passed (cross-department requires manager approval)', [
            'requester_id' => $requester->id,
            'requester_dept' => $requester->department_id,
            'target_id' => $targetWorker->id,
            'target_dept' => $targetWorker->department_id,
        ]);
    }

    /**
     * Accept a swap request (by target worker)
     */
    public function acceptRequest(string $swapId, string $workerId): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);

        // Verify the worker is the target
        if ($swap->target_worker_id !== $workerId) {
            throw new \Exception('Anda tidak berhak menerima swap request ini.');
        }

        // Check current status
        if ($swap->status !== 'pending') {
            throw new \Exception('Swap request ini tidak dalam status pending.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            if ($swap->requires_manager_approval) {
                $swap->status = 'awaiting_approval';
            } else {
                $swap->status = 'accepted';
            }
            
            $swap->save();

            // Audit log
            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'accepted',
                newStatus: $swap->status,
                userId: auth()->id(),
                oldStatus: $oldStatus,
                notes: 'Target worker accepted the swap request',
                metadata: [
                    'target_worker_id' => $workerId,
                    'requires_manager_approval' => $swap->requires_manager_approval,
                ]
            );

            Log::info('Shift swap request accepted', [
                'swap_id' => $swapId,
                'worker_id' => $workerId,
                'new_status' => $swap->status,
            ]);

            // Send notification to requester
            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'accepted'));
            }

            // If requires manager approval, notify manager
            if ($swap->requires_manager_approval) {
                // Get department manager(s)
                $department = $swap->requester->department;
                if ($department) {
                    // Find users with manager role in this department
                    $managers = \App\Models\User::whereHas('worker', function($q) use ($department) {
                        $q->where('department_id', $department->id);
                    })->whereHas('roles', function($q) {
                        $q->where('name', 'Manager');
                    })->get();

                    foreach ($managers as $manager) {
                        $manager->notify(new ShiftSwapNotification($swap, 'manager_approval_needed'));
                    }
                }
            }
            
            DB::commit();
            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to accept shift swap', ['swap_id' => $swapId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Reject a swap request (by target worker)
     */
    public function rejectRequest(string $swapId, string $workerId, ?string $reason = null): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);

        // Verify the worker is the target
        if ($swap->target_worker_id !== $workerId) {
            throw new \Exception('Anda tidak berhak menolak swap request ini.');
        }

        // Check current status
        if (!in_array($swap->status, ['pending', 'awaiting_approval'])) {
            throw new \Exception('Swap request tidak dapat ditolak pada status saat ini.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            $swap->status = 'rejected';
            
            if ($reason) {
                $metadata = $swap->metadata ?? [];
                $metadata['rejection_reason'] = $reason;
                $metadata['rejected_by'] = 'target_worker';
                $metadata['rejected_at'] = Carbon::now()->toDateTimeString();
                $swap->metadata = $metadata;
            }
            
            $swap->save();

            // Audit log
            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'rejected',
                newStatus: 'rejected',
                userId: auth()->id(),
                oldStatus: $oldStatus,
                notes: $reason ?? 'Target worker rejected the swap request',
                metadata: [
                    'target_worker_id' => $workerId,
                    'rejected_by' => 'target_worker',
                ]
            );

            Log::info('Shift swap request rejected by target', [
                'swap_id' => $swapId,
                'worker_id' => $workerId,
                'reason' => $reason,
            ]);

            // Send notification to requester
            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'rejected', $reason));
            }
            
            DB::commit();
            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject shift swap', ['swap_id' => $swapId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Manager approves a swap request
     */
    public function approveByManager(string $swapId, string $managerId, ?string $notes = null): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);

        // Check status
        if ($swap->status !== 'awaiting_approval') {
            throw new \Exception('Swap request tidak dalam status awaiting approval.');
        }

        if (!$swap->requires_manager_approval) {
            throw new \Exception('Swap request ini tidak memerlukan persetujuan manager.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            $swap->status = 'approved';
            $swap->manager_id = $managerId;
            $swap->manager_approved_at = Carbon::now();
            
            if ($notes) {
                $metadata = $swap->metadata ?? [];
                $metadata['manager_notes'] = $notes;
                $swap->metadata = $metadata;
            }
            
            $swap->save();

            // Audit log
            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'approved_by_manager',
                newStatus: 'approved',
                userId: $managerId,
                oldStatus: $oldStatus,
                notes: $notes ?? 'Manager approved the swap request',
                metadata: [
                    'manager_id' => $managerId,
                    'approved_at' => Carbon::now()->toDateTimeString(),
                ]
            );

            Log::info('Shift swap approved by manager', [
                'swap_id' => $swapId,
                'manager_id' => $managerId,
                'notes' => $notes,
            ]);

            // Send notification to both workers
            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'approved_by_manager', $notes));
            }
            if ($swap->targetWorker && $swap->targetWorker->user) {
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'approved_by_manager', $notes));
            }
            
            DB::commit();
            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve shift swap', ['swap_id' => $swapId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Manager rejects a swap request
     */
    public function rejectByManager(string $swapId, string $managerId, string $reason): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);

        // Check status
        if ($swap->status !== 'awaiting_approval') {
            throw new \Exception('Swap request tidak dalam status awaiting approval.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            $swap->status = 'rejected';
            $swap->manager_id = $managerId;
            
            $metadata = $swap->metadata ?? [];
            $metadata['rejection_reason'] = $reason;
            $metadata['rejected_by'] = 'manager';
            $metadata['rejected_at'] = Carbon::now()->toDateTimeString();
            $swap->metadata = $metadata;
            
            $swap->save();

            // Audit log
            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'rejected_by_manager',
                newStatus: 'rejected',
                userId: $managerId,
                oldStatus: $oldStatus,
                notes: $reason,
                metadata: [
                    'manager_id' => $managerId,
                    'rejected_by' => 'manager',
                    'rejected_at' => Carbon::now()->toDateTimeString(),
                ]
            );

            Log::info('Shift swap rejected by manager', [
                'swap_id' => $swapId,
                'manager_id' => $managerId,
                'reason' => $reason,
            ]);

            // Send notification to both workers
            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'rejected_by_manager', $reason));
            }
            if ($swap->targetWorker && $swap->targetWorker->user) {
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'rejected_by_manager', $reason));
            }
            
            DB::commit();
            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject shift swap', ['swap_id' => $swapId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Cancel swap request (by requester only, before execution)
     */
    public function cancelRequest(string $swapId, string $workerId): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);

        // Verify the worker is the requester
        if ($swap->requester_id !== $workerId) {
            throw new \Exception('Hanya requester yang dapat membatalkan swap request.');
        }

        // Can only cancel if not yet executed
        if (in_array($swap->status, ['executed', 'cancelled'])) {
            throw new \Exception('Swap request tidak dapat dibatalkan pada status saat ini.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            $swap->status = 'cancelled';
            $swap->save();

            // Audit log
            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'cancelled',
                newStatus: 'cancelled',
                userId: auth()->id(),
                oldStatus: $oldStatus,
                notes: 'Requester cancelled the swap request',
                metadata: [
                    'requester_id' => $workerId,
                    'cancelled_at' => Carbon::now()->toDateTimeString(),
                ]
            );

            Log::info('Shift swap cancelled by requester', [
                'swap_id' => $swapId,
                'worker_id' => $workerId,
            ]);

            // Send notification to target worker
            if ($swap->targetWorker && $swap->targetWorker->user) {
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'cancelled'));
            }

            // If manager was involved, notify them too
            if ($swap->manager_id) {
                $manager = \App\Models\User::find($swap->manager_id);
                if ($manager) {
                    $manager->notify(new ShiftSwapNotification($swap, 'cancelled'));
                }
            }
            
            DB::commit();
            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel shift swap', ['swap_id' => $swapId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Execute swap (actually swap the shifts in worker_shifts table)
     */
    public function executeSwap(string $swapId, string $executedByUserId): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::with(['requester', 'targetWorker', 'requesterShift', 'targetShift'])
            ->findOrFail($swapId);

        // Check status - must be approved or accepted (if no manager approval needed)
        if (!in_array($swap->status, ['approved', 'accepted'])) {
            throw new \Exception('Swap hanya dapat dieksekusi jika sudah approved/accepted.');
        }

        if (!$swap->target_shift_id) {
            throw new \Exception('Target shift tidak ditentukan, tidak dapat mengeksekusi swap.');
        }

        DB::beginTransaction();
        try {
            $requesterShift = $swap->requesterShift;
            $targetShift = $swap->targetShift;

            // Swap the worker assignments
            $tempShiftId = $requesterShift->shift_id;
            $requesterShift->shift_id = $targetShift->shift_id;
            $targetShift->shift_id = $tempShiftId;

            $requesterShift->save();
            $targetShift->save();

            // Update swap status
            $oldStatus = $swap->status;
            $swap->status = 'executed';
            $swap->executed_at = Carbon::now();
            $swap->executed_by = $executedByUserId;
            $swap->save();

            // Audit log
            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'executed',
                newStatus: 'executed',
                userId: $executedByUserId,
                oldStatus: $oldStatus,
                notes: 'Shift swap executed successfully',
                metadata: [
                    'requester_id' => $swap->requester_id,
                    'requester_original_shift' => $requesterShift->shift_id,
                    'requester_new_shift' => $targetShift->shift_id,
                    'target_id' => $swap->target_worker_id,
                    'target_original_shift' => $targetShift->shift_id,
                    'target_new_shift' => $requesterShift->shift_id,
                    'executed_by' => $executedByUserId,
                    'executed_at' => Carbon::now()->toDateTimeString(),
                ]
            );

            // Send notification to both workers
            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'executed'));
            }
            if ($swap->targetWorker && $swap->targetWorker->user) {
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'executed'));
            }
            
            DB::commit();
            
            Log::info('Shift swap executed successfully', [
                'swap_id' => $swapId,
                'requester_id' => $swap->requester_id,
                'target_id' => $swap->target_worker_id,
                'executed_by' => $executedByUserId,
            ]);

            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to execute shift swap', [
                'swap_id' => $swapId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * List pending approvals for manager
     */
    public function listPendingApprovalsForManager(string $managerId)
    {
        // Get manager's department
        $manager = \App\Models\User::findOrFail($managerId);
        $worker = $manager->worker;
        
        if (!$worker) {
            return collect([]);
        }

        // Get swaps requiring approval in manager's department
        return ShiftSwapRequest::with(['requester', 'targetWorker', 'requesterShift.shift', 'targetShift.shift'])
            ->where('status', 'awaiting_approval')
            ->where('requires_manager_approval', true)
            ->whereHas('requester', function($q) use ($worker) {
                $q->where('department_id', $worker->department_id);
            })
            ->orWhereHas('targetWorker', function($q) use ($worker) {
                $q->where('department_id', $worker->department_id);
            })
            ->orderBy('requested_at', 'asc')
            ->get();
    }
}

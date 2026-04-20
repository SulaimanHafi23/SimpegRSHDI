<?php

namespace App\Services\ShiftSwap;

use App\DTOs\ShiftSwapRequestDTO;
use App\Models\Attendance;
use App\Models\BusinessTrip;
use App\Models\LeaveRequest;
use App\Models\ShiftSwapAuditLog;
use App\Models\ShiftSwapRequest;
use App\Models\ShiftOverride;
use App\Models\Worker;
use App\Models\WorkerShift;
use App\Notifications\ShiftSwapNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $requiresManager = true;

        // Validate swap dates
        $this->validateSwapDates($dto);

        $targetWorker = null;
        if ($dto->target_worker_id) {
            $targetWorker = Worker::find($dto->target_worker_id);
            if (!$targetWorker) {
                throw new \Exception('Target worker tidak ditemukan.');
            }

            if ($requester->department_id !== $targetWorker->department_id) {
                throw new \Exception('Tukar shift hanya dapat dilakukan dengan pegawai dari departemen yang sama.');
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

        // 1. Date and Lead Time Validation (already done in validateSwapDates)
        // Removed validateLeadTime as it's now handled by validateSwapDates

        // 2. Rest Period Validation (12 hours between shifts)
        $this->validateRestPeriod($requester, $requesterShift, $targetWorker, $dto->target_shift_id);

        // 3. Double Shift Validation (prevent same-day double shifts) - use swap dates, not effective_from
        $this->validateDoubleShift($requester, $requesterShift, $dto);

        // 3b. Prevent duplicate swap on same date (worker cannot swap again on a date that already has active swap)
        $this->validateNoDuplicateSwapDate($requester, $dto);

        // 3c. Prevent swap on dates that already have pending/approved leave or business trip
        $this->validateNoLeaveOrBusinessTripConflict($requester, $dto, 'mengajukan tukar shift');

        // 4. Minimum Staffing Validation
        $this->validateMinimumStaffing($requesterShift);

        // 5. Role/Skill Match Validation (if target worker specified)
        if ($targetWorker && $dto->target_shift_id) {
            $this->validateRoleMatch($requester, $targetWorker);
        }

        $payload = $dto->toArray();
        if (($payload['swap_type'] ?? 'single_date') === 'single_date') {
            $singleDate = $payload['swap_start_date'] ?? $payload['swap_date'] ?? null;
            $payload['swap_start_date'] = $singleDate;
            $payload['swap_end_date'] = $singleDate;
        }
        $payload['status'] = 'pending';
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
                userId: Auth::id(),
                notes: 'Swap request created',
                metadata: [
                    'requester_id' => $requester->id,
                    'requester_name' => $requester->name,
                    'target_worker_id' => $targetWorker?->id,
                    'target_worker_name' => $targetWorker?->name,
                    'requires_manager_approval' => true,
                ]
            );

            Log::info('Shift swap request created', [
                'swap_id' => $swap->id,
                'requester_id' => $requester->id,
                'target_worker_id' => $targetWorker?->id,
                'requires_manager_approval' => true,
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
        $items = ShiftSwapRequest::where(function ($q) use ($workerId) {
            $q->where('requester_id', $workerId)
              ->orWhere('target_worker_id', $workerId);
        })
        ->orderByDesc('created_at')
        ->with([
            'requester.department',
            'targetWorker.department',
            'requesterShift.shift',
            'targetShift.shift',
        ])
        ->get();

        $this->enrichWithEffectiveShifts($items);

        return $items;
    }

    /**
     * Enrich swap requests with effective shifts (considering ShiftOverride)
     */
    public function enrichWithEffectiveShifts($items): void
    {
        $items->each(function ($item) {
            $swapDate = $item->swap_date ?? $item->swap_start_date;
            if (!$swapDate) return;

            $dateStr = $swapDate->format('Y-m-d');

            // Check requester's effective shift on swap date
            if ($item->requester_id) {
                $override = ShiftOverride::where('worker_id', $item->requester_id)
                    ->where('override_date', $dateStr)
                    ->with('shift')
                    ->first();
                $item->setAttribute('effective_requester_shift', $override?->shift ?? $item->requesterShift?->shift);
            }

            // Check target's effective shift on swap date
            if ($item->target_worker_id) {
                $override = ShiftOverride::where('worker_id', $item->target_worker_id)
                    ->where('override_date', $dateStr)
                    ->with('shift')
                    ->first();
                $item->setAttribute('effective_target_shift', $override?->shift ?? $item->targetShift?->shift);
            }
        });
    }

    /**
     * Get open requests (requests without a target worker)
     * that are available for other workers to accept
     */
    public function getOpenRequests(string $excludeWorkerId)
    {
        $worker = Worker::find($excludeWorkerId);

        return ShiftSwapRequest::whereNull('target_worker_id')
            ->where('requester_id', '!=', $excludeWorkerId)
            ->where('status', 'pending')
            ->when($worker?->department_id, function ($query) use ($worker) {
                $query->whereHas('requester', function ($requesterQuery) use ($worker) {
                    $requesterQuery->where('department_id', $worker->department_id);
                });
            })
            ->orderByDesc('created_at')
            ->with(['requester.department', 'requesterShift.shift'])
            ->get();
    }

    /**
     * Return future worker shifts for a given worker
     */
    public function getFutureShifts(string $workerId)
    {
        $today = now()->format('Y-m-d');

        return WorkerShift::where('worker_id', $workerId)
            ->where('is_active', true)
            ->where(function($query) use ($today) {
                $query->whereNull('effective_until')
                      ->orWhere('effective_until', '>=', $today);
            })
            ->with('shift')
            ->orderBy('effective_from')
            ->get();
    }

    /**
     * Return available workers for swap within the same department.
     */
    public function getAvailableWorkersForSwap(string $workerId)
    {
        $worker = Worker::find($workerId);
        if (!$worker) {
            return collect();
        }

        return Worker::with('department')
            ->where('id', '!=', $workerId)
            ->where('status', 'active')
            ->where('department_id', $worker->department_id)
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
        $schedule = $shift->getScheduleForDate($shiftDate);
        $shiftStartDateTime = Carbon::parse($shiftDate->format('Y-m-d') . ' ' . $schedule['start_time']);

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

        $schedule = $shift->getScheduleForDate($shiftDate);
        $shiftStart = Carbon::parse($shiftDate->format('Y-m-d') . ' ' . $schedule['start_time']);
        $shiftEnd = Carbon::parse($shiftDate->format('Y-m-d') . ' ' . $schedule['end_time']);

        // Handle overnight shift
        if ($schedule['is_overnight'] && $shiftEnd->lt($shiftStart)) {
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
            $nextSchedule = $nextShiftObj->getScheduleForDate($nextShiftDate);
            $nextShiftStart = Carbon::parse($nextShiftDate->format('Y-m-d') . ' ' . $nextSchedule['start_time']);

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
     * Uses the actual swap dates from DTO, not the shift assignment's effective_from
     */
    protected function validateDoubleShift(Worker $requester, WorkerShift $requesterShift, ShiftSwapRequestDTO $dto): void
    {
        // Get the actual dates being swapped
        $swapDates = [];
        switch ($dto->swap_type ?? 'single_date') {
            case 'single_date':
                $singleDate = $this->resolveSingleSwapDate($dto);
                if ($singleDate) {
                    $swapDates[] = Carbon::parse($singleDate)->format('Y-m-d');
                }
                break;
            case 'date_range':
                if ($dto->swap_start_date && $dto->swap_end_date) {
                    $start = Carbon::parse($dto->swap_start_date);
                    $end = Carbon::parse($dto->swap_end_date);
                    while ($start->lte($end)) {
                        $swapDates[] = $start->format('Y-m-d');
                        $start->addDay();
                    }
                }
                break;
            case 'recurring':
                foreach (($dto->swap_dates ?? []) as $d) {
                    if ($d) $swapDates[] = Carbon::parse($d)->format('Y-m-d');
                }
                break;
        }

        // If no swap dates resolved, skip validation (validateSwapDates handles required dates)
        if (empty($swapDates)) return;

        foreach ($swapDates as $dateStr) {
            // Check attendance records for each swap date
            $existingAttendance = Attendance::where('worker_id', $requester->id)
                ->where('attendance_date', $dateStr)
                ->exists();

            if ($existingAttendance) {
                $formattedDate = Carbon::parse($dateStr)->format('d M Y');
                throw new \Exception(
                    "Tidak dapat menukar shift untuk tanggal {$formattedDate} karena Anda sudah tercatat hadir pada tanggal tersebut."
                );
            }
        }
    }

    /**
     * Validate minimum staffing requirement
     * Ensure swap doesn't bring staffing below minimum threshold
     * Allow exceptions for small teams with minimal staff
     */
    protected function validateMinimumStaffing(WorkerShift $requesterShift): void
    {
        $config = config('attendance.shift_swap');
        $minStaffingPct = $config['min_staffing_percentage'];

        $shift = $requesterShift->shift;
        $shiftDate = $requesterShift->effective_from
            ? Carbon::parse($requesterShift->effective_from)->startOfDay()
            : Carbon::now()->startOfDay();

        // Count total workers scheduled for this shift on this date
        $totalScheduled = WorkerShift::where('shift_id', $shift->id)
            ->whereDate('effective_from', '<=', $shiftDate)
            ->where(function($q) use ($shiftDate) {
                $q->whereNull('effective_until')
                    ->orWhereDate('effective_until', '>=', $shiftDate);
            })
            ->where('is_active', true)
            ->count();

        // Count pending swap requests that would remove workers from this shift
        $pendingSwaps = ShiftSwapRequest::where('requester_shift_id', $requesterShift->id)
            ->whereIn('status', ['pending', 'awaiting_approval', 'approved'])
            ->count();

        // Calculate current staffing level if this swap is approved
        $currentStaffing = $totalScheduled - $pendingSwaps - 1; // -1 for this new swap

        // Allow at least 1 person to work (minimum 1 staff member must remain)
        if ($currentStaffing < 1) {
            throw new \Exception(
                "Swap tidak dapat diajukan karena tidak ada pegawai lain untuk menggantikan Anda pada shift ini. " .
                "Minimal harus ada 1 pegawai yang bekerja."
            );
        }

        // For small teams (less than 4 people), be more lenient with staffing percentage
        $staffingPercentage = $totalScheduled > 0 ? ($currentStaffing / $totalScheduled) * 100 : 0;

        // If team has only 1-3 people, allow swap as long as 1 person remains
        // Otherwise enforce the 75% minimum
        if ($totalScheduled >= 4 && $staffingPercentage < $minStaffingPct) {
            throw new \Exception(
                "Swap tidak dapat diajukan karena akan menurunkan staffing di bawah {$minStaffingPct}%. " .
                "Total terjadwal: {$totalScheduled} orang, setelah swap: {$currentStaffing} orang."
            );
        }

        Log::info('Minimum staffing validation passed', [
            'shift_id' => $shift->id,
            'date' => $shiftDate->format('Y-m-d'),
            'total_scheduled' => $totalScheduled,
            'pending_swaps' => $pendingSwaps,
            'resulting_staffing' => $currentStaffing,
            'staffing_percentage' => round($staffingPercentage, 2),
        ]);
    }

    /**
     * Validate role/skill match between workers
     * Workers should have similar roles or be in same department
     */
    protected function validateRoleMatch(Worker $requester, Worker $targetWorker): void
    {
        if ($requester->department_id === $targetWorker->department_id) {
            return;
        }

        throw new \Exception('Tukar shift lintas departemen tidak diperbolehkan.');
    }

    /**
     * Accept an open request (one that has no target worker set)
     */
    public function acceptOpenRequest(string $swapId, string $workerId, string $targetShiftId): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);

        // Verify it's an open request
        if ($swap->target_worker_id !== null) {
            throw new \Exception('Ini bukan open request.');
        }

        // Verify worker is not the requester
        if ($swap->requester_id === $workerId) {
            throw new \Exception('Anda tidak bisa menerima request Anda sendiri.');
        }

        // Verify status
        if ($swap->status !== 'pending') {
            throw new \Exception('Request ini sudah tidak tersedia.');
        }

        // Verify target shift exists and belongs to worker
        $targetShift = WorkerShift::where('id', $targetShiftId)
            ->where('worker_id', $workerId)
            ->first();

        if (!$targetShift) {
            throw new \Exception('Shift yang dipilih tidak valid.');
        }

        $requester = $swap->requester;
        $acceptingWorker = Worker::findOrFail($workerId);

        if (!$requester || $requester->department_id !== $acceptingWorker->department_id) {
            throw new \Exception('Open request hanya dapat diterima oleh pegawai dari departemen yang sama.');
        }

        $this->validateWorkerNoAbsenceConflicts(
            $workerId,
            $this->getSwapDates($swap),
            'menerima open request tukar shift'
        );

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;

            // Update swap request with target worker info and move to approval queue
            $swap->target_worker_id = $workerId;
            $swap->target_shift_id = $targetShiftId;
            $swap->status = 'awaiting_approval';
            $swap->requires_manager_approval = true;
            $swap->save();

            // Audit log
            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'open_request_accepted',
                newStatus: $swap->status,
                userId: Auth::id(),
                oldStatus: $oldStatus,
                notes: 'Open request accepted by another worker',
                metadata: [
                    'target_worker_id' => $workerId,
                    'target_shift_id' => $targetShiftId,
                    'requires_manager_approval' => true,
                ]
            );

            Log::info('Open shift swap request accepted', [
                'swap_id' => $swapId,
                'target_worker_id' => $workerId,
                'target_shift_id' => $targetShiftId,
                'requires_manager_approval' => true,
            ]);

            // Notify requester
            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'open_request_accepted'));
            }

            $this->notifyFinalApprovers($swap);

            DB::commit();

            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to accept open request', [
                'error' => $e->getMessage(),
                'swap_id' => $swapId,
                'worker_id' => $workerId,
            ]);
            throw $e;
        }
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

        $this->validateWorkerNoAbsenceConflicts(
            $workerId,
            $this->getSwapDates($swap),
            'menerima permintaan tukar shift'
        );

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            $swap->status = 'awaiting_approval';
            $swap->requires_manager_approval = true;

            $swap->save();

            // Audit log
            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'accepted',
                newStatus: $swap->status,
                userId: Auth::id(),
                oldStatus: $oldStatus,
                notes: 'Target worker accepted the swap request',
                metadata: [
                    'target_worker_id' => $workerId,
                    'requires_manager_approval' => true,
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

            $this->notifyFinalApprovers($swap);

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
                userId: Auth::id(),
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
        $approver = \App\Models\User::findOrFail($managerId);

        if (!$approver->hasRole('Manager') && !$approver->hasRole('HR') && !$approver->hasRole('Super Admin')) {
            throw new \Exception('Anda tidak berhak menyetujui permintaan tukar shift ini.');
        }

        // Check status
        if ($swap->status !== 'awaiting_approval') {
            throw new \Exception('Swap request tidak dalam status awaiting approval.');
        }

        if (!$swap->requires_manager_approval) {
            throw new \Exception('Swap request ini tidak memerlukan persetujuan manager/HR.');
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

            // Auto-execute after approval - create ShiftOverride records immediately
            return $this->executeSwap($swapId, $managerId);
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
        $approver = \App\Models\User::findOrFail($managerId);

        if (!$approver->hasRole('Manager') && !$approver->hasRole('HR') && !$approver->hasRole('Super Admin')) {
            throw new \Exception('Anda tidak berhak menolak permintaan tukar shift ini.');
        }

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
                userId: Auth::id(),
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
     * Execute swap (create ShiftOverride records for specific dates instead of permanently swapping)
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

            // Get the list of dates for this swap
            $dates = $this->getSwapDates($swap);

            if (empty($dates)) {
                throw new \Exception('Tidak ada tanggal yang valid untuk tukar shift.');
            }

            // Create ShiftOverride records for each date (date-specific, not permanent)
            foreach ($dates as $date) {
                if (!$date) continue;

                $dateStr = $date instanceof Carbon ? $date->toDateString() : $date;

                // Override for requester: use target's shift on this date
                ShiftOverride::updateOrCreate(
                    [
                        'worker_id' => $swap->requester_id,
                        'override_date' => $dateStr,
                    ],
                    [
                        'shift_id' => $targetShift->shift_id,
                        'reason' => 'Tukar shift dengan ' . ($swap->targetWorker->full_name ?? 'pegawai lain') . ': ' . ($swap->reason ?? ''),
                        'created_by' => $executedByUserId,
                        'shift_swap_request_id' => $swap->id,
                    ]
                );

                // Override for target: use requester's shift on this date
                if ($swap->target_worker_id) {
                    ShiftOverride::updateOrCreate(
                        [
                            'worker_id' => $swap->target_worker_id,
                            'override_date' => $dateStr,
                        ],
                        [
                            'shift_id' => $requesterShift->shift_id,
                            'reason' => 'Tukar shift dengan ' . ($swap->requester->full_name ?? 'pegawai lain') . ': ' . ($swap->reason ?? ''),
                            'created_by' => $executedByUserId,
                            'shift_swap_request_id' => $swap->id,
                        ]
                    );
                }
            }

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
                notes: 'Shift swap executed successfully (date-specific overrides created)',
                metadata: [
                    'requester_id' => $swap->requester_id,
                    'requester_original_shift' => $requesterShift->shift_id,
                    'requester_new_shift' => $targetShift->shift_id,
                    'target_id' => $swap->target_worker_id,
                    'target_original_shift' => $targetShift->shift_id,
                    'target_new_shift' => $requesterShift->shift_id,
                    'swap_dates' => $dates,
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
                'dates' => $dates,
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
     * Get the list of dates for a swap request
     */
    private function getSwapDates(ShiftSwapRequest $swap): array
    {
        $dates = [];

        switch ($swap->swap_type ?? 'single_date') {
            case 'single_date':
                if ($swap->swap_date) {
                    $dates = [Carbon::parse($swap->swap_date)->toDateString()];
                }
                break;

            case 'date_range':
                if ($swap->swap_start_date && $swap->swap_end_date) {
                    $start = Carbon::parse($swap->swap_start_date);
                    $end = Carbon::parse($swap->swap_end_date);
                    while ($start->lte($end)) {
                        $dates[] = $start->toDateString();
                        $start->addDay();
                    }
                }
                break;

            case 'recurring':
                $dates = array_filter(array_map(function ($d) {
                    return $d ? Carbon::parse($d)->toDateString() : null;
                }, $swap->swap_dates ?? []));
                break;
        }

        // Fallback: if no dates found but swap_date exists in metadata
        if (empty($dates) && !empty($swap->metadata['swap_date'])) {
            $dates = [Carbon::parse($swap->metadata['swap_date'])->toDateString()];
        }

        // Legacy fallback: some records may keep single date in swap_dates[0].
        if (empty($dates) && !empty($swap->swap_dates) && is_array($swap->swap_dates)) {
            $firstDate = $swap->swap_dates[0] ?? null;
            if ($firstDate) {
                $dates = [Carbon::parse($firstDate)->toDateString()];
            }
        }

        return $dates;
    }

    /**
     * Revert an executed swap by deleting the ShiftOverride records created for it
     */
    public function revertSwap(string $swapId, string $revertedByUserId, string $reason = ''): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::with(['requester', 'targetWorker'])
            ->findOrFail($swapId);

        if ($swap->status !== 'executed') {
            throw new \Exception('Hanya swap yang sudah dieksekusi yang dapat di-revert.');
        }

        DB::beginTransaction();
        try {
            // Delete all ShiftOverride records created by this swap
            $deletedCount = ShiftOverride::where('shift_swap_request_id', $swap->id)->delete();

            // Update swap status
            $oldStatus = $swap->status;
            $swap->status = 'reverted';
            $swap->save();

            // Audit log
            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'reverted',
                newStatus: 'reverted',
                userId: $revertedByUserId,
                oldStatus: $oldStatus,
                notes: 'Shift swap reverted - ' . ($reason ?: 'overrides removed') . " ({$deletedCount} overrides deleted)",
                metadata: [
                    'reverted_by' => $revertedByUserId,
                    'reverted_at' => Carbon::now()->toDateTimeString(),
                    'overrides_deleted' => $deletedCount,
                    'reason' => $reason,
                ]
            );

            // Send notification to both workers
            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'reverted'));
            }
            if ($swap->targetWorker && $swap->targetWorker->user) {
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'reverted'));
            }

            DB::commit();

            Log::info('Shift swap reverted successfully', [
                'swap_id' => $swapId,
                'reverted_by' => $revertedByUserId,
                'overrides_deleted' => $deletedCount,
            ]);

            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to revert shift swap', ['swap_id' => $swapId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * List pending approvals for manager
     */
    public function listPendingApprovalsForManager(string $managerId, array $filters = [])
    {
        // Get manager's department
        $manager = \App\Models\User::findOrFail($managerId);
        $worker = $manager->worker;

        // Build base query
        $query = ShiftSwapRequest::with(['requester', 'targetWorker', 'requesterShift.shift', 'targetShift.shift']);

        // Super Admin and HR can see all data.
        // Manager is scoped to their department.
        $canViewAllDepartments = $manager->hasRole('Super Admin') || $manager->hasRole('HR');

        if ($worker && !$canViewAllDepartments) {
            $query->where(function($q) use ($worker) {
                $q->whereHas('requester', function($query) use ($worker) {
                    $query->where('department_id', $worker->department_id);
                })
                ->orWhereHas('targetWorker', function($query) use ($worker) {
                    $query->where('department_id', $worker->department_id);
                });
            });
        }

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            // Explicit status filter selected
            $query->where('status', $filters['status']);
        } elseif (!isset($filters['status'])) {
            // No filter applied, show only items waiting final approval (default view)
            $query->where('status', 'awaiting_approval')
                  ->where('requires_manager_approval', true);
        }
        // If status is empty string '', show all statuses

        if (!empty($filters['requester_id'])) {
            $query->where('requester_id', $filters['requester_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('requested_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('requested_at', '<=', $filters['date_to']);
        }

        $perPage = $filters['per_page'] ?? 15;
        $result = $query->orderBy('requested_at', 'desc')->paginate($perPage);

        // Enrich paginated items with effective shifts
        $this->enrichWithEffectiveShifts($result->getCollection());

        return $result;
    }

    /**
     * Validate that the worker does not already have an active (non-cancelled, non-rejected) swap
     * on the same date(s). Prevents double-swapping on the same day.
     */
    protected function validateNoDuplicateSwapDate(Worker $worker, ShiftSwapRequestDTO $dto): void
    {
        // Collect all swap dates from the DTO
        $swapDates = [];
        switch ($dto->swap_type ?? 'single_date') {
            case 'single_date':
                $singleDate = $this->resolveSingleSwapDate($dto);
                if ($singleDate) {
                    $swapDates[] = Carbon::parse($singleDate)->format('Y-m-d');
                }
                break;
            case 'date_range':
                if ($dto->swap_start_date && $dto->swap_end_date) {
                    $start = Carbon::parse($dto->swap_start_date);
                    $end = Carbon::parse($dto->swap_end_date);
                    while ($start->lte($end)) {
                        $swapDates[] = $start->format('Y-m-d');
                        $start->addDay();
                    }
                }
                break;
            case 'recurring':
                foreach (($dto->swap_dates ?? []) as $d) {
                    if ($d) $swapDates[] = Carbon::parse($d)->format('Y-m-d');
                }
                break;
        }

        if (empty($swapDates)) return;

        // Active statuses that count as "occupied" swap dates
        $activeStatuses = ['pending', 'accepted', 'awaiting_approval', 'approved', 'executed'];

        foreach ($swapDates as $dateStr) {
            $formattedDate = Carbon::parse($dateStr)->format('d M Y');

            // Check as requester
            $existingAsRequester = ShiftSwapRequest::where('requester_id', $worker->id)
                ->whereIn('status', $activeStatuses)
                ->where(function ($q) use ($dateStr) {
                    $q->where(function ($q2) use ($dateStr) {
                        $q2->where('swap_type', 'single_date')
                           ->whereDate('swap_start_date', $dateStr);
                    })->orWhere(function ($q2) use ($dateStr) {
                        $q2->where('swap_type', 'date_range')
                           ->whereDate('swap_start_date', '<=', $dateStr)
                           ->whereDate('swap_end_date', '>=', $dateStr);
                    })->orWhere(function ($q2) use ($dateStr) {
                        $q2->where('swap_type', 'recurring')
                           ->whereJsonContains('swap_dates', $dateStr);
                    });
                })
                ->exists();

            if ($existingAsRequester) {
                throw new \Exception("Anda sudah memiliki tukar shift aktif pada tanggal {$formattedDate}. Batalkan terlebih dahulu jika ingin tukar ulang.");
            }

            // Check as target
            $existingAsTarget = ShiftSwapRequest::where('target_worker_id', $worker->id)
                ->whereIn('status', $activeStatuses)
                ->where(function ($q) use ($dateStr) {
                    $q->where(function ($q2) use ($dateStr) {
                        $q2->where('swap_type', 'single_date')
                           ->whereDate('swap_start_date', $dateStr);
                    })->orWhere(function ($q2) use ($dateStr) {
                        $q2->where('swap_type', 'date_range')
                           ->whereDate('swap_start_date', '<=', $dateStr)
                           ->whereDate('swap_end_date', '>=', $dateStr);
                    })->orWhere(function ($q2) use ($dateStr) {
                        $q2->where('swap_type', 'recurring')
                           ->whereJsonContains('swap_dates', $dateStr);
                    });
                })
                ->exists();

            if ($existingAsTarget) {
                throw new \Exception("Anda sudah menjadi target tukar shift pada tanggal {$formattedDate}. Tidak dapat mengajukan tukar shift lagi pada tanggal tersebut.");
            }
        }
    }

    /**
     * Prevent creating swap requests on dates that already have pending/approved leave or business trip.
     */
    protected function validateNoLeaveOrBusinessTripConflict(Worker $worker, ShiftSwapRequestDTO $dto, string $action): void
    {
        $swapDates = $this->extractSwapDatesFromDto($dto);
        $this->validateWorkerNoAbsenceConflicts($worker->id, $swapDates, $action);
    }

    /**
     * Validate worker has no pending/approved leave or business trip on the given dates.
     */
    protected function validateWorkerNoAbsenceConflicts(string $workerId, array $dateStrings, string $action): void
    {
        if (empty($dateStrings)) {
            return;
        }

        foreach (array_unique($dateStrings) as $dateStr) {
            $formattedDate = Carbon::parse($dateStr)->format('d M Y');

            $hasLeave = LeaveRequest::where('worker_id', $workerId)
                ->whereIn('status', ['pending', 'approved'])
                ->whereDate('start_date', '<=', $dateStr)
                ->whereDate('end_date', '>=', $dateStr)
                ->exists();

            if ($hasLeave) {
                throw new \Exception("Tidak dapat {$action} pada tanggal {$formattedDate} karena Anda memiliki pengajuan cuti pending/disetujui pada tanggal tersebut.");
            }

            $hasBusinessTrip = BusinessTrip::where('worker_id', $workerId)
                ->whereIn('status', ['pending', 'approved'])
                ->whereDate('start_date', '<=', $dateStr)
                ->whereDate('end_date', '>=', $dateStr)
                ->exists();

            if ($hasBusinessTrip) {
                throw new \Exception("Tidak dapat {$action} pada tanggal {$formattedDate} karena Anda memiliki pengajuan perjalanan dinas pending/disetujui pada tanggal tersebut.");
            }
        }
    }

    /**
     * Normalize swap dates from DTO to YYYY-MM-DD list.
     */
    private function extractSwapDatesFromDto(ShiftSwapRequestDTO $dto): array
    {
        $swapDates = [];

        switch ($dto->swap_type ?? 'single_date') {
            case 'single_date':
                $singleDate = $this->resolveSingleSwapDate($dto);
                if ($singleDate) {
                    $swapDates[] = Carbon::parse($singleDate)->format('Y-m-d');
                }
                break;

            case 'date_range':
                if ($dto->swap_start_date && $dto->swap_end_date) {
                    $start = Carbon::parse($dto->swap_start_date);
                    $end = Carbon::parse($dto->swap_end_date);

                    while ($start->lte($end)) {
                        $swapDates[] = $start->format('Y-m-d');
                        $start->addDay();
                    }
                }
                break;

            case 'recurring':
                foreach (($dto->swap_dates ?? []) as $date) {
                    if ($date) {
                        $swapDates[] = Carbon::parse($date)->format('Y-m-d');
                    }
                }
                break;
        }

        return $swapDates;
    }

    /**
     * Notify authorized final approvers (Manager departemen, HR, Super Admin).
     */
    protected function notifyFinalApprovers(ShiftSwapRequest $swap): void
    {
        $swap->loadMissing(['requester.department']);
        $departmentId = $swap->requester?->department_id;

        $approvers = \App\Models\User::query()
            ->where(function ($query) use ($departmentId) {
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->whereIn('name', ['HR', 'Super Admin']);
                });

                if ($departmentId) {
                    $query->orWhere(function ($managerQuery) use ($departmentId) {
                        $managerQuery->whereHas('roles', function ($roleQuery) {
                            $roleQuery->where('name', 'Manager');
                        })->whereHas('worker', function ($workerQuery) use ($departmentId) {
                            $workerQuery->where('department_id', $departmentId);
                        });
                    });
                }
            })
            ->get()
            ->unique('id');

        foreach ($approvers as $approver) {
            $approver->notify(new ShiftSwapNotification($swap, 'manager_approval_needed'));
        }
    }

    /**
     * Validate swap dates based on swap type
     */
    private function validateSwapDates(ShiftSwapRequestDTO $dto): void
    {
        $now = Carbon::now();
        $minHours = 48; // Uniform requirement: 48 hours (2 days) for all departments

        switch ($dto->swap_type) {
            case 'single_date':
                $singleDate = $this->resolveSingleSwapDate($dto);
                if (!$singleDate) {
                    throw new \Exception('Tanggal tukar shift harus diisi.');
                }
                $swapDateTime = Carbon::parse($singleDate)->startOfDay();
                $hoursUntilSwap = $now->diffInHours($swapDateTime, false);

                if ($hoursUntilSwap < $minHours) {
                    throw new \Exception("Swap request harus diajukan minimal {$minHours} jam (2 hari) sebelum shift dimulai. Saat ini hanya {$hoursUntilSwap} jam.");
                }
                break;

            case 'date_range':
                if (!$dto->swap_start_date || !$dto->swap_end_date) {
                    throw new \Exception('Tanggal mulai dan akhir harus diisi untuk rentang tanggal.');
                }

                $startDate = Carbon::parse($dto->swap_start_date)->startOfDay();
                $endDate = Carbon::parse($dto->swap_end_date)->endOfDay();

                if ($endDate->lte($startDate)) {
                    throw new \Exception('Tanggal akhir harus setelah tanggal mulai.');
                }

                $hoursUntilSwap = $now->diffInHours($startDate, false);
                if ($hoursUntilSwap < $minHours) {
                    throw new \Exception("Swap request harus diajukan minimal {$minHours} jam (2 hari) sebelum shift dimulai. Saat ini hanya {$hoursUntilSwap} jam.");
                }

                // Check maximum range (e.g., 30 days)
                $daysDiff = $startDate->diffInDays($endDate);
                if ($daysDiff > 30) {
                    throw new \Exception('Rentang tanggal tidak boleh lebih dari 30 hari.');
                }
                break;

            case 'recurring':
                if (!$dto->swap_dates || !is_array($dto->swap_dates) || empty($dto->swap_dates)) {
                    throw new \Exception('Minimal satu tanggal harus dipilih untuk tukar shift berulang.');
                }

                foreach ($dto->swap_dates as $date) {
                    if (!$date) continue;

                    $swapDateTime = Carbon::parse($date)->startOfDay();
                    $hoursUntilSwap = $now->diffInHours($swapDateTime, false);

                    if ($hoursUntilSwap < $minHours) {
                        $formattedDate = $swapDateTime->format('d M Y');
                        throw new \Exception("Swap request untuk tanggal {$formattedDate} harus diajukan minimal {$minHours} jam (2 hari) sebelumnya.");
                    }
                }

                // Check maximum number of dates
                if (count(array_filter($dto->swap_dates)) > 10) {
                    throw new \Exception('Maksimal 10 tanggal untuk tukar shift berulang.');
                }
                break;

            default:
                throw new \Exception('Jenis tukar shift tidak valid.');
        }
    }

    private function resolveSingleSwapDate(ShiftSwapRequestDTO $dto): ?string
    {
        return $dto->swap_date ?? $dto->swap_start_date ?? null;
    }

    /**
     * Create shift overrides when swap is executed (legacy method, now handled by executeSwap directly)
     */
    public function createShiftOverrides(ShiftSwapRequest $swap): void
    {
        $dates = $this->getSwapDates($swap);

        foreach ($dates as $date) {
            if (!$date) continue;

            $dateStr = $date instanceof Carbon ? $date->toDateString() : $date;

            // Override for requester: use target's shift on this date
            ShiftOverride::updateOrCreate(
                [
                    'worker_id' => $swap->requester_id,
                    'override_date' => $dateStr,
                ],
                [
                    'shift_id' => $swap->target_shift_id,
                    'reason' => "Tukar shift: {$swap->reason}",
                    'created_by' => $swap->executed_by ?? $swap->requester_id,
                    'shift_swap_request_id' => $swap->id,
                ]
            );

            // Reverse override for target worker
            if ($swap->target_worker_id && $swap->requester_shift_id) {
                ShiftOverride::updateOrCreate(
                    [
                        'worker_id' => $swap->target_worker_id,
                        'override_date' => $dateStr,
                    ],
                    [
                        'shift_id' => $swap->requester_shift_id,
                        'reason' => "Tukar shift (reverse): {$swap->reason}",
                        'created_by' => $swap->executed_by ?? $swap->requester_id,
                        'shift_swap_request_id' => $swap->id,
                    ]
                );
            }
        }
    }
}

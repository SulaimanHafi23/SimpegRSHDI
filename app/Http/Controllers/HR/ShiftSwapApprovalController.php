<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\ShiftOverride;
use App\Models\ShiftSwapAuditLog;
use App\Models\ShiftSwapRequest;
use App\Models\User;
use App\Notifications\ShiftSwapNotification;
use App\Traits\DepartmentFilterable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftSwapApprovalController extends Controller
{
    use DepartmentFilterable;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:shift-swap.approve');
    }

    /**
     * List manager-verified shift swaps awaiting HR approval
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isHR = $user->hasRole(['HR', 'hr']) && !$user->hasRole(['Admin', 'Super Admin', 'admin', 'super admin', 'superadmin']);
        $isAdmin = $user->hasRole(['Admin', 'Super Admin', 'admin', 'super admin', 'superadmin']);
        $isManager = $user->hasRole(['Manager', 'manager']) && !$isHR && !$isAdmin;

        $defaultStatus = 'all';

        $status = $request->input('status');
        
        if ($status === null && $request->has('original_status')) {
            $status = $request->input('original_status');
        }
        
        if ($status === null) {
            if ($request->exists('status')) {
                $status = 'all'; 
            } else {
                $status = $defaultStatus;
            }
        }

        $displayStatus = $status;
        
        if ($status === 'all' || $status === '') {
            $status = null;
            $displayStatus = 'all';
        }

        // Role-based status constraints
        if ($isHR && !$isAdmin) {
            if ($status === 'pending' || $status === 'awaiting_approval') {
                $status = 'manager_verified';
                $displayStatus = 'manager_verified';
            }
        }

        try {
            $query = ShiftSwapRequest::with([
                'requester.user',
                'requester.department',
                'targetWorker.user',
                'targetWorker.department',
                'requesterShift.shift',
                'targetShift.shift',
                'manager',
            ]);

            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            } elseif ($isHR && !$isAdmin) {
                $query->whereNotIn('status', ['pending', 'awaiting_approval']);
            }

            // Apply other filters
            if ($request->filled('requester_id')) {
                $query->where('requester_id', $request->input('requester_id'));
            }

            if ($request->filled('date_from')) {
                $query->whereDate('requested_at', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('requested_at', '<=', $request->input('date_to'));
            }

            $perPage = $request->input('per_page', 15);
            $items = $query->orderBy('requested_at', 'desc')->paginate($perPage);

            // Get statistics
            $statsQuery = ShiftSwapRequest::query();
            if ($isHR && !$isAdmin) {
                $statsQuery->whereNotIn('status', ['pending', 'awaiting_approval']);
            }

            $statistics = [
                'manager_verified' => (clone $statsQuery)->where('status', 'manager_verified')->count(),
                'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
                'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
                'total' => (clone $statsQuery)->count(),
            ];

            $filters = [
                'status' => $status,
                'original_status' => $displayStatus,
            ];

            return view('hr.shift-swap-approvals.index', compact('items', 'statistics', 'filters'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengambil data: ' . $e->getMessage());
        }
    }

    /**
     * Show detail of manager-verified swap request
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $isHR = $user->hasRole(['HR', 'hr']) && !$user->hasRole(['Admin', 'Super Admin', 'admin', 'super admin', 'superadmin']);
        $isAdmin = $user->hasRole(['Admin', 'Super Admin', 'admin', 'super admin', 'superadmin']);
        $isManager = $user->hasRole(['Manager', 'manager']) && !$isHR && !$isAdmin;

        $swap = ShiftSwapRequest::with([
            'requester.user',
            'requester.department',
            'targetWorker.user',
            'targetWorker.department',
            'requesterShift.shift',
            'targetShift.shift',
            'manager',
            'approvedBy',
            'auditLogs.user',
        ])->findOrFail($id);

        // Security check for HR: should not see pending/awaiting_approval if not admin
        if ($isHR && !$isAdmin) {
            if ($swap->status === 'pending' || $swap->status === 'awaiting_approval') {
                abort(403, 'Unauthorized - permintaan belum diverifikasi manager');
            }
        }

        return view('hr.shift-swap-approvals.show', compact('swap', 'isHR', 'isAdmin', 'isManager'));
    }

    /**
     * Approve manager-verified swap request (HR only - second stage)
     */
    public function approve(Request $request, string $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        if ($user->hasRole('Super Admin')) {
            return back()->with('error', 'Super Admin hanya dapat menghapus data, tidak dapat memberikan persetujuan.');
        }

        try {
            $this->approveByHR($id, $user->id, $request->input('notes'));
            return redirect()->route('hr.shift-swap-approvals.index')
                ->with('success', 'Permintaan tukar shift berhasil disetujui oleh HR.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Reject manager-verified swap request
     */
    public function reject(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        if ($user->hasRole('Super Admin')) {
            return back()->with('error', 'Super Admin hanya dapat menghapus data, tidak dapat menolak pengajuan.');
        }

        try {
            $this->rejectByHR($id, $user->id, $request->input('reason'));
            return redirect()->route('hr.shift-swap-approvals.index')
                ->with('success', 'Permintaan tukar shift ditolak oleh HR.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Approve by HR (second stage of approval)
     */
    private function approveByHR(string $swapId, string $hrId, ?string $notes = null): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);
        $approver = User::findOrFail($hrId);

        if (!$approver->can('shift-swap.approve')) {
            throw new \Exception('Anda tidak berhak menyetujui permintaan tukar shift ini.');
        }

        if ($swap->status !== 'manager_verified') {
            throw new \Exception('Hanya permintaan tukar shift yang sudah diverifikasi manager yang dapat disetujui HR.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            $swap->status = 'approved';
            $swap->approved_by = $hrId;
            $swap->approved_at = Carbon::now();

            if ($notes) {
                $metadata = $swap->metadata ?? [];
                $metadata['hr_approval_notes'] = $notes;
                $swap->metadata = $metadata;
            }

            $swap->save();

            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'approved_by_hr',
                newStatus: 'approved',
                userId: $hrId,
                oldStatus: $oldStatus,
                notes: $notes ?? 'HR approved the swap request',
                metadata: [
                    'hr_id' => $hrId,
                    'approved_at' => Carbon::now()->toDateTimeString(),
                ]
            );

            Log::info('Shift swap approved by HR', [
                'swap_id' => $swapId,
                'hr_id' => $hrId,
                'notes' => $notes,
            ]);

            \Log::info('Attempting to notify users about HR approval', ['swap_id' => $swapId]);
            if ($swap->requester && $swap->requester->user) {
                \Log::info('Notifying requester', ['user_id' => $swap->requester->user->id]);
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'approved_by_hr', $notes));
            }
            if ($swap->targetWorker && $swap->targetWorker->user) {
                \Log::info('Notifying target worker', ['user_id' => $swap->targetWorker->user->id]);
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'approved_by_hr', $notes));
            }
            \Log::info('Notifications triggered for HR approval');

            DB::commit();

            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve shift swap by HR', ['swap_id' => $swapId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Reject by HR
     */
    private function rejectByHR(string $swapId, string $hrId, string $reason): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);
        $approver = User::findOrFail($hrId);

        if (!$approver->can('shift-swap.approve')) {
            throw new \Exception('Anda tidak berhak menolak permintaan tukar shift ini.');
        }

        if ($swap->status !== 'manager_verified') {
            throw new \Exception('Hanya permintaan tukar shift yang sudah diverifikasi manager yang dapat ditolak HR.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            $swap->status = 'rejected';
            $swap->approved_by = $hrId;
            $swap->approved_at = Carbon::now();

            $metadata = $swap->metadata ?? [];
            $metadata['hr_rejection_reason'] = $reason;
            $swap->metadata = $metadata;

            $swap->save();

            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'rejected_by_hr',
                newStatus: 'rejected',
                userId: $hrId,
                oldStatus: $oldStatus,
                notes: $reason,
                metadata: [
                    'hr_id' => $hrId,
                    'rejected_at' => Carbon::now()->toDateTimeString(),
                ]
            );

            Log::info('Shift swap rejected by HR', [
                'swap_id' => $swapId,
                'hr_id' => $hrId,
                'reason' => $reason,
            ]);

            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'rejected_by_hr', $reason));
            }
            if ($swap->targetWorker && $swap->targetWorker->user) {
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'rejected_by_hr', $reason));
            }

            DB::commit();

            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject shift swap by HR', ['swap_id' => $swapId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    /**
     * Execute the shift swap by creating overrides (auto-called by approve)
     */
    private function executeSwap(string $swapId, string $executedByUserId): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::with(['requester', 'targetWorker', 'requesterShift', 'targetShift'])
            ->findOrFail($swapId);

        // For HR approval flow, status is 'approved' right before execution
        if (!in_array($swap->status, ['approved', 'accepted', 'manager_verified'])) {
            throw new \Exception('Swap hanya dapat dieksekusi jika sudah approved/accepted.');
        }

        if (!$swap->target_shift_id) {
            throw new \Exception('Target shift tidak ditentukan, tidak dapat mengeksekusi swap.');
        }

        DB::beginTransaction();
        try {
            $requesterShift = $swap->requesterShift;
            $targetShift = $swap->targetShift;

            $dates = $this->getSwapDates($swap);
            if (empty($dates)) {
                throw new \Exception('Tidak ada tanggal yang valid untuk tukar shift.');
            }

            foreach ($dates as $date) {
                if (!$date) {
                    continue;
                }

                $dateStr = $date instanceof Carbon ? $date->toDateString() : $date;

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

            $oldStatus = $swap->status;
            $swap->status = 'executed';
            $swap->executed_at = Carbon::now();
            $swap->executed_by = $executedByUserId;
            $swap->save();

            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'executed',
                newStatus: 'executed',
                userId: $executedByUserId,
                oldStatus: $oldStatus,
                notes: 'Shift swap executed successfully after HR approval',
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

            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'executed'));
            }
            if ($swap->targetWorker && $swap->targetWorker->user) {
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'executed'));
            }

            DB::commit();

            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to execute shift swap in HR controller', ['swap_id' => $swapId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

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

        if (empty($dates) && !empty($swap->metadata['swap_date'])) {
            $dates = [Carbon::parse($swap->metadata['swap_date'])->toDateString()];
        }

        return $dates;
    }
}

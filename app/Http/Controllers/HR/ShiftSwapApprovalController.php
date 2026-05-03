<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
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

        try {
            $query = ShiftSwapRequest::with([
                'requester.user',
                'requester.department',
                'targetWorker.user',
                'targetWorker.department',
                'requesterShift.shift',
                'targetShift.shift',
                'manager',
            ])->where('status', 'manager_verified');

            // Apply filters
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
            $statistics = [
                'pending_approval' => ShiftSwapRequest::where('status', 'manager_verified')->count(),
                'approved' => ShiftSwapRequest::where('status', 'approved')->count(),
                'rejected' => ShiftSwapRequest::where('status', 'rejected')->count(),
            ];

            return view('hr.shift-swap-approvals.index', compact('items', 'statistics'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengambil data: ' . $e->getMessage());
        }
    }

    /**
     * Show detail of manager-verified swap request
     */
    public function show(string $id)
    {
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

        if ($swap->status !== 'manager_verified') {
            abort(403, 'Unauthorized - permintaan belum diverifikasi manager');
        }

        return view('hr.shift-swap-approvals.show', compact('swap'));
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

            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'approved_by_hr', $notes));
            }
            if ($swap->targetWorker && $swap->targetWorker->user) {
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'approved_by_hr', $notes));
            }

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
}

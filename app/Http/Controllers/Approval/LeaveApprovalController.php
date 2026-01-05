<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\Leave\LeaveRequestService;
use Illuminate\Http\Request;

class LeaveApprovalController extends Controller
{
    public function __construct(
        private readonly LeaveRequestService $leaveService
    ) {
        $this->middleware('auth');
        $this->middleware('role:Manager|HR');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $filters = [
            'status' => $request->input('status', 'pending'),
            'per_page' => 20,
        ];

        // If user is Manager, only show requests from their department
        if ($user->hasRole('Manager') && $user->worker) {
            $filters['department_id'] = $user->worker->department_id;
        }

        $leaveRequests = $this->leaveService->getAll($filters);

        return view('approvals.leaves.index', compact('leaveRequests'));
    }

    public function show(string $id)
    {
        $leaveRequest = LeaveRequest::with(['worker.department', 'worker.position', 'leaveType', 'approver'])
            ->findOrFail($id);

        // Check if user has permission to view this request
        $user = auth()->user();
        if ($user->hasRole('Manager') && $user->worker) {
            if ($leaveRequest->worker->department_id !== $user->worker->department_id) {
                abort(403, 'Unauthorized');
            }
        }

        return view('approvals.leaves.show', compact('leaveRequest'));
    }

    public function approve(Request $request, string $id)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            // Check permission
            $user = auth()->user();
            if ($user->hasRole('Manager') && $user->worker) {
                if ($leaveRequest->worker->department_id !== $user->worker->department_id) {
                    return back()->with('error', 'Anda tidak memiliki akses untuk menyetujui pengajuan ini.');
                }
            }

            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $request->input('approval_notes'),
            ]);

            return redirect()
                ->route('approvals.leaves.index')
                ->with('success', 'Pengajuan cuti berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            // Check permission
            $user = auth()->user();
            if ($user->hasRole('Manager') && $user->worker) {
                if ($leaveRequest->worker->department_id !== $user->worker->department_id) {
                    return back()->with('error', 'Anda tidak memiliki akses untuk menolak pengajuan ini.');
                }
            }

            $leaveRequest->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $request->input('rejection_reason'),
            ]);

            return redirect()
                ->route('approvals.leaves.index')
                ->with('success', 'Pengajuan cuti telah ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}

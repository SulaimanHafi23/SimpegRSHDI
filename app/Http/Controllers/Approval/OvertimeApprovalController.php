<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use App\Services\Overtime\OvertimeRequestService;
use Illuminate\Http\Request;

class OvertimeApprovalController extends Controller
{
    public function __construct(
        private readonly OvertimeRequestService $overtimeService
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

        $overtimeRequests = $this->overtimeService->getAll($filters);

        return view('approvals.overtimes.index', compact('overtimeRequests'));
    }

    public function show(string $id)
    {
        $overtimeRequest = OvertimeRequest::with(['worker.department', 'worker.position', 'approver'])
            ->findOrFail($id);

        // Check if user has permission to view this request
        $user = auth()->user();
        if ($user->hasRole('Manager') && $user->worker) {
            if ($overtimeRequest->worker->department_id !== $user->worker->department_id) {
                abort(403, 'Unauthorized');
            }
        }

        return view('approvals.overtimes.show', compact('overtimeRequest'));
    }

    public function approve(Request $request, string $id)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $overtimeRequest = OvertimeRequest::findOrFail($id);

            // Check permission
            $user = auth()->user();
            if ($user->hasRole('Manager') && $user->worker) {
                if ($overtimeRequest->worker->department_id !== $user->worker->department_id) {
                    return back()->with('error', 'Anda tidak memiliki akses untuk menyetujui pengajuan ini.');
                }
            }

            $overtimeRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $request->input('approval_notes'),
            ]);

            return redirect()
                ->route('approvals.overtimes.index')
                ->with('success', 'Pengajuan lembur berhasil disetujui.');
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
            $overtimeRequest = OvertimeRequest::findOrFail($id);

            // Check permission
            $user = auth()->user();
            if ($user->hasRole('Manager') && $user->worker) {
                if ($overtimeRequest->worker->department_id !== $user->worker->department_id) {
                    return back()->with('error', 'Anda tidak memiliki akses untuk menolak pengajuan ini.');
                }
            }

            $overtimeRequest->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $request->input('rejection_reason'),
            ]);

            return redirect()
                ->route('approvals.overtimes.index')
                ->with('success', 'Pengajuan lembur telah ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}

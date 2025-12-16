<?php

// filepath: app/Http/Controllers/LeaveRequestController.php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Services\Leave\LeaveRequestService;
use App\Services\Worker\WorkerService;
use App\Services\Master\LeaveTypeService;
use App\Http\Requests\Leave\LeaveRequestRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected LeaveRequestService $leaveRequestService,
        protected WorkerService $workerService,
        protected LeaveTypeService $leaveTypeService
    ) {
        $this->middleware('auth');
        // Permission check dilakukan di blade dengan @can
    }

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->status,
            'worker_id' => $request->worker_id,
            'leave_type_id' => $request->leave_type_id,
            'leave_type' => $request->leave_type,
            'month' => $request->month,
            'year' => $request->year,
            'per_page' => $request->per_page ?? 15,
        ];

        $leaveRequests = $this->leaveRequestService->getAll($filters);
        $workers = $this->workerService->getAllActive();
        $leaveTypes = $this->leaveTypeService->getAllActive();
        
        // Statistics
        $statistics = [
            'total' => $this->leaveRequestService->getAll(['per_page' => 9999])->total(),
            'pending' => $this->leaveRequestService->getAll(['status' => 'pending', 'per_page' => 9999])->total(),
            'approved' => $this->leaveRequestService->getAll(['status' => 'approved', 'per_page' => 9999])->total(),
            'rejected' => $this->leaveRequestService->getAll(['status' => 'rejected', 'per_page' => 9999])->total(),
            'cancelled' => $this->leaveRequestService->getAll(['status' => 'cancelled', 'per_page' => 9999])->total(),
        ];

        return view('admin.leave.index', compact('leaveRequests', 'workers', 'leaveTypes', 'statistics', 'filters'));
    }

    public function create()
    {
        $workers = $this->workerService->getAllActive();
        $leaveTypes = $this->leaveTypeService->getAllActive();

        return view('admin.leave.create', compact('workers', 'leaveTypes'));
    }

    public function store(LeaveRequestRequest $request)
    {
        try {
            $this->leaveRequestService->create($request->validated());

            return redirect()
                ->route('admin.leave.index')
                ->with('success', 'Permohonan cuti berhasil diajukan');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $leave = $this->leaveRequestService->getById($id);

        return view('admin.leave.show', compact('leave'));
    }

    public function edit(string $id)
    {
        $leave = $this->leaveRequestService->getById($id);
        $workers = $this->workerService->getAllActive();
        $leaveTypes = $this->leaveTypeService->getAllActive();

        return view('admin.leave.edit', compact('leave', 'workers', 'leaveTypes'));
    }

    public function update(LeaveRequestRequest $request, string $id)
    {
        try {
            $this->leaveRequestService->update($id, $request->validated());

            return redirect()
                ->route('admin.leave.show', $id)
                ->with('success', 'Permohonan cuti berhasil diperbarui');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->leaveRequestService->delete($id);

            return redirect()
                ->route('admin.leave.index')
                ->with('success', 'Permohonan cuti berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(string $id)
    {
        try {
            $this->leaveRequestService->approve($id, Auth::id());

            return back()->with('success', 'Permohonan cuti berhasil disetujui');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, string $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        try {
            $this->leaveRequestService->reject($id, Auth::id(), $validated['rejection_reason']);

            return back()->with('success', 'Permohonan cuti berhasil ditolak');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(string $id)
    {
        try {
            $this->leaveRequestService->cancel($id);

            return back()->with('success', 'Permohonan cuti berhasil dibatalkan');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function workerLeaveBalance(string $workerId)
    {
        $worker = $this->workerService->getById($workerId);
        $leaveTypes = $this->leaveTypeService->getAllActive();
        
        $balances = [];
        foreach ($leaveTypes as $leaveType) {
            $balances[$leaveType->id] = $this->leaveRequestService->getLeaveBalance(
                $workerId,
                $leaveType->id,
                now()->year
            );
        }

        return view('admin.leave.balance', compact('worker', 'leaveTypes', 'balances'));
    }
}

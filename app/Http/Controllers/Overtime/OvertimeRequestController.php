<?php

namespace App\Http\Controllers\Overtime;

use App\Http\Controllers\Controller;
use App\Services\Overtime\OvertimeRequestService;
use App\Services\Worker\WorkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeRequestController extends Controller
{
    public function __construct(
        protected OvertimeRequestService $overtimeRequestService,
        protected WorkerService $workerService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->status,
            'worker_id' => $request->worker_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'month' => $request->month,
            'year' => $request->year,
            'per_page' => $request->per_page ?? 15,
        ];

        $overtimes = $this->overtimeRequestService->getAll($filters);
        $workers = $this->workerService->getAllActive();
        
        // Statistics
        $statistics = [
            'total' => $this->overtimeRequestService->getAll(['per_page' => 9999])->total(),
            'pending' => $this->overtimeRequestService->getAll(['status' => 'pending', 'per_page' => 9999])->total(),
            'approved' => $this->overtimeRequestService->getAll(['status' => 'approved', 'per_page' => 9999])->total(),
            'rejected' => $this->overtimeRequestService->getAll(['status' => 'rejected', 'per_page' => 9999])->total(),
            'total_hours' => 0, // TODO: Calculate from database
        ];

        return view('admin.overtime.index', compact('overtimes', 'workers', 'statistics', 'filters'));
    }

    public function create()
    {
        $workers = $this->workerService->getAllActive();

        return view('admin.overtime.create', compact('workers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|uuid|exists:workers,id',
            'overtime_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string',
        ]);

        try {
            $this->overtimeRequestService->create($validated);

            return redirect()
                ->route('admin.overtime.index')
                ->with('success', 'Permohonan lembur berhasil diajukan');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $overtime = $this->overtimeRequestService->getById($id);

        return view('admin.overtime.show', compact('overtime'));
    }

    public function edit(string $id)
    {
        $overtime = $this->overtimeRequestService->getById($id);
        $workers = $this->workerService->getAllActive();

        return view('admin.overtime.edit', compact('overtime', 'workers'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'overtime_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string',
        ]);

        try {
            $this->overtimeRequestService->update($id, $validated);

            return redirect()
                ->route('admin.overtime.show', $id)
                ->with('success', 'Permohonan lembur berhasil diperbarui');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->overtimeRequestService->delete($id);

            return redirect()
                ->route('admin.overtime.index')
                ->with('success', 'Permohonan lembur berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(string $id)
    {
        try {
            $this->overtimeRequestService->approve($id, Auth::id());

            return back()->with('success', 'Permohonan lembur berhasil disetujui');
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
            $this->overtimeRequestService->reject($id, Auth::id(), $validated['rejection_reason']);

            return back()->with('success', 'Permohonan lembur berhasil ditolak');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'uuid|exists:overtime_requests,id',
        ]);

        try {
            $this->overtimeRequestService->bulkApprove($validated['ids'], Auth::id());

            return back()->with('success', 'Permohonan lembur berhasil disetujui secara massal');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

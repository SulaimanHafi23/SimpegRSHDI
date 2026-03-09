<?php

// filepath: app/Http/Controllers/LeaveRequestController.php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Services\Leave\LeaveRequestService;
use App\Services\Worker\WorkerService;
use App\Services\Master\LeaveTypeService;
use App\Traits\DepartmentFilterable;
use App\Http\Requests\Leave\LeaveRequestRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    use DepartmentFilterable;

    public function __construct(
        protected LeaveRequestService $leaveRequestService,
        protected WorkerService $workerService,
        protected LeaveTypeService $leaveTypeService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:leave.manage');
    }

    public function index(Request $request)
    {
        $departmentId = $this->getManagerDepartmentFilter();

        $month = $request->month;
        $year = $request->year;
        $dateFrom = null;
        $dateTo = null;
        if ($month || $year) {
            $year = $year ?: now()->year;
            if ($month) {
                $dateFrom = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $dateTo = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
            } else {
                $dateFrom = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear()->format('Y-m-d');
                $dateTo = \Carbon\Carbon::createFromDate($year, 1, 1)->endOfYear()->format('Y-m-d');
            }
        }

        $filters = [
            'status' => $request->status,
            'worker_id' => $request->worker_id,
            'leave_type_id' => $request->leave_type_id,
            'leave_type' => $request->leave_type,
            'month' => $month,
            'year' => $year,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'department_id' => $departmentId,
            'per_page' => $request->per_page ?? 15,
        ];

        $leaveRequests = $this->leaveRequestService->getAll($filters);

        // Get workers from user's department if Manager
        if ($departmentId) {
            $workers = $this->workerService->getByDepartment($departmentId);
        } else {
            $workers = $this->workerService->getAllActive();
        }

        $leaveTypes = $this->leaveTypeService->getAllActive();

        // Statistics - single grouped count query instead of 5 paginated queries
        $statCounts = \App\Models\LeaveRequest::when($departmentId, function ($q) use ($departmentId) {
                $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId));
            })
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $statistics = [
            'total' => $statCounts->sum(),
            'pending' => $statCounts->get('pending', 0),
            'approved' => $statCounts->get('approved', 0),
            'rejected' => $statCounts->get('rejected', 0),
            'cancelled' => $statCounts->get('cancelled', 0),
        ];

        // Rename for view compatibility
        $leaves = $leaveRequests;

        return view('admin.leave.index', compact('leaves', 'leaveRequests', 'workers', 'leaveTypes', 'statistics', 'filters'));
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
        $leaveRequest = $this->leaveRequestService->getById($id);

        return view('admin.leave.show', compact('leaveRequest'));
    }

    public function edit(string $id)
    {
        $leaveRequest = $this->leaveRequestService->getById($id);
        $workers = $this->workerService->getAllActive();
        $leaveTypes = $this->leaveTypeService->getAllActive();

        return view('admin.leave.edit', compact('leaveRequest', 'workers', 'leaveTypes'));
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

    public function export(Request $request)
    {
        try {
            $format = $request->input('format', 'excel');

            $filters = [
                'worker_id' => $request->input('worker_id'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'status' => $request->input('status'),
                'leave_type_id' => $request->input('leave_type_id'),
                'department_id' => $this->getManagerDepartmentFilter(),
            ];

            $query = \App\Models\LeaveRequest::with(['worker.department', 'leaveType', 'approver']);

            if ($filters['worker_id']) {
                $query->where('worker_id', $filters['worker_id']);
            }
            if ($filters['date_from']) {
                $query->whereDate('start_date', '>=', $filters['date_from']);
            }
            if ($filters['date_to']) {
                $query->whereDate('start_date', '<=', $filters['date_to']);
            }
            if ($filters['status']) {
                $query->where('status', $filters['status']);
            }
            if ($filters['leave_type_id']) {
                $query->where('leave_type_id', $filters['leave_type_id']);
            }
            if ($filters['department_id']) {
                $query->whereHas('worker', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            }

            $leaves = $query->orderBy('start_date', 'desc')->get();

            $dateFrom = $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d F Y') : 'Semua';
            $dateTo = $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d F Y') : 'Semua';

            $filename = 'laporan-cuti-' . now()->format('Y-m-d-His');

            switch ($format) {
                case 'pdf':
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.leave-pdf', [
                        'leaves' => $leaves,
                        'dateFrom' => $dateFrom,
                        'dateTo' => $dateTo,
                        'status' => $filters['status'],
                    ]);
                    $pdf->setPaper('a4', 'landscape');
                    return $pdf->download($filename . '.pdf');

                case 'csv':
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\LeaveExport($filters),
                        $filename . '.csv',
                        \Maatwebsite\Excel\Excel::CSV
                    );

                default:
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\LeaveExport($filters),
                        $filename . '.xlsx'
                    );
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
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

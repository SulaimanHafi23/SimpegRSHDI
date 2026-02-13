<?php

namespace App\Http\Controllers\Overtime;

use App\Http\Controllers\Controller;
use App\Traits\DepartmentFilterable;
use App\Services\Overtime\OvertimeRequestService;
use App\Services\Worker\WorkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeRequestController extends Controller
{
    use DepartmentFilterable;
    public function __construct(
        protected OvertimeRequestService $overtimeRequestService,
        protected WorkerService $workerService
    ) {
        $this->middleware(['auth']);
        $this->middleware('permission:overtime.manage');
    }

    public function index(Request $request)
    {
        $departmentId = $this->getManagerDepartmentFilter();

        $month = $request->month;
        $year = $request->year;
        $dateFrom = $request->start_date;
        $dateTo = $request->end_date;
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
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'month' => $month,
            'year' => $year,
            'department_id' => $departmentId,
            'per_page' => $request->per_page ?? 15,
        ];

        $overtimes = $this->overtimeRequestService->getAll($filters);

        // Get workers from user's department if Manager
        if ($departmentId) {
            $workers = $this->workerService->getByDepartment($departmentId);
        } else {
            $workers = $this->workerService->getAllActive();
        }

        // Statistics - filter by department if Manager
        $baseFilters = $departmentId ? ['department_id' => $departmentId, 'per_page' => 9999] : ['per_page' => 9999];

        $statistics = [
            'total' => $this->overtimeRequestService->getAll($baseFilters)->total(),
            'pending' => $this->overtimeRequestService->getAll([...$baseFilters, 'status' => 'pending'])->total(),
            'approved' => $this->overtimeRequestService->getAll([...$baseFilters, 'status' => 'approved'])->total(),
            'rejected' => $this->overtimeRequestService->getAll([...$baseFilters, 'status' => 'rejected'])->total(),
            'total_hours' => \App\Models\OvertimeRequest::where('status', 'approved')
                ->when($departmentId, function($q) use ($departmentId) {
                    $q->whereHas('worker', function($w) use ($departmentId) {
                        $w->where('department_id', $departmentId);
                    });
                })
                ->sum('total_hours'),
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

    public function export(Request $request)
    {
        try {
            $format = $request->input('format', 'excel');

            $filters = [
                'worker_id' => $request->input('worker_id'),
                'date_from' => $request->input('date_from', now()->startOfMonth()->format('Y-m-d')),
                'date_to' => $request->input('date_to', now()->endOfMonth()->format('Y-m-d')),
                'status' => $request->input('status'),
            ];

            $query = \App\Models\OvertimeRequest::with(['worker.department', 'approver']);

            if ($filters['worker_id']) {
                $query->where('worker_id', $filters['worker_id']);
            }
            if ($filters['date_from']) {
                $query->whereDate('overtime_date', '>=', $filters['date_from']);
            }
            if ($filters['date_to']) {
                $query->whereDate('overtime_date', '<=', $filters['date_to']);
            }
            if ($filters['status']) {
                $query->where('status', $filters['status']);
            }

            $overtimes = $query->orderBy('overtime_date', 'desc')->get();

            $dateFrom = \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d F Y');
            $dateTo = \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d F Y');

            $filename = 'laporan-lembur-' . now()->format('Y-m-d-His');

            switch ($format) {
                case 'pdf':
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.overtime-pdf', [
                        'overtimes' => $overtimes,
                        'dateFrom' => $dateFrom,
                        'dateTo' => $dateTo,
                        'status' => $filters['status'],
                    ]);
                    $pdf->setPaper('a4', 'landscape');
                    return $pdf->download($filename . '.pdf');

                case 'csv':
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\OvertimeExport($filters),
                        $filename . '.csv',
                        \Maatwebsite\Excel\Excel::CSV
                    );

                default:
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\OvertimeExport($filters),
                        $filename . '.xlsx'
                    );
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
        }
    }
}

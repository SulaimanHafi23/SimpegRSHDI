<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Overtime\OvertimeRequestService;
use App\Services\Export\PdfExportService;
use App\Exports\EmployeeOvertimeExport;
use App\DTOs\OvertimeRequestDTO;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OvertimeController extends Controller
{
    public function __construct(
        protected OvertimeRequestService $overtimeService,
        protected PdfExportService $pdfExportService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display employee's overtime requests
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $filters = [
            'worker_id' => $worker->id,
            'status' => $request->status,
            'search' => $request->search,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'year' => $request->year ?? now()->year,
            'per_page' => $request->per_page ?? 15,
        ];

        $overtimeRequests = $this->overtimeService->getAll($filters);

        // Calculate summary for current year
        $summaryFilters = ['worker_id' => $worker->id, 'year' => $filters['year']];
        $summary = [
            'total' => $this->overtimeService->getAll($summaryFilters)->total(),
            'pending' => $this->overtimeService->getAll(array_merge($summaryFilters, ['status' => 'pending']))->total(),
            'approved' => $this->overtimeService->getAll(array_merge($summaryFilters, ['status' => 'approved']))->total(),
            'rejected' => $this->overtimeService->getAll(array_merge($summaryFilters, ['status' => 'rejected']))->total(),
            'total_hours' => $this->overtimeService->getAll(array_merge($summaryFilters, ['status' => 'approved']))->sum('total_hours'),
        ];

        return view('employee.overtimes.index', compact('overtimeRequests', 'filters', 'summary', 'worker'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        return view('employee.overtimes.create');
    }

    /**
     * Store overtime request
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:1000',
        ]);

        try {
            // Calculate total hours
            $start = \Carbon\Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
            $end = \Carbon\Carbon::parse($validated['date'] . ' ' . $validated['end_time']);
            $totalHours = $start->diffInHours($end);

            $dto = OvertimeRequestDTO::fromRequest([
                'worker_id' => $worker->id,
                'overtime_date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'total_hours' => $totalHours,
                'reason' => $validated['reason'],
                'status' => 'pending',
            ]);

            $this->overtimeService->create($dto->toArray());

            return redirect()->route('employee.overtimes.index')
                ->with('success', 'Permohonan lembur berhasil diajukan!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengajukan lembur: ' . $e->getMessage());
        }
    }

    /**
     * Show overtime detail
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $overtime = $this->overtimeService->getById($id);

        // Verify ownership
        if ($overtime->worker_id !== $worker->id) {
            abort(403, 'Unauthorized');
        }

        return view('employee.overtimes.show', compact('overtime'));
    }

    /**
     * Cancel overtime request
     */
    public function cancel(string $id)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        try {
            $overtime = $this->overtimeService->getById($id);

            // Verify ownership
            if ($overtime->worker_id !== $worker->id) {
                abort(403, 'Unauthorized');
            }

            // Only pending can be cancelled
            if ($overtime->status !== 'pending') {
                return back()->with('error', 'Hanya permohonan yang masih pending yang bisa dibatalkan.');
            }

            $this->overtimeService->delete($id);

            return redirect()->route('employee.overtimes.index')
                ->with('success', 'Permohonan lembur berhasil dibatalkan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan lembur: ' . $e->getMessage());
        }
    }

    /**
     * Export overtime data (PDF, Excel, CSV)
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $format = $request->format ?? 'pdf';

        $filters = [
            'worker_id' => $worker->id,
            'status' => $request->status,
            'search' => $request->search,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'year' => $request->year ?? now()->year,
        ];

        // Get all records without pagination
        $overtimes = collect($this->overtimeService->getAll(array_merge($filters, ['per_page' => 10000]))->items());

        if ($format === 'excel') {
            return Excel::download(
                new EmployeeOvertimeExport($overtimes, $worker),
                'laporan-lembur-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        if ($format === 'csv') {
            return Excel::download(
                new EmployeeOvertimeExport($overtimes, $worker),
                'laporan-lembur-' . now()->format('Y-m-d') . '.csv'
            );
        }

        // Default: PDF
        return $this->pdfExportService->exportOvertimeReport($overtimes->toArray(), $worker, $filters);
    }

    /**
     * Export overtime to PDF
     */
    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $filters = [
            'worker_id' => $worker->id,
            'status' => $request->status,
            'search' => $request->search,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'year' => $request->year ?? now()->year,
        ];

        // Get all records without pagination for PDF
        $overtimes = $this->overtimeService->getAll(array_merge($filters, ['per_page' => 10000]))->items();

        return $this->pdfExportService->exportOvertimeReport($overtimes, $worker, $filters);
    }}

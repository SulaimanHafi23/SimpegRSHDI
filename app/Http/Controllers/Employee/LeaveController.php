<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Leave\LeaveRequestService;
use App\Services\Master\LeaveTypeService;
use App\Services\Export\PdfExportService;
use App\Exports\EmployeeLeaveExport;
use App\DTOs\LeaveRequestDTO;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function __construct(
        protected LeaveRequestService $leaveService,
        protected LeaveTypeService $leaveTypeService,
        protected PdfExportService $pdfExportService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display employee's leave requests
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
            'leave_type_id' => $request->leave_type_id,
            'search' => $request->search,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'year' => $request->year ?? now()->year,
            'per_page' => $request->per_page ?? 15,
        ];

        $leaveRequests = $this->leaveService->getAll($filters);
        $leaveTypes = $this->leaveTypeService->getActive();

        // Calculate leave summary (1 query instead of 4)
        $year = $filters['year'];
        $summaryData = \App\Models\LeaveRequest::where('worker_id', $worker->id)
            ->whereYear('start_date', $year)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            ")
            ->first();

        $summary = [
            'total' => $summaryData->total ?? 0,
            'pending' => $summaryData->pending ?? 0,
            'approved' => $summaryData->approved ?? 0,
            'rejected' => $summaryData->rejected ?? 0,
        ];

        return view('employee.leaves.index', compact('leaveRequests', 'leaveTypes', 'filters', 'summary', 'worker'));
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

        $leaveTypes = $this->leaveTypeService->getActive();

        // Calculate days used per leave type this year (approved + pending)
        $usedDays = \App\Models\LeaveRequest::where('worker_id', $worker->id)
            ->whereYear('start_date', now()->year)
            ->whereIn('status', ['approved', 'pending'])
            ->groupBy('leave_type_id')
            ->selectRaw('leave_type_id, COALESCE(SUM(total_days), 0) as used_days')
            ->pluck('used_days', 'leave_type_id');

        return view('employee.leaves.create', compact('leaveTypes', 'usedDays'));
    }

    /**
     * Store leave request
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
            'leave_type_id' => 'required|uuid|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        try {
            // Calculate total days
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = \Carbon\Carbon::parse($validated['end_date']);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            $dto = LeaveRequestDTO::fromRequest([
                'worker_id' => $worker->id,
                'leave_type_id' => $validated['leave_type_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_days' => $totalDays,
                'reason' => $validated['reason'],
                'status' => 'pending',
                'document' => $request->file('document'),
            ]);

            $this->leaveService->create($dto->toArray());

            return redirect()->route('employee.leaves.index')
                ->with('success', 'Permohonan cuti berhasil diajukan!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengajukan cuti: ' . $e->getMessage());
        }
    }

    /**
     * Show leave detail
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $leave = $this->leaveService->getById($id);

        // Verify this leave belongs to the logged-in worker
        if ($leave->worker_id !== $worker->id) {
            abort(403, 'Unauthorized');
        }

        return view('employee.leaves.show', compact('leave'));
    }

    /**
     * Cancel leave request (only pending)
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
            $leave = $this->leaveService->getById($id);

            // Verify ownership
            if ($leave->worker_id !== $worker->id) {
                abort(403, 'Unauthorized');
            }

            // Only pending can be cancelled
            if ($leave->status !== 'pending') {
                return back()->with('error', 'Hanya permohonan yang masih pending yang bisa dibatalkan.');
            }

            $this->leaveService->delete($id);

            return redirect()->route('employee.leaves.index')
                ->with('success', 'Permohonan cuti berhasil dibatalkan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan cuti: ' . $e->getMessage());
        }
    }
    /**
     * Export leave data (PDF, Excel, CSV)
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
            'leave_type_id' => $request->leave_type_id,
            'search' => $request->search,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'year' => $request->year ?? now()->year,
        ];

        // Get all records without pagination
        $leaves = collect($this->leaveService->getAll(array_merge($filters, ['per_page' => 10000]))->items());

        if ($format === 'excel') {
            return Excel::download(
                new EmployeeLeaveExport($leaves, $worker),
                'laporan-cuti-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        if ($format === 'csv') {
            return Excel::download(
                new EmployeeLeaveExport($leaves, $worker),
                'laporan-cuti-' . now()->format('Y-m-d') . '.csv'
            );
        }

        // Default: PDF
        return $this->pdfExportService->exportLeaveReport($leaves->toArray(), $worker, $filters);
    }

    /**
     * Export leave to PDF
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
            'leave_type_id' => $request->leave_type_id,
            'search' => $request->search,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'year' => $request->year ?? now()->year,
        ];

        // Get all records without pagination for PDF
        $leaves = $this->leaveService->getAll(array_merge($filters, ['per_page' => 10000]))->items();

        return $this->pdfExportService->exportLeaveReport($leaves, $worker, $filters);
    }}

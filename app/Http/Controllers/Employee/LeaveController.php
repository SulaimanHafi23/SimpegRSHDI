<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Leave\LeaveRequestService;
use App\Services\Master\LeaveTypeService;
use App\Services\Export\PdfExportService;
use App\DTOs\LeaveRequestDTO;
use Illuminate\Http\Request;
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

        // Calculate leave summary using count method for accuracy
        $year = $filters['year'];
        $summary = [
            'total' => $this->leaveService->countByStatus($worker->id, $year, null),
            'pending' => $this->leaveService->countByStatus($worker->id, $year, 'pending'),
            'approved' => $this->leaveService->countByStatus($worker->id, $year, 'approved'),
            'rejected' => $this->leaveService->countByStatus($worker->id, $year, 'rejected'),
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

        return view('employee.leaves.create', compact('leaveTypes'));
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
            'start_date' => 'required|date',
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

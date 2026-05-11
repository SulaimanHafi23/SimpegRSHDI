<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Exports\EmployeeLeaveExport;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class LeaveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display employee's leave requests
     */
    public function index(Request $request)
    {
        $user = Auth::user();
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

        $leaveRequests = $this->buildLeaveQuery($filters)
            ->latest('start_date')
            ->paginate($filters['per_page'])
            ->appends($filters);
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();

        // Calculate leave summary (1 query instead of 4)
        $year = $filters['year'];
        $summaryData = \App\Models\LeaveRequest::where('worker_id', $worker->id)
            ->whereYear('start_date', $year)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('pending', 'manager_verified') THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            ")
            ->first();

        $summary = [
            'total' => $summaryData->total ?? 0,
            'pending' => $summaryData->pending ?? 0,
            'approved' => $summaryData->approved ?? 0,
            'rejected' => $summaryData->rejected ?? 0,
            'cancelled' => $summaryData->cancelled ?? 0,
        ];

        return view('employee.leaves.index', compact('leaveRequests', 'leaveTypes', 'filters', 'summary', 'worker'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();

        // Calculate days used per leave type this year (approved + pending)
        $usedDays = \App\Models\LeaveRequest::where('worker_id', $worker->id)
            ->whereYear('start_date', now()->year)
            ->whereIn('status', ['approved', 'pending'])
            ->groupBy('leave_type_id')
            ->selectRaw('leave_type_id, COALESCE(SUM(total_days), 0) as used_days')
            ->pluck('used_days', 'leave_type_id');

        $blockedLeaveDates = [];
        $pendingOrApprovedLeaves = \App\Models\LeaveRequest::where('worker_id', $worker->id)
            ->whereIn('status', ['approved', 'pending'])
            ->get(['start_date', 'end_date']);

        foreach ($pendingOrApprovedLeaves as $leave) {
            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($period as $date) {
                $blockedLeaveDates[] = $date->format('Y-m-d');
            }
        }

        $blockedLeaveDates = array_values(array_unique($blockedLeaveDates));

        return view('employee.leaves.create', compact('leaveTypes', 'usedDays', 'blockedLeaveDates'));
    }

    /**
     * Store leave request
     */
    public function store(Request $request)
    {
        $user = Auth::user();
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
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

            // Calculate total days (server-side only)
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            // Validate leave balance
            if ($leaveType->max_days_per_year) {
                $usedDays = LeaveRequest::where('worker_id', $worker->id)
                    ->where('leave_type_id', $validated['leave_type_id'])
                    ->whereYear('start_date', now()->year)
                    ->whereIn('status', ['approved', 'pending'])
                    ->sum('total_days');

                $balance = max(0, $leaveType->max_days_per_year - $usedDays);
                if ($balance < $totalDays) {
                    return back()
                        ->withInput()
                        ->with('error', "Sisa cuti tidak mencukupi. Sisa cuti tersedia: {$balance} hari");
                }
            }

            // Validate days notice
            $startOfDay = Carbon::parse($validated['start_date'])->startOfDay();
            $today = now()->startOfDay();
            $daysUntilStart = $today->diffInDays($startOfDay, false);

            if ($startOfDay->isFuture() && $daysUntilStart < $leaveType->days_notice) {
                return back()
                    ->withInput()
                    ->with('error', "Permohonan cuti harus diajukan minimal {$leaveType->days_notice} hari sebelumnya.");
            }

            // Don't allow backdated leave requests
            if ($startOfDay->isPast()) {
                return back()
                    ->withInput()
                    ->with('error', 'Tidak dapat mengajukan cuti untuk tanggal yang sudah lewat.');
            }

            $hasOverlappingLeave = \App\Models\LeaveRequest::where('worker_id', $worker->id)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($query) use ($validated) {
                    $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                        ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                        ->orWhere(function ($nested) use ($validated) {
                            $nested->where('start_date', '<=', $validated['start_date'])
                                ->where('end_date', '>=', $validated['end_date']);
                        });
                })
                ->exists();

            if ($hasOverlappingLeave) {
                return back()
                    ->withInput()
                    ->with('error', 'Tanggal cuti bentrok dengan pengajuan sebelumnya. Tanggal hanya bisa dipilih lagi jika pengajuan sebelumnya ditolak.');
            }

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachment = $request->file('attachment');
                $filename = sprintf(
                    '%s_leave_%s.%s',
                    $worker->id,
                    now()->format('YmdHis'),
                    $attachment->getClientOriginalExtension()
                );
                $attachmentPath = $attachment->storeAs('leave-attachments', $filename, 'public');
            }

            LeaveRequest::create([
                'worker_id' => $worker->id,
                'leave_type_id' => $validated['leave_type_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_days' => $totalDays,
                'reason' => $validated['reason'],
                'status' => 'pending',
                'attachment_path' => $attachmentPath,
            ]);

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
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $leave = LeaveRequest::with(['worker', 'leaveType', 'approver'])->findOrFail($id);

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
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        try {
            $leave = LeaveRequest::with(['worker', 'leaveType', 'approver'])->findOrFail($id);

            // Verify ownership
            if ($leave->worker_id !== $worker->id) {
                abort(403, 'Unauthorized');
            }

            // Only pending can be cancelled
            if ($leave->status !== 'pending') {
                return back()->with('error', 'Hanya permohonan yang masih pending yang bisa dibatalkan.');
            }

            $leave->update(['status' => 'cancelled']);

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
        $user = Auth::user();
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
        $leaves = $this->buildLeaveQuery($filters)
            ->latest('start_date')
            ->get();

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
        return $this->exportLeavePdfReport($leaves, $worker, $filters);
    }

    /**
     * Export leave to PDF
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
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
        $leaves = $this->buildLeaveQuery($filters)
            ->latest('start_date')
            ->get();

        return $this->exportLeavePdfReport($leaves, $worker, $filters);
    }

    private function buildLeaveQuery(array $filters)
    {
        $query = LeaveRequest::with(['worker', 'leaveType', 'approver']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('start_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('end_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('start_date', $filters['year']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('start_date', 'like', "%{$search}%")
                    ->orWhere('end_date', 'like', "%{$search}%")
                    ->orWhereHas('leaveType', function ($inner) use ($search) {
                        $inner->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('worker', function ($inner) use ($search) {
                        $inner->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    private function exportLeavePdfReport($leaves, $worker, array $filters)
    {
        $collection = collect($leaves);

        $data = [
            'title' => 'Laporan Riwayat Cuti',
            'worker' => $worker,
            'leaves' => $leaves,
            'filters' => $filters,
            'generated_at' => now()->format('d F Y H:i'),
            'summary' => [
                'total' => $collection->count(),
                'pending' => $collection->where('status', 'pending')->count(),
                'approved' => $collection->where('status', 'approved')->count(),
                'rejected' => $collection->where('status', 'rejected')->count(),
            ],
        ];

        $pdf = Pdf::loadView('employee.exports.leave-pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Cuti_' . $worker->name . '_' . now()->format('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }
}

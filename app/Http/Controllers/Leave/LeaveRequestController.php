<?php

// filepath: app/Http/Controllers/LeaveRequestController.php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Notification;
use App\Models\User;
use App\Models\Worker;
use App\Traits\DepartmentFilterable;
use App\Http\Requests\Leave\LeaveRequestRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    use DepartmentFilterable;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:leave.manage')->except([
            'approvalIndex',
            'approvalShow',
            'approvalApprove',
            'approvalReject',
        ]);
        $this->middleware('role:Manager|HR|Super Admin')->only([
            'approvalIndex',
            'approvalShow',
            'approvalApprove',
            'approvalReject',
        ]);
    }

    public function approvalIndex(Request $request)
    {
        $user = Auth::user();

        $filters = [
            'status' => $request->input('status', 'pending'),
            'per_page' => 20,
        ];

        if ($user->hasRole('Manager') && $user->worker) {
            $filters['department_id'] = $user->worker->department_id;
        }

        $leaves = $this->buildLeaveQuery($filters)
            ->latest('start_date')
            ->paginate($filters['per_page'])
            ->appends($filters);

        $leaveTypes = LeaveType::orderBy('name')->get();

        $baseQuery = LeaveRequest::query();
        if (!empty($filters['department_id'])) {
            $baseQuery->whereHas('worker', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        $totalLeaves = (clone $baseQuery)->count();
        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $baseQuery)->where('status', 'approved')->count();
        $rejectedCount = (clone $baseQuery)->where('status', 'rejected')->count();

        return view('approvals.leaves.index', compact(
            'leaves',
            'leaveTypes',
            'totalLeaves',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'filters'
        ));
    }

    public function approvalShow(string $id)
    {
        $leave = LeaveRequest::with(['worker.department', 'leaveType', 'approver'])->findOrFail($id);
        $this->ensureApprovalAccess($leave);

        // Compatibility for existing approval view that references approvedBy.
        $leave->setRelation('approvedBy', $leave->approver);

        return view('approvals.leaves.show', compact('leave'));
    }

    public function approvalApprove(Request $request, string $id)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $leaveRequest = LeaveRequest::with('worker')->findOrFail($id);
            $this->ensureApprovalAccess($leaveRequest);

            if ($leaveRequest->status !== 'pending') {
                throw new \Exception('Hanya permohonan cuti yang berstatus pending yang dapat disetujui.');
            }

            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            $user = User::where('worker_id', $leaveRequest->worker_id)->first();
            if ($user) {
                Notification::create([
                    'user_id' => $user->id,
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id' => $user->id,
                    'type' => 'leave_approved',
                    'title' => 'Cuti Disetujui',
                    'message' => sprintf(
                        'Permohonan cuti Anda dari %s sampai %s telah disetujui.',
                        $leaveRequest->start_date,
                        $leaveRequest->end_date
                    ),
                    'data' => [
                        'leave_id' => $leaveRequest->id,
                        'type' => 'leave',
                        'action' => 'approved',
                    ],
                ]);
            }

            return redirect()
                ->route('approvals.leaves.index')
                ->with('success', 'Pengajuan cuti berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approvalReject(Request $request, string $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            $leaveRequest = LeaveRequest::with('worker')->findOrFail($id);
            $this->ensureApprovalAccess($leaveRequest);

            if ($leaveRequest->status !== 'pending') {
                throw new \Exception('Hanya permohonan cuti yang berstatus pending yang dapat ditolak.');
            }

            $leaveRequest->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            $user = User::where('worker_id', $leaveRequest->worker_id)->first();
            if ($user) {
                Notification::create([
                    'user_id' => $user->id,
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id' => $user->id,
                    'type' => 'leave_rejected',
                    'title' => 'Cuti Ditolak',
                    'message' => sprintf(
                        'Permohonan cuti Anda dari %s sampai %s telah ditolak. Alasan: %s',
                        $leaveRequest->start_date,
                        $leaveRequest->end_date,
                        $validated['rejection_reason']
                    ),
                    'data' => [
                        'leave_id' => $leaveRequest->id,
                        'type' => 'leave',
                        'action' => 'rejected',
                        'reason' => $validated['rejection_reason'],
                    ],
                ]);
            }

            return redirect()
                ->route('approvals.leaves.index')
                ->with('success', 'Pengajuan cuti berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
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

        $leaveRequests = $this->buildLeaveQuery($filters)
            ->latest('start_date')
            ->paginate($filters['per_page'])
            ->appends($filters);

        // Get workers from user's department if Manager
        $workers = Worker::where('status', 'active')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->with(['department'])
            ->get();

        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();

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
        $workers = Worker::where('status', 'active')->with(['department'])->get();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();

        return view('admin.leave.create', compact('workers', 'leaveTypes'));
    }

    public function store(LeaveRequestRequest $request)
    {
        try {
            $validated = $request->validated();

            $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            if ($leaveType->max_days_per_year) {
                $usedDays = LeaveRequest::where('worker_id', $validated['worker_id'])
                    ->where('leave_type_id', $validated['leave_type_id'])
                    ->whereYear('start_date', now()->year)
                    ->whereIn('status', ['approved', 'pending'])
                    ->sum('total_days');

                $balance = max(0, $leaveType->max_days_per_year - $usedDays);
                if ($balance < $totalDays) {
                    throw new \Exception("Sisa cuti tidak mencukupi. Sisa cuti tersedia: {$balance} hari");
                }
            }

            $startOfDay = Carbon::parse($validated['start_date'])->startOfDay();
            $today = now()->startOfDay();
            $daysUntilStart = $today->diffInDays($startOfDay, false);

            if ($startOfDay->isFuture() && $daysUntilStart < $leaveType->days_notice) {
                throw new \Exception("Permohonan cuti harus diajukan minimal {$leaveType->days_notice} hari sebelumnya.");
            }

            if ($startOfDay->isPast()) {
                throw new \Exception('Tidak dapat mengajukan cuti untuk tanggal yang sudah lewat.');
            }

            $overlapping = LeaveRequest::where('worker_id', $validated['worker_id'])
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($q) use ($validated) {
                    $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                        ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                        ->orWhere(function ($q2) use ($validated) {
                            $q2->where('start_date', '<=', $validated['start_date'])
                                ->where('end_date', '>=', $validated['end_date']);
                        });
                })
                ->exists();

            if ($overlapping) {
                throw new \Exception('Sudah ada permohonan cuti yang tumpang tindih pada tanggal tersebut.');
            }

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachment = $request->file('attachment');
                $filename = sprintf(
                    '%s_leave_%s.%s',
                    $validated['worker_id'],
                    now()->format('YmdHis'),
                    $attachment->getClientOriginalExtension()
                );
                $attachmentPath = $attachment->storeAs('leave-attachments', $filename, 'public');
            }

            LeaveRequest::create([
                'worker_id' => $validated['worker_id'],
                'leave_type_id' => $validated['leave_type_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_days' => $totalDays,
                'reason' => $validated['reason'],
                'attachment_path' => $attachmentPath,
                'status' => 'pending',
            ]);

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
        $leaveRequest = LeaveRequest::with(['worker', 'leaveType', 'approver'])->findOrFail($id);

        return view('admin.leave.show', compact('leaveRequest'));
    }

    public function destroy(string $id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            if ($leaveRequest->attachment_path) {
                if (Storage::exists($leaveRequest->attachment_path)) {
                    Storage::delete($leaveRequest->attachment_path);
                } elseif (Storage::disk('public')->exists($leaveRequest->attachment_path)) {
                    Storage::disk('public')->delete($leaveRequest->attachment_path);
                }
            }

            $leaveRequest->delete();

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
            $approvedBy = Auth::id();
            if (!$approvedBy) {
                throw new \Exception('User tidak terautentikasi.');
            }

            $leaveRequest = LeaveRequest::findOrFail($id);
            if ($leaveRequest->status !== 'pending') {
                throw new \Exception('Hanya permohonan cuti yang berstatus pending yang dapat disetujui.');
            }

            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            $user = User::where('worker_id', $leaveRequest->worker_id)->first();
            if ($user) {
                Notification::create([
                    'user_id' => $user->id,
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id' => $user->id,
                    'type' => 'leave_approved',
                    'title' => 'Cuti Disetujui',
                    'message' => sprintf(
                        'Permohonan cuti Anda dari %s sampai %s telah disetujui.',
                        $leaveRequest->start_date,
                        $leaveRequest->end_date
                    ),
                    'data' => [
                        'leave_id' => $leaveRequest->id,
                        'type' => 'leave',
                        'action' => 'approved',
                    ],
                ]);
            }

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
            $approvedBy = Auth::id();
            if (!$approvedBy) {
                throw new \Exception('User tidak terautentikasi.');
            }

            $leaveRequest = LeaveRequest::findOrFail($id);
            if ($leaveRequest->status !== 'pending') {
                throw new \Exception('Hanya permohonan cuti yang berstatus pending yang dapat ditolak.');
            }

            $leaveRequest->update([
                'status' => 'rejected',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            $user = User::where('worker_id', $leaveRequest->worker_id)->first();
            if ($user) {
                Notification::create([
                    'user_id' => $user->id,
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id' => $user->id,
                    'type' => 'leave_rejected',
                    'title' => 'Cuti Ditolak',
                    'message' => sprintf(
                        'Permohonan cuti Anda dari %s sampai %s telah ditolak. Alasan: %s',
                        $leaveRequest->start_date,
                        $leaveRequest->end_date,
                        $validated['rejection_reason']
                    ),
                    'data' => [
                        'leave_id' => $leaveRequest->id,
                        'type' => 'leave',
                        'action' => 'rejected',
                        'reason' => $validated['rejection_reason'],
                    ],
                ]);
            }

            return back()->with('success', 'Permohonan cuti berhasil ditolak');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(string $id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            if (!in_array($leaveRequest->status, ['pending', 'approved'], true)) {
                throw new \Exception('Hanya permohonan cuti yang berstatus pending atau approved yang dapat dibatalkan.');
            }

            $leaveRequest->update(['status' => 'cancelled']);

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
                        new \App\Exports\LeaveRecapExport($filters),
                        $filename . '.xlsx'
                    );
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
        }
    }

    public function workerLeaveBalance(string $workerId)
    {
        $worker = Worker::with(['department', 'user', 'activeWorkerShift.shift'])->findOrFail($workerId);
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();

        $balances = [];
        foreach ($leaveTypes as $leaveType) {
            if (!$leaveType->max_days_per_year) {
                $balances[$leaveType->id] = 0;
                continue;
            }

            $usedDays = LeaveRequest::where('worker_id', $workerId)
                ->where('leave_type_id', $leaveType->id)
                ->whereYear('start_date', now()->year)
                ->whereIn('status', ['approved', 'pending'])
                ->sum('total_days');

            $balances[$leaveType->id] = max(0, $leaveType->max_days_per_year - $usedDays);
        }

        return view('admin.leave.balance', compact('worker', 'leaveTypes', 'balances'));
    }

    private function buildLeaveQuery(array $filters)
    {
        $query = LeaveRequest::with(['worker', 'leaveType', 'approver']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('worker', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        if (!empty($dateFrom)) {
            $query->where('start_date', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->where('end_date', '<=', $dateTo);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('start_date', $filters['year']);
        }

        return $query;
    }

    private function ensureApprovalAccess(LeaveRequest $leaveRequest): void
    {
        $user = Auth::user();
        if ($user && $user->hasRole('Manager') && $user->worker) {
            if ((string) $leaveRequest->worker->department_id !== (string) $user->worker->department_id) {
                abort(403, 'Unauthorized');
            }
        }
    }
}

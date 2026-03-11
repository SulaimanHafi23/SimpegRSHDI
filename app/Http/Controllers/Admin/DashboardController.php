<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Services\Overtime\OvertimeRequestService;
use App\Services\Attendance\AttendanceService;
use App\Services\Document\DocumentExpiryService;
use App\Traits\DepartmentFilterable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use DepartmentFilterable;

    protected OvertimeRequestService $overtimeRequestService;
    protected AttendanceService $attendanceService;
    protected DocumentExpiryService $documentExpiryService;

    public function __construct(
        OvertimeRequestService $overtimeRequestService,
        AttendanceService $attendanceService,
        DocumentExpiryService $documentExpiryService
    )
    {
        $this->middleware('auth');
        $this->middleware('permission:dashboard.admin');

        $this->overtimeRequestService = $overtimeRequestService;
        $this->attendanceService = $attendanceService;
        $this->documentExpiryService = $documentExpiryService;
    }

    public function index()
    {
        // Check if user should be redirected to employee dashboard.
        // Prefer explicit admin/superadmin roles; only redirect users who are
        // employees/workers and do NOT have higher-privilege roles.
        $user = auth()->user();
        if ($user && $user->roles->isNotEmpty()) {
            // normalize role names to lowercase for robust checks
            $roles = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();

            // If the user has an administrative role, do not redirect to employee dashboard
            if (in_array('superadmin', $roles) || in_array('admin', $roles)) {
                // allow admin to see the admin dashboard
            } elseif (in_array('employee', $roles) || in_array('worker', $roles)) {
                // user is an employee/worker and has no admin role -> redirect
                return redirect()->route('employee.dashboard');
            }
        }

        // ========== STATISTICS ==========

        $departmentId = $this->getManagerDepartmentFilter();

        // Workers Statistics — single aggregate query instead of 7
        $workerStats = Worker::when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive")
            ->selectRaw("SUM(CASE WHEN status = 'resigned' THEN 1 ELSE 0 END) as resigned")
            ->selectRaw("SUM(CASE WHEN employment_status = 'permanent' THEN 1 ELSE 0 END) as permanent")
            ->selectRaw("SUM(CASE WHEN employment_status = 'contract' THEN 1 ELSE 0 END) as contract")
            ->selectRaw("SUM(CASE WHEN employment_status = 'internship' THEN 1 ELSE 0 END) as internship")
            ->first();

        $totalWorkers = $workerStats->total ?? 0;
        $activeWorkers = $workerStats->active ?? 0;
        $inactiveWorkers = $workerStats->inactive ?? 0;
        $resignedWorkers = $workerStats->resigned ?? 0;
        $permanentWorkers = $workerStats->permanent ?? 0;
        $contractWorkers = $workerStats->contract ?? 0;
        $internshipWorkers = $workerStats->internship ?? 0;

        // Scope helper for attendance queries
        $scopeAttendance = function($query) use ($departmentId) {
            return $departmentId ? $query->whereHas('worker', fn($q) => $q->where('department_id', $departmentId)) : $query;
        };

        // Attendance Statistics (Today) — single grouped query
        $today = Carbon::today();
        $todayCounts = $scopeAttendance(Attendance::whereDate('attendance_date', $today))
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');
        $todayAttendance = $todayCounts->sum();
        $todayPresent = $todayCounts->get('present', 0);
        $todayLate = $todayCounts->get('late', 0);
        $todayAbsent = $todayCounts->get('absent', 0);

        // Scope helper for leave queries
        $scopeLeave = function($query) use ($departmentId) {
            return $departmentId ? $query->whereHas('worker', fn($q) => $q->where('department_id', $departmentId)) : $query;
        };

        // Leave Requests Statistics
        $totalLeaveRequests = $scopeLeave(LeaveRequest::query())->count();
        $pendingLeaves = $scopeLeave(LeaveRequest::where('status', 'pending'))->count();
        $approvedLeaves = $scopeLeave(LeaveRequest::where('status', 'approved'))->count();
        $rejectedLeaves = $scopeLeave(LeaveRequest::where('status', 'rejected'))->count();

        // Overtime Requests Statistics
        $totalOvertimeRequests = $departmentId
            ? OvertimeRequest::whereHas('worker', fn($q) => $q->where('department_id', $departmentId))->count()
            : OvertimeRequest::count();
    $pendingOvertimes = $this->overtimeRequestService->getAll(['status' => 'pending', 'department_id' => $departmentId, 'per_page' => 9999])->total();
    $approvedOvertimes = $this->overtimeRequestService->getAll(['status' => 'approved', 'department_id' => $departmentId, 'per_page' => 9999])->total();
    $rejectedOvertimes = $this->overtimeRequestService->getAll(['status' => 'rejected', 'department_id' => $departmentId, 'per_page' => 9999])->total();

        // ========== RECENT ACTIVITIES ==========

        // Recent Leave Requests
        $recentLeaves = LeaveRequest::with(['worker', 'leaveType'])
            ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))
            ->latest()
            ->take(5)
            ->get();

        // Recent Overtime Requests
        $recentOvertimes = OvertimeRequest::with(['worker'])
            ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))
            ->latest()
            ->take(5)
            ->get();

        // Today's Absences
        $todayAbsences = Attendance::with(['worker'])
            ->whereDate('attendance_date', $today)
            ->where('status', 'absent')
            ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))
            ->get();

        // ========== CHARTS DATA ==========

        // Monthly Attendance Stats (Last 6 Months)
        $attendanceStats = $this->getMonthlyAttendanceStats($departmentId);

        // Leave Stats by Type
        $leaveStats = $this->getLeaveStatsByType($departmentId);

        // Department Statistics
        $departmentStats = $this->getDepartmentStats($departmentId);

        // ========== UPCOMING EVENTS ==========

        // Upcoming Leaves
        $upcomingLeaves = LeaveRequest::with(['worker', 'leaveType'])
            ->where('status', 'approved')
            ->where('start_date', '>=', $today)
            ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))
            ->orderBy('start_date')
            ->take(5)
            ->get();

        // ========== PENDING CHECKOUTS ==========

        // Get workers who need to checkout (shift ended but still no checkout)
        $pendingCheckouts = $this->attendanceService->getPendingCheckouts();

        // ========== DOCUMENT EXPIRY ALERTS ==========

        // Get document expiry statistics
        $documentExpiryStats = $this->documentExpiryService->getExpiryStatistics();

        // Get documents grouped by urgency
        $documentsByUrgency = $this->documentExpiryService->getDocumentsByUrgency();
        $criticalDocuments = $documentsByUrgency['critical'];
        $urgentDocuments = $documentsByUrgency['urgent'];
        $warningDocuments = $documentsByUrgency['warning'];

        // ========== PREPARE VIEW DATA SHAPES EXPECTED BY BLADE ==========

        // Statistics array expected by the view
        $statistics = [
            'total_workers' => $totalWorkers,
            'active_workers' => $activeWorkers,
            'present_today' => $todayPresent,
            'attendance_rate' => $totalWorkers > 0 ? round(($todayPresent / max(1, $totalWorkers)) * 100, 1) : 0,
            'pending_leaves' => $pendingLeaves,
            'pending_overtimes' => $pendingOvertimes,
        ];

        // Attendance chart for the last 7 days — single batch query
        $labels = [];
        $data = [];
        $start = Carbon::today()->subDays(6);
        $dayNames = [0 => 'Min', 1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab'];

        $chartCounts = $scopeAttendance(Attendance::whereBetween('attendance_date', [$start, Carbon::today()])
            ->where('status', 'present'))
            ->selectRaw("DATE(attendance_date) as att_date, COUNT(*) as cnt")
            ->groupBy('att_date')
            ->pluck('cnt', 'att_date');

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $labels[] = $dayNames[$date->dayOfWeek] ?? $date->format('D');
            $data[] = $chartCounts->get($date->format('Y-m-d'), 0);
        }

        $attendanceChart = [
            'labels' => $labels,
            'data' => $data,
        ];

        // Build a distribution array from department stats so the view can show
        // "Distribusi Pegawai per Departemen" using the same shape previously used for positions.
        // $departmentStats comes from getDepartmentStats() and contains objects with 'name' and 'total'.
        $positionDistribution = collect($departmentStats)->map(function ($d) {
            return (object) [
                'name' => $d->name ?? ($d->department_name ?? 'Unknown'),
                'workers_count' => $d->total ?? ($d->workers_count ?? 0),
            ];
        })->toArray();

        return view('admin.dashboard.index', compact(
            // Workers
            'totalWorkers',
            'activeWorkers',
            'inactiveWorkers',
            'resignedWorkers',
            'permanentWorkers',
            'contractWorkers',
            'internshipWorkers',
            // view-friendly aggregates
            'statistics',
            'positionDistribution',

            // Attendance
            'todayAttendance',
            'todayPresent',
            'todayLate',
            'todayAbsent',

            // Leaves
            'totalLeaveRequests',
            'pendingLeaves',
            'approvedLeaves',
            'rejectedLeaves',

            // Overtimes
            'totalOvertimeRequests',
            'pendingOvertimes',
            'approvedOvertimes',
            'rejectedOvertimes',

            // Recent Activities
            'recentLeaves',
            'recentOvertimes',
            'todayAbsences',

            // Charts
            'attendanceStats',
            'attendanceChart',
            'leaveStats',
            'departmentStats',

            // Upcoming
            'upcomingLeaves',

            // Pending Checkouts
            'pendingCheckouts',

            // Document Expiry
            'documentExpiryStats',
            'criticalDocuments',
            'urgentDocuments',
            'warningDocuments'
        ));
    }

    /**
     * Get monthly attendance statistics for the last 6 months
     */
    private function getMonthlyAttendanceStats(?string $departmentId = null): array
    {
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Single grouped query for all 6 months × all statuses
        $query = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))
            ->selectRaw("DATE_FORMAT(attendance_date, '%Y-%m') as ym, status, COUNT(*) as cnt")
            ->groupBy('ym', 'status')
            ->get();

        // Group by month then status
        $grouped = $query->groupBy('ym')->map(function ($items) {
            return $items->pluck('cnt', 'status');
        });

        $stats = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $ym = $month->format('Y-m');
            $monthData = $grouped->get($ym, collect());

            $stats[] = [
                'month' => $month->format('M Y'),
                'present' => $monthData->get('present', 0),
                'late' => $monthData->get('late', 0),
                'absent' => $monthData->get('absent', 0),
            ];
        }

        return $stats;
    }

    /**
     * Get leave statistics by type
     */
    private function getLeaveStatsByType(?string $departmentId = null): array
    {
        $query = DB::table('leave_requests')
            ->join('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id');

        if ($departmentId) {
            $query->join('workers', 'leave_requests.worker_id', '=', 'workers.id')
                ->where('workers.department_id', $departmentId);
        }

        return $query->select(
                'leave_types.name',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN leave_requests.status = "approved" THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(CASE WHEN leave_requests.status = "pending" THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN leave_requests.status = "rejected" THEN 1 ELSE 0 END) as rejected')
            )
            ->groupBy('leave_types.id', 'leave_types.name')
            ->get()
            ->toArray();
    }

    /**
     * Get statistics by department
     */
    private function getDepartmentStats(?string $departmentId = null): array
    {
        $query = DB::table('workers')
            ->join('departments', 'workers.department_id', '=', 'departments.id');

        if ($departmentId) {
            $query->where('workers.department_id', $departmentId);
        }

        return $query->select(
                'departments.name',
                'departments.code',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN workers.status = "active" THEN 1 ELSE 0 END) as active'),
                DB::raw('SUM(CASE WHEN workers.status = "inactive" THEN 1 ELSE 0 END) as inactive')
            )
            ->groupBy('departments.id', 'departments.name', 'departments.code')
            ->get()
            ->toArray();
    }

    /**
     * Get attendance summary for today
     */
    public function getTodayAttendanceSummary()
    {
        $today = Carbon::today();
        $departmentId = $this->getManagerDepartmentFilter();

        $summary = [
            'total_workers' => Worker::where('status', 'active')->when($departmentId, fn($q) => $q->where('department_id', $departmentId))->count(),
            'present' => Attendance::whereDate('attendance_date', $today)->where('status', 'present')
                ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))->count(),
            'late' => Attendance::whereDate('attendance_date', $today)->where('status', 'late')
                ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))->count(),
            'absent' => Attendance::whereDate('attendance_date', $today)->where('status', 'absent')
                ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))->count(),
            'on_leave' => LeaveRequest::where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))
                ->count(),
        ];

        $summary['not_checked_in'] = $summary['total_workers'] -
            ($summary['present'] + $summary['late'] + $summary['absent']);

        return response()->json($summary);
    }

    /**
     * Get pending approvals count
     */
    public function getPendingApprovalsCount()
    {
        $departmentId = $this->getManagerDepartmentFilter();
        $pendingOvertimes = $this->overtimeRequestService->getAll(['status' => 'pending', 'department_id' => $departmentId, 'per_page' => 9999])->total();
        $pendingLeaves = LeaveRequest::where('status', 'pending')
            ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))
            ->count();
        $count = [
            'leaves' => $pendingLeaves,
            'overtimes' => $pendingOvertimes,
            'total' => $pendingLeaves + $pendingOvertimes,
        ];

        return response()->json($count);
    }
}

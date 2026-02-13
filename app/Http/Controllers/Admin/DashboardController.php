<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Services\Overtime\OvertimeRequestService;
use App\Services\Attendance\AttendanceService;
use App\Traits\DepartmentFilterable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use DepartmentFilterable;

    protected OvertimeRequestService $overtimeRequestService;
    protected AttendanceService $attendanceService;

    public function __construct(
        OvertimeRequestService $overtimeRequestService,
        AttendanceService $attendanceService
    )
    {
        $this->middleware('auth');
        $this->middleware('permission:dashboard.admin');

        $this->overtimeRequestService = $overtimeRequestService;
        $this->attendanceService = $attendanceService;
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

        // Workers Statistics
        $totalWorkers = Worker::when($departmentId, fn($q) => $q->where('department_id', $departmentId))->count();
        $activeWorkers = Worker::where('status', 'active')->when($departmentId, fn($q) => $q->where('department_id', $departmentId))->count();
        $inactiveWorkers = Worker::where('status', 'inactive')->when($departmentId, fn($q) => $q->where('department_id', $departmentId))->count();
        $resignedWorkers = Worker::where('status', 'resigned')->when($departmentId, fn($q) => $q->where('department_id', $departmentId))->count();

        // Workers by Employment Status
        $permanentWorkers = Worker::where('employment_status', 'permanent')->when($departmentId, fn($q) => $q->where('department_id', $departmentId))->count();
        $contractWorkers = Worker::where('employment_status', 'contract')->when($departmentId, fn($q) => $q->where('department_id', $departmentId))->count();
        $internshipWorkers = Worker::where('employment_status', 'internship')->when($departmentId, fn($q) => $q->where('department_id', $departmentId))->count();

        // Scope helper for attendance queries
        $scopeAttendance = function($query) use ($departmentId) {
            return $departmentId ? $query->whereHas('worker', fn($q) => $q->where('department_id', $departmentId)) : $query;
        };

        // Attendance Statistics (Today)
        $today = Carbon::today();
        $todayAttendance = $scopeAttendance(Attendance::whereDate('created_at', $today))->count();
        $todayPresent = $scopeAttendance(Attendance::whereDate('created_at', $today)
            ->where('status', 'present'))->count();
        $todayLate = $scopeAttendance(Attendance::whereDate('created_at', $today)
            ->where('status', 'late'))->count();
        $todayAbsent = $scopeAttendance(Attendance::whereDate('created_at', $today)
            ->where('status', 'absent'))->count();

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
            ->whereDate('created_at', $today)
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

        // Attendance chart for the last 7 days (labels in Indonesian short form)
        $labels = [];
        $data = [];
        $start = Carbon::today()->subDays(6);
        $dayNames = [0 => 'Min', 1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab'];
        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $labels[] = $dayNames[$date->dayOfWeek] ?? $date->format('D');
            $count = Attendance::whereDate('created_at', $date)
                ->where('status', 'present')
                ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))
                ->count();
            $data[] = $count;
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
            'pendingCheckouts'
        ));
    }

    /**
     * Get monthly attendance statistics for the last 6 months
     */
    private function getMonthlyAttendanceStats(?string $departmentId = null): array
    {
        $stats = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            $present = Attendance::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', 'present')
                ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))
                ->count();

            $late = Attendance::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', 'late')
                ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))
                ->count();

            $absent = Attendance::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', 'absent')
                ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))
                ->count();

            $stats[] = [
                'month' => $month->format('M Y'),
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
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
            'present' => Attendance::whereDate('created_at', $today)->where('status', 'present')
                ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))->count(),
            'late' => Attendance::whereDate('created_at', $today)->where('status', 'late')
                ->when($departmentId, fn($q) => $q->whereHas('worker', fn($w) => $w->where('department_id', $departmentId)))->count(),
            'absent' => Attendance::whereDate('created_at', $today)->where('status', 'absent')
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

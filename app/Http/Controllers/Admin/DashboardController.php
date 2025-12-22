<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {
        $this->middleware('auth');
        // Allow any authenticated user to access dashboard
        // Permission check can be added back if needed: $this->middleware('permission:view-dashboard');
    }

    public function index(Request $request)
    {
        // Check if user should be redirected to employee dashboard
        $user = auth()->user();
        if ($user && $user->roles->isNotEmpty()) {
            $role = $user->roles->first()->name;
            if (in_array($role, ['Employee'])) {
                return redirect()->route('employee.dashboard');
            }
        }

        // ========== STATISTICS ==========
        $statistics = [
            'total_workers' => $this->dashboardService->getTotalWorkers(),
            'active_workers' => $this->dashboardService->getActiveWorkers(),
            'present_today' => $this->dashboardService->getAttendanceStats('today')['on_time'],
            'attendance_rate' => $this->dashboardService->getTotalWorkers() > 0 
                ? round(($this->dashboardService->getAttendanceStats('today')['on_time'] / $this->dashboardService->getTotalWorkers()) * 100, 1)
                : 0,
            'pending_leaves' => $this->dashboardService->getPendingApprovalsCount()['leaves'],
            'pending_overtimes' => $this->dashboardService->getPendingApprovalsCount()['overtimes'],
        ];

        // ========== CHART DATA ==========
        $attendanceChartData = $this->dashboardService->getAttendanceChartData();
        $attendanceChartLabels = array_column($attendanceChartData, 'date');
        $attendanceChartData = array_column($attendanceChartData, 'count');

        // Position Distribution (empty for now, can be implemented later)
        $positionDistribution = [];

        // Recent leaves
        $recentLeaves = LeaveRequest::with(['worker', 'leaveType'])
            ->latest()
            ->take(5)
            ->get();

        // Birthday workers this month
        $birthdayWorkers = [];

        return view('admin.dashboard.index', compact(
            'statistics',
            'attendanceChartLabels',
            'attendanceChartData',
            'positionDistribution',
            'recentLeaves',
            'birthdayWorkers'
        ));
    }

    /**
     * Get monthly attendance statistics for the last 6 months
     */
    private function getMonthlyAttendanceStats(): array
    {
        $stats = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            
            $present = Attendance::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', 'present')
                ->count();
                
            $late = Attendance::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', 'late')
                ->count();
                
            $absent = Attendance::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', 'absent')
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
    private function getLeaveStatsByType(): array
    {
        return DB::table('leave_requests')
            ->join('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id')
            ->select(
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
    private function getDepartmentStats(): array
    {
        return DB::table('workers')
            ->join('departments', 'workers.department_id', '=', 'departments.id')
            ->select(
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
        
        $summary = [
            'total_workers' => Worker::where('status', 'active')->count(),
            'present' => Attendance::whereDate('created_at', $today)->where('status', 'present')->count(),
            'late' => Attendance::whereDate('created_at', $today)->where('status', 'late')->count(),
            'absent' => Attendance::whereDate('created_at', $today)->where('status', 'absent')->count(),
            'on_leave' => LeaveRequest::where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
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
        $count = [
            'leaves' => LeaveRequest::where('status', 'pending')->count(),
            'overtimes' => OvertimeRequest::where('status', 'pending')->count(),
            'total' => LeaveRequest::where('status', 'pending')->count() + 
                      OvertimeRequest::where('status', 'pending')->count(),
        ];
        
        return response()->json($count);
    }
}

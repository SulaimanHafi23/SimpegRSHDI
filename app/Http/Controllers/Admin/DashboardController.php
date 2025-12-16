<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Allow any authenticated user to access dashboard
        // Permission check can be added back if needed: $this->middleware('permission:view-dashboard');
    }

    public function index()
    {
        // Check if user should be redirected to employee dashboard
        $user = auth()->user();
        if ($user && $user->roles->isNotEmpty()) {
            $role = $user->roles->first()->name;
            if (in_array($role, ['employee', 'worker'])) {
                return redirect()->route('employee.dashboard');
            }
        }

        // ========== STATISTICS ==========
        
        // Workers Statistics
        $totalWorkers = Worker::count();
        $activeWorkers = Worker::where('status', 'active')->count();
        $inactiveWorkers = Worker::where('status', 'inactive')->count();
        $resignedWorkers = Worker::where('status', 'resigned')->count();
        
        // Workers by Employment Status
        $permanentWorkers = Worker::where('employment_status', 'permanent')->count();
        $contractWorkers = Worker::where('employment_status', 'contract')->count();
        $internshipWorkers = Worker::where('employment_status', 'internship')->count();

        // Attendance Statistics (Today)
        $today = Carbon::today();
        $todayAttendance = Attendance::whereDate('created_at', $today)->count();
        $todayPresent = Attendance::whereDate('created_at', $today)
            ->where('status', 'present')
            ->count();
        $todayLate = Attendance::whereDate('created_at', $today)
            ->where('status', 'late')
            ->count();
        $todayAbsent = Attendance::whereDate('created_at', $today)
            ->where('status', 'absent')
            ->count();

        // Leave Requests Statistics
        $totalLeaveRequests = LeaveRequest::count();
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        $approvedLeaves = LeaveRequest::where('status', 'approved')->count();
        $rejectedLeaves = LeaveRequest::where('status', 'rejected')->count();

        // Overtime Requests Statistics
        $totalOvertimeRequests = OvertimeRequest::count();
        $pendingOvertimes = OvertimeRequest::where('status', 'pending')->count();
        $approvedOvertimes = OvertimeRequest::where('status', 'approved')->count();
        $rejectedOvertimes = OvertimeRequest::where('status', 'rejected')->count();

        // ========== RECENT ACTIVITIES ==========
        
        // Recent Leave Requests
        $recentLeaves = LeaveRequest::with(['worker', 'leaveType'])
            ->latest()
            ->take(5)
            ->get();

        // Recent Overtime Requests
        $recentOvertimes = OvertimeRequest::with(['worker'])
            ->latest()
            ->take(5)
            ->get();

        // Today's Absences
        $todayAbsences = Attendance::with(['worker'])
            ->whereDate('created_at', $today)
            ->where('status', 'absent')
            ->get();

        // ========== CHARTS DATA ==========
        
        // Monthly Attendance Stats (Last 6 Months)
        $attendanceStats = $this->getMonthlyAttendanceStats();
        
        // Leave Stats by Type
        $leaveStats = $this->getLeaveStatsByType();
        
        // Department Statistics
        $departmentStats = $this->getDepartmentStats();

        // ========== UPCOMING EVENTS ==========
        
        // Upcoming Leaves
        $upcomingLeaves = LeaveRequest::with(['worker', 'leaveType'])
            ->where('status', 'approved')
            ->where('start_date', '>=', $today)
            ->orderBy('start_date')
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact(
            // Workers
            'totalWorkers',
            'activeWorkers',
            'inactiveWorkers',
            'resignedWorkers',
            'permanentWorkers',
            'contractWorkers',
            'internshipWorkers',
            
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
            'leaveStats',
            'departmentStats',
            
            // Upcoming
            'upcomingLeaves'
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

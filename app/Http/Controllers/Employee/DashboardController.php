<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Worker\WorkerService;
use App\Services\Attendance\AttendanceService;
use App\Services\Leave\LeaveRequestService;
use App\Services\Dashboard\EmployeeDashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private readonly WorkerService $workerService,
        private readonly AttendanceService $attendanceService,
        private readonly LeaveRequestService $leaveService,
        private readonly EmployeeDashboardService $dashboardService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:dashboard.employee');
    }

    public function index()
    {
        $user = auth()->user();

        // Get worker data from user relationship
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('login')
                ->with('error', 'Data pekerja tidak ditemukan. Silakan hubungi administrator.');
        }

        // Eager load department to avoid lazy loading in view
        $worker->load('department');

        // Get attendance summary
        $attendanceSummary = $this->dashboardService->getAttendanceSummary($worker->id, 'month');

        // Get attendance chart data
        $attendanceChart = $this->dashboardService->getAttendanceChart($worker->id, 7);

        // Get leave summary
        $leaveSummary = $this->dashboardService->getLeaveSummary($worker->id);

        // Get recent activities
        $recentActivities = $this->dashboardService->getRecentActivities($worker->id, 5);

        // Get upcoming leaves
        $upcomingLeaves = $this->dashboardService->getUpcomingLeaves($worker->id, 5);

        // Get recent leave requests
        $recentLeaves = $this->leaveService->getAll([
            'worker_id' => $worker->id,
            'per_page' => 5,
        ]);

        // Get leave balance with quota
        $leaveBalances = $this->dashboardService->getLeaveBalance($worker->id);

        // Split pending checkout into actionable vs expired window to avoid misleading CTA.
        $pendingCheckouts = $this->attendanceService->getPendingCheckouts($worker->id);
        $pendingCheckout = $pendingCheckouts->firstWhere('can_checkout', true);
        $expiredPendingCheckout = $pendingCheckouts->firstWhere('is_window_expired', true);

        return view('employee.dashboard.index', compact(
            'worker',
            'attendanceSummary',
            'attendanceChart',
            'leaveSummary',
            'recentActivities',
            'upcomingLeaves',
            'recentLeaves',
            'leaveBalances',
            'pendingCheckout',
            'expiredPendingCheckout'
        ));
    }
}

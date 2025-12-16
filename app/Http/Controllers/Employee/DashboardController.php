<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Worker\WorkerService;
use App\Services\Attendance\AttendanceService;
use App\Services\Leave\LeaveRequestService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly WorkerService $workerService,
        private readonly AttendanceService $attendanceService,
        private readonly LeaveRequestService $leaveService
    ) {
        $this->middleware('auth');
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

        // Get attendance summary for current month
        $currentMonth = now()->format('Y-m');
        $attendances = $this->attendanceService->getAll([
            'worker_id' => $worker->id,
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to' => now()->endOfMonth()->format('Y-m-d'),
        ]);
        
        // Calculate attendance statistics
        $attendanceSummary = [
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('is_late', true)->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'total_working_days' => now()->day,
        ];
        
        // Get recent leave requests
        $recentLeaves = $this->leaveService->getAll([
            'worker_id' => $worker->id,
            'per_page' => 5,
        ]);
        
        // Get leave balance (simplified)
        $leaveBalance = [
            'annual_leave' => 12, // Default, should be calculated from database
            'sick_leave' => 12,
            'used_annual' => $this->leaveService->getAll([
                'worker_id' => $worker->id,
                'leave_type' => 'annual',
                'status' => 'approved',
            ])->count(),
            'used_sick' => $this->leaveService->getAll([
                'worker_id' => $worker->id,
                'leave_type' => 'sick',
                'status' => 'approved',
            ])->count(),
        ];

        return view('employee.dashboard.index', compact(
            'worker',
            'attendanceSummary',
            'leaveBalance',
            'recentLeaves'
        ));
    }
}

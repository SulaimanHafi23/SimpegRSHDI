<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\ShiftSwapRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Manager');
    }

    public function index()
    {
        $user = auth()->user();
        $manager = $user->worker;

        if (!$manager || !$manager->department_id) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Anda tidak memiliki departemen yang terdaftar.');
        }

        $departmentId = $manager->department_id;

        // ========== DEPARTMENT STATISTICS ==========
        $departmentWorkers = Worker::where('department_id', $departmentId)
            ->where('status', 'active')
            ->count();

        $departmentWorkersActive = Worker::where('department_id', $departmentId)
            ->where('status', 'active')
            ->count();

        // ========== ATTENDANCE TODAY ==========
        $today = now()->format('Y-m-d');
        $departmentAttendanceToday = Attendance::whereDate('attendance_date', $today)
            ->whereHas('worker', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->count();

        $departmentLateToday = Attendance::whereDate('attendance_date', $today)
            ->where('is_late', true)
            ->whereHas('worker', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->count();

        $departmentAbsentToday = $departmentWorkersActive - $departmentAttendanceToday;

        $attendanceRate = $departmentWorkersActive > 0 
            ? round(($departmentAttendanceToday / $departmentWorkersActive) * 100, 1)
            : 0;

        // ========== PENDING APPROVALS ==========
        $pendingLeaves = LeaveRequest::where('status', 'pending')
            ->whereHas('worker', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->count();

        $pendingOvertimes = OvertimeRequest::where('status', 'pending')
            ->whereHas('worker', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->count();

        $pendingShiftSwaps = ShiftSwapRequest::where('status', 'pending')
            ->where(function ($query) use ($departmentId) {
                $query->whereHas('requester', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                })
                ->orWhereHas('targetWorker', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            })
            ->count();

        // ========== RECENT LEAVE REQUESTS ==========
        $recentLeaves = LeaveRequest::with(['worker', 'leaveType'])
            ->where('status', 'pending')
            ->whereHas('worker', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->latest()
            ->take(5)
            ->get();

        // ========== RECENT OVERTIME REQUESTS ==========
        $recentOvertimes = OvertimeRequest::with('worker')
            ->where('status', 'pending')
            ->whereHas('worker', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->latest()
            ->take(5)
            ->get();

        // ========== RECENT SHIFT SWAP REQUESTS ==========
        $recentShiftSwaps = ShiftSwapRequest::with(['requester', 'targetWorker', 'requesterShift'])
            ->where('status', 'pending')
            ->where(function ($query) use ($departmentId) {
                $query->whereHas('requester', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                })
                ->orWhereHas('targetWorker', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            })
            ->latest()
            ->take(5)
            ->get();

        // ========== ATTENDANCE CHART (Last 7 Days) ==========
        $attendanceChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dayName = $date->format('D');
            
            $present = Attendance::whereDate('attendance_date', $dateStr)
                ->where('status', 'present')
                ->whereHas('worker', function ($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })
                ->count();
            
            $late = Attendance::whereDate('attendance_date', $dateStr)
                ->where('is_late', true)
                ->whereHas('worker', function ($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })
                ->count();
            
            $totalAttendance = Attendance::whereDate('attendance_date', $dateStr)
                ->whereHas('worker', function ($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })
                ->count();
            
            $absent = $departmentWorkersActive - $totalAttendance;
            
            $attendanceChart[] = [
                'date' => $dayName,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
            ];
        }

        // ========== TEAM PERFORMANCE ==========
        $topPerformers = Attendance::select('worker_id', DB::raw('COUNT(*) as total_days'))
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->where('is_late', false)
            ->whereHas('worker', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->groupBy('worker_id')
            ->orderByDesc('total_days')
            ->take(5)
            ->with('worker')
            ->get()
            ->map(function ($attendance) {
                return [
                    'name' => $attendance->worker->name,
                    'days' => $attendance->total_days,
                    'rate' => round(($attendance->total_days / now()->day) * 100, 1),
                ];
            });

        return view('manager.dashboard.index', compact(
            'manager',
            'departmentWorkers',
            'departmentAttendanceToday',
            'departmentLateToday',
            'departmentAbsentToday',
            'attendanceRate',
            'pendingLeaves',
            'pendingOvertimes',
            'pendingShiftSwaps',
            'recentLeaves',
            'recentOvertimes',
            'recentShiftSwaps',
            'attendanceChart',
            'topPerformers'
        ));
    }
}

<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\WorkerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HRDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HR');
    }

    public function index()
    {
        // ========== WORKER STATISTICS ==========
        $totalWorkers = Worker::count();
        $activeWorkers = Worker::where('status', 'active')->count();
        $inactiveWorkers = Worker::where('status', 'inactive')->count();
        $resignedWorkers = Worker::where('status', 'resigned')->count();

        // Workers by Employment Status
        $permanentWorkers = Worker::where('employment_status', 'permanent')->count();
        $contractWorkers = Worker::where('employment_status', 'contract')->count();
        $probationWorkers = Worker::where('employment_status', 'probation')->count();
        $internWorkers = Worker::where('employment_status', 'intern')->count();

        // Workers by Department
        $workersByDepartment = Worker::select('department_id', DB::raw('count(*) as total'))
            ->with('department')
            ->groupBy('department_id')
            ->get()
            ->map(function ($item) {
                return [
                    'department' => $item->department->name ?? 'N/A',
                    'total' => $item->total
                ];
            });

        // ========== ATTENDANCE TODAY ==========
        $today = now()->format('Y-m-d');
        $attendanceToday = Attendance::whereDate('attendance_date', $today)->count();
        $lateToday = Attendance::whereDate('attendance_date', $today)
            ->where('is_late', true)
            ->count();
        $absentToday = $activeWorkers - $attendanceToday;

        // ========== LEAVE REQUESTS ==========
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        $approvedLeavesThisMonth = LeaveRequest::where('status', 'approved')
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->count();

        // Recent Leave Requests
        $recentLeaves = LeaveRequest::with(['worker', 'leaveType'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // ========== OVERTIME REQUESTS ==========
        $pendingOvertimes = OvertimeRequest::where('status', 'pending')->count();
        $approvedOvertimesThisMonth = OvertimeRequest::where('status', 'approved')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();

        $totalOvertimeHours = OvertimeRequest::where('status', 'approved')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('total_hours');

        // Recent Overtime Requests
        $recentOvertimes = OvertimeRequest::with('worker')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // ========== DOCUMENTS ==========
        $pendingDocuments = WorkerDocument::where('status', 'pending')->count();
        $verifiedDocumentsThisMonth = WorkerDocument::where('status', 'verified')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // ========== ATTENDANCE CHART (Last 7 Days) ==========
        $attendanceChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dayName = $date->format('D');
            
            $present = Attendance::whereDate('attendance_date', $dateStr)
                ->where('status', 'present')
                ->count();
            
            $late = Attendance::whereDate('attendance_date', $dateStr)
                ->where('is_late', true)
                ->count();
            
            $absent = $activeWorkers - Attendance::whereDate('attendance_date', $dateStr)->count();
            
            $attendanceChart[] = [
                'date' => $dayName,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
            ];
        }

        // ========== RECENT ACTIVITIES ==========
        $recentActivities = collect([]);

        // Add recent hires
        $recentHires = Worker::where('status', 'active')
            ->latest('join_date')
            ->take(3)
            ->get()
            ->map(function ($worker) {
                return [
                    'type' => 'worker_joined',
                    'icon' => 'user-plus',
                    'color' => 'blue',
                    'title' => 'Pegawai Baru',
                    'description' => $worker->name . ' bergabung sebagai ' . ($worker->position->name ?? 'Pegawai'),
                    'time' => $worker->join_date->diffForHumans(),
                ];
            });

        // Add recent resignations
        $recentResignations = Worker::where('status', 'resigned')
            ->whereNotNull('resign_date')
            ->latest('resign_date')
            ->take(2)
            ->get()
            ->map(function ($worker) {
                return [
                    'type' => 'worker_resigned',
                    'icon' => 'user-minus',
                    'color' => 'red',
                    'title' => 'Pegawai Resign',
                    'description' => $worker->name . ' telah resign',
                    'time' => $worker->resign_date->diffForHumans(),
                ];
            });

        $recentActivities = $recentHires->concat($recentResignations)
            ->sortByDesc('time')
            ->take(5)
            ->values();

        return view('hr.dashboard.index', compact(
            'totalWorkers',
            'activeWorkers',
            'inactiveWorkers',
            'resignedWorkers',
            'permanentWorkers',
            'contractWorkers',
            'probationWorkers',
            'internWorkers',
            'workersByDepartment',
            'attendanceToday',
            'lateToday',
            'absentToday',
            'pendingLeaves',
            'approvedLeavesThisMonth',
            'recentLeaves',
            'pendingOvertimes',
            'approvedOvertimesThisMonth',
            'totalOvertimeHours',
            'recentOvertimes',
            'pendingDocuments',
            'verifiedDocumentsThisMonth',
            'attendanceChart',
            'recentActivities'
        ));
    }
}

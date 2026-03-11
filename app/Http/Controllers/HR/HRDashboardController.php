<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\WorkerDocument;
use App\Services\Attendance\AttendanceService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HRDashboardController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->middleware('auth');
        $this->middleware('role:HR');
        $this->attendanceService = $attendanceService;
    }

    public function index()
    {
        // ========== WORKER STATISTICS ==========
        $workerStats = Worker::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
            SUM(CASE WHEN status = 'resigned' THEN 1 ELSE 0 END) as resigned,
            SUM(CASE WHEN employment_status = 'permanent' THEN 1 ELSE 0 END) as permanent,
            SUM(CASE WHEN employment_status = 'contract' THEN 1 ELSE 0 END) as contract,
            SUM(CASE WHEN employment_status = 'probation' THEN 1 ELSE 0 END) as probation,
            SUM(CASE WHEN employment_status = 'intern' THEN 1 ELSE 0 END) as intern
        ")->first();

        $totalWorkers = $workerStats->total;
        $activeWorkers = $workerStats->active;
        $inactiveWorkers = $workerStats->inactive;
        $resignedWorkers = $workerStats->resigned;
        $permanentWorkers = $workerStats->permanent;
        $contractWorkers = $workerStats->contract;
        $probationWorkers = $workerStats->probation;
        $internWorkers = $workerStats->intern;

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

        // ========== DOCUMENTS ==========
        $pendingDocuments = WorkerDocument::where('status', 'pending')->count();
        $verifiedDocumentsThisMonth = WorkerDocument::where('status', 'verified')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // ========== ATTENDANCE CHART (Last 7 Days) ==========
        $startDate = now()->subDays(6)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $chartData = Attendance::selectRaw("
            attendance_date,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) as late_count,
            COUNT(*) as total_count
        ")
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->groupBy('attendance_date')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->attendance_date)->format('Y-m-d');
            });

        $attendanceChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dayName = $date->format('D');
            $dayData = $chartData->get($dateStr);

            $attendanceChart[] = [
                'date' => $dayName,
                'present' => $dayData->present_count ?? 0,
                'late' => $dayData->late_count ?? 0,
                'absent' => $activeWorkers - ($dayData->total_count ?? 0),
            ];
        }

        // ========== RECENT ACTIVITIES ==========
        $recentActivities = collect([]);

        // Add recent hires
        $recentHires = Worker::where('status', 'active')
            ->latest('hire_date')
            ->take(3)
            ->get()
            ->map(function ($worker) {
                return [
                    'type' => 'worker_joined',
                    'icon' => 'user-plus',
                    'color' => 'blue',
                    'title' => 'Pegawai Baru',
                    'description' => $worker->name . ' bergabung sebagai ' . ($worker->employment_status ?? 'Pegawai'),
                    'time' => $worker->hire_date?->diffForHumans() ?? '-',
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

        // ========== PENDING CHECKOUTS ==========
        $pendingCheckouts = $this->attendanceService->getPendingCheckouts();

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
            'pendingDocuments',
            'verifiedDocumentsThisMonth',
            'attendanceChart',
            'recentActivities',
            'pendingCheckouts'
        ));
    }
}

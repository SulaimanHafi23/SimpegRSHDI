<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\ShiftSwapRequest;
use App\Services\Attendance\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerDashboardController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->middleware('auth');
        $this->middleware('role:Manager');
        $this->attendanceService = $attendanceService;
    }

    public function index()
    {
        $user = auth()->user();
        $user->load('worker.department');
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
        $startDate = now()->subDays(6)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        // Get department worker IDs for subquery
        $departmentWorkerIds = Worker::where('department_id', $departmentId)
            ->where('status', 'active')
            ->pluck('id');

        $chartData = Attendance::selectRaw("
            attendance_date,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) as late_count,
            COUNT(*) as total_count
        ")
            ->whereIn('worker_id', $departmentWorkerIds)
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
                'absent' => $departmentWorkersActive - ($dayData->total_count ?? 0),
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

        // ========== PENDING CHECKOUTS ==========
        // Get workers in this department who need to checkout
        $allPendingCheckouts = $this->attendanceService->getPendingCheckouts();
        $pendingCheckouts = $allPendingCheckouts->filter(function($checkout) use ($departmentWorkerIds) {
            return $departmentWorkerIds->contains($checkout['worker_id']);
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
            'topPerformers',
            'pendingCheckouts'
        ));
    }
}

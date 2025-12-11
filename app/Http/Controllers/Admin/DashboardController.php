<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Absent;
use App\Models\LeaveRequest;
use App\Models\Overtime;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get Statistics
        $statistics = $this->getStatistics();

        // Get Position Distribution
        $positionDistribution = Position::withCount('workers')->get();

        // Get Recent Leaves
        $recentLeaves = LeaveRequest::with(['worker.position'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get Birthday Workers This Month
        $birthdayWorkers = Worker::with('position')
            ->whereMonth('birth_date', Carbon::now()->month)
            ->orderByRaw('DAY(birth_date)')
            ->get();

        // Get Attendance Chart Data
        $attendanceData = $this->getWeeklyAttendanceData();

        return view('admin.dashboard.index', [
            'statistics' => $statistics,
            'positionDistribution' => $positionDistribution,
            'recentLeaves' => $recentLeaves,
            'birthdayWorkers' => $birthdayWorkers,
            'attendanceChartLabels' => $attendanceData['labels'],
            'attendanceChartData' => $attendanceData['data'],
        ]);
    }

    private function getStatistics(): array
    {
        $today = Carbon::today();

        // Total Workers
        $totalWorkers = Worker::count();
        $activeWorkers = Worker::where('status', 'Active')->count();

        // Today's Attendance
        $presentToday = Absent::whereDate('check_in', $today)
            ->whereNotNull('check_in')
            ->count();
        $attendanceRate = $totalWorkers > 0 ? round(($presentToday / $totalWorkers) * 100, 1) : 0;

        // Pending Approvals
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        $pendingOvertimes = Overtime::where('status', 'pending')->count();

        return [
            'total_workers' => $totalWorkers,
            'active_workers' => $activeWorkers,
            'present_today' => $presentToday,
            'attendance_rate' => $attendanceRate,
            'pending_leaves' => $pendingLeaves,
            'pending_overtimes' => $pendingOvertimes,
        ];
    }

    private function getWeeklyAttendanceData(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $labels = [];
        $data = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $labels[] = $date->isoFormat('ddd');
            
            // Get attendance count for this date
            $count = Absent::whereDate('check_in', $date)
                ->whereNotNull('check_in')
                ->count();
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}

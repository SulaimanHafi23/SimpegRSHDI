<?php

namespace App\Services\Dashboard;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get total workers count
     */
    public function getTotalWorkers(): int
    {
        return Worker::count();
    }

    /**
     * Get total active workers (have attendance this month)
     */
    public function getActiveWorkers(): int
    {
        return Worker::whereHas('attendances', function($query) {
            $query->whereMonth('attendance_date', now()->month)
                  ->whereYear('attendance_date', now()->year);
        })->count();
    }

    /**
     * Get attendance statistics for a period
     */
    public function getAttendanceStats(string $period = 'today')
    {
        $query = Attendance::query();

        switch ($period) {
            case 'today':
                $query->whereDate('attendance_date', now());
                break;
            case 'week':
                $query->whereBetween('attendance_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('attendance_date', now()->month)
                      ->whereYear('attendance_date', now()->year);
                break;
            case 'year':
                $query->whereYear('attendance_date', now()->year);
                break;
        }

        // Single grouped query instead of 4 separate cumulative queries
        $counts = (clone $query)->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return [
            'total' => $counts->sum(),
            'on_time' => $counts->get('present', 0),
            'late' => $counts->get('late', 0),
            'absent' => $counts->get('absent', 0),
        ];
    }

    /**
     * Get attendance chart data for last 7 days
     */
    public function getAttendanceChartData()
    {
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        // Single query for all 7 days
        $counts = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->selectRaw("DATE(attendance_date) as date, COUNT(*) as cnt")
            ->groupBy('date')
            ->pluck('cnt', 'date');

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'date' => $date->format('d M'),
                'count' => $counts->get($date->format('Y-m-d'), 0),
            ];
        }
        return $data;
    }

    /**
     * Get monthly attendance chart (last 6 months)
     */
    public function getMonthlyAttendanceChart()
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        // Single query for all 6 months
        $counts = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(attendance_date, '%Y-%m') as ym, COUNT(*) as cnt")
            ->groupBy('ym')
            ->pluck('cnt', 'ym');

        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $data[] = [
                'month' => $date->format('M Y'),
                'count' => $counts->get($date->format('Y-m'), 0),
            ];
        }
        return $data;
    }

    /**
     * Get leave request statistics
     */
    public function getLeaveStats(string $period = 'month')
    {
        $query = LeaveRequest::query();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', now());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        $counts = $query->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return [
            'total' => $counts->sum(),
            'pending' => $counts->get('pending', 0),
            'approved' => $counts->get('approved', 0),
            'rejected' => $counts->get('rejected', 0),
        ];
    }

    /**
     * Get overtime request statistics
     */
    public function getOvertimeStats(string $period = 'month')
    {
        $query = OvertimeRequest::query();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', now());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        $counts = (clone $query)->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $totalHours = (clone $query)->where('status', 'approved')->sum('total_hours');

        return [
            'total' => $counts->sum(),
            'pending' => $counts->get('pending', 0),
            'approved' => $counts->get('approved', 0),
            'rejected' => $counts->get('rejected', 0),
            'total_hours' => $totalHours,
        ];
    }

    /**
     * Get top 10 most active workers (by attendance)
     */
    public function getTopWorkers(int $limit = 10)
    {
        return Worker::withCount(['attendances' => function($query) {
                $query->whereMonth('attendance_date', now()->month)
                      ->whereYear('attendance_date', now()->year);
            }])
            ->orderBy('attendances_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get attendance by status chart data
     */
    public function getAttendanceByStatusChart()
    {
        $counts = Attendance::whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return [
            'on_time' => $counts->get('present', 0),
            'late' => $counts->get('late', 0),
            'absent' => $counts->get('absent', 0),
        ];
    }

    /**
     * Get pending approvals count
     */
    public function getPendingApprovalsCount()
    {
        return [
            'leaves' => LeaveRequest::where('status', 'pending')->count(),
            'overtimes' => OvertimeRequest::where('status', 'pending')->count(),
        ];
    }

    /**
     * Get recent activities (latest attendances, leaves, overtimes)
     */
    public function getRecentActivities(int $limit = 10)
    {
        $activities = [];

        // Recent attendances
        $attendances = Attendance::with('worker')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function($attendance) {
                return [
                    'type' => 'attendance',
                    'title' => $attendance->worker->name . ' check-in',
                    'description' => 'Status: ' . $attendance->status,
                    'time' => $attendance->created_at,
                ];
            });

        // Recent leaves
        $leaves = LeaveRequest::with('worker')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function($leave) {
                return [
                    'type' => 'leave',
                    'title' => $leave->worker->name . ' mengajukan cuti',
                    'description' => 'Status: ' . ucfirst($leave->status),
                    'time' => $leave->created_at,
                ];
            });

        // Merge and sort
        $activities = $attendances->concat($leaves)
            ->sortByDesc('time')
            ->take($limit);

        return $activities->values();
    }
}

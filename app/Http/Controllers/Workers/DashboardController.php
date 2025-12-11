<?php

namespace App\Http\Controllers\Workers;

use App\Http\Controllers\Controller;
use App\Models\Absent;
use App\Models\LeaveRequest;
use App\Models\WorkerShiftSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('home')->with('error', 'Data pegawai tidak ditemukan.');
        }

        // Get Today's Attendance
        $todayAttendance = Absent::where('worker_id', $worker->id)
            ->whereDate('check_in', Carbon::today())
            ->first();

        // Get Monthly Statistics
        $monthlyStats = $this->getMonthlyStats($worker->id);

        // Get Leave Balance - Default 12 days
        $usedLeaves = LeaveRequest::where('worker_id', $worker->id)
            ->where('status', 'approved')
            ->whereYear('start_date', Carbon::now()->year)
            ->sum('total_days');
        $leaveBalance = 12 - $usedLeaves;

        // Get Weekly Schedule
        $weeklySchedule = WorkerShiftSchedule::with('shift')
            ->where('worker_id', $worker->id)
            ->whereBetween('schedule_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->orderBy('schedule_date')
            ->get();

        // Get Recent Requests (Leaves, Overtimes, etc)
        $recentRequests = $this->getRecentRequests($worker->id);

        return view('workers.dashboard.index', [
            'todayAttendance' => $todayAttendance,
            'monthlyStats' => $monthlyStats,
            'leaveBalance' => $leaveBalance,
            'weeklySchedule' => $weeklySchedule,
            'recentRequests' => $recentRequests,
        ]);
    }

    private function getMonthlyStats(string $workerId): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $workingDays = $this->getWorkingDaysInMonth($startOfMonth, $endOfMonth);

        // Count attendance this month
        $attendanceCount = Absent::where('worker_id', $workerId)
            ->whereBetween('check_in', [$startOfMonth, $endOfMonth])
            ->whereNotNull('check_in')
            ->count();

        $attendanceRate = $workingDays > 0 ? round(($attendanceCount / $workingDays) * 100, 1) : 0;

        return [
            'attendance' => $attendanceCount,
            'working_days' => $workingDays,
            'attendance_rate' => $attendanceRate,
        ];
    }

    private function getWorkingDaysInMonth(Carbon $start, Carbon $end): int
    {
        $workingDays = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            // Skip Sundays (0) - adjust based on your business rules
            if ($current->dayOfWeek !== Carbon::SUNDAY) {
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }

    private function getRecentRequests(string $workerId): array
    {
        // Get recent leave requests
        $leaves = LeaveRequest::where('worker_id', $workerId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Format as generic requests
        $requests = [];
        
        foreach ($leaves as $leave) {
            $requests[] = (object)[
                'type' => 'leave',
                'title' => $leave->leave_type . ' - ' . $leave->total_days . ' hari',
                'status' => $leave->status,
                'created_at' => $leave->created_at,
            ];
        }

        return $requests;
    }
}

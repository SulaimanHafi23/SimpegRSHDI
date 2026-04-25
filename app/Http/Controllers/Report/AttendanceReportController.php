<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Worker;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:report.view');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Default date range: current month
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $departmentId = $request->input('department_id');
        $workerId = $request->input('worker_id');
        $status = $request->input('status'); // present, late, absent

        $query = Attendance::with(['worker.user', 'worker.department', 'shift'])
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        // Manager can only see their department
        if ($user->worker) {
            $query->whereHas('worker', function($q) use ($user) {
                $q->where('department_id', $user->worker->department_id);
            });
        } elseif ($departmentId) {
            $query->whereHas('worker', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        // Filter by worker
        if ($workerId) {
            $query->where('worker_id', $workerId);
        }

        // Filter by status
        if ($status === 'late') {
            $query->where('is_late', true);
        } elseif ($status === 'present') {
            $query->whereNotNull('check_in');
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in', 'asc')
            ->paginate(50);

        // Calculate statistics
        $totalAttendances = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->when($departmentId, function($q) use ($departmentId) {
                $q->whereHas('worker', function($subQ) use ($departmentId) {
                    $subQ->where('department_id', $departmentId);
                });
            })
            ->count();

        $lateCount = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->where('is_late', true)
            ->when($departmentId, function($q) use ($departmentId) {
                $q->whereHas('worker', function($subQ) use ($departmentId) {
                    $subQ->where('department_id', $departmentId);
                });
            })
            ->count();

        $activeWorkers = Worker::where('status', 'active')
            ->when($departmentId, function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->count();

        $workingDays = $this->calculateWorkingDays($startDate, $endDate);
        $expectedAttendances = $activeWorkers * $workingDays;
        $absentCount = max(0, $expectedAttendances - $totalAttendances);

        $statistics = [
            'total' => $totalAttendances,
            'late' => $lateCount,
            'absent' => $absentCount,
            'on_time' => $totalAttendances - $lateCount,
            'attendance_rate' => $expectedAttendances > 0 
                ? round(($totalAttendances / $expectedAttendances) * 100, 1) 
                : 0,
        ];

        // Get departments for filter
        $departments = Department::orderBy('name')->get();

        // Get workers for filter
        $workers = Worker::with('user')
            ->where('status', 'active')
            ->when($user->worker, function($q) use ($user) {
                $q->where('department_id', $user->worker->department_id);
            })
            ->orderBy('name')
            ->get();

        return view('reports.attendance.index', compact(
            'attendances',
            'statistics',
            'departments',
            'workers',
            'startDate',
            'endDate',
            'departmentId',
            'workerId',
            'status'
        ));
    }

    /**
     * Calculate working days between two dates (excluding weekends)
     */
    private function calculateWorkingDays($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $workingDays = 0;

        while ($start->lte($end)) {
            if (!$start->isWeekend()) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
    }
}

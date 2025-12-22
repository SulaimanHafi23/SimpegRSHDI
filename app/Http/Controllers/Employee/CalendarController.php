<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Services\Leave\LeaveRequestService;
use App\Services\Overtime\OvertimeRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct(
        protected LeaveRequestService $leaveService,
        protected OvertimeRequestService $overtimeService
    ) {}

    /**
     * Display calendar view
     */
    public function index()
    {
        return view('employee.calendar.index');
    }

    /**
     * Get calendar events (API)
     */
    public function events(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->worker) {
            return response()->json([]);
        }
        
        $workerId = $user->worker->id;
        $start = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end', now()->endOfMonth()->format('Y-m-d'));

        // Get leave requests
        $leaves = $this->leaveService->getAll([
            'worker_id' => $workerId,
            'start_date' => $start,
            'end_date' => $end,
        ]);

        // Get overtime requests
        $overtimes = $this->overtimeService->getAll([
            'worker_id' => $workerId,
            'start_date' => $start,
            'end_date' => $end,
        ]);

        // Get holidays
        $holidays = Holiday::dateRange($start, $end)->get();

        $events = [];

        // Process leaves
        foreach ($leaves as $leave) {
            $events[] = [
                'id' => 'leave-' . $leave->id,
                'type' => 'leave',
                'title' => $leave->leaveType->name ?? 'Cuti',
                'start' => $leave->start_date,
                'end' => Carbon::parse($leave->end_date)->addDay()->format('Y-m-d'), // FullCalendar exclusive end
                'status' => $leave->status,
                'color' => $this->getLeaveColor($leave->status),
                'description' => $leave->reason,
                'days' => $leave->total_days,
            ];
        }

        // Process overtimes
        foreach ($overtimes as $overtime) {
            $events[] = [
                'id' => 'overtime-' . $overtime->id,
                'type' => 'overtime',
                'title' => 'Lembur',
                'start' => $overtime->overtime_date . 'T' . $overtime->start_time,
                'end' => $overtime->overtime_date . 'T' . $overtime->end_time,
                'status' => $overtime->status,
                'color' => $this->getOvertimeColor($overtime->status),
                'description' => $overtime->description,
                'hours' => $overtime->total_hours,
            ];
        }

        // Process holidays
        foreach ($holidays as $holiday) {
            $events[] = [
                'id' => 'holiday-' . $holiday->id,
                'type' => 'holiday',
                'title' => $holiday->name,
                'start' => $holiday->date->format('Y-m-d'),
                'end' => Carbon::parse($holiday->date)->addDay()->format('Y-m-d'),
                'status' => 'holiday',
                'color' => '#dc2626', // red-600
                'description' => $holiday->description,
                'isNational' => $holiday->is_national,
            ];
        }

        return response()->json($events);
    }

    /**
     * Get color based on leave status
     */
    private function getLeaveColor(string $status): string
    {
        return match($status) {
            'approved' => '#10b981', // green
            'pending' => '#f59e0b', // amber
            'rejected' => '#ef4444', // red
            default => '#6b7280', // gray
        };
    }

    /**
     * Get color based on overtime status
     */
    private function getOvertimeColor(string $status): string
    {
        return match($status) {
            'approved' => '#3b82f6', // blue
            'pending' => '#f59e0b', // amber
            'rejected' => '#ef4444', // red
            default => '#6b7280', // gray
        };
    }
}

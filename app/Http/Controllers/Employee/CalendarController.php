<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessTrip;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct() {}

    /**
     * Redirect the old calendar URL to the merged shift schedule page.
     */
    public function index()
    {
        return redirect()
            ->route('employee.shifts.index')
            ->with('info', 'Kalender aktivitas sudah dipindahkan ke halaman Jadwal Kerja Saya.');
    }

    /**
     * Get calendar events (API)
     */
    public function events(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->worker) {
            return response()->json([]);
        }

        $workerId = $user->worker->id;
        $start = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end', now()->endOfMonth()->format('Y-m-d'));

        // Get leave requests
        $leaves = LeaveRequest::with(['worker', 'leaveType', 'approver'])
            ->where('worker_id', $workerId)
            ->where('start_date', '>=', $start)
            ->where('end_date', '<=', $end)
            ->latest('start_date')
            ->paginate(15);

        // Get holidays
        $holidays = Holiday::dateRange($start, $end)->get();

        // Get business trips
        $businessTrips = BusinessTrip::where('worker_id', $workerId)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)
                          ->where('end_date', '>=', $end);
                    });
            })
            ->get();

        $events = [];

        // Process leaves
        foreach ($leaves as $leave) {
            $events[] = [
                'id' => 'leave-' . $leave->id,
                'type' => 'leave',
                'title' => $leave->leaveType->name ?? 'Cuti',
                'start' => Carbon::parse($leave->start_date)->toDateString(),
                'end' => Carbon::parse($leave->end_date)->addDay()->toDateString(), // FullCalendar exclusive end
                'status' => $leave->status,
                'color' => $this->getLeaveColor($leave->status),
                'description' => $leave->reason,
                'days' => $leave->total_days,
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

        // Process business trips
        foreach ($businessTrips as $trip) {
            $events[] = [
                'id' => 'business-trip-' . $trip->id,
                'type' => 'business-trip',
                'title' => '✈️ Perjalanan Dinas: ' . $trip->destination,
                'start' => Carbon::parse($trip->start_date)->toDateString(),
                'end' => Carbon::parse($trip->end_date)->addDay()->toDateString(), // FullCalendar exclusive end
                'status' => $trip->status,
                'color' => $this->getBusinessTripColor($trip->status),
                'description' => $trip->purpose,
                'destination' => $trip->destination,
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
     * Get color based on business trip status
     */
    private function getBusinessTripColor(string $status): string
    {
        return match($status) {
            'approved' => '#8b5cf6', // purple - perjalanan dinas disetujui
            'pending' => '#f59e0b', // amber - menunggu persetujuan
            'rejected' => '#ef4444', // red - ditolak
            'completed' => '#10b981', // green - selesai
            default => '#6b7280', // gray
        };
    }
}

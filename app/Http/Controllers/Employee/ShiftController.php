<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\WorkerShift\WorkerShiftService;
use App\Services\ShiftOverride\ShiftOverrideService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function __construct(
        protected WorkerShiftService $workerShiftService,
        protected ShiftOverrideService $shiftOverrideService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display employee's shift schedule
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        // Get month and year from request or use current
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        
        $date = Carbon::create($year, $month, 1);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Get worker shift (regular schedule) - get active one
        $workerShift = $this->workerShiftService->getActiveByWorkerId($worker->id);

        // Get shift overrides for this month
        $shiftOverrides = $this->shiftOverrideService->getAll([
            'worker_id' => $worker->id,
            'date_from' => $startOfMonth->format('Y-m-d'),
            'date_to' => $endOfMonth->format('Y-m-d'),
        ]);

        // Build calendar data
        $calendar = $this->buildCalendar($startOfMonth, $endOfMonth, $workerShift, $shiftOverrides);

        return view('employee.shifts.index', compact(
            'calendar',
            'workerShift',
            'date',
            'month',
            'year'
        ));
    }

    /**
     * Show shift detail for specific date
     */
    public function show(Request $request)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $date = Carbon::parse($request->date);

        // Get worker shift - get active one
        $workerShift = $this->workerShiftService->getActiveByWorkerId($worker->id);

        // Check for override on this date
        $override = $this->shiftOverrideService->getAll([
            'worker_id' => $worker->id,
            'date_from' => $date->format('Y-m-d'),
            'date_to' => $date->format('Y-m-d'),
        ])->first();

        return view('employee.shifts.show', compact(
            'date',
            'workerShift',
            'override'
        ));
    }

    /**
     * Build calendar array with shift information
     */
    private function buildCalendar($startOfMonth, $endOfMonth, $workerShift, $shiftOverrides)
    {
        $calendar = [];
        $current = $startOfMonth->copy();

        // Start from the first day of the week containing the 1st (Sunday)
        $current->startOfWeek(Carbon::SUNDAY);

        // Build 6 weeks (42 days) to ensure full calendar
        for ($week = 0; $week < 6; $week++) {
            $calendar[$week] = [];
            
            for ($day = 0; $day < 7; $day++) {
                $dayData = [
                    'date' => $current->copy(),
                    'isCurrentMonth' => $current->month == $startOfMonth->month,
                    'isToday' => $current->isToday(),
                    'shift' => null,
                    'isOverride' => false,
                ];

                // Check if there's an override for this date
                $override = $shiftOverrides->first(function ($item) use ($current) {
                    return Carbon::parse($item->override_date)->isSameDay($current);
                });

                if ($override) {
                    $dayData['shift'] = $override->shift;
                    $dayData['isOverride'] = true;
                } elseif ($workerShift) {
                    // Get regular shift for this day
                    if ($workerShift->pattern_type === 'fixed') {
                        // Fixed pattern - same shift every day
                        $dayData['shift'] = $workerShift->shift;
                    } elseif ($workerShift->pattern_type === 'custom' && $workerShift->custom_working_days) {
                        // Custom pattern - shift only applies on specified working days
                        $dayOfWeek = $current->dayOfWeekIso; // 1 (Monday) to 7 (Sunday)
                        $workingDays = $workerShift->custom_working_days ?? [];
                        
                        if (in_array($dayOfWeek, $workingDays, true)) {
                            $dayData['shift'] = $workerShift->shift;
                        }
                    } elseif ($workerShift->pattern_type === 'rotating' && $workerShift->rotating_days) {
                        // Rotating pattern - different shift per day of week
                        $dayOfWeek = $current->dayOfWeekIso; // 1 (Monday) to 7 (Sunday)
                        $shiftId = $workerShift->rotating_days[$dayOfWeek] ?? null;
                        
                        if ($shiftId) {
                            // Load shift relationship if not already loaded
                            $dayData['shift'] = \App\Models\Shift::find($shiftId);
                        }
                    }
                }

                $calendar[$week][] = $dayData;
                $current->addDay();
            }
        }

        return $calendar;
    }
}

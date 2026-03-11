<?php

namespace App\Services\Attendance;

use App\Models\Worker;
use App\Models\User;
use App\Notifications\AttendanceReminderNotification;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class AttendanceReminderService
{
    /**
     * Send check-in reminders to workers who have shifts today
     */
    public function sendCheckInReminders(): int
    {
        $today = today();
        $count = 0;

        // Get all workers who have shifts scheduled for today
        $workers = Worker::with(['user', 'workerShifts.shift'])
            ->whereHas('workerShifts', function ($query) use ($today) {
                $query->where('date', $today)
                    ->where('status', 'active');
            })
            ->get();

        foreach ($workers as $worker) {
            // Get the shift for today
            $todayShift = $worker->workerShifts
                ->where('date', $today)
                ->where('status', 'active')
                ->first();

            if ($todayShift && $worker->user) {
                // Get shift start time
                $shift = $todayShift->shift;
                $shiftStartTime = Carbon::parse($shift->start_time);
                $reminderTime = $shiftStartTime->copy()->subHours(1); // Send reminder 1 hour before shift

                // Only send if current time is close to reminder time (within 5 minutes)
                if (now()->diffInMinutes($reminderTime, false) <= 5 && now()->diffInMinutes($reminderTime, false) >= -5) {
                    Notification::send($worker->user, new AttendanceReminderNotification(
                        'check_in',
                        $shift->name,
                        $shiftStartTime,
                        $worker->name
                    ));
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Send check-out reminders to workers who have shifts ending soon
     */
    public function sendCheckOutReminders(): int
    {
        $today = today();
        $count = 0;

        // Get all workers who have active attendance (checked in but not checked out)
        $attendances = \App\Models\Attendance::with(['worker.user', 'worker.workerShifts.shift'])
            ->whereDate('created_at', $today)
            ->whereNull('check_out')
            ->get();

        foreach ($attendances as $attendance) {
            if ($attendance->worker->user) {
                // Get the shift for today
                $todayShift = $attendance->worker->workerShifts
                    ->where('date', $today)
                    ->where('status', 'active')
                    ->first();

                if ($todayShift) {
                    $shift = $todayShift->shift;
                    $shiftEndTime = Carbon::parse($shift->end_time);
                    $reminderTime = $shiftEndTime->copy()->subMinutes(30); // Send reminder 30 minutes before shift end

                    // Only send if current time is close to reminder time (within 5 minutes)
                    if (now()->diffInMinutes($reminderTime, false) <= 5 && now()->diffInMinutes($reminderTime, false) >= -5) {
                        Notification::send($attendance->worker->user, new AttendanceReminderNotification(
                            'check_out',
                            $shift->name,
                            $shiftEndTime,
                            $attendance->worker->name
                        ));
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Send check-in reminder for a specific worker
     */
    public function sendCheckInReminderToWorker(string $workerId, ?string $shiftName = null, ?Carbon $shiftTime = null): bool
    {
        $worker = Worker::with('user')->find($workerId);

        if (!$worker || !$worker->user) {
            return false;
        }

        // If shift details not provided, get from today's shift
        if (!$shiftName || !$shiftTime) {
            $todayShift = $worker->workerShifts
                ->where('date', today())
                ->where('status', 'active')
                ->first();

            if ($todayShift) {
                $shiftName = $todayShift->shift->name;
                $shiftTime = Carbon::parse($todayShift->shift->start_time);
            }
        }

        Notification::send($worker->user, new AttendanceReminderNotification(
            'check_in',
            $shiftName,
            $shiftTime,
            $worker->name
        ));

        return true;
    }

    /**
     * Send check-out reminder for a specific worker
     */
    public function sendCheckOutReminderToWorker(string $workerId, ?string $shiftName = null, ?Carbon $shiftTime = null): bool
    {
        $worker = Worker::with('user')->find($workerId);

        if (!$worker || !$worker->user) {
            return false;
        }

        // If shift details not provided, get from today's shift
        if (!$shiftName || !$shiftTime) {
            $todayShift = $worker->workerShifts
                ->where('date', today())
                ->where('status', 'active')
                ->first();

            if ($todayShift) {
                $shiftName = $todayShift->shift->name;
                $shiftTime = Carbon::parse($todayShift->shift->end_time);
            }
        }

        Notification::send($worker->user, new AttendanceReminderNotification(
            'check_out',
            $shiftName,
            $shiftTime,
            $worker->name
        ));

        return true;
    }
}

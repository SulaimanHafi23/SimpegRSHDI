<?php

namespace App\Services\Master;

use App\Models\Holiday;
use App\Models\User;
use App\Notifications\HolidayNotification;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class HolidayNotificationService
{
    /**
     * Send notifications for upcoming holidays (7 days in advance)
     */
    public function sendUpcomingHolidayNotifications(): int
    {
        $count = 0;

        // Get holidays in the next 7 days
        $startDate = today();
        $endDate = today()->addDays(7);

        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])
            ->where('notify_users', true) // Only if notification is enabled
            ->get();

        if ($holidays->isEmpty()) {
            return 0;
        }

        // Get all active users
        $users = User::where('is_active', true)
            ->orWhere('status', 'active')
            ->get();

        foreach ($holidays as $holiday) {
            // Check if we've already sent notification today for this holiday
            if ($this->hasNotificationBeenSent($holiday->id, 'upcoming')) {
                continue;
            }

            foreach ($users as $user) {
                Notification::send($user, new HolidayNotification($holiday, 'upcoming'));
                $count++;
            }

            // Mark notification as sent
            $this->markNotificationAsSent($holiday->id, 'upcoming');
        }

        return $count;
    }

    /**
     * Send reminders for tomorrow's holiday
     */
    public function sendTomorrowHolidayReminders(): int
    {
        $count = 0;
        $tomorrow = today()->addDay();

        $holiday = Holiday::whereDate('date', $tomorrow)
            ->where('notify_users', true)
            ->first();

        if (!$holiday) {
            return 0;
        }

        // Check if reminder has already been sent today
        if ($this->hasNotificationBeenSent($holiday->id, 'reminder')) {
            return 0;
        }

        // Get all active users
        $users = User::where('is_active', true)
            ->orWhere('status', 'active')
            ->get();

        foreach ($users as $user) {
            Notification::send($user, new HolidayNotification($holiday, 'reminder'));
            $count++;
        }

        // Mark reminder as sent
        $this->markNotificationAsSent($holiday->id, 'reminder');

        return $count;
    }

    /**
     * Send notification for a specific holiday to all users
     */
    public function sendHolidayNotificationToAllUsers(string $holidayId, string $type = 'upcoming'): int
    {
        $count = 0;

        $holiday = Holiday::find($holidayId);
        if (!$holiday) {
            return 0;
        }

        $users = User::where('is_active', true)
            ->orWhere('status', 'active')
            ->get();

        foreach ($users as $user) {
            Notification::send($user, new HolidayNotification($holiday, $type));
            $count++;
        }

        return $count;
    }

    /**
     * Send notification for a specific holiday to a specific user
     */
    public function sendHolidayNotificationToUser(string $holidayId, string $userId, string $type = 'upcoming'): bool
    {
        $holiday = Holiday::find($holidayId);
        $user = User::find($userId);

        if (!$holiday || !$user) {
            return false;
        }

        Notification::send($user, new HolidayNotification($holiday, $type));
        return true;
    }

    /**
     * Check if notification for a holiday has already been sent
     */
    protected function hasNotificationBeenSent(string $holidayId, string $type): bool
    {
        $cacheKey = "holiday_notification_sent_{$holidayId}_{$type}_" . today()->format('Y-m-d');
        return \Illuminate\Support\Facades\Cache::has($cacheKey);
    }

    /**
     * Mark a holiday notification as sent
     */
    protected function markNotificationAsSent(string $holidayId, string $type): void
    {
        $cacheKey = "holiday_notification_sent_{$holidayId}_{$type}_" . today()->format('Y-m-d');
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, 86400); // Cache for 24 hours
    }
}

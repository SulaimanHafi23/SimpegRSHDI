<?php

namespace App\Console\Commands;

use App\Services\Master\HolidayNotificationService;
use Illuminate\Console\Command;

class SendHolidayNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-holiday-notifications
                            {--type=all : Type of notification to send (upcoming, reminder, or all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send holiday notifications to all users';

    /**
     * Execute the console command.
     */
    public function handle(HolidayNotificationService $holidayService): int
    {
        $type = $this->option('type');

        $this->info('Sending holiday notifications...');

        $upcomingCount = 0;
        $reminderCount = 0;

        if ($type === 'upcoming' || $type === 'all') {
            $upcomingCount = $holidayService->sendUpcomingHolidayNotifications();
            $this->info("Sent {$upcomingCount} upcoming holiday notifications.");
        }

        if ($type === 'reminder' || $type === 'all') {
            $reminderCount = $holidayService->sendTomorrowHolidayReminders();
            $this->info("Sent {$reminderCount} holiday reminder notifications.");
        }

        $total = $upcomingCount + $reminderCount;
        $this->info("Total holiday notifications sent: {$total}");

        return Command::SUCCESS;
    }
}

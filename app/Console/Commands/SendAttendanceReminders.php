<?php

namespace App\Console\Commands;

use App\Services\Attendance\AttendanceReminderService;
use Illuminate\Console\Command;

class SendAttendanceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-attendance-reminders
                            {--type=all : Type of reminder to send (check_in, check_out, or all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send attendance check-in/check-out reminders to workers';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceReminderService $reminderService): int
    {
        $type = $this->option('type');

        $this->info('Sending attendance reminders...');

        $checkInCount = 0;
        $checkOutCount = 0;

        if ($type === 'check_in' || $type === 'all') {
            $checkInCount = $reminderService->sendCheckInReminders();
            $this->info("Sent {$checkInCount} check-in reminders.");
        }

        if ($type === 'check_out' || $type === 'all') {
            $checkOutCount = $reminderService->sendCheckOutReminders();
            $this->info("Sent {$checkOutCount} check-out reminders.");
        }

        $total = $checkInCount + $checkOutCount;
        $this->info("Total reminders sent: {$total}");

        return Command::SUCCESS;
    }
}

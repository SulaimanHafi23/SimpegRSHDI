<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto checkout workers who forgot to checkout, runs every hour
Schedule::command('attendance:auto-checkout --hours=3')->hourly();

// Send attendance reminders - check-in reminder at 06:00, check-out reminder at 15:00
Schedule::command('notifications:send-attendance-reminders --type=check_in')->dailyAt('06:00');
Schedule::command('notifications:send-attendance-reminders --type=check_out')->dailyAt('15:30');

// Send holiday notifications
Schedule::command('notifications:send-holiday-notifications --type=upcoming')->dailyAt('08:00');
Schedule::command('notifications:send-holiday-notifications --type=reminder')->dailyAt('18:00');

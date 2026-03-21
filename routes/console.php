<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto checkout workers who forgot to checkout, runs every hour
Schedule::command('attendance:auto-checkout --hours=3')->hourly();
Schedule::command('notifications:send-holiday-notifications --type=upcoming')->dailyAt('08:00');
Schedule::command('notifications:send-holiday-notifications --type=reminder')->dailyAt('18:00');

// Check for expiring documents and send notifications
// Runs daily at 9:00 AM to notify about documents expiring in next 30 days
Schedule::command('documents:check-expiration --days=30 --send')->dailyAt('09:00');


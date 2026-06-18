<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Update invoice statuses daily at midnight
Schedule::command('invoices:update-statuses')->daily();

// Send payment reminders daily at 9 AM
Schedule::command('reminders:send')->dailyAt('09:00');

// Run database backup daily at 2 AM
Schedule::command('backup:database')->dailyAt('02:00');

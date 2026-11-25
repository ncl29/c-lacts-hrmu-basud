<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('leave:send-reminders')->dailyAt('09:00');

// Weekly Disaster Recovery & Database Backup (Sundays at 1:00 AM, retains last 4 weekly archives)
Schedule::command('backup:run --type=db --retention=4')->weeklyOn(0, '01:00');

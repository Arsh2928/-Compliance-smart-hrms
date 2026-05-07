<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('hr:evaluate-monthly-performance')
    ->monthlyOn(1, '00:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/cron-performance.log'));

Schedule::command('payroll:generate')
    ->monthlyOn(1, '01:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/cron-payroll.log'));

Schedule::command('hr:check-contracts')->dailyAt('08:00')->withoutOverlapping();

Schedule::command('hr:auto-assign-tasks')
    ->weeklyOn(5, '23:00') // Every Friday at 11:00 PM
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/cron-tasks.log'));

Schedule::command('hr:sync-live-scores')
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/cron-livescores.log'));

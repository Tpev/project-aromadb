<?php


use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\SendDailyKpiEmail;
use App\Console\Commands\CheckMilestones;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
// Schedule the daily KPI email at 6 AM
Schedule::command(SendDailyKpiEmail::class)->dailyAt('6:00');

// Schedule the milestone check command to run hourly
Schedule::command(CheckMilestones::class)->hourly();


<?php


use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\SendDailyKpiEmail;
use App\Console\Commands\CheckMilestones;
use App\Console\Commands\SendAppointmentReminders;
use App\Console\Commands\SendOneHourReminder;
use App\Console\Commands\FetchFacebookMetrics;
use App\Console\Commands\UpdateLicenseStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
// Schedule the daily KPI email at 6 AM
Schedule::command(SendDailyKpiEmail::class)->dailyAt('6:00');

// Schedule the milestone check command to run hourly
Schedule::command(CheckMilestones::class)->hourly();

// Schedule the appointment reminder email command to run every hour
Schedule::command(SendAppointmentReminders::class)->hourly();

// Schedule the 1-hour appointment reminder command
Schedule::command(SendOneHourReminder::class)->hourly();

// Fetch FB data
Schedule::command(FetchFacebookMetrics::class)->hourly();


// Expired Trial
Schedule::command(UpdateLicenseStatus::class)->daily();



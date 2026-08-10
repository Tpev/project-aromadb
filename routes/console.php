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
use App\Console\Commands\ReleaseStaleGiftVoucherBookingReservations;
use App\Console\Commands\SyncStripeFinance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Console\Commands\ImportGoogleEvents; 



Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
// Schedule the daily KPI email at 6 AM
Schedule::command(SendDailyKpiEmail::class)->dailyAt('6:00');

// Schedule the milestone check command to run hourly
Schedule::command(CheckMilestones::class)->hourly();

// Schedule the appointment reminder email command to run every hour
Schedule::command(SendAppointmentReminders::class)->hourly()->withoutOverlapping(30);

// Schedule the 1-hour appointment reminder command
Schedule::command(SendOneHourReminder::class)->everyTenMinutes()->withoutOverlapping(10);

Schedule::command('appointments:cleanup-cancelled-google-events --limit=100')
    ->everyFifteenMinutes()
    ->withoutOverlapping(20);

// Event reminder
Schedule::command('events:send-reminders')->everyMinute();

// Expired Trial
Schedule::command(UpdateLicenseStatus::class)->daily();

//  import Google toutes les 10 min  
Schedule::command(ImportGoogleEvents::class)
		->everyFiveMinutes()
		->withoutOverlapping(20);

Schedule::command(ReleaseStaleGiftVoucherBookingReservations::class)
    ->everyFiveMinutes()
    ->withoutOverlapping();

Schedule::command('appointments:expire-pending-payments --limit=500')
    ->everyFiveMinutes()
    ->withoutOverlapping(10);

// Finance Stripe: réconciliation cashflow/frais/payouts.
Schedule::command(SyncStripeFinance::class)
    ->dailyAt('03:10')
    ->withoutOverlapping(120);

// SUPER PDP: réception des factures d'achat.
Schedule::command('super-pdp:sync-received-invoices')
    ->hourly()
    ->withoutOverlapping(30);

// Rebuild public sitemap every night
Schedule::command('sitemap:generate')
    ->dailyAt('02:00')
    ->withoutOverlapping();

// Parcours d'offre: la commande reste sans effet tant que les flags du pilote sont coupés.
Schedule::command('offer-journeys:dispatch-due')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('offer-journeys:reconcile-conversions --days=35')
    ->dailyAt('04:20')
    ->withoutOverlapping(120);

Schedule::command('offer-journeys:apply-retention --limit=1000')
    ->dailyAt('04:50')
    ->withoutOverlapping(120);

Schedule::command('offer-journeys:dispatch-campaigns --limit=20')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('offer-journeys:dispatch-abandonments --limit=100')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Sensitive account exports are temporary and stay outside the public disk.
Schedule::command('account:exports:purge --days=7')
    ->dailyAt('03:40')
    ->withoutOverlapping();

<?php

namespace App\Console\Commands;

use App\Jobs\SendAppointmentReminderJob;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SendOneHourReminder extends Command
{
    protected $signature = 'email:send-one-hour-reminder';
    protected $description = 'Place les rappels de rendez-vous à une heure dans la file';

    public function handle(): int
    {
        if (!Schema::hasColumn('appointments', 'reminder_1h_queued_at')) {
            $this->warn('Migration des rappels non appliquée : aucun email placé en file.');
            return self::FAILURE;
        }

        $start = Carbon::now()->addMinutes(50)->startOfMinute();
        $end = Carbon::now()->addMinutes(70)->endOfMinute();
        $queued = 0;

        Appointment::query()
            ->whereBetween('appointment_date', [$start, $end])
            ->notCancelled()
            ->whereNull('reminder_1h_sent_at')
            ->where(function ($query) {
                $query->whereNull('reminder_1h_queued_at')
                    ->orWhere('reminder_1h_queued_at', '<', now()->subHours(2));
            })
            ->orderBy('id')
            ->chunkById(200, function ($appointments) use (&$queued) {
                foreach ($appointments as $appointment) {
                    $claimedAt = now();
                    $claimed = Appointment::query()
                        ->whereKey($appointment->id)
                        ->notCancelled()
                        ->whereNull('reminder_1h_sent_at')
                        ->where(function ($query) {
                            $query->whereNull('reminder_1h_queued_at')
                                ->orWhere('reminder_1h_queued_at', '<', now()->subHours(2));
                        })
                        ->update(['reminder_1h_queued_at' => $claimedAt]);

                    if ($claimed === 1) {
                        SendAppointmentReminderJob::dispatch(
                            $appointment->id,
                            '1h',
                            $appointment->appointment_date->toIso8601String(),
                            $claimedAt->toIso8601String()
                        );
                        $queued++;
                    }
                }
            });

        $this->info("Rappels 1 h placés en file : {$queued}");
        return self::SUCCESS;
    }
}

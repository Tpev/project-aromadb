<?php

namespace App\Console\Commands;

use App\Mail\EventReminderClientMail;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';
    protected $description = 'Send event reservation reminders (24h and 1h before start)';

    public function handle(): int
    {
        $this->send24hReminders();
        $this->send1hReminders();

        return self::SUCCESS;
    }

    private function send24hReminders(): void
    {
        // Window: 24h ± 5 minutes
        $from = now()->addHours(24)->subMinutes(5);
        $to   = now()->addHours(24)->addMinutes(5);

        $reservations = Reservation::query()
            ->whereNull('reminder_24h_sent_at')
            ->whereHas('event', function ($q) use ($from, $to) {
                $q->whereBetween('start_date_time', [$from, $to]);
            })
            ->with(['event.user'])
            ->get();

        foreach ($reservations as $reservation) {
            $event = $reservation->event;

            if (!$event || empty($reservation->email)) {
                continue;
            }

            // Queue email
            Mail::to($reservation->email)->queue(
                new EventReminderClientMail($event, $reservation, '24h')
            );

            $reservation->forceFill([
                'reminder_24h_sent_at' => now(),
            ])->save();
        }

        $this->info("Event 24h reminders sent: {$reservations->count()}");
    }

    private function send1hReminders(): void
    {
        // Window: 1h ± 5 minutes
        $from = now()->addHour()->subMinutes(5);
        $to   = now()->addHour()->addMinutes(5);

        $reservations = Reservation::query()
            ->whereNull('reminder_1h_sent_at')
            ->whereHas('event', function ($q) use ($from, $to) {
                $q->whereBetween('start_date_time', [$from, $to]);
            })
            ->with(['event.user'])
            ->get();

        foreach ($reservations as $reservation) {
            $event = $reservation->event;

            if (!$event || empty($reservation->email)) {
                continue;
            }

            Mail::to($reservation->email)->queue(
                new EventReminderClientMail($event, $reservation, '1h')
            );

            $reservation->forceFill([
                'reminder_1h_sent_at' => now(),
            ])->save();
        }

        $this->info("Event 1h reminders sent: {$reservations->count()}");
    }
}

<?php

namespace App\Jobs;

use App\Mail\NewReservationNotification;
use App\Mail\ReservationConfirmation;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendPaidEventConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $reservationId) {}

    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function handle(): void
    {
        Cache::lock('event-confirmation:'.$this->reservationId, 120)->block(10, function () {
            $reservation = Reservation::with('event.user')->find($this->reservationId);
            if (! $reservation || $reservation->status !== 'paid' || ! $reservation->isEmailEligible()) {
                return;
            }
            if (! $reservation->confirmation_sent_at && filled($reservation->email)) {
                Mail::to($reservation->email)->send(new ReservationConfirmation($reservation));
                $reservation->forceFill(['confirmation_sent_at' => now()])->save();
            }
            if (! $reservation->therapist_notification_sent_at && filled($reservation->event->user?->email)) {
                Mail::to($reservation->event->user->email)->send(new NewReservationNotification($reservation));
                $reservation->forceFill(['therapist_notification_sent_at' => now()])->save();
            }
        });
    }
}

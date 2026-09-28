<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Services\EventPaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;

class ExpireEventReservationCheckout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $reservationId) {}

    public function backoff(): array
    {
        return [30, 120, 600, 1800];
    }

    public function handle(): void
    {
        $reservation = Reservation::with('event.user')->find($this->reservationId);
        if (! $reservation?->cancelled_at || ! $reservation->stripe_session_id || $reservation->status === 'paid') {
            return;
        }

        $account = $reservation->event?->user?->stripe_account_id;
        if (! $account) {
            return;
        }

        $stripe = new StripeClient(config('services.stripe.secret'));
        $options = ['stripe_account' => $account];
        $session = $stripe->checkout->sessions->retrieve($reservation->stripe_session_id, [], $options);
        if ($session->status === 'open') {
            try {
                $session = $stripe->checkout->sessions->expire($session->id, [], $options);
            } catch (InvalidRequestException $exception) {
                // Checkout may have completed between retrieval and expiration.
                $session = $stripe->checkout->sessions->retrieve($reservation->stripe_session_id, [], $options);
                if ($session->status === 'open') {
                    throw $exception;
                }
            }
        }

        if ($session->payment_status === 'paid' && $session->payment_intent) {
            app(EventPaymentService::class)->confirm($reservation->id, $account,
                (int) $session->amount_total, (string) $session->currency,
                (string) $session->payment_intent, (string) $session->id);
        }
    }
}

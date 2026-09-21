<?php

namespace App\Services;

use App\Jobs\SendPaidEventConfirmationJob;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class EventPaymentService
{
    public function confirm(int $reservationId, string $accountId, int $amountCents, string $currency, string $paymentIntentId, ?string $sessionId = null): bool
    {
        $reservation = DB::transaction(function () use ($reservationId, $accountId, $amountCents, $currency, $paymentIntentId, $sessionId) {
            $reservation = Reservation::with('event.user')->lockForUpdate()->find($reservationId);
            if (! $reservation || ! $reservation->event || ! $reservation->event->user
                || $accountId === '' || $paymentIntentId === ''
                || ! hash_equals((string) $reservation->event->user->stripe_account_id, $accountId)
                || (int) round((float) $reservation->amount_ttc * 100) !== $amountCents
                || strtolower($reservation->currency ?? 'eur') !== strtolower($currency)
                || ($sessionId && $reservation->stripe_session_id && $reservation->stripe_session_id !== $sessionId)
                || ($reservation->stripe_payment_intent_id && $reservation->stripe_payment_intent_id !== $paymentIntentId)
                || ! in_array($reservation->status, ['pending_payment', 'canceled', 'paid'], true)) {
                return null;
            }

            // Only a newly verified payment owns email delivery. Historical
            // paid rows already had their confirmations queued by the old controller.
            // "canceled" is also set by the legacy checkout return URL; a later
            // verified payment still needs fulfillment, as with the old success handler.
            if ($reservation->status !== 'paid') {
                $reservation->payment_confirmation_requested_at = now();
            }
            $reservation->fill(['status' => 'paid', 'stripe_payment_intent_id' => $paymentIntentId])->save();

            return $reservation;
        });

        if (! $reservation) {
            return false;
        }

        if ($reservation->payment_confirmation_requested_at && (! $reservation->confirmation_sent_at || ! $reservation->therapist_notification_sent_at)) {
            SendPaidEventConfirmationJob::dispatch($reservation->id)->afterCommit();
        }

        return true;
    }

    public function handleWebhook(object $event, string $accountId): bool
    {
        $object = $event->data->object ?? null;
        if (! $object) {
            return false;
        }
        $reservationId = (int) ($object->metadata->reservation_id ?? 0);
        if (! $reservationId) {
            return false;
        }

        if (in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            if (($object->payment_status ?? '') === 'paid') {
                $paymentId = is_object($object->payment_intent ?? null) ? $object->payment_intent->id : ($object->payment_intent ?? '');
                $this->confirm($reservationId, $accountId, (int) ($object->amount_total ?? 0), (string) ($object->currency ?? ''), (string) $paymentId, (string) $object->id);
            }

            return true;
        }

        // Also covers checkout sessions created before session-level metadata was added.
        if ($event->type === 'payment_intent.succeeded') {
            $this->confirm($reservationId, $accountId, (int) ($object->amount_received ?? 0), (string) ($object->currency ?? ''), (string) $object->id);

            return true;
        }

        return false;
    }
}

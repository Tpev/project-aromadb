<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Mail\Events\MessageSending;

class EventMailDeliveryGuard
{
    public const RESERVATION_HEADER = 'X-Olithea-Reservation-Id';

    public const MESSAGE_HEADER = 'X-Olithea-Event-Message';

    public function handle(MessageSending $event): ?bool
    {
        $id = $event->message->getHeaders()->get(self::RESERVATION_HEADER);
        $type = $event->message->getHeaders()->get(self::MESSAGE_HEADER)?->getBodyAsString();
        if (! $id || ! $type) {
            return null;
        }

        $reservation = Reservation::with('event')->find((int) $id->getBodyAsString());
        if (! $reservation || ! $reservation->isEmailEligible()) {
            return false;
        }

        if (str_starts_with($type, 'reminder:')) {
            $minutes = now()->diffInMinutes($reservation->event->start_date_time, false);
            $allowed = $type === 'reminder:1h'
                ? $minutes >= 45 && $minutes <= 75
                : $minutes >= 23 * 60 && $minutes <= 25 * 60;

            return $allowed ? null : false;
        }

        return null;
    }
}

<?php

namespace App\Services;

use App\Jobs\ExpireEventReservationCheckout;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventReservationService
{
    public function reserve(Event $event, array $attributes, bool $preventDuplicate = false): Reservation
    {
        return DB::transaction(function () use ($event, $attributes, $preventDuplicate) {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);

            if ($preventDuplicate && $event->reservations()->active()
                ->whereRaw('LOWER(email) = ?', [strtolower(trim($attributes['email']))])->exists()) {
                throw ValidationException::withMessages(['client_profile_id' => 'Ce client est déjà inscrit à cet événement.']);
            }

            if ($event->limited_spot && $event->reservations()->active()->count() >= (int) $event->number_of_spot) {
                throw ValidationException::withMessages(['reservation' => 'Il n’y a plus de place disponible pour cet événement.']);
            }

            return $event->reservations()->create(array_merge(['status' => 'confirmed'], $attributes));
        });
    }

    public function cancel(Reservation $reservation, User $actor): Reservation
    {
        return DB::transaction(function () use ($reservation, $actor) {
            $reservation = Reservation::with('event')->lockForUpdate()->findOrFail($reservation->id);
            abort_unless((int) $reservation->event->user_id === (int) $actor->id, 403);

            if (! $reservation->cancelled_at) {
                $reservation->forceFill(['cancelled_at' => now(), 'cancelled_by' => $actor->id])->save();
            }

            if ($reservation->stripe_session_id && $reservation->status !== 'paid') {
                ExpireEventReservationCheckout::dispatch($reservation->id)->afterCommit();
            }

            return $reservation;
        });
    }
}

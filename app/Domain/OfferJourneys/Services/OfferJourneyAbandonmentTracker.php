<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyAbandonmentCandidate;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OfferJourneyAbandonmentTracker
{
    private const STARTED_STATUSES = ['pending', 'pending_payment', 'awaiting_payment', 'created', 'incomplete'];
    private const COMPLETED_STATUSES = ['confirmed', 'paid', 'scheduled', 'completed'];
    private const CANCELLED_STATUSES = ['cancelled', 'canceled', 'refunded', 'annulée', 'annulee', 'remboursé', 'remboursee'];

    public function sync(Model $model, int $userId, ?string $email, string $journeySourceType, ?int $journeySourceId, string $rawStatus): void
    {
        if (! config('offer_journeys.abandonment_reminders_enabled', false)
            || $userId <= 0
            || ! $model->getKey()
            || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $status = Str::lower(trim($rawStatus));
        $key = 'oj:abandonment:'.Str::snake(class_basename($model)).':'.$model->getKey();
        $candidate = OfferJourneyAbandonmentCandidate::query()->where('idempotency_key', $key)->first();

        if (in_array($status, self::COMPLETED_STATUSES, true)) {
            $candidate?->update(['state' => 'completed', 'completed_at' => now(), 'stop_reason' => 'conversion_confirmed']);
            return;
        }
        if (in_array($status, self::CANCELLED_STATUSES, true)) {
            $candidate?->update(['state' => 'cancelled', 'cancelled_at' => now(), 'stop_reason' => 'business_object_cancelled']);
            return;
        }
        if (! in_array($status, self::STARTED_STATUSES, true) || $candidate) {
            return;
        }

        $contact = OfferJourneyContact::query()
            ->where('user_id', $userId)
            ->where('email_normalized', Str::lower(trim((string) $email)))
            ->first();
        if (! $contact) {
            return;
        }

        $entry = OfferJourneyEntry::query()
            ->where('offer_journey_contact_id', $contact->id)
            ->whereHas('journey', function ($query) use ($journeySourceType, $journeySourceId) {
                $query->where('source_type', $journeySourceType);
                if ($journeySourceId !== null) {
                    $query->where('source_id', $journeySourceId);
                }
            })
            ->latest('last_activity_at')
            ->first();
        if (! $entry) {
            return;
        }

        OfferJourneyAbandonmentCandidate::query()->create([
            'user_id' => $userId,
            'offer_journey_id' => $entry->offer_journey_id,
            'offer_journey_contact_id' => $contact->id,
            'offer_journey_entry_id' => $entry->id,
            'source_type' => $model::class,
            'source_id' => $model->getKey(),
            'state' => 'started',
            'started_at' => now(),
            'reminder_due_at' => now()->addHours(max(1, (int) config('offer_journeys.abandonment_delay_hours', 24))),
            'idempotency_key' => $key,
        ]);
    }
}

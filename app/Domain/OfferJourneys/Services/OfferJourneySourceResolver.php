<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Models\DigitalTraining;
use App\Models\Event;
use App\Models\User;

class OfferJourneySourceResolver
{
    public function publicActionUrl(OfferJourney $journey, User $therapist, bool $published = false): ?string
    {
        [$sourceType, $sourceId] = $this->sourceReference($journey, $published);

        if ($sourceType === 'gift_voucher' && $journey->objective === 'gift_voucher') {
            return route('gift-vouchers.checkout.show', ['slug' => $therapist->slug]);
        }

        if (! $sourceType || ! $sourceId) {
            return null;
        }

        return match ($sourceType) {
            'product' => route('appointments.createPatient', ['therapist' => $therapist->id])
                .'?product_id='.$sourceId,
            'event' => $this->eventUrl($sourceId, $therapist),
            'digital_training' => $this->trainingUrl($sourceId, $therapist),
            'gift_voucher' => route('gift-vouchers.checkout.show', ['slug' => $therapist->slug]),
            default => null,
        };
    }

    public function sourceAvailable(OfferJourney $journey, User $therapist, bool $published = false): bool
    {
        [$sourceType, $sourceId] = $this->sourceReference($journey, $published);

        if ($sourceType === 'gift_voucher' && $journey->objective === 'gift_voucher') {
            return true;
        }

        if (! $sourceType || ! $sourceId) {
            return in_array($journey->objective, ['lead_magnet', 'contact_request'], true);
        }

        return match ($sourceType) {
            'product' => $therapist->products()->whereKey($sourceId)->exists(),
            'event' => Event::query()->whereKey($sourceId)->where('user_id', $therapist->id)->exists(),
            'digital_training' => DigitalTraining::query()->whereKey($sourceId)->where('user_id', $therapist->id)->exists(),
            'gift_voucher' => true,
            default => false,
        };
    }

    public function sourceLabel(OfferJourney $journey, User $therapist, bool $published = false): ?string
    {
        [$sourceType, $sourceId] = $this->sourceReference($journey, $published);

        if ($sourceType === 'gift_voucher' && $journey->objective === 'gift_voucher') {
            return 'Votre page de bons cadeaux';
        }

        if (! $sourceType || ! $sourceId) {
            return null;
        }

        return match ($sourceType) {
            'product' => $therapist->products()->whereKey($sourceId)->value('name'),
            'event' => Event::query()->whereKey($sourceId)->where('user_id', $therapist->id)->value('name'),
            'digital_training' => DigitalTraining::query()->whereKey($sourceId)->where('user_id', $therapist->id)->value('title'),
            default => null,
        };
    }

    private function eventUrl(int $sourceId, User $therapist): ?string
    {
        $event = Event::query()->whereKey($sourceId)->where('user_id', $therapist->id)->first();

        return $event ? route('events.reserve.create', $event) : null;
    }

    private function trainingUrl(int $sourceId, User $therapist): ?string
    {
        $training = DigitalTraining::query()
            ->whereKey($sourceId)
            ->where('user_id', $therapist->id)
            ->first();

        return $training ? route('digital-trainings.public.show', $training) : null;
    }

    private function sourceReference(OfferJourney $journey, bool $published): array
    {
        if (! $published) {
            return [$journey->source_type, $journey->source_id ? (int) $journey->source_id : null];
        }

        $journey->loadMissing('publishedVersion');
        $snapshot = $journey->publishedVersion?->snapshot_json ?? [];

        return [
            $snapshot['source_type'] ?? null,
            isset($snapshot['source_id']) ? (int) $snapshot['source_id'] : null,
        ];
    }
}

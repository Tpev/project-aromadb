<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Models\DigitalTraining;
use App\Models\Event;
use App\Models\User;

class OfferJourneySourceResolver
{
    public function publicActionUrl(OfferJourney $journey, User $therapist): ?string
    {
        if ($journey->source_type === 'gift_voucher' && $journey->objective === 'gift_voucher') {
            return route('gift-vouchers.checkout.show', ['slug' => $therapist->slug]);
        }

        if (! $journey->source_type || ! $journey->source_id) {
            return null;
        }

        return match ($journey->source_type) {
            'product' => route('appointments.createPatient', ['therapist' => $therapist->id])
                .'?product_id='.$journey->source_id,
            'event' => $this->eventUrl($journey, $therapist),
            'digital_training' => $this->trainingUrl($journey, $therapist),
            'gift_voucher' => route('gift-vouchers.checkout.show', ['slug' => $therapist->slug]),
            default => null,
        };
    }

    public function sourceAvailable(OfferJourney $journey, User $therapist): bool
    {
        if ($journey->source_type === 'gift_voucher' && $journey->objective === 'gift_voucher') {
            return true;
        }

        if (! $journey->source_type || ! $journey->source_id) {
            return in_array($journey->objective, ['lead_magnet', 'contact_request'], true);
        }

        return match ($journey->source_type) {
            'product' => $therapist->products()->whereKey($journey->source_id)->exists(),
            'event' => Event::query()->whereKey($journey->source_id)->where('user_id', $therapist->id)->exists(),
            'digital_training' => DigitalTraining::query()->whereKey($journey->source_id)->where('user_id', $therapist->id)->exists(),
            'gift_voucher' => true,
            default => false,
        };
    }

    private function eventUrl(OfferJourney $journey, User $therapist): ?string
    {
        $event = Event::query()->whereKey($journey->source_id)->where('user_id', $therapist->id)->first();

        return $event ? route('events.reserve.create', $event) : null;
    }

    private function trainingUrl(OfferJourney $journey, User $therapist): ?string
    {
        $training = DigitalTraining::query()
            ->whereKey($journey->source_id)
            ->where('user_id', $therapist->id)
            ->first();

        return $training ? route('digital-trainings.public.show', $training) : null;
    }
}

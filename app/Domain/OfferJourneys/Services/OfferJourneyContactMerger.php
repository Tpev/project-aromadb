<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use Illuminate\Support\Facades\DB;

class OfferJourneyContactMerger
{
    public function merge(OfferJourneyContact $target, OfferJourneyContact $duplicate): OfferJourneyContact
    {
        abort_unless($target->user_id === $duplicate->user_id && $target->id !== $duplicate->id, 422);

        return DB::transaction(function () use ($target, $duplicate) {
            $target->tags()->syncWithoutDetaching($duplicate->tags()->pluck('offer_journey_tags.id'));

            foreach ($duplicate->entries()->get() as $entry) {
                $existing = $target->entries()->where('offer_journey_id', $entry->offer_journey_id)->first();
                if ($existing) {
                    if (($entry->last_activity_at?->timestamp ?? 0) > ($existing->last_activity_at?->timestamp ?? 0)) {
                        $existing->update(['last_activity_at' => $entry->last_activity_at]);
                    }
                    $entry->delete();
                } else {
                    $entry->update(['offer_journey_contact_id' => $target->id]);
                }
            }

            foreach ([
                'offer_journey_consents', 'offer_journey_tasks', 'offer_journey_contact_activities',
                'offer_journey_message_deliveries', 'offer_journey_form_answers', 'offer_journey_suppressions',
                'offer_journey_automation_runs', 'offer_journey_conversions', 'offer_journey_abandonment_candidates',
            ] as $table) {
                DB::table($table)->where('offer_journey_contact_id', $duplicate->id)->update(['offer_journey_contact_id' => $target->id]);
            }

            $target->update([
                'client_profile_id' => $target->client_profile_id ?: $duplicate->client_profile_id,
                'first_name' => $target->first_name ?: $duplicate->first_name,
                'last_name' => $target->last_name ?: $duplicate->last_name,
                'phone' => $target->phone ?: $duplicate->phone,
                'phone_normalized' => $target->phone_normalized ?: $duplicate->phone_normalized,
                'city' => $target->city ?: $duplicate->city,
                'postal_code' => $target->postal_code ?: $duplicate->postal_code,
                'last_activity_at' => collect([$target->last_activity_at, $duplicate->last_activity_at])->filter()->max(),
            ]);
            $duplicate->forceDelete();
            $target->activities()->create([
                'type' => 'contacts_merged',
                'title' => 'Deux fiches marketing ont été fusionnées',
                'metadata' => ['merged_contact_id' => $duplicate->id],
                'occurred_at' => now(),
            ]);

            return $target->fresh();
        });
    }
}

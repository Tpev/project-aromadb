<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationRun;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyEntry;
use App\Support\OfferJourneys\OfferJourneyAccess;

class OfferJourneyAutomationStarter
{
    public function start(OfferJourney $journey, OfferJourneyContact $contact, OfferJourneyEntry $entry): ?OfferJourneyAutomationRun
    {
        if (! app(OfferJourneyAccess::class)->automationAvailableFor($journey->user)) {
            return null;
        }

        $automation = $journey->automations()
            ->where('status', 'active')
            ->whereNotNull('published_version_id')
            ->with('publishedVersion.nodes')
            ->first();
        $firstNode = $automation?->publishedVersion?->nodes
            ->sortBy('position_y')
            ->first(fn ($node) => (bool) ($node->config_json['is_enabled'] ?? false));

        if (! $automation || ! $firstNode || ! $journey->published_version_id) {
            return null;
        }

        $latestRun = OfferJourneyAutomationRun::query()
            ->where('offer_journey_automation_id', $automation->id)
            ->where('offer_journey_contact_id', $contact->id)
            ->latest('started_at')
            ->first();
        if ($latestRun && $automation->reentry_mode === 'once') {
            return $latestRun;
        }
        if ($latestRun && $automation->reentry_mode === 'after_delay'
            && $latestRun->started_at->greaterThan(now()->subDays(max(1, (int) $automation->reentry_delay_days)))) {
            return $latestRun;
        }

        $idempotencyKey = 'oj:'.$automation->id.':contact:'.$contact->id
            .($automation->reentry_mode === 'once' ? '' : ':'.now()->format('YmdHis'));

        return OfferJourneyAutomationRun::query()->firstOrCreate([
            'idempotency_key' => $idempotencyKey,
        ], [
            'offer_journey_automation_id' => $automation->id,
            'offer_journey_automation_version_id' => $automation->published_version_id,
            'offer_journey_version_id' => $journey->published_version_id,
            'offer_journey_contact_id' => $contact->id,
            'offer_journey_entry_id' => $entry->id,
            'status' => 'running',
            'current_node_key' => $firstNode->node_key,
            'started_at' => now(),
            'next_action_at' => now()->addMinutes((int) ($firstNode->config_json['delay_minutes'] ?? 0)),
        ]);
    }
}

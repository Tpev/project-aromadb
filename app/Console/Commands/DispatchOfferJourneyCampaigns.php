<?php

namespace App\Console\Commands;

use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Domain\OfferJourneys\Services\OfferJourneyCampaignSender;
use Illuminate\Console\Command;

class DispatchOfferJourneyCampaigns extends Command
{
    protected $signature = 'offer-journeys:dispatch-campaigns {--limit=20}';
    protected $description = 'Envoie les campagnes de parcours arrivees a echeance.';

    public function handle(OfferJourneyCampaignSender $sender): int
    {
        if (! config('offer_journeys.campaigns_enabled', false)) {
            return self::SUCCESS;
        }

        OfferJourneyMessageCampaign::query()
            ->where('status', 'processing')
            ->where('processing_started_at', '<=', now()->subMinutes(15))
            ->update(['status' => 'scheduled', 'processing_started_at' => null]);

        OfferJourneyMessageCampaign::query()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit(max(1, (int) $this->option('limit')))
            ->pluck('id')
            ->each(fn ($id) => $sender->send((int) $id));

        return self::SUCCESS;
    }
}

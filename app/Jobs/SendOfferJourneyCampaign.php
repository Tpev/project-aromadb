<?php

namespace App\Jobs;

use App\Domain\OfferJourneys\Services\OfferJourneyCampaignSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOfferJourneyCampaign implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;
    public int $uniqueFor = 900;

    public function __construct(public readonly int $campaignId)
    {
        $this->onQueue('default');
    }

    public function uniqueId(): string
    {
        return 'offer-journey-campaign-'.$this->campaignId;
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(OfferJourneyCampaignSender $sender): void
    {
        $sender->send($this->campaignId);
    }
}

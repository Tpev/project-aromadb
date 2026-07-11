<?php

namespace App\Jobs;

use App\Domain\OfferJourneys\Services\OfferJourneyDeliverabilityEventIngestor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOfferJourneySesEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public readonly array $message)
    {
        $this->onQueue('default');
    }

    public function backoff(): array
    {
        return [30, 120, 600, 1800];
    }

    public function handle(OfferJourneyDeliverabilityEventIngestor $ingestor): void
    {
        if (! (bool) config('offer_journeys.deliverability.enabled', false)) {
            return;
        }

        $ingestor->ingest($this->message);
    }
}

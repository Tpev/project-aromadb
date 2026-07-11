<?php

namespace App\Jobs;

use App\Domain\OfferJourneys\Services\OfferJourneyAutomationProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOfferJourneyAutomationRun implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public int $uniqueFor = 300;

    public function __construct(public readonly int $runId)
    {
        $this->onQueue('default');
    }

    public function uniqueId(): string
    {
        return 'offer-journey-run-'.$this->runId;
    }

    public function handle(OfferJourneyAutomationProcessor $processor): void
    {
        $processor->process($this->runId);
    }
}

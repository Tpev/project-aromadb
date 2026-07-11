<?php

namespace App\Console\Commands;

use App\Domain\OfferJourneys\Models\OfferJourneyAutomationRun;
use App\Jobs\ProcessOfferJourneyAutomationRun;
use Illuminate\Console\Command;

class DispatchOfferJourneyAutomations extends Command
{
    protected $signature = 'offer-journeys:dispatch-due';
    protected $description = 'Place les actions de parcours arrivées à échéance dans la file d’attente';

    public function handle(): int
    {
        if (! config('offer_journeys.enabled') || ! config('offer_journeys.automation_enabled')) {
            return self::SUCCESS;
        }

        OfferJourneyAutomationRun::query()
            ->where('status', 'running')
            ->whereNotNull('next_action_at')
            ->where('next_action_at', '<=', now())
            ->orderBy('next_action_at')
            ->limit(100)
            ->pluck('id')
            ->each(fn (int $runId) => ProcessOfferJourneyAutomationRun::dispatch($runId));

        return self::SUCCESS;
    }
}

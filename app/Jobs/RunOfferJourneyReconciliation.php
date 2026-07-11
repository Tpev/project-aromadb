<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class RunOfferJourneyReconciliation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $days = 35,
        public readonly bool $dryRun = false
    ) {
    }

    public function handle(): void
    {
        Artisan::call('offer-journeys:reconcile-conversions', array_filter([
            '--days' => max(1, min(365, $this->days)),
            '--dry-run' => $this->dryRun ?: null,
        ], fn ($value): bool => $value !== null));
    }
}

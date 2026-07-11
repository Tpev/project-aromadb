<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyAutomationRun;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfferJourneySafeRetry
{
    public function retry(OfferJourneyAutomationRun $run): void
    {
        DB::transaction(function () use ($run): void {
            $run = OfferJourneyAutomationRun::query()->lockForUpdate()->findOrFail($run->id);
            if ($run->status !== 'failed') {
                throw ValidationException::withMessages(['run' => 'Seule une execution en echec peut etre relancee.']);
            }

            $delivery = OfferJourneyMessageDelivery::query()
                ->where('offer_journey_automation_run_id', $run->id)
                ->where('node_key', $run->current_node_key)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if (! $delivery || $delivery->status !== 'failed' || $delivery->sent_at || $delivery->delivered_at) {
                throw ValidationException::withMessages(['run' => 'Cette execution ne peut pas etre relancee sans risque de doublon.']);
            }

            $delivery->update([
                'status' => 'retry_pending',
                'failed_at' => null,
                'failure_reason' => null,
            ]);
            $run->update([
                'status' => 'running',
                'last_error' => null,
                'next_action_at' => now(),
                'exited_at' => null,
                'exit_reason' => null,
            ]);
        });
    }
}

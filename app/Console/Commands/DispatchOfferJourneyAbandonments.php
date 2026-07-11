<?php

namespace App\Console\Commands;

use App\Domain\OfferJourneys\Models\OfferJourneyAbandonmentCandidate;
use App\Domain\OfferJourneys\Services\OfferJourneyAbandonmentReminder;
use Illuminate\Console\Command;

class DispatchOfferJourneyAbandonments extends Command
{
    protected $signature = 'offer-journeys:dispatch-abandonments {--limit=100}';
    protected $description = 'Envoie au maximum une relance pour les demandes réellement commencees puis abandonnees.';

    public function handle(OfferJourneyAbandonmentReminder $reminder): int
    {
        if (! config('offer_journeys.abandonment_reminders_enabled', false)) {
            return self::SUCCESS;
        }

        OfferJourneyAbandonmentCandidate::query()->where('state', 'started')->where('reminder_due_at', '<=', now())
            ->orderBy('reminder_due_at')->limit(max(1, (int) $this->option('limit')))->pluck('id')
            ->each(fn ($id) => $reminder->send((int) $id));

        return self::SUCCESS;
    }
}

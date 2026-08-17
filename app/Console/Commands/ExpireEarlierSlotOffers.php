<?php

namespace App\Console\Commands;

use App\Services\AppointmentEarlierSlotService;
use Illuminate\Console\Command;

class ExpireEarlierSlotOffers extends Command
{
    protected $signature = 'appointments:expire-earlier-slot-offers {--limit=500}';

    protected $description = 'Expire les propositions de créneaux antérieurs et purge leur ancien historique';

    public function handle(AppointmentEarlierSlotService $service): int
    {
        $result = $service->expireAndPurge(max(1, (int) $this->option('limit')));
        $this->info("Propositions expirées : {$result['expired']}; historiques purgés : {$result['purged']}.");

        return self::SUCCESS;
    }
}

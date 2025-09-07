<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\PracticeLocation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillPracticeLocations extends Command
{
    protected $signature = 'app:backfill-practice-locations';
    protected $description = 'Crée un lieu primaire par user et rattache les RDV "cabinet" existants.';

    public function handle(): int
    {
        DB::transaction(function () {
            // 1) Créer un lieu primaire si adresse société connue
            User::query()->chunkById(200, function ($users) {
                foreach ($users as $u) {
                    $hasPrimary = $u->practiceLocations()->where('is_primary', true)->exists();
                    if ($hasPrimary) continue;

                    $addr = $u->company_address ?? null; // déjà utilisée dans ton code actuel
                    if (!$addr) continue;

                    // Split très simple; tu peux adapter si tu stockes différemment
                    $pl = new PracticeLocation([
                        'label'         => 'Cabinet principal',
                        'address_line1' => $addr,
                        'country'       => 'FR',
                        'is_primary'    => true,
                    ]);
                    $u->practiceLocations()->save($pl);
                }
            });

            // 2) Rattacher les RDV "Dans le cabinet" au lieu primaire (si dispo)
            Appointment::with(['product','user'])
                ->whereNull('practice_location_id')
                ->chunkById(200, function ($apps) {
                    foreach ($apps as $a) {
                        $p = $a->product;
                        if (!$p) continue;

                        // Produit "dans le cabinet" ?
                        $isCabinet = (bool)($p->dans_le_cabinet ?? $p->dans_le_cabinet ?? false);
                        if (!$isCabinet) continue;

                        $primary = $a->user?->practiceLocations()->where('is_primary', true)->first();
                        if ($primary) {
                            $a->practice_location_id = $primary->id;
                            $a->saveQuietly();
                        }
                    }
                });
        });

        $this->info('Backfill terminé.');
        return self::SUCCESS;
    }
}

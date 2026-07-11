<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyPipelineStage;
use App\Models\User;

class OfferJourneyPipeline
{
    private const DEFAULT_STAGES = [
        ['name' => 'Nouveau contact', 'slug' => 'nouveau-contact', 'system_key' => 'new', 'color' => 'olive'],
        ['name' => 'À qualifier', 'slug' => 'a-qualifier', 'system_key' => 'qualify', 'color' => 'amber'],
        ['name' => 'Échange en cours', 'slug' => 'echange-en-cours', 'system_key' => 'contacted', 'color' => 'blue'],
        ['name' => 'Rendez-vous proposé', 'slug' => 'rendez-vous-propose', 'system_key' => 'proposed', 'color' => 'indigo'],
        ['name' => 'Réservé ou acheté', 'slug' => 'reserve-ou-achete', 'system_key' => 'converted', 'color' => 'green'],
        ['name' => 'Pas maintenant', 'slug' => 'pas-maintenant', 'system_key' => 'not_now', 'color' => 'gray'],
    ];

    public function ensureDefaults(User $user): void
    {
        foreach (self::DEFAULT_STAGES as $position => $stage) {
            OfferJourneyPipelineStage::query()->firstOrCreate(
                ['user_id' => $user->id, 'system_key' => $stage['system_key']],
                [...$stage, 'position' => $position]
            );
        }
    }
}

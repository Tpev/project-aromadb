<?php

namespace App\Console\Commands;

use App\Domain\OfferJourneys\Services\OfferJourneyRetentionService;
use Illuminate\Console\Command;

class ApplyOfferJourneyRetention extends Command
{
    protected $signature = 'offer-journeys:apply-retention
        {--dry-run : Afficher les volumes sans modifier les donnees}
        {--user= : Limiter a un praticien}
        {--limit=500 : Limite par categorie et par execution}';

    protected $description = 'Applique la politique de conservation des donnees marketing des parcours';

    public function handle(OfferJourneyRetentionService $retention): int
    {
        $result = $retention->apply(
            $this->option('user') ? (int) $this->option('user') : null,
            (int) $this->option('limit'),
            (bool) $this->option('dry-run')
        );

        $this->table(['Categorie', 'Nombre'], collect($result)->except('applied')->map(fn ($count, $category) => [$category, $count])->values()->all());
        $this->info($result['applied'] ? 'Politique appliquee.' : 'Simulation uniquement; aucune donnee modifiee.');

        return self::SUCCESS;
    }
}

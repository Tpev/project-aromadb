<?php

namespace App\Console\Commands;

use App\Services\StripeFinanceSyncService;
use Illuminate\Console\Command;

class SyncStripeFinance extends Command
{
    protected $signature = 'stripe-finance:sync {--days=730 : Fenêtre de réconciliation en jours} {--max=5000 : Maximum par type de ressource}';

    protected $description = 'Synchronise les clients, abonnements, factures, frais, transactions et payouts Stripe pour le reporting finance.';

    public function handle(StripeFinanceSyncService $sync): int
    {
        if (!$sync->isConfigured()) {
            $this->error('STRIPE_FINANCE_SECRET ou STRIPE_SECRET est manquant.');
            return self::FAILURE;
        }

        $days = max(1, (int) $this->option('days'));
        $max = max(100, (int) $this->option('max'));

        $this->info("Synchronisation Stripe Finance sur {$days} jours...");

        $summary = $sync->syncAll($days, $max);

        foreach ($summary as $type => $count) {
            $this->line("- {$type}: {$count}");
        }

        $this->info('Synchronisation terminée.');

        return self::SUCCESS;
    }
}

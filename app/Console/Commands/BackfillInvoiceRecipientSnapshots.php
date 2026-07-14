<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\InvoiceActivityService;
use App\Services\InvoiceRecipientSnapshotService;
use Illuminate\Console\Command;

class BackfillInvoiceRecipientSnapshots extends Command
{
    protected $signature = 'invoices:backfill-recipient-snapshots
        {--dry-run : Affiche le nombre de factures concernées sans les modifier}
        {--chunk=200 : Nombre de factures traitées par lot}';

    protected $description = 'Fige les coordonnées actuelles sur les anciennes factures sans snapshot.';

    public function handle(
        InvoiceRecipientSnapshotService $snapshots,
        InvoiceActivityService $activity
    ): int {
        $query = Invoice::query()
            ->whereIn('type', ['invoice', 'credit_note'])
            ->whereNull('recipient_snapshot')
            ->orderBy('id');
        $count = (clone $query)->count();

        $this->info($count.' document(s) sans snapshot destinataire.');

        if ($this->option('dry-run') || $count === 0) {
            $this->line($this->option('dry-run') ? 'Simulation terminée. Aucune donnée modifiée.' : 'Aucune action nécessaire.');

            return self::SUCCESS;
        }

        $processed = 0;
        $query->chunkById(max(1, (int) $this->option('chunk')), function ($invoices) use ($snapshots, $activity, &$processed) {
            foreach ($invoices as $invoice) {
                $snapshots->capture($invoice, true);
                $activity->record(
                    $invoice,
                    'recipient_snapshot_backfilled',
                    'Coordonnées destinataire figées depuis les données disponibles lors de la reprise.'
                );
                $processed++;
            }
        });

        $this->info($processed.' document(s) mis à jour.');
        $this->warn('Les données historiques exactes antérieures ne peuvent pas être reconstruites automatiquement.');

        return self::SUCCESS;
    }
}

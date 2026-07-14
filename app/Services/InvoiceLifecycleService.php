<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\User;

class InvoiceLifecycleService
{
    public function __construct(
        private readonly InvoiceRecipientSnapshotService $snapshots,
        private readonly InvoiceActivityService $activity,
    ) {}

    public function finalize(Invoice $invoice, ?User $actor = null, string $event = 'finalized'): Invoice
    {
        if (! $invoice->finalized_at) {
            // A draft snapshot may be refreshed until the document is issued.
            $this->snapshots->capture($invoice, true);
            $invoice->forceFill(['finalized_at' => now()])->saveQuietly();
            $this->activity->record(
                $invoice,
                $event,
                'Facture finalisée et informations du destinataire figées.',
                $actor
            );
        } else {
            // Repairs finalized legacy documents that have no snapshot yet.
            $this->snapshots->capture($invoice);
        }

        return $invoice->refresh();
    }
}

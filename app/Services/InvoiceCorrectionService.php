<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceCorrectionService
{
    public function __construct(
        private readonly InvoiceActivityService $activity,
        private readonly InvoiceLifecycleService $lifecycle,
    ) {}

    public function create(Invoice $original, string $kind, string $reason, User $actor): Invoice
    {
        $reason = trim($reason);

        if (($original->type ?? 'invoice') !== 'invoice') {
            throw ValidationException::withMessages([
                'correction_kind' => 'Seule une facture peut être corrigée.',
            ]);
        }

        if (! in_array($kind, ['credit_note', 'replacement'], true)) {
            throw ValidationException::withMessages([
                'correction_kind' => 'Type de correction invalide.',
            ]);
        }

        if ($reason === '') {
            throw ValidationException::withMessages([
                'correction_reason' => 'Le motif de la correction est obligatoire.',
            ]);
        }

        return DB::transaction(function () use ($original, $kind, $reason, $actor) {
            $lockedOriginal = Invoice::query()
                ->with(['items', 'clientProfile.company', 'corporateClient'])
                ->lockForUpdate()
                ->findOrFail($original->id);

            $hasCreditNote = $lockedOriginal->corrections()
                ->where('type', 'credit_note')
                ->exists();
            $hasReplacement = $lockedOriginal->corrections()
                ->where('correction_kind', 'replacement')
                ->exists();

            if ($kind === 'credit_note' && $hasCreditNote) {
                throw ValidationException::withMessages([
                    'correction_kind' => 'Un avoir existe déjà pour cette facture.',
                ]);
            }

            if ($kind === 'replacement' && $hasReplacement) {
                throw ValidationException::withMessages([
                    'correction_kind' => 'Une facture rectificative existe déjà pour cette facture.',
                ]);
            }

            if ($kind === 'replacement' && $lockedOriginal->hasPositiveNetReceipt() && ! $hasCreditNote) {
                throw ValidationException::withMessages([
                    'correction_kind' => 'Créez d’abord un avoir pour cette facture encaissée.',
                ]);
            }

            $lastInvoice = Invoice::query()
                ->where('user_id', $lockedOriginal->user_id)
                ->whereNotNull('invoice_number')
                ->lockForUpdate()
                ->orderByDesc('invoice_number')
                ->first();

            $nextInvoiceNumber = ((int) ($lastInvoice?->invoice_number ?? 0)) + 1;

            $isCreditNote = $kind === 'credit_note';
            $document = Invoice::create([
                'client_profile_id' => $lockedOriginal->client_profile_id,
                'corporate_client_id' => $lockedOriginal->corporate_client_id,
                'appointment_id' => $lockedOriginal->appointment_id,
                'original_invoice_id' => $lockedOriginal->id,
                'correction_kind' => $kind,
                'correction_reason' => $reason,
                'user_id' => $lockedOriginal->user_id,
                'invoice_date' => now()->toDateString(),
                'due_date' => $isCreditNote ? null : $lockedOriginal->due_date,
                'invoice_number' => $nextInvoiceNumber,
                'status' => $isCreditNote ? 'Émise' : 'En attente',
                'type' => $isCreditNote ? 'credit_note' : 'invoice',
                'notes' => $isCreditNote
                    ? 'Avoir relatif à la facture n°'.$lockedOriginal->invoice_number.'. '.$reason
                    : 'Facture rectificative de la facture n°'.$lockedOriginal->invoice_number.'. '.$reason,
                'total_amount' => $lockedOriginal->total_amount,
                'total_tax_amount' => $lockedOriginal->total_tax_amount,
                'total_amount_with_tax' => $lockedOriginal->total_amount_with_tax,
                'global_discount_type' => $lockedOriginal->global_discount_type,
                'global_discount_value' => $lockedOriginal->global_discount_value,
                'global_discount_amount_ht' => $lockedOriginal->global_discount_amount_ht,
                'recipient_snapshot' => $lockedOriginal->recipient_data,
                'finalized_at' => $isCreditNote ? now() : null,
            ]);

            foreach ($lockedOriginal->items as $item) {
                $attributes = $item->getAttributes();
                unset($attributes['id'], $attributes['invoice_id'], $attributes['created_at'], $attributes['updated_at']);
                $document->items()->create($attributes);
            }

            if ($isCreditNote) {
                $this->lifecycle->finalize($document, $actor, 'credit_note_finalized');
            }

            $this->activity->record(
                $lockedOriginal,
                'correction_created',
                $isCreditNote ? 'Avoir créé.' : 'Facture rectificative créée.',
                $actor,
                ['correction_invoice_id' => $document->id, 'reason' => $reason]
            );
            $this->activity->record(
                $document,
                'created_from_correction',
                'Document créé depuis la facture n°'.$lockedOriginal->invoice_number.'.',
                $actor,
                ['original_invoice_id' => $lockedOriginal->id, 'reason' => $reason]
            );

            return $document->fresh(['items', 'originalInvoice']);
        }, 3);
    }
}

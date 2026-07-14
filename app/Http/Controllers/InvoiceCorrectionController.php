<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceCorrectionService;
use Illuminate\Http\Request;

class InvoiceCorrectionController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function store(Request $request, Invoice $invoice, InvoiceCorrectionService $corrections)
    {
        $this->authorize('view', $invoice);

        if ((int) $invoice->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'correction_kind' => ['required', 'in:credit_note,replacement'],
            'correction_reason' => ['required', 'string', 'max:1000'],
        ]);

        $document = $corrections->create(
            $invoice,
            $validated['correction_kind'],
            trim($validated['correction_reason']),
            $request->user()
        );

        return redirect()
            ->route('invoices.show', $document)
            ->with('success', $document->isCreditNote()
                ? 'Avoir créé. La facture d’origine reste inchangée.'
                : 'Facture rectificative créée. Vérifiez-la avant de l’envoyer.');
    }
}

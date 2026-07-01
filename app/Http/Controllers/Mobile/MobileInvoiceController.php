<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MobileInvoiceController extends Controller
{
    use AuthorizesRequests;

    public function show(Invoice $invoice)
    {
        abort_unless(($invoice->type ?? 'invoice') === 'invoice', 404);

        return $this->documentView($invoice, false);
    }

    public function showQuote(Invoice $invoice)
    {
        abort_unless($invoice->type === 'quote', 404);

        return $this->documentView($invoice, true);
    }

    private function documentView(Invoice $document, bool $isQuote)
    {
        $this->authorize('view', $document);

        $document->load([
            'clientProfile',
            'corporateClient',
            'items.product',
            'items.inventoryItem',
            'receipts' => fn ($query) => $query->orderBy('encaissement_date')->orderBy('id'),
        ]);

        return view('mobile.invoices.show', [
            'document' => $document,
            'isQuote' => $isQuote,
        ]);
    }
}

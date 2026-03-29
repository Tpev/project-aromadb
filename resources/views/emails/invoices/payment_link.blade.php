@component('mail::message')
# Facture #{{ $invoice->invoice_number }}

Bonjour {{ $recipientName ?: 'Madame, Monsieur' }},

Veuillez trouver ci-dessous le lien pour effectuer le paiement de votre facture :

@component('mail::button', ['url' => $invoice->payment_link])
Payer la Facture
@endcomponent

**Détails de la Facture:**

| Description | Quantité | Prix unitaire TTC | Total TTC |
| ----------- | -------- | ----------------- | --------- |
@foreach($invoice->items as $item)
| {{ $item->name }} | {{ rtrim(rtrim(number_format($item->quantity, 2, ',', ' '), '0'), ',') }} | {{ number_format($item->unit_price_ttc, 2, ',', ' ') }} € | {{ number_format($item->total_price_with_tax, 2, ',', ' ') }} € |
@endforeach

**Date d'émission :** {{ optional($invoice->invoice_date)->format('d/m/Y') }}

@if($invoice->due_date)
**Date d'échéance :** {{ optional($invoice->due_date)->format('d/m/Y') }}
@endif

**Montant Total :** **{{ number_format($invoice->total_amount_with_tax, 2, ',', ' ') }} €**

Merci pour votre confiance.

Cordialement,<br>
{{ $therapistName }}
@endcomponent

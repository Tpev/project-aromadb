@component('mail::message')
# Facture #{{ $invoice->invoice_number }}

Bonjour {{ $invoice->clientProfile->name }},

Veuillez trouver ci-dessous le lien pour effectuer le paiement de votre facture :

@component('mail::button', ['url' => $invoice->payment_link])
Payer la Facture
@endcomponent

**Détails de la Facture:**

| Description        | Quantité | Prix Unitaire | Total        |
| ------------------ | -------- | ------------- | ------------ |
@foreach($invoice->items as $item)
| {{ $item->product->name }} | {{ $item->quantity }}        | {{ number_format($item->product->price, 2, ',', ' ') }} €    | {{ number_format($item->quantity * $item->product->price, 2, ',', ' ') }} € |
@endforeach


**Montant Total:**   **{{ number_format($invoice->total_amount_with_tax, 2, ',', ' ') }} €** 


Merci pour votre confiance.


Cordialement,<br>
{{ $invoice->user->name }}
@endcomponent

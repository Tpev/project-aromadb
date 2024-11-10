@component('mail::message')
# Facture #{{ $invoice->invoice_number }}

Bonjour {{ $invoice->clientProfile->name }},

Veuillez trouver ci-dessous le lien pour effectuer le paiement de votre facture :

@component('mail::button', ['url' => $invoice->payment_link])
Payer la Facture
@endcomponent

**Détails de la Facture:**
- **Montant Total:** {{ number_format($invoice->total_amount_with_tax, 2, ',', ' ') }} €
- **Date d'émission:** {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
- **Date d'échéance:** {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}

Merci pour votre confiance.

Cordialement,<br>
{{ $invoice->user->name }}
@endcomponent

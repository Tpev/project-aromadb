@component('mail::message')
# Bonjour {{ $invoice->clientProfile->first_name }},

Nous espérons que vous allez bien.

Veuillez trouver ci-joint votre facture n° **{{ $invoice->invoice_number }}**.

Merci pour votre confiance.

Cordialement,  
{{ $therapistName }}
@endcomponent

@component('mail::message')
# Bonjour {{ $recipientName }},

Nous espérons que vous allez bien.

Veuillez trouver ci-joint le devis n° **{{ $quote->quote_number ?? '—' }}**.

Si ce devis vous convient, n'hésitez pas à répondre à ce message ou à prendre contact avec votre thérapeute.

Merci pour votre confiance.

Cordialement,  
{{ $therapistName }}
@endcomponent

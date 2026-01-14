@component('mail::message')
# Votre bon cadeau est prêt 🎁

Bonjour,

Vous trouverez en pièce jointe votre **bon cadeau** émis par **{{ $therapist->company_name ?? $therapist->name ?? 'votre thérapeute' }}**.

**Code secret :** {{ $voucher->code }}  
**Montant :** {{ $voucher->originalAmountStr() }}  
@if($voucher->expires_at)
**Valable jusqu’au :** {{ $voucher->expiresAtStr() }}
@endif

Vous pouvez transmettre ce bon cadeau au bénéficiaire (PDF en pièce jointe).

Merci,  
{{ config('app.name') }}
@endcomponent

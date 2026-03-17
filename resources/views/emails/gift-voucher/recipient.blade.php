@component('mail::message')
# Vous avez reçu un bon cadeau 🎁

Bonjour,

Vous avez reçu un **bon cadeau** offert par
**{{ $voucher->buyer_name ?: 'un proche' }}**
pour des prestations de **{{ $therapist->company_name ?? $therapist->name ?? 'votre thérapeute' }}**.

**Code secret :** {{ $voucher->code }}  
**Montant :** {{ $voucher->originalAmountStr() }}  
@if($voucher->expires_at)
**Valable jusqu’au :** {{ $voucher->expiresAtStr() }}
@endif

Le bon cadeau est en pièce jointe (PDF).  
Pour réserver, utilisez le QR code présent sur le bon ou rendez-vous sur le portail du thérapeute.

Merci,  
{{ config('app.name') }}
@endcomponent

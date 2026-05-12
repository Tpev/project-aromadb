@component('mail::message')
# Bonjour,

{{ $referrer->name ?? 'Un thérapeute' }} vous invite à rejoindre **Olithea PRO**.

@if(!empty($invite->message))
> {{ $invite->message }}
@endif

@component('mail::button', ['url' => $signupUrl])
Créer mon compte
@endcomponent

Ce lien est valable jusqu’au **{{ optional($invite->expires_at)->format('d/m/Y') }}**.

À très vite,  
L’équipe Olithea PRO
@endcomponent

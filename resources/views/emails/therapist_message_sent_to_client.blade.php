@component('mail::message')
# Bonjour {{ $clientProfile->first_name ?? '' }},

Vous avez reçu un nouveau message de
**{{ $clientProfile->user->company_name ?? $clientProfile->user->name ?? 'votre praticien' }}** :

@component('mail::panel')
{{ $message->content }}
@endcomponent

Connectez-vous à votre espace client pour répondre.

Merci,<br>
{{ config('app.name') }}
@endcomponent

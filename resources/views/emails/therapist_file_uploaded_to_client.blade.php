@component('mail::message')
# Bonjour {{ $clientProfile->first_name ?? '' }},

Un nouveau document a été ajouté par
**{{ $clientProfile->user->company_name ?? $clientProfile->user->name ?? 'votre thérapeute' }}**.

**Fichier :** {{ $clientFile->original_name }}

Connectez-vous à votre espace client pour le consulter / télécharger.

Merci,<br>
{{ config('app.name') }}
@endcomponent

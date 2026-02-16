@component('mail::message')
# Bonjour {{ $clientProfile->user->name ?? '' }},

Un client vient d’envoyer un document depuis son espace client.

**Client :** {{ $clientProfile->first_name }} {{ $clientProfile->last_name }}  
**Fichier :** {{ $clientFile->original_name }}

@if($downloadUrl)
@component('mail::button', ['url' => $downloadUrl])
Voir / télécharger le document
@endcomponent
@endif

@php
    $url = null;
    try { $url = route('client_profiles.show', $clientProfile); } catch (\Throwable $e) {}
@endphp

@if($url)
@component('mail::button', ['url' => $url])
Ouvrir la fiche client
@endcomponent
@endif

Merci,<br>
{{ config('app.name') }}
@endcomponent

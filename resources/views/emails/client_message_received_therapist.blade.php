@component('mail::message')
# Bonjour {{ $clientProfile->user->name ?? '' }},

Vous avez reçu un nouveau message depuis l’espace client.

**Client :** {{ $clientProfile->first_name }} {{ $clientProfile->last_name }}

@component('mail::panel')
{{ $message->content }}
@endcomponent

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

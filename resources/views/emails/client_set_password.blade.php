@component('mail::message')
# Bonjour {{ $client->first_name }} 👋

Votre thérapeute a créé votre espace personnel.

@component('mail::button', ['url' => $url])
Définir mon mot de passe
@endcomponent

Ce lien expire dans 72 h.
@endcomponent

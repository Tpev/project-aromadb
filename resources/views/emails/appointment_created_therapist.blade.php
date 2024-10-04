@component('mail::message')
# Bonjour {{ $appointment->user->name }},

Un nouveau rendez-vous a été programmé.

**Détails du rendez-vous :**

- **Client :** {{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}
- **Date et heure :** {{ $appointment->appointment_date->format('d/m/Y à H:i') }}
- **Durée :** {{ $appointment->duration }} minutes
- **Prestation :** {{ $appointment->product->name }}

Vous pouvez consulter ce rendez-vous dans votre agenda.

Merci,<br>
{{ config('app.name') }}
@endcomponent

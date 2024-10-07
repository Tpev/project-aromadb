<!-- resources/views/emails/appointment_reminder.blade.php -->

@component('mail::message')
# Bonjour {{ $appointment->clientProfile->first_name }},

Ceci est un rappel pour votre rendez-vous prévu le {{ $appointment->appointment_date->format('d/m/Y à H:i') }}.

**Détails du rendez-vous :**

- **Date et heure :** {{ $appointment->appointment_date->format('d/m/Y à H:i') }}
- **Durée :** {{ $appointment->duration }} minutes
- **Prestation :** {{ $appointment->product->name }}
- **Thérapeute :** {{ $appointment->user->name }}

Si vous avez des questions, n'hésitez pas à nous contacter.

Merci,<br>
{{ config('app.name') }}
@endcomponent

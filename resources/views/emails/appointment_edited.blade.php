@component('mail::message')
# Bonjour {{ $appointment->clientProfile->first_name }},

Votre rendez-vous prévu le **{{ $appointment->appointment_date->format('d/m/Y à H:i') }}** a été modifié avec succès.

**Détails mis à jour du rendez-vous :**

- **Nouvelle date et heure :** {{ $appointment->appointment_date->format('d/m/Y à H:i') }}
- **Durée :** {{ $appointment->duration }} minutes
- **Prestation :** {{ $appointment->product->name }}
- **Thérapeute :** {{ $appointment->user->name }}

Si vous avez des questions ou besoin de plus d'informations, n'hésitez pas à nous contacter.

Merci,<br>
{{ config('app.name') }}
@endcomponent

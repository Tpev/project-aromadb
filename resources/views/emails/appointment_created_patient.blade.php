@component('mail::message')
# Bonjour {{ $appointment->clientProfile->first_name }},

Votre rendez-vous a été programmé avec succès.

**Détails du rendez-vous :**

- **Date et heure :** {{ $appointment->appointment_date->format('d/m/Y à H:i') }}
- **Durée :** {{ $appointment->duration }} minutes
- **Prestation :** {{ $appointment->product->name }}
- **Mode de consultation :** {{ $appointment->product->mode }} 
- **Thérapeute :** {{ $appointment->user->name }}

Si vous avez des questions, n'hésitez pas à nous contacter.

Merci,<br>
{{ $appointment->user->name }}
@endcomponent

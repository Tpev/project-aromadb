@component('mail::message')
# Bonjour {{ $appointment->clientProfile->first_name }},

Votre rendez-vous a été programmé avec succès.

**Détails du rendez-vous :**

- **Date et heure :** {{ $appointment->appointment_date->format('d/m/Y à H:i') }}
- **Durée :** {{ $appointment->duration }} minutes
- **Prestation :** {{ $appointment->product->name }}
**Mode de consultation :** {{ $appointment->mode }}

@if($appointment->mode === 'Dans le Cabinet' && $appointment->user && $appointment->user->company_address)
**Adresse du cabinet :**  
{{ $appointment->user->company_address }}
@endif
- **Thérapeute :** {{ $appointment->user->name }}

Si vous avez des questions, n'hésitez pas à nous contacter.
test
Merci,<br>
{{ $appointment->user->name }}
@endcomponent

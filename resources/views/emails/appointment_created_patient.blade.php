@component('mail::message')
# Bonjour {{ $appointment->clientProfile->first_name }},

Votre rendez-vous a été programmé avec succès.

**Détails du rendez-vous :**

- **Date et heure :** {{ $appointment->appointment_date->format('d/m/Y \à H:i') }}
- **Durée :** {{ $appointment->duration }} minutes
- **Prestation :** {{ $appointment->product->name ?? '—' }}
- **Mode de consultation :** {{ $modes }}

@isset($cabinetAddress)
**Adresse du cabinet :**  
{!! nl2br(e($cabinetAddress)) !!}
@endisset

- **Thérapeute :** {{ $appointment->user->company_name ?? $appointment->user->name }}

Si vous avez des questions, n'hésitez pas à nous contacter.

Merci,  
{{ $appointment->user->company_name ?? $appointment->user->name }}
@endcomponent

@component('mail::message')
# Bonjour {{ $appointment->clientProfile->first_name }},

Votre rendez-vous a été programmé avec succès.

**Détails du rendez-vous&nbsp;:**

- **Date et heure :** {{ $appointment->appointment_date->format('d/m/Y \à H:i') }}
- **Durée :** {{ $appointment->duration }} minutes
- **Prestation :** {{ $appointment->product->name }}
- **Mode{{ count($appointment->product->getConsultationModes()) > 1 ? 's' : '' }} de consultation :** {{ implode(', ', $appointment->product->getConsultationModes()) }}

{{-- Afficher l’adresse seulement si “Dans le Cabinet” est l’un des modes --}}
@if (in_array('Dans le Cabinet', $appointment->product->getConsultationModes()) 
    && $appointment->user?->company_address)
**Adresse du cabinet :**  
{{ $appointment->user->company_address }}
@endif

- **Thérapeute :** {{ $appointment->user->name }}

Si vous avez des questions, n'hésitez pas à nous contacter.

Merci,<br>
{{ $appointment->user->name }}
@endcomponent

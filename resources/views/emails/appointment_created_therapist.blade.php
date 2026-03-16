@component('mail::message')
# Bonjour {{ $appointment->user->name }},

Un nouveau rendez-vous a été programmé.

**Détails du rendez-vous :**

- **Client :** {{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}
- **Date et heure :** {{ $appointment->appointment_date->format('d/m/Y à H:i') }}
- **Durée :** {{ $appointment->duration }} minutes
- **Prestation :** {{ $appointment->product->name }}
- **Mode de consultation :** {{ $modeLabel ?? ($appointment->product->getConsultationModes() ?? '—') }}

@if(($resolvedMode ?? null) === 'cabinet' && !empty($cabinetAddress))
**Adresse du cabinet :**  
{!! nl2br(e($cabinetAddress)) !!}
@elseif(in_array(($resolvedMode ?? ''), ['domicile', 'entreprise'], true))
**{{ ($resolvedMode ?? '') === 'entreprise' ? "Adresse de l’entreprise" : 'Adresse du domicile' }} :**  
{!! nl2br(e($clientAddress ?? 'Adresse non renseignée')) !!}
@endif

Vous pouvez consulter ce rendez-vous dans votre agenda.

Merci,<br>
{{ config('app.name') }}
@endcomponent

@component('mail::message')
# Votre rendez-vous a bien été avancé

Bonjour {{ $appointment->clientProfile?->first_name }},

Le créneau plus tôt est confirmé. Votre paiement éventuel et toutes les informations de votre rendez-vous restent bien associés à cette nouvelle date.

- **Ancien créneau :** {{ $oldStart->format('d/m/Y à H:i') }}
- **Nouveau créneau :** {{ $appointment->appointment_date?->format('d/m/Y à H:i') }}
- **Prestation :** {{ $appointment->product?->name ?? 'Rendez-vous' }}
- **Mode :** {{ $appointment->getResolvedModeLabel() }}

@if(!empty($visioUrl))
@component('mail::button', ['url' => $visioUrl])
Rejoindre la visio
@endcomponent
@endif

@component('mail::button', ['url' => $managementUrl])
Voir mon rendez-vous
@endcomponent

@component('mail::button', ['url' => $icsUrl, 'color' => 'success'])
Mettre à jour mon calendrier
@endcomponent

Merci,

{{ $appointment->user?->company_name ?: $appointment->user?->name }}
@endcomponent

@component('mail::message')
# Votre nouveau créneau est confirmé

Bonjour {{ $appointment->clientProfile?->first_name }},

Votre rendez-vous a été déplacé :

- **Ancien créneau :** {{ $oldStart->format('d/m/Y à H:i') }}
- **Nouveau créneau :** {{ $appointment->appointment_date?->format('d/m/Y à H:i') }}
- **Prestation :** {{ $appointment->product?->name ?? 'Rendez-vous' }}
- **Praticien :** {{ $appointment->user?->company_name ?: $appointment->user?->name }}

@if(!empty($visioUrl))
@component('mail::button', ['url' => $visioUrl])
Rejoindre la visio
@endcomponent
@endif

@component('mail::button', ['url' => $managementUrl])
Gérer mon rendez-vous
@endcomponent

@component('mail::button', ['url' => $icsUrl, 'color' => 'success'])
Mettre à jour mon calendrier
@endcomponent

Si vous aviez déjà ajouté le rendez-vous à votre calendrier, ouvrez le nouveau fichier pour appliquer le changement.

Merci,  
{{ $appointment->user?->company_name ?: $appointment->user?->name }}
@endcomponent

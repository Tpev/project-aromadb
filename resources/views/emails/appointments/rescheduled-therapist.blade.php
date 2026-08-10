@component('mail::message')
# Un rendez-vous a été déplacé

**Client :** {{ trim(($appointment->clientProfile?->first_name ?? '').' '.($appointment->clientProfile?->last_name ?? '')) }}  
**Prestation :** {{ $appointment->product?->name ?? 'Rendez-vous' }}  
**Ancien créneau :** {{ $oldStart->format('d/m/Y à H:i') }}  
**Nouveau créneau :** {{ $appointment->appointment_date?->format('d/m/Y à H:i') }}

@component('mail::button', ['url' => $appointmentUrl])
Voir le rendez-vous
@endcomponent

Merci,  
{{ config('app.name') }}
@endcomponent

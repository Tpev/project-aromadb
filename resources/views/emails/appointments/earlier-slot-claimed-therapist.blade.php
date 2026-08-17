@component('mail::message')
# Un rendez-vous a été avancé

{{ trim(($appointment->clientProfile?->first_name ?? '').' '.($appointment->clientProfile?->last_name ?? '')) }} a accepté un créneau plus tôt.

- **Prestation :** {{ $appointment->product?->name ?? 'Rendez-vous' }}
- **Ancien créneau :** {{ $oldStart->format('d/m/Y à H:i') }}
- **Nouveau créneau :** {{ $appointment->appointment_date?->format('d/m/Y à H:i') }}
- **Mode :** {{ $appointment->getResolvedModeLabel() }}

Le rendez-vous existant a été déplacé : ses paiements, sa facturation et ses informations associées sont conservés.

@component('mail::button', ['url' => $appointmentUrl])
Voir le rendez-vous
@endcomponent

Merci,

{{ config('app.name') }}
@endcomponent

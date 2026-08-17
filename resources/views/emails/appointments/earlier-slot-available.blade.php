@component('mail::message')
# Un créneau plus tôt est disponible

Bonjour {{ $appointment->clientProfile?->first_name }},

Un nouveau créneau compatible avec votre rendez-vous vient de se libérer.

**Votre rendez-vous actuel**

{{ $appointment->appointment_date?->format('d/m/Y à H:i') }}

**Créneau proposé**

{{ $opportunity->slot_start?->format('d/m/Y à H:i') }}

- **Prestation :** {{ $appointment->product?->name ?? 'Rendez-vous' }}
- **Durée :** {{ \App\Support\EventDuration::format((int) $appointment->duration) }}
- **Mode :** {{ ['cabinet' => 'Au cabinet', 'visio' => 'En visio', 'domicile' => 'À domicile', 'entreprise' => 'En entreprise'][$opportunity->mode] ?? $appointment->getResolvedModeLabel() }}
@if($opportunity->mode !== 'visio')
- **Lieu :** {{ $appointment->getResolvedLocationString() }}
@endif

@component('mail::button', ['url' => $offerUrl])
Voir ce créneau
@endcomponent

Ce créneau est proposé à plusieurs personnes et sera attribué à la première personne qui le confirme. Votre rendez-vous actuel reste réservé tant que vous n’avez pas confirmé le nouveau créneau.

Vous ne souhaitez plus recevoir ces propositions pour ce rendez-vous ? [Gérer mon rendez-vous]({{ $managementUrl }}).

Merci,

{{ $appointment->user?->company_name ?: $appointment->user?->name }}
@endcomponent

@component('mail::message')
# Bonjour {{ $reservation->full_name }},

Ceci est un rappel concernant votre réservation pour l’événement **{{ $event->name }}**.

**Détails de l’événement :**
- **Date et heure :** {{ $event->formatted_period }}
- **Durée :** {{ $event->formatted_duration }}
- **Format :** {{ ($isVisio ?? false) ? 'Visio' : 'Présentiel' }}
@if(($isVisio ?? false))
- **Accès :** En ligne (Visio)
@else
- **Lieu :** {{ $event->location ?? '—' }}
@endif
- **Organisateur :** {{ $event->user->company_name ?? $event->user->name ?? '—' }}

@isset($timingLabel)
@if($timingLabel === '1h')
⏰ *L’événement commence dans environ 1 heure.*
@else
📅 *L’événement a lieu dans environ 24 heures.*
@endif
@endisset

@if(filled($event->reminder_email_note))
<div style="margin:16px 0;padding:12px 16px;border-left:4px solid #647a0b;">{!! \App\Support\EmailNote::html($event->reminder_email_note) !!}</div>
@endif

@if(($isVisio ?? false))
---

## 🔗 Lien de connexion (Visio)

@if(!empty($visioJoinLink))
@component('mail::button', ['url' => $visioJoinLink])
Rejoindre la visio
@endcomponent

> Si le bouton ne fonctionne pas, copiez-collez ce lien dans votre navigateur :  
{{ $visioJoinLink }}
@else
> Le lien de visio n’est pas disponible pour le moment.  
Merci de répondre à cet email et nous vous aiderons rapidement.
@endif
@endif

---

Si vous avez des questions, répondez simplement à cet email.

Merci,  
{{ config('app.name') }}
@endcomponent

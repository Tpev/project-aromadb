@component('mail::message')
# Bonjour {{ $reservation->full_name }},

Ceci est un rappel concernant votre réservation pour l’événement **{{ $event->name }}**.

**Détails de l’événement :**
- **Date et heure :** {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y \à H:i') }}
- **Durée :** {{ $event->duration ?? '—' }} minutes
- **Lieu :** {{ $event->location ?? '—' }}
- **Organisateur :** {{ $event->user->company_name ?? $event->user->name ?? '—' }}

@isset($timingLabel)
@if($timingLabel === '1h')
⏰ *L’événement commence dans environ 1 heure.*
@else
📅 *L’événement a lieu dans environ 24 heures.*
@endif
@endisset

Si vous avez des questions, répondez simplement à cet email.

Merci,  
{{ config('app.name') }}
@endcomponent

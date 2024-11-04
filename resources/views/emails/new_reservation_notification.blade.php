@component('mail::message')
# Nouvelle réservation pour votre événement

Bonjour {{ $event->user->name }},

Vous avez une nouvelle réservation pour votre événement **{{ $event->name }}**.

## Détails de la réservation:

- **Nom Complet:** {{ $reservation->full_name }}
- **Email:** {{ $reservation->email }}
@if($reservation->phone)
- **Téléphone:** {{ $reservation->phone }}
@endif

## Détails de l'événement:

- **Date et Heure:** {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}
- **Durée:** {{ $event->duration }} minutes
- **Lieu:** {{ $event->location }}



Cordialement,

{{ config('app.name') }}
@endcomponent

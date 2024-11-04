@component('mail::message')
# Confirmation de votre réservation

Bonjour {{ $reservation->full_name }},

Nous vous confirmons votre réservation pour l'événement **{{ $event->name }}**.

## Détails de l'événement:

- **Date et Heure:** {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}
- **Durée:** {{ $event->duration }} minutes
- **Lieu:** {{ $event->location }}

@if($event->associatedProduct && $event->associatedProduct->price > 0)
- **Prix:** {{ number_format($event->associatedProduct->price_incl_tax, 2, ',', ' ') }} €
@endif

## Vos informations:

- **Nom Complet:** {{ $reservation->full_name }}
- **Email:** {{ $reservation->email }}
@if($reservation->phone)
- **Téléphone:** {{ $reservation->phone }}
@endif

Nous vous remercions de votre confiance et avons hâte de vous voir lors de l'événement.

@component('mail::button', ['url' => route('therapist.show', $event->user->slug)])
Voir le profil du thérapeute
@endcomponent

Cordialement,

{{ config('app.name') }}
@endcomponent

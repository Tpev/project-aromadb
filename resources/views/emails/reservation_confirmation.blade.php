{{-- resources/views/emails/reservation_confirmation.blade.php --}}
@component('mail::message')
# Bonjour {{ $reservation->full_name }},

Votre réservation est bien enregistrée ✅  
Voici les informations pour l’événement **{{ $event->name }}**.

@if(filled($event->confirmation_email_note))
<div style="margin:16px 0;padding:12px 16px;border-left:4px solid #647a0b;">{!! \App\Support\EmailNote::html($event->confirmation_email_note) !!}</div>
@endif

---

## Détails de l’événement

- **Date & heure :** {{ $event->formatted_period }}
- **Durée :** {{ $event->formatted_duration }}
- **Lieu :** {{ $event->location ?: (($event->event_type ?? 'in_person') === 'visio' ? 'En ligne (Visio)' : '—') }}

@if($event->associatedProduct && ($event->associatedProduct->price ?? 0) > 0)
- **Prix :** {{ number_format($event->associatedProduct->price_incl_tax, 2, ',', ' ') }} €
@endif

@if(!empty($visioUrl))
---

## Lien de visioconférence

@component('mail::button', ['url' => $visioUrl])
{{ $visioLabel ?? 'Rejoindre la visio' }}
@endcomponent

*(Conseil : connectez-vous 2 minutes avant l’heure de début.)*
@endif

---

## Vos informations

- **Nom :** {{ $reservation->full_name }}
- **Email :** {{ $reservation->email }}
@if(!empty($reservation->phone))
- **Téléphone :** {{ $reservation->phone }}
@endif

---

@php
    $therapistName = $event->user->company_name ?? $event->user->name ?? 'Votre praticien';
@endphp

Si vous avez une question, vous pouvez contacter **{{ $therapistName }}**.

@component('mail::button', ['url' => route('therapist.show', $event->user->slug)])
Voir le profil du thérapeute
@endcomponent

Merci,  
{{ config('app.name') }}
@endcomponent

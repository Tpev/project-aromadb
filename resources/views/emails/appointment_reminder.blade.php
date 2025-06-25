<!-- resources/views/emails/appointment_reminder.blade.php -->
@php
    // Chaîne lisible prête à être affichée (ex.: "En Visio, Dans le Cabinet")
    $modes = $appointment->product->getConsultationModes();

    // Vérifie si la chaîne contient exactement "Dans le Cabinet"
    $showsCabinetAddress = str_contains($modes, 'Dans le Cabinet');
@endphp

@component('mail::message')
# Bonjour {{ $appointment->clientProfile->first_name }},

Ceci est un rappel pour votre rendez-vous prévu le {{ $appointment->appointment_date->format('d/m/Y à H:i') }}.

**Détails du rendez-vous :**

- **Date et heure :** {{ $appointment->appointment_date->format('d/m/Y à H:i') }}
- **Durée :** {{ $appointment->duration }} minutes
- **Prestation :** {{ $appointment->product->name }}
- **Mode de consultation :** {{ $modes }}

@if ($showsCabinetAddress && $appointment->user?->company_address)
**Adresse du cabinet :**  
{{ $appointment->user->company_address }}
@endif
- **Thérapeute :** {{ $appointment->user->name }}

Si vous avez des questions, n'hésitez pas à nous contacter.

Merci,<br>
{{ config('app.name') }}
@endcomponent

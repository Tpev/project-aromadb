@component('mail::message')
# Votre rendez-vous est annulé

Bonjour {{ $appointment->clientProfile?->first_name }},

Votre rendez-vous du **{{ $appointment->appointment_date?->format('d/m/Y à H:i') }}** pour la prestation **{{ $appointment->product?->name ?? 'Rendez-vous' }}** a bien été annulé.

@if($appointment->requiresFinancialFollowUp())
L’annulation du rendez-vous n’entraîne pas automatiquement un remboursement. Votre praticien vous contactera si une régularisation est nécessaire.
@endif

@component('mail::button', ['url' => $managementUrl])
Gérer mon rendez-vous
@endcomponent

@component('mail::button', ['url' => $icsUrl, 'color' => 'secondary'])
Mettre à jour mon calendrier
@endcomponent

Pour toute question, vous pouvez répondre directement à cet email.

Merci,  
{{ $appointment->user?->company_name ?: $appointment->user?->name }}
@endcomponent

@component('mail::message')
# Un paiement nécessite votre vérification

Un paiement de **{{ number_format($amountCents / 100, 2, ',', ' ') }} €** a été reçu après l’annulation du rendez-vous suivant :

**Client :** {{ trim(($appointment->clientProfile?->first_name ?? '').' '.($appointment->clientProfile?->last_name ?? '')) }}  
**Prestation :** {{ $appointment->product?->name ?? 'Rendez-vous' }}  
**Créneau initial :** {{ $appointment->appointment_date?->format('d/m/Y à H:i') }}  
**Référence Stripe :** {{ $providerReference }}

Le rendez-vous reste annulé. Aucun remboursement ou avoir n’a été créé automatiquement : vérifiez la situation financière avant de contacter le client.

@component('mail::button', ['url' => $appointmentUrl])
Voir le rendez-vous
@endcomponent

Merci,  
{{ config('app.name') }}
@endcomponent

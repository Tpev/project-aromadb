{{-- resources/views/emails/appointment_created_patient.blade.php --}}

@component('mail::message')
# Bonjour {{ $appointment->clientProfile->first_name }},

Votre rendez-vous a été programmé avec succès.

**Détails du rendez-vous :**

- **Date et heure :** {{ $appointment->appointment_date->format('d/m/Y \à H:i') }}
- **Durée :** {{ $appointment->duration }} minutes
- **Prestation :** {{ $appointment->product->name ?? '—' }}
- **Mode de consultation :** {{ $modes }}

@isset($cabinetAddress)
**Adresse du cabinet :**  
{!! nl2br(e($cabinetAddress)) !!}
@endisset

@if(!empty($visioUrl))
**Lien de visioconférence :**  
@component('mail::button', ['url' => $visioUrl])
Rejoindre la visio
@endcomponent

*(Conseil : connectez-vous 2 minutes avant l’heure du rendez-vous.)*
@endif

- **Praticien :** {{ $appointment->user->company_name ?? $appointment->user->name }}

---

## Gérer votre rendez-vous

Vous pouvez retrouver les informations de votre rendez-vous via ce lien :

@component('mail::button', ['url' => $confirmationUrl])
Voir mon rendez-vous
@endcomponent

@if(($cutoffHours ?? 0) > 0 && !empty($latestCancelAt))
Si vous avez un empêchement, vous pouvez également **annuler votre rendez-vous** depuis cette page, **jusqu’à {{ $cutoffHours }}h avant** (soit jusqu’au **{{ $latestCancelAt->format('d/m/Y à H:i') }}**).
@else
Si vous avez un empêchement, vous pouvez également **annuler votre rendez-vous** depuis cette page.
@endif

---

Si vous avez des questions, n'hésitez pas à contacter votre praticien.

Merci,  
{{ $appointment->user->company_name ?? $appointment->user->name }}
@endcomponent

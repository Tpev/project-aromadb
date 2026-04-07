@component('mail::message')
# Invitation à rejoindre un cabinet partagé

{{ $inviter->company_name ?: trim(($inviter->first_name ?? '').' '.($inviter->last_name ?? '')) ?: $inviter->name }} vous invite à rejoindre le cabinet **{{ $invite->practiceLocation->label }}** sur AromaMade.

@component('mail::panel')
Adresse : {{ $invite->practiceLocation->address_line1 }}@if($invite->practiceLocation->postal_code || $invite->practiceLocation->city), {{ trim(($invite->practiceLocation->postal_code ?? '').' '.($invite->practiceLocation->city ?? '')) }}@endif
@endcomponent

Vous pourrez utiliser ce cabinet dans vos disponibilités et rendez-vous. Une fois accepté, les créneaux réservés dans ce cabinet seront bloqués pour tous les membres du cabinet partagé.

@component('mail::button', ['url' => $inviteUrl])
Voir l’invitation
@endcomponent

Ce lien expire le {{ optional($invite->expires_at)->format('d/m/Y H:i') }}.

Merci,<br>
{{ config('app.name') }}
@endcomponent

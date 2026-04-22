@component('mail::message')
# Bonjour {{ $clientFirstName }},

{{ $practitionerName }} vous invite à rejoindre la communauté privée **{{ $communityName }}** sur AromaMade.

@if(!empty($communityDescription))
@component('mail::panel')
{{ $communityDescription }}
@endcomponent
@endif

@if($requiresAccountSetup)
Activez d’abord votre espace client, puis vous pourrez retrouver cette invitation et rejoindre la communauté.
@else
Connectez-vous à votre espace client pour retrouver l’invitation et rejoindre la communauté.
@endif

@component('mail::button', ['url' => $joinUrl])
Rejoindre la communauté
@endcomponent

L’invitation reste également visible dans votre espace client tant qu’elle n’a pas été acceptée.

Merci,<br>
{{ config('app.name') }}
@endcomponent

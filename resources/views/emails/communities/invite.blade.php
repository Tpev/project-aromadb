@component('mail::message')
# Bonjour {{ $clientFirstName }},

{{ $practitionerName }} vous invite a rejoindre la communaute privee **{{ $communityName }}** sur AromaMade.

@if(!empty($communityDescription))
@component('mail::panel')
{{ $communityDescription }}
@endcomponent
@endif

@if($requiresAccountSetup)
Activez d'abord votre espace client, puis vous pourrez retrouver cette invitation et rejoindre la communaute.
@else
Connectez-vous a votre espace client pour retrouver l'invitation et rejoindre la communaute.
@endif

@component('mail::button', ['url' => $joinUrl])
Rejoindre la communaute
@endcomponent

L'invitation reste egalement visible dans votre espace client tant qu'elle n'a pas ete acceptee.

Merci,<br>
{{ config('app.name') }}
@endcomponent

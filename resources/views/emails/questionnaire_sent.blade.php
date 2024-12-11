@component('mail::message')
# Bonjour {{ $client_profile_name }},

Vous avez reçu un nouveau questionnaire intitulé : **{{ $questionnaireTitle }}**.

Ce questionnaire a été envoyé par : **{{ $therapistName }}**.

Pour remplir le questionnaire, cliquez sur le bouton ci-dessous :


@component('mail::button', ['url' => $link])
    Remplir le questionnaire
@endcomponent


Si vous rencontrez des problèmes, copiez et collez le lien suivant dans votre navigateur :

{{ $link }}

Merci et à bientôt !

Cordialement,  
{{ $therapistName }}
@endcomponent

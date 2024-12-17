@component('mail::message')
# Bonjour {{ $clientProfile->first_name }} {{ $clientProfile->last_name }},

Votre thérapeute vous a envoyé un nouveau conseil : **{{ $conseil->name }}**.

Vous pouvez accéder à ce conseil en cliquant sur le bouton ci-dessous :

@component('mail::button', ['url' => $link])
Voir le Conseil
@endcomponent

Ce lien est unique et ne doit pas être partagé.

Si vous avez des questions, n'hésitez pas à répondre directement à cet email.

Bien à vous,<br>
**{{ $therapistName }}**
@endcomponent

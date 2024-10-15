@component('mail::message')
# Bonjour {{ $testimonialRequest->clientProfile->first_name }},

Je suis {{ $testimonialRequest->therapist->name }}.

J'espère que vous avez eu une expérience positive lors de nos séances.

Pourriez-vous prendre un moment pour partager votre témoignage ? Cliquez sur le bouton ci-dessous pour le soumettre.

@component('mail::button', ['url' => route('testimonials.submit', ['token' => $testimonialRequest->token])])
    Soumettre mon Témoignage
@endcomponent

Merci beaucoup pour votre temps et votre confiance.

Cordialement,

{{ $testimonialRequest->therapist->name }}  

@endcomponent

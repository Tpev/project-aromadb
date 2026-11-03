@component('mail::message')
# Bonjour {{ $testimonialRequest->clientProfile->first_name }},

Je suis {{ $testimonialRequest->therapist->name }}.

Merci pour votre confiance. Si vous le souhaitez, vous pouvez partager votre expérience en laissant un avis sur ma fiche Google.

Votre retour aide d’autres personnes à découvrir mon accompagnement.

@component('mail::button', ['url' => $testimonialRequest->google_review_url])
Laisser un avis sur Google
@endcomponent

Le bouton ouvre directement Google, où vous pourrez rédiger votre avis.

Merci pour le temps que vous y consacrerez.

Cordialement,

{{ $testimonialRequest->therapist->name }}
@endcomponent

@component('mail::message')
# Signature de votre feuille d’émargement

Bonjour,

Votre praticien vous invite à signer la feuille d’émargement pour votre séance :
- **Prestation** : {{ $em->meta['product']['name'] ?? '—' }}
- **Date / Heure** : {{ \Carbon\Carbon::parse($em->meta['appointment']['date'] ?? null)->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
- **Durée** : {{ $em->meta['product']['duration'] ?? '—' }} min

@component('mail::button', ['url' => $url])
Signer ma présence
@endcomponent

Ce lien est valable **14 jours**.

Merci,  
{{ $em->meta['therapist']['name'] ?? config('app.name') }}
@endcomponent

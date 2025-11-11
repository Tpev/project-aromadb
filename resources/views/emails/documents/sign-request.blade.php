@component('mail::message')
# {{ $clientName ? "Bonjour $clientName," : 'Bonjour,' }}

{{ $document->owner?->name ?? 'Votre praticien' }} vous a envoyé un document à signer{{ $document->original_name ? " : « {$document->original_name} »" : '' }}.

@component('mail::button', ['url' => $url])
Signer le document
@endcomponent

**Validité du lien :** jusqu’au {{ \Illuminate\Support\Carbon::parse($signing->expires_at)->translatedFormat('d F Y à H:i') }}.  
Si le bouton ne fonctionne pas, copiez-collez ce lien dans votre navigateur :  
{{ $url }}

Merci,  
L’équipe AromaMade PRO
@endcomponent

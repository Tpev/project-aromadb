@component('mail::message')
# {{ $clientName ? "Bonjour $clientName," : 'Bonjour,' }}

Votre document a été **signé par toutes les parties**{{ $document->original_name ? " : « {$document->original_name} »" : '' }}.

@isset($downloadUrl)
@component('mail::button', ['url' => $downloadUrl])
Télécharger le document signé (PDF)
@endcomponent
@endisset

Nous joignons également le PDF en pièce jointe lorsque possible.

Merci,  
L’équipe AromaMade PRO
@endcomponent

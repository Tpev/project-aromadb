{{-- resources/views/emails/digital-trainings/access.blade.php --}}

<p>Bonjour {{ $enrollment->participant_name ?? '!' }},</p>

<p>Votre thérapeute vous a invité(e) à suivre la formation digitale :</p>

<p><strong>{{ $training->title }}</strong></p>

<p>Pour accéder à votre espace de formation, cliquez simplement sur ce lien :</p>

<p>
    <a href="{{ $accessUrl }}" target="_blank">
        Accéder à la formation
    </a>
</p>

<p>Ce lien est personnel et ne doit pas être partagé.</p>

<p>À très vite,</p>
<p>L’équipe {{ config('app.name') }}</p>

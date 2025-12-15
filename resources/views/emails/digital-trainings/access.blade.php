{{-- resources/views/emails/digital-trainings/access.blade.php --}}

@php
    $participantName = $enrollment->participant_name ?: null;

    $practitioner = optional($training->user);
    $practitionerName  = $practitioner->name ?? __('Votre praticien');
    $practitionerEmail = $practitioner->email ?? null;
@endphp

<p>Bonjour{{ $participantName ? ' ' . e($participantName) : '' }},</p>

<p>
    <strong>{{ e($practitionerName) }}</strong>
    vous a envoyé un accès personnel à une formation digitale sur {{ config('app.name') }} :
</p>

<p style="margin: 12px 0;">
    <strong>{{ e($training->title) }}</strong>
</p>

<p>
    Pour démarrer, cliquez sur le bouton ci-dessous :
</p>

<p style="margin: 16px 0;">
    <a href="{{ $accessUrl }}"
       target="_blank"
       style="display:inline-block;background:#647a0b;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:999px;font-weight:600;">
        Accéder à la formation
    </a>
</p>

<p style="font-size:12px;color:#6b7280;margin-top:8px;">
    Ce lien est personnel et ne doit pas être partagé.
</p>

@if($practitionerEmail)
    <p style="font-size:12px;color:#6b7280;margin-top:14px;">
        Besoin d’aide ? Vous pouvez répondre à votre praticien :
        <a href="mailto:{{ e($practitionerEmail) }}" style="color:#647a0b;text-decoration:underline;">
            {{ e($practitionerEmail) }}
        </a>
    </p>
@endif

<p>À très vite,</p>
<p>L’équipe {{ config('app.name') }}</p>

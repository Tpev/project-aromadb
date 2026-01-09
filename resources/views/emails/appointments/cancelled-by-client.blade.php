@php
    $a = $appointment;
    $clientName = trim(($a->clientProfile?->first_name ?? '').' '.($a->clientProfile?->last_name ?? ''));
    $therapistName = $a->user?->company_name ?? $a->user?->name ?? 'Votre thérapeute';
    $dateStr = $a->appointment_date ? $a->appointment_date->format('d/m/Y') : '';
    $timeStr = $a->appointment_date ? $a->appointment_date->format('H:i') : '';
    $product = $a->product?->name;
@endphp

<p>Bonjour {{ $therapistName }},</p>

<p>
    Un client vient d’annuler un rendez-vous depuis son lien de confirmation.
</p>

<ul>
    <li><strong>Client :</strong> {{ $clientName ?: '—' }}</li>
    <li><strong>Date :</strong> {{ $dateStr }} à {{ $timeStr }}</li>
    <li><strong>Durée :</strong> {{ $a->duration }} min</li>
    @if($product)
        <li><strong>Prestation :</strong> {{ $product }}</li>
    @endif
</ul>

<p>
    Vous pouvez retrouver ce rendez-vous dans votre agenda / tableau de bord.
</p>

<p style="margin-top: 16px;">
    — AromaMade PRO
</p>

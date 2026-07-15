@php
    $billingLabel = $appointment->billing_tracking_label;
    $noteLabel = $appointment->note_tracking_label;
    $sessionLabel = $appointment->session_tracking_label;
@endphp

<div class="appointment-status-stack d-flex flex-column align-items-center gap-1" aria-label="Suivi du rendez-vous">
    <span class="appointment-status-pill badge rounded-pill {{ $sessionLabel === 'Terminée' ? 'bg-success-subtle text-success' : ($sessionLabel === 'Annulée' ? 'bg-secondary-subtle text-secondary' : 'bg-warning-subtle text-warning') }}">
        <i class="fas fa-calendar-check" aria-hidden="true"></i>
        <span class="appointment-status-label">{{ $sessionLabel }}</span>
    </span>
    <span class="appointment-status-pill badge rounded-pill {{ $noteLabel === 'Note créée' ? 'bg-success-subtle text-success' : 'bg-light text-secondary' }}">
        <i class="fas fa-notes-medical" aria-hidden="true"></i>
        <span class="appointment-status-label">{{ $noteLabel }}</span>
    </span>
    <span class="appointment-status-pill badge rounded-pill {{ $billingLabel === 'Réglée' ? 'bg-success-subtle text-success' : ($billingLabel === 'Plusieurs factures' ? 'bg-danger-subtle text-danger' : 'bg-light text-secondary') }}">
        <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i>
        <span class="appointment-status-label">{{ $billingLabel }}</span>
    </span>
</div>

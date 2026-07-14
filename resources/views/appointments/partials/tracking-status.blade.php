@php
    $billingLabel = $appointment->billing_tracking_label;
    $noteLabel = $appointment->note_tracking_label;
    $sessionLabel = $appointment->session_tracking_label;
@endphp

<div class="d-flex flex-column align-items-center gap-1" aria-label="Suivi du rendez-vous">
    <span class="badge rounded-pill px-2 py-1 {{ $sessionLabel === 'Terminée' ? 'bg-success-subtle text-success' : ($sessionLabel === 'Annulée' ? 'bg-secondary-subtle text-secondary' : 'bg-warning-subtle text-warning') }}">
        <i class="fas fa-calendar-check me-1"></i>{{ $sessionLabel }}
    </span>
    <span class="badge rounded-pill px-2 py-1 {{ $noteLabel === 'Note créée' ? 'bg-success-subtle text-success' : 'bg-light text-secondary' }}">
        <i class="fas fa-notes-medical me-1"></i>{{ $noteLabel }}
    </span>
    <span class="badge rounded-pill px-2 py-1 {{ $billingLabel === 'Réglée' ? 'bg-success-subtle text-success' : ($billingLabel === 'Plusieurs factures' ? 'bg-danger-subtle text-danger' : 'bg-light text-secondary') }}">
        <i class="fas fa-file-invoice-dollar me-1"></i>{{ $billingLabel }}
    </span>
</div>

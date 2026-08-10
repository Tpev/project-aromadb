<x-app-layout>
    @section('title', 'Gérer mon rendez-vous | Olithea')

    @php
        $mode = $appointment->getResolvedMode();
        $canManage = $appointment->canBeManagedOnline();
        $deadline = $appointment->managementDeadlineAt();
        $location = $appointment->getResolvedLocationString();
    @endphp

    <style>
        .appointment-manage-page { background:#f5f6f1; min-height:70vh; padding:32px 16px 64px; }
        .appointment-manage-shell { max-width:760px; margin:0 auto; }
        .appointment-manage-card { background:#fff; border:1px solid #e0e5d3; border-radius:8px; box-shadow:0 10px 28px rgba(37,49,25,.07); overflow:hidden; }
        .appointment-manage-header { padding:28px 30px 22px; border-bottom:1px solid #e8ebdf; }
        .appointment-manage-header h1 { margin:0; color:#26351f; font-size:28px; font-weight:700; }
        .appointment-manage-header p { margin:8px 0 0; color:#687064; }
        .appointment-status { display:inline-flex; align-items:center; min-height:30px; margin-top:16px; padding:5px 11px; border-radius:999px; background:#edf3db; color:#536907; font-weight:700; font-size:13px; }
        .appointment-status.cancelled { background:#f8e6e2; color:#944b38; }
        .appointment-manage-content { padding:26px 30px 30px; }
        .appointment-detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px 28px; margin:0; }
        .appointment-detail-grid div { min-width:0; }
        .appointment-detail-grid dt { color:#777f72; font-size:12px; text-transform:uppercase; font-weight:700; }
        .appointment-detail-grid dd { margin:5px 0 0; color:#26351f; font-size:16px; overflow-wrap:anywhere; }
        .appointment-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:28px; padding-top:24px; border-top:1px solid #e8ebdf; }
        .appointment-button { display:inline-flex; min-height:44px; align-items:center; justify-content:center; border-radius:6px; padding:10px 17px; font-weight:700; text-decoration:none; border:1px solid transparent; cursor:pointer; }
        .appointment-button.primary { background:#647a0b; color:#fff; }
        .appointment-button.secondary { background:#fff; border-color:#647a0b; color:#526508; }
        .appointment-button.danger { background:#fff; border-color:#a5513b; color:#934832; }
        .appointment-alert { margin-bottom:16px; border-radius:6px; padding:13px 15px; }
        .appointment-alert.success { background:#edf6e4; color:#365b24; }
        .appointment-alert.error { background:#fbe9e5; color:#843d2e; }
        .appointment-help { margin-top:18px; color:#687064; font-size:14px; line-height:1.5; }
        .appointment-reason { width:100%; margin-top:18px; }
        .appointment-reason label { display:block; margin-bottom:6px; color:#4f584a; font-size:13px; font-weight:700; }
        .appointment-reason input { width:100%; border:1px solid #cfd6c5; border-radius:6px; padding:10px 12px; }
        @media (max-width:640px) {
            .appointment-manage-page { padding:18px 12px 44px; }
            .appointment-manage-header, .appointment-manage-content { padding:22px 18px; }
            .appointment-manage-header h1 { font-size:23px; }
            .appointment-detail-grid { grid-template-columns:1fr; gap:15px; }
            .appointment-actions, .appointment-button { width:100%; }
        }
    </style>

    <main class="appointment-manage-page">
        <div class="appointment-manage-shell">
            @if(session('success'))
                <div class="appointment-alert success" role="status">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="appointment-alert error" role="alert">{{ session('error') }}</div>
            @endif

            <article class="appointment-manage-card">
                <header class="appointment-manage-header">
                    <h1>Gérer mon rendez-vous</h1>
                    <p>Retrouvez ici toutes les informations utiles et les actions disponibles.</p>
                    <span class="appointment-status {{ $appointment->isCancelled() ? 'cancelled' : '' }}">{{ $appointment->status_label }}</span>
                </header>

                <div class="appointment-manage-content">
                    <dl class="appointment-detail-grid">
                        <div><dt>Date et heure</dt><dd>{{ $appointment->appointment_date?->format('d/m/Y à H:i') }}</dd></div>
                        <div><dt>Durée</dt><dd>{{ $appointment->formatted_duration ?? ((int) $appointment->duration.' min') }}</dd></div>
                        <div><dt>Prestation</dt><dd>{{ $appointment->product?->name ?? 'Rendez-vous' }}</dd></div>
                        <div><dt>Praticien</dt><dd>{{ $appointment->user?->company_name ?: $appointment->user?->name }}</dd></div>
                        <div><dt>Mode</dt><dd>{{ $appointment->getResolvedModeLabel() }}</dd></div>
                        @if($mode !== 'visio' && $location)
                            <div><dt>Lieu</dt><dd>{!! nl2br(e($location)) !!}</dd></div>
                        @endif
                    </dl>

                    @if($appointment->isCancelled())
                        <p class="appointment-help">Ce rendez-vous est annulé. Aucun nouveau rappel ne sera envoyé.</p>
                    @elseif($canManage)
                        @if($appointment->isPendingPayment() && $appointment->stripe_session_id)
                            <div class="appointment-alert error" role="status">
                                Votre créneau est réservé temporairement, mais le paiement n’est pas encore finalisé.
                            </div>
                        @endif
                        <div class="appointment-actions">
                            @if($appointment->isPendingPayment() && $appointment->stripe_session_id)
                                <a class="appointment-button primary" href="{{ route('appointment.confirmation.payment.resume', $appointment->token) }}">Reprendre le paiement</a>
                            @endif
                            <a class="appointment-button primary" href="{{ route('appointment.confirmation.reschedule.form', $appointment->token) }}">Modifier le créneau</a>
                            <a class="appointment-button secondary" id="appointment-calendar-link" href="{{ $icsUrl }}" data-google-calendar-url="{{ $googleCalendarUrl }}">Ajouter à mon calendrier</a>
                        </div>

                        <form method="POST" action="{{ route('appointment.confirmation.cancel', $appointment->token) }}" onsubmit="return confirm('Confirmer l’annulation de ce rendez-vous ?');">
                            @csrf
                            <div class="appointment-reason">
                                <label for="cancellation_reason">Motif facultatif, sans information médicale</label>
                                <input id="cancellation_reason" name="cancellation_reason" maxlength="500" placeholder="Ex. : empêchement personnel">
                            </div>
                            <div class="appointment-actions"><button class="appointment-button danger" type="submit">Annuler le rendez-vous</button></div>
                        </form>

                        @if($deadline)
                            <p class="appointment-help">Modification et annulation possibles en ligne jusqu’au {{ $deadline->format('d/m/Y à H:i') }}.</p>
                        @endif
                        @if($appointment->requiresFinancialFollowUp())
                            <p class="appointment-help">L’annulation du rendez-vous n’entraîne pas automatiquement un remboursement. Votre praticien vous contactera si une régularisation est nécessaire.</p>
                        @endif
                    @else
                        <p class="appointment-help">La modification en ligne n’est plus disponible. Contactez directement votre praticien pour toute demande.</p>
                    @endif
                </div>
            </article>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const link = document.getElementById('appointment-calendar-link');
            if (link && /Android/i.test(window.navigator.userAgent || '') && link.dataset.googleCalendarUrl) {
                link.href = link.dataset.googleCalendarUrl;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
            }
        });
    </script>
</x-app-layout>

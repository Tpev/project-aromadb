{{-- resources/views/appointments/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-center">
            <h2 class="font-semibold text-xl" style="color: #647a0b;">
                {{ __('Liste des Rendez-vous') }}
            </h2>
        </div>
    </x-slot>

    {{-- Font Awesome --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <div class="am-page">
        @if(session('success'))
            <div class="alert alert-success mb-4" role="status">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-4" role="alert">{{ session('error') }}</div>
        @endif

        {{-- En-tête + recherche + bouton --}}
        <div class="am-card mb-4">
            <div class="am-card-body">
                <h1 class="page-title mb-2 text-center">Liste des rendez-vous</h1>
                <p class="text-muted small text-center mb-4">
                    Visualisez vos rendez-vous dans le calendrier et retrouvez-les en dessous,
                    séparés en rendez-vous à venir et passés.
                </p>

@php
    $user = auth()->user();
    $canCreateAppointment = $user->canUseFeature('appointement'); // feature key

    // Determine the minimum license family that includes this feature
    $plansConfig = config('license_features.plans', []);

    // Ignore trial (temporary)
    $familyOrder = ['free', 'starter', 'pro', 'premium'];

    $requiredFamily = null;
    foreach ($familyOrder as $family) {
        if (in_array('appointement', $plansConfig[$family] ?? [], true)) {
            $requiredFamily = $family;
            break;
        }
    }

    $familyLabels = [
        'free'    => __('Gratuit'),
        'starter' => __('Starter'),
        'pro'     => __('PRO'),
        'premium' => __('Premium'),
    ];

    $requiredLabel = $requiredFamily
        ? ($familyLabels[$requiredFamily] ?? $requiredFamily)
        : __('une formule supérieure');
@endphp

<div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3">
    {{-- Recherche client --}}
    <input type="text"
           id="search"
           class="form-control am-search-input"
           placeholder="Recherche par client..."
           onkeyup="filterTable()">

    {{-- Bouton pour créer un nouveau rendez-vous --}}
    <div style="position: relative; display: inline-flex;">
        @if($canCreateAppointment)
            <a href="{{ route('appointments.create') }}"
               class="btn-primary">
                <i class="fas fa-plus me-2"></i>
                Créer un rendez-vous
            </a>
        @else
            {{-- Greyed-out version that sends to pricing --}}
            <a href="/license-tiers/pricing"
               class="btn"
               style="
                   background-color: #e5e7eb;
                   border: 1px solid #d1d5db;
                   color: #6b7280;
                   font-weight: 600;
                   padding: 0.5rem 1rem;
                   border-radius: 9999px;
               ">
                <i class="fas fa-plus me-2"></i>
                Créer un rendez-vous
            </a>

            {{-- Small pill showing required plan --}}
            <div style="
                position: absolute;
                top: -10px;
                right: -10px;
                background-color: #fff1d6;
                border: 1px solid rgba(250, 204, 21, 0.4);
                border-radius: 9999px;
                padding: 2px 8px;
                font-size: 9px;
                font-weight: 600;
                color: #854f38;
                display: inline-flex;
                align-items: center;
                gap: 4px;
                box-shadow: 0 1px 2px rgba(0,0,0,0.08);
            ">
                <svg xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 20 20"
                     fill="currentColor"
                     style="width: 12px; height: 12px;">
                    <path fill-rule="evenodd"
                          d="M10 2a4 4 0 00-4 4v2H5a2 2 0 
                             00-2 2v6a2 2 0 002 2h10a2 2 0 
                             002-2v-6a2 2 0 00-2-2h-1V6a4 4 
                             0 00-4-4zm0 6a2 2 0 00-2 2v2a2 2 0 104 0v-2a2 2 0 00-2-2z"
                          clip-rule="evenodd" />
                </svg>
                <span>
                    {{ __('À partir de :') }}
                    <span style="font-weight: 700;">{{ $requiredLabel }}</span>
                </span>
            </div>
        @endif
    </div>
</div>

<form action="{{ route('appointments.index') }}" method="GET" class="google-events-toggle-form mt-3">
    <input type="hidden" name="appointment_status" value="{{ $appointmentStatusFilter }}">
    <input type="hidden" name="calendar_source" id="desktop-calendar-source" value="{{ $showGoogleEvents ? 'all' : 'olithea' }}">
    <label for="desktop-google-events-toggle" class="google-events-toggle">
        <span class="google-events-toggle-copy">
            <span class="google-events-toggle-title">Afficher les événements Google</span>
            <span class="google-events-toggle-state">{{ $showGoogleEvents ? 'Affichés dans le calendrier' : 'Masqués du calendrier' }}</span>
        </span>
        <span class="google-events-switch">
            <input type="checkbox"
                   id="desktop-google-events-toggle"
                   @checked($showGoogleEvents)
                   aria-label="Afficher les événements Google dans le calendrier"
                   onchange="document.getElementById('desktop-calendar-source').value = this.checked ? 'all' : 'olithea'; this.form.submit();">
            <span class="google-events-switch-track" aria-hidden="true"></span>
        </span>
    </label>
</form>

            </div>
        </div>

        {{-- Calendrier --}}
        <div class="am-card mb-4">
            <div class="am-card-header text-center">
                <h2 class="h5 mb-1" style="color:#647a0b;">
                    Calendrier des rendez-vous
                </h2>
                <p class="text-muted small mb-0">
                    Cliquez sur une période libre pour ajouter un rendez-vous ou une indisponibilité. Cliquez sur un élément existant pour l’ouvrir.
                </p>
            </div>
            <div class="am-card-body">
                <div id="calendar" class="am-calendar-wrapper"></div>
            </div>
        </div>

        <div id="calendar-action-modal" class="calendar-action-modal" aria-hidden="true">
            <div class="calendar-action-backdrop" data-calendar-modal-close></div>
            <section class="calendar-action-panel" role="dialog" aria-modal="true" aria-labelledby="calendar-action-title">
                <button type="button" class="calendar-action-close" data-calendar-modal-close aria-label="Fermer">×</button>

                <div id="calendar-action-choice">
                    <p class="calendar-action-eyebrow">Agenda</p>
                    <h3 id="calendar-action-title">Que souhaitez-vous ajouter ?</h3>
                    <p id="calendar-action-selected-slot" class="calendar-action-copy"></p>

                    <div class="calendar-action-options">
                        <a id="calendar-create-appointment" href="{{ route('appointments.create') }}" class="calendar-action-option calendar-action-option-primary">
                            <i class="fas fa-user-clock" aria-hidden="true"></i>
                            <span>
                                <strong>Créer un rendez-vous</strong>
                                <small>Le formulaire RDV sera prérempli avec ce créneau.</small>
                            </span>
                        </a>
                        <button type="button" id="calendar-show-unavailability" class="calendar-action-option">
                            <i class="fas fa-calendar-times" aria-hidden="true"></i>
                            <span>
                                <strong>Créer une indisponibilité</strong>
                                <small>Bloquer cette période pour les nouvelles réservations.</small>
                            </span>
                        </button>
                    </div>
                </div>

                <div id="calendar-unavailability-form-panel" hidden>
                    <button type="button" id="calendar-action-back" class="calendar-action-back">← Retour au choix</button>
                    <p class="calendar-action-eyebrow">Nouvelle indisponibilité</p>
                    <h3>Bloquer une période</h3>
                    <p class="calendar-action-copy">Les rendez-vous déjà présents seront conservés. Un avertissement vous demandera confirmation en cas de chevauchement.</p>

                    @if(old('unavailability_source') === 'calendar' && session('unavailability_conflicts'))
                        @php
                            $calendarConflicts = session('unavailability_conflicts');
                        @endphp
                        <div class="calendar-conflict-warning" role="alert">
                            <strong>Chevauchement détecté</strong>
                            <ul>
                                @foreach($calendarConflicts['appointments'] ?? [] as $conflict)
                                    <li>{{ $conflict }}</li>
                                @endforeach
                                @foreach($calendarConflicts['unavailabilities'] ?? [] as $conflict)
                                    <li>{{ $conflict }}</li>
                                @endforeach
                            </ul>
                            <span>Enregistrez à nouveau pour confirmer. Aucun rendez-vous existant ne sera supprimé.</span>
                        </div>
                    @endif

                    @if(old('unavailability_source') === 'calendar' && $errors->any())
                        <div class="calendar-conflict-warning" role="alert">
                            <strong>Vérifiez les informations saisies</strong>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('unavailabilities.store') }}" method="POST" class="calendar-unavailability-form">
                        @csrf
                        <input type="hidden" name="unavailability_source" value="calendar">
                        <input type="hidden" name="confirm_conflicts" value="{{ old('unavailability_source') === 'calendar' ? (session('unavailability_conflicts.confirmation_token') ?? '') : '' }}">

                        <div class="calendar-form-grid">
                            <label>
                                <span>Date de début</span>
                                <input type="date" id="calendar-unavailability-start-date" name="start_date" value="{{ old('unavailability_source') === 'calendar' ? old('start_date') : '' }}" required>
                            </label>
                            <label>
                                <span>Heure de début</span>
                                <input type="time" id="calendar-unavailability-start-time" name="start_time" value="{{ old('unavailability_source') === 'calendar' ? old('start_time') : '' }}" required>
                            </label>
                            <label>
                                <span>Date de fin</span>
                                <input type="date" id="calendar-unavailability-end-date" name="end_date" value="{{ old('unavailability_source') === 'calendar' ? old('end_date') : '' }}" required>
                            </label>
                            <label>
                                <span>Heure de fin</span>
                                <input type="time" id="calendar-unavailability-end-time" name="end_time" value="{{ old('unavailability_source') === 'calendar' ? old('end_time') : '' }}" required>
                            </label>
                        </div>

                        <label class="calendar-form-reason">
                            <span>Motif <small>(optionnel, visible uniquement dans votre agenda)</small></span>
                            <input type="text" name="reason" maxlength="255" value="{{ old('unavailability_source') === 'calendar' ? old('reason') : '' }}" placeholder="Ex. Congés, formation, absence personnelle">
                        </label>

                        <button type="submit" class="calendar-unavailability-submit">
                            <i class="fas fa-calendar-times" aria-hidden="true"></i>
                            Enregistrer l’indisponibilité
                        </button>
                    </form>
                </div>
            </section>
        </div>

        <style>
            .calendar-action-modal{position:fixed;inset:0;z-index:1055;display:none;align-items:center;justify-content:center;padding:1rem}.calendar-action-modal.is-open{display:flex}.calendar-action-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(3px)}.calendar-action-panel{position:relative;width:min(100%,620px);max-height:calc(100vh - 2rem);overflow:auto;border-radius:1.25rem;background:#fff;padding:1.5rem;box-shadow:0 24px 70px rgba(15,23,42,.28)}.calendar-action-close{position:absolute;right:1rem;top:1rem;width:2.25rem;height:2.25rem;border:0;border-radius:999px;background:#f1f5f9;color:#475569;font-size:1.5rem;line-height:1}.calendar-action-eyebrow{margin:0 0 .25rem;color:#647a0b;font-size:.75rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.calendar-action-panel h3{margin:0;color:#1e293b;font-size:1.35rem;font-weight:800}.calendar-action-copy{margin:.5rem 0 1rem;color:#64748b;font-size:.875rem}.calendar-action-options{display:grid;gap:.75rem}.calendar-action-option{display:flex;width:100%;align-items:center;gap:1rem;border:1px solid #dbe2be;border-radius:1rem;background:#fbfcf7;padding:1rem;color:#334155;text-align:left;text-decoration:none;transition:.15s}.calendar-action-option:hover{border-color:#647a0b;background:#f5f7eb;color:#334155;transform:translateY(-1px)}.calendar-action-option i{display:flex;width:2.75rem;height:2.75rem;flex:0 0 auto;align-items:center;justify-content:center;border-radius:.8rem;background:#fff;color:#647a0b}.calendar-action-option span{display:grid;gap:.15rem}.calendar-action-option strong{font-size:.95rem}.calendar-action-option small{color:#64748b;font-size:.78rem}.calendar-action-option-primary{background:#647a0b;border-color:#647a0b;color:#fff}.calendar-action-option-primary:hover{background:#526409;color:#fff}.calendar-action-option-primary i{background:rgba(255,255,255,.16);color:#fff}.calendar-action-option-primary small{color:rgba(255,255,255,.82)}.calendar-action-back{margin:0 0 1rem;padding:0;border:0;background:transparent;color:#647a0b;font-size:.8rem;font-weight:700}.calendar-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:.8rem}.calendar-unavailability-form label{display:grid;gap:.35rem;color:#475569;font-size:.78rem;font-weight:700}.calendar-unavailability-form input{width:100%;min-height:2.75rem;border:1px solid #cbd5e1;border-radius:.75rem;padding:.55rem .7rem;color:#1e293b}.calendar-unavailability-form input:focus{border-color:#647a0b;box-shadow:0 0 0 3px rgba(100,122,11,.12);outline:0}.calendar-form-reason{margin-top:.85rem}.calendar-form-reason small{font-weight:500}.calendar-unavailability-submit{display:inline-flex;width:100%;align-items:center;justify-content:center;gap:.5rem;margin-top:1rem;border:0;border-radius:.8rem;background:#647a0b;padding:.75rem 1rem;color:#fff;font-size:.9rem;font-weight:800}.calendar-conflict-warning{margin:0 0 1rem;border:1px solid #f6c453;border-radius:.8rem;background:#fff8df;padding:.8rem;color:#7c5512;font-size:.8rem}.calendar-conflict-warning ul{margin:.4rem 0 .4rem 1.1rem;padding:0}@media(max-width:575px){.calendar-action-panel{padding:1.2rem}.calendar-form-grid{grid-template-columns:1fr}.calendar-action-option{padding:.85rem}}
        </style>

        @php
            $appointmentStatusOptions = [
                'active' => 'Actifs',
                'cancelled' => 'Annulés',
                'all' => 'Tous',
            ];
        @endphp
        <nav class="appointment-status-filter mb-4" aria-label="Filtrer les rendez-vous par statut">
            <span class="appointment-status-filter-label">Afficher</span>
            <div class="appointment-status-filter-tabs">
                @foreach($appointmentStatusOptions as $statusValue => $statusLabel)
                    <a href="{{ request()->fullUrlWithQuery(['appointment_status' => $statusValue]) }}"
                       class="appointment-status-filter-tab {{ $appointmentStatusFilter === $statusValue ? 'is-active' : '' }}"
                       @if($appointmentStatusFilter === $statusValue) aria-current="page" @endif>
                        {{ $statusLabel }}
                        <span>{{ $appointmentStatusCounts[$statusValue] }}</span>
                    </a>
                @endforeach
            </div>
        </nav>

        {{-- ============================
             Rendez-vous à venir
        ============================= --}}
        <div class="am-card mb-4">
            <div class="am-card-header text-center">
                <h2 class="h5 mb-1" style="color:#647a0b;">
                    Rendez-vous à venir
                </h2>
                <p class="text-muted small mb-0">
                    Tri possible sur chaque colonne. Cliquez sur le nom pour ouvrir la fiche client, ou ailleurs sur la ligne pour ouvrir le rendez-vous.
                </p>
            </div>

            <div class="am-card-body">
                @if($rendezVousAVenir->isNotEmpty())
                    <div class="am-table-wrapper">
                        <table class="table appointment-table"
                               id="upcomingTable">
                            <thead>
                                <tr>
                                    <th onclick="sortTable('upcomingTable', 0)">
                                        Nom du client
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('upcomingTable', 1)">
                                        Date du rendez-vous
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('upcomingTable', 2)">
                                        Durée
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('upcomingTable', 3)">
                                        Produit
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th class="appointment-status-column" onclick="sortTable('upcomingTable', 4)">
                                        Statut
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th class="appointment-actions-column">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rendezVousAVenir as $appointment)
                                    <tr class="table-row am-row-clickable {{ $appointment->isCancelled() ? 'am-row-cancelled' : '' }}"
                                        data-url="{{ route('appointments.show', $appointment->id) }}">
                                        {{-- Nom du client / externe --}}
                                        <td>
                                            @if($appointment->external)
                                                <span class="d-inline-flex align-items-center justify-content-center gap-1 text-secondary">
                                                    <i class="fas fa-link small text-muted"></i>
                                                    {{ $appointment->notes ?: 'Occupé' }}
                                                </span>
                                            @elseif($appointment->clientProfile)
                                                <a href="{{ route('client_profiles.show', $appointment->clientProfile) }}"
                                                   class="am-client-link d-inline-flex align-items-center justify-content-center gap-1"
                                                   title="Ouvrir la fiche client">
                                                    <i class="fas fa-user small" style="color:#647a0b;"></i>
                                                    {{ optional($appointment->clientProfile)->first_name }}
                                                    {{ optional($appointment->clientProfile)->last_name }}
                                                </a>
                                            @else
                                                <span class="text-muted">Client indisponible</span>
                                            @endif
                                        </td>

                                        {{-- Date + heure --}}
                                        <td>
                                            <div class="d-flex flex-column align-items-center">
                                                <span>
                                                    <i class="fas fa-calendar-alt me-1 small" style="color:#647a0b;"></i>
                                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d/m/Y') }}
                                                </span>
                                                <span class="small text-muted mt-1">
                                                    <i class="fas fa-clock me-1 small" style="color:#647a0b;"></i>
                                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('H:i') }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Durée --}}
                                        <td>
                                            {{ $appointment->duration }} min
                                        </td>

                                        {{-- Produit --}}
                                        <td>
                                            {{ $appointment->product->name ?? '—' }}
                                        </td>

                                        {{-- Statut --}}
                                        <td class="appointment-status-column">
                                            @include('appointments.partials.tracking-status', ['appointment' => $appointment])
                                        </td>
                                        {{-- Actions (masquées pour external) --}}
                                        <td class="appointment-actions-column">
                                            @unless($appointment->external)
                                                <div class="appointment-actions-stack d-flex flex-column align-items-center gap-2">
                                                    @include('appointments.partials.tracking-actions', ['appointment' => $appointment])

                                                    {{-- Marquer comme complété --}}
                                                    @if (!$appointment->isCompleted() && !$appointment->isCancelled())
                                                        <form action="{{ route('appointments.completeindex', $appointment->id) }}"
                                                              method="POST"
                                                              class="btn-complete-form">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit"
                                                                    class="btn-complete"
                                                                    onclick="return confirm('Marquer ce rendez-vous comme complété ?')">
                                                                <i class="fas fa-check-circle"></i>
                                                                Compléter
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @endunless
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted small text-center mb-0">
                        {{ $appointmentStatusFilter === 'cancelled' ? 'Aucun rendez-vous annulé à venir.' : 'Aucun rendez-vous à venir pour le moment.' }}
                    </p>
                @endif
            </div>
        </div>

        {{-- ============================
             Rendez-vous passés
        ============================= --}}
        <div class="am-card mb-5">
            <div class="am-card-header text-center">
                <h2 class="h5 mb-1" style="color:#854f38;">
                    Rendez-vous passés
                </h2>
                <p class="text-muted small mb-0">
                    Historique de vos rendez-vous déjà effectués.
                </p>
            </div>

            <div class="am-card-body">
                @if($rendezVousPasses->isNotEmpty())
                    <div class="am-table-wrapper">
                        <table class="table appointment-table"
                               id="pastTable">
                            <thead>
                                <tr>
                                    <th onclick="sortTable('pastTable', 0)">
                                        Nom du client
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('pastTable', 1)">
                                        Date du rendez-vous
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('pastTable', 2)">
                                        Durée
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('pastTable', 3)">
                                        Produit
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th class="appointment-status-column" onclick="sortTable('pastTable', 4)">
                                        Statut
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th class="appointment-actions-column">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rendezVousPasses as $appointment)
                                    <tr class="table-row am-row-clickable {{ $appointment->isCancelled() ? 'am-row-cancelled' : '' }}"
                                        data-url="{{ route('appointments.show', $appointment->id) }}">
                                        {{-- Nom du client / externe --}}
                                        <td>
                                            @if($appointment->external)
                                                <span class="d-inline-flex align-items-center justify-content-center gap-1 text-secondary">
                                                    <i class="fas fa-link small text-muted"></i>
                                                    {{ $appointment->notes ?: 'Occupé' }}
                                                </span>
                                            @elseif($appointment->clientProfile)
                                                <a href="{{ route('client_profiles.show', $appointment->clientProfile) }}"
                                                   class="am-client-link d-inline-flex align-items-center justify-content-center gap-1"
                                                   title="Ouvrir la fiche client">
                                                    <i class="fas fa-user small" style="color:#854f38;"></i>
                                                    {{ optional($appointment->clientProfile)->first_name }}
                                                    {{ optional($appointment->clientProfile)->last_name }}
                                                </a>
                                            @else
                                                <span class="text-muted">Client indisponible</span>
                                            @endif
                                        </td>

                                        {{-- Date + heure --}}
                                        <td>
                                            <div class="d-flex flex-column align-items-center">
                                                <span>
                                                    <i class="fas fa-calendar-alt me-1 small" style="color:#854f38;"></i>
                                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d/m/Y') }}
                                                </span>
                                                <span class="small text-muted mt-1">
                                                    <i class="fas fa-clock me-1 small" style="color:#854f38;"></i>
                                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('H:i') }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Durée --}}
                                        <td>
                                            {{ $appointment->duration }} min
                                        </td>

                                        {{-- Produit --}}
                                        <td>
                                            {{ $appointment->product->name ?? '—' }}
                                        </td>

                                        <td class="appointment-status-column">
                                            @include('appointments.partials.tracking-status', ['appointment' => $appointment])
                                        </td>
                                        {{-- Actions (masquées pour external) --}}
                                        <td class="appointment-actions-column">
                                            @unless($appointment->external)
                                                <div class="appointment-actions-stack d-flex flex-column align-items-center gap-2">
                                                    @include('appointments.partials.tracking-actions', ['appointment' => $appointment])

                                                    {{-- Marquer comme complété (au cas où oublié) --}}
                                                    @if (!$appointment->isCompleted() && !$appointment->isCancelled())
                                                        <form action="{{ route('appointments.completeindex', $appointment->id) }}"
                                                              method="POST"
                                                              class="btn-complete-form">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit"
                                                                    class="btn-complete"
                                                                    onclick="return confirm('Marquer ce rendez-vous comme complété ?')">
                                                                <i class="fas fa-check-circle"></i>
                                                                Compléter
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @endunless
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted small text-center mb-0">
                        {{ $appointmentStatusFilter === 'cancelled' ? 'Aucun rendez-vous annulé dans l’historique.' : 'Aucun rendez-vous passé enregistré.' }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Meta CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Scripts personnalisés --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialisation du calendrier
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: [
                    FullCalendar.dayGridPlugin,
                    FullCalendar.timeGridPlugin,
                    FullCalendar.interactionPlugin,
                    FullCalendar.listPlugin,
                    FullCalendar.bootstrapPlugin
                ],
                locale: 'fr',
                initialView: 'dayGridMonth',
                themeSystem: 'bootstrap',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    month: 'Mois',
                    week:  'Semaine',
                    day:   'Jour',
                },
                firstDay: 1, // Lundi
                dateClick: function(info) {
                    openCalendarAction(info.date, info.allDay);
                },
                events: @json($events),
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        info.jsEvent.preventDefault();
                    }
                }
            });

            calendar.render();

            const modal = document.getElementById('calendar-action-modal');
            const choicePanel = document.getElementById('calendar-action-choice');
            const unavailabilityPanel = document.getElementById('calendar-unavailability-form-panel');
            const appointmentLink = document.getElementById('calendar-create-appointment');
            const selectedSlotCopy = document.getElementById('calendar-action-selected-slot');
            const appointmentCreateUrl = @json(route('appointments.create'));

            function localDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function localTime(date) {
                return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
            }

            function openCalendarAction(date, allDay) {
                const start = new Date(date.getTime());
                const end = new Date(date.getTime());
                if (allDay) {
                    start.setHours(0, 0, 0, 0);
                    end.setDate(end.getDate() + 1);
                    end.setHours(0, 0, 0, 0);
                } else {
                    end.setMinutes(end.getMinutes() + 60);
                }

                document.getElementById('calendar-unavailability-start-date').value = localDate(start);
                document.getElementById('calendar-unavailability-start-time').value = localTime(start);
                document.getElementById('calendar-unavailability-end-date').value = localDate(end);
                document.getElementById('calendar-unavailability-end-time').value = localTime(end);

                const params = new URLSearchParams({ appointment_date: localDate(start) });
                if (!allDay) params.set('appointment_time', localTime(start));
                appointmentLink.href = `${appointmentCreateUrl}?${params.toString()}`;

                selectedSlotCopy.textContent = allDay
                    ? `Le ${start.toLocaleDateString('fr-FR')}`
                    : `Le ${start.toLocaleDateString('fr-FR')} à ${localTime(start)}`;

                choicePanel.hidden = false;
                unavailabilityPanel.hidden = true;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeCalendarAction() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            document.querySelectorAll('[data-calendar-modal-close]').forEach(button => button.addEventListener('click', closeCalendarAction));
            document.getElementById('calendar-show-unavailability').addEventListener('click', function() {
                choicePanel.hidden = true;
                unavailabilityPanel.hidden = false;
                document.getElementById('calendar-unavailability-start-date').focus();
            });
            document.getElementById('calendar-action-back').addEventListener('click', function() {
                choicePanel.hidden = false;
                unavailabilityPanel.hidden = true;
            });
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) closeCalendarAction();
            });

            @if(old('unavailability_source') === 'calendar')
                choicePanel.hidden = true;
                unavailabilityPanel.hidden = false;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            @endif

            // Clic sur la ligne -> show rendez-vous
            const rows = document.querySelectorAll('.table-row');
            rows.forEach(row => {
                row.addEventListener('click', function(e) {
                    // Pas de redirection si clic sur bouton ou lien
                    if (e.target.closest('button') || e.target.closest('a')) return;
                    const url = this.getAttribute('data-url');
                    if (url) window.location.href = url;
                });
            });
        });

        // Filtrage global (deux tableaux)
        function filterTable() {
            let input  = document.getElementById('search');
            let filter = input.value.toLowerCase();

            const rows = document.querySelectorAll('.appointment-table tbody tr');

            rows.forEach(row => {
                let firstCell = row.querySelector('td');
                if (firstCell) {
                    let txtValue = firstCell.textContent || firstCell.innerText;
                    row.style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            });
        }

        // Tri par table + colonne
        function sortTable(tableId, n) {
            let table = document.getElementById(tableId);
            if (!table) return;

            let rows = table.rows;
            let switching = true;
            let asc = true;
            let switchcount = 0;

            while (switching) {
                switching = false;
                for (let i = 1; i < rows.length - 1; i++) {
                    let shouldSwitch = false;
                    let x = rows[i].getElementsByTagName('td')[n];
                    let y = rows[i + 1].getElementsByTagName('td')[n];

                    if (!x || !y) continue;

                    let xContent = x.innerText.toLowerCase();
                    let yContent = y.innerText.toLowerCase();

                    if (asc ? (xContent > yContent) : (xContent < yContent)) {
                        shouldSwitch = true;
                        rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                        switching = true;
                        switchcount++;
                        break;
                    }
                }
                if (!switching && switchcount === 0 && asc) {
                    asc = false;
                    switching = true;
                }
            }
        }
    </script>

    {{-- Styles personnalisés --}}
    <style>
        .am-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem 3rem 1rem;
            text-align: center;
        }

        .page-title {
            font-size: 1.9rem;
            font-weight: 600;
            color: #647a0b;
        }

        .am-card {
            background-color: #ffffff;
            border-radius: 18px;
            border: 1px solid #e7ebd8;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.04);
        }

        .am-card-header {
            padding: 16px 22px 10px 22px;
            border-bottom: 1px solid #edf0dd;
            background: linear-gradient(180deg, #fafcf3, #ffffff);
            border-radius: 18px 18px 0 0;
        }

        .am-card-body {
            padding: 20px 22px 22px 22px;
        }

        .am-calendar-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .am-search-input {
            border-radius: 9999px;
            border-color: #854f38;
            padding-inline: 16px;
            max-width: 280px;
        }

        .google-events-toggle-form {
            display: flex;
            justify-content: center;
        }

        .appointment-status-filter {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .appointment-status-filter-label {
            color: #6b7280;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .appointment-status-filter-tabs {
            display: inline-flex;
            gap: 4px;
            padding: 4px;
            border: 1px solid #dfe5c9;
            border-radius: 8px;
            background: #ffffff;
        }

        .appointment-status-filter-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 34px;
            padding: 6px 11px;
            border-radius: 5px;
            color: #4b5563;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
        }

        .appointment-status-filter-tab:hover {
            background: #f5f7eb;
            color: #526509;
        }

        .appointment-status-filter-tab.is-active {
            background: #647a0b;
            color: #ffffff;
        }

        .appointment-status-filter-tab span {
            min-width: 20px;
            padding: 1px 5px;
            border-radius: 999px;
            background: rgba(100, 122, 11, 0.12);
            text-align: center;
            font-size: 0.7rem;
        }

        .appointment-status-filter-tab.is-active span {
            background: rgba(255, 255, 255, 0.2);
        }

        .google-events-toggle {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            min-width: 310px;
            padding: 10px 12px 10px 16px;
            border: 1px solid #dfe5c9;
            border-radius: 8px;
            background: #ffffff;
            cursor: pointer;
            text-align: left;
        }

        .google-events-toggle-copy {
            display: flex;
            flex: 1;
            flex-direction: column;
            min-width: 0;
        }

        .google-events-toggle-title {
            color: #26321f;
            font-size: 0.86rem;
            font-weight: 600;
        }

        .google-events-toggle-state {
            color: #6b7280;
            font-size: 0.72rem;
        }

        .google-events-switch {
            position: relative;
            display: inline-flex;
            flex: 0 0 auto;
        }

        .google-events-switch input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
        }

        .google-events-switch-track {
            position: relative;
            display: block;
            width: 44px;
            height: 24px;
            border-radius: 999px;
            background: #cbd0c1;
            transition: background-color 0.2s ease;
        }

        .google-events-switch-track::after {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            content: '';
            transition: transform 0.2s ease;
        }

        .google-events-switch input:checked + .google-events-switch-track {
            background: #647a0b;
        }

        .google-events-switch input:checked + .google-events-switch-track::after {
            transform: translateX(20px);
        }

        .google-events-switch input:focus-visible + .google-events-switch-track {
            outline: 3px solid rgba(100, 122, 11, 0.25);
            outline-offset: 2px;
        }

        .btn-primary {
            background-color: #647a0b;
            border-color: #647a0b;
            color: #ffffff;
            padding: 9px 22px;
            border-radius: 9999px;
            text-decoration: none;
            transition: background-color 0.2s ease, transform 0.1s ease;
            display: inline-flex;
            align-items: center;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .btn-primary:hover {
            background-color: #854f38;
            border-color: #854f38;
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* Wrapper pour scroll horizontal interne */
        .am-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .appointment-table {
            width: 100%;
            min-width: 900px; /* force le scroll horizontal sur petit écran */
            border-collapse: separate;
            border-spacing: 0 8px;
            margin-bottom: 0;
        }

        .appointment-table thead {
            background-color: #f5f6ea;
        }

        .appointment-table thead th {
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #4b5563;
            border-bottom: none;
            cursor: pointer;
            white-space: nowrap;
            text-align: center;
            padding: 0.75rem 1.25rem;
        }

        .appointment-table tbody tr {
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            border-radius: 14px;
            transition: background-color 0.2s ease, transform 0.1s ease, box-shadow 0.2s ease;
        }

        .appointment-table tbody tr:hover {
            background-color: #f5f7eb;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        }
        .appointment-table tbody tr.am-row-cancelled {
            background-color: #f8f9fa;
            opacity: 0.68;
            box-shadow: none;
        }

        .appointment-table tbody tr.am-row-cancelled:hover {
            background-color: #f3f4f6;
            transform: none;
            box-shadow: none;
            opacity: 0.82;
        }


        .appointment-table tbody td {
            text-align: center;
            padding: 0.85rem 1.4rem;
            border-top: none !important;
        }

        .appointment-table .appointment-status-column {
            width: 220px;
            min-width: 220px;
            padding-right: 0.75rem;
            padding-left: 0.75rem;
        }

        .appointment-table .appointment-actions-column {
            width: 210px;
            min-width: 210px;
            padding-right: 0.75rem;
            padding-left: 0.75rem;
        }

        .appointment-status-stack {
            display: grid !important;
            width: 190px;
            margin: 0 auto;
            gap: 6px !important;
            align-items: stretch !important;
        }

        .appointment-status-pill {
            box-sizing: border-box;
            display: flex !important;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
            min-height: 28px;
            padding: 5px 9px !important;
            border: 1px solid transparent;
            border-radius: 999px !important;
            font-size: 0.76rem;
            font-weight: 600;
            line-height: 1.1;
            white-space: nowrap;
        }

        .appointment-status-pill i {
            width: 15px;
            flex: 0 0 15px;
            text-align: center;
        }

        .appointment-status-label {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .appointment-actions-stack {
            min-width: 180px;
        }

        .am-row-clickable {
            cursor: pointer;
        }

        .am-client-link {
            color: inherit;
            font-weight: 600;
            text-decoration: none;
        }

        .am-client-link:hover,
        .am-client-link:focus-visible {
            color: #647a0b;
            text-decoration: underline;
        }

        /* Bouton "Générer une facture" */
        .btn-invoice {
            background-color: #647a0b;
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 9999px;
            text-decoration: none;
            transition: background-color 0.2s ease, transform 0.1s ease;
            display: inline-flex;
            align-items: center;
            font-size: 0.8rem;
            border: none;
            white-space: nowrap;
        }

        .btn-invoice i {
            margin-right: 4px;
        }

        .btn-invoice:hover {
            background-color: #854f38;
            transform: translateY(-1px);
            color: #ffffff;
        }

        /* Bouton "Marquer comme complété" */
        .btn-complete {
            background-color: #ffffff;
            color: #647a0b;
            padding: 6px 12px;
            border-radius: 9999px;
            border: 1px solid #647a0b;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.1s ease;
            display: inline-flex;
            align-items: center;
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .btn-complete i {
            margin-right: 4px;
        }

        .btn-complete:hover {
            background-color: #647a0b;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-complete-form {
            display: inline-block;
        }

        /* Soft badges */
        .bg-success-subtle {
            background-color: #e6f7ec !important;
        }
        .bg-warning-subtle {
            background-color: #fff7e6 !important;
        }
        .bg-secondary-subtle {
            background-color: #f3f4f6 !important;
        }
        .bg-danger-subtle {
            background-color: #fdecec !important;
        }

        .gap-2 {
            gap: .5rem !important;
        }
        .gap-3 {
            gap: 1rem !important;
        }
    </style>
</x-app-layout>

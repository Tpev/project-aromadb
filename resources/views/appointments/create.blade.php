{{-- resources/views/appointments/create_therapist.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer un Rendez-vous (Thérapeute)') }}
        </h2>
    </x-slot>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Flatpickr --}}
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    {{-- Select2 (searchable dropdown) --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .container { max-width: 820px; }
        .details-container {
            background-color: #f9f9f9; border-radius: 10px; padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,.1); margin: 0 auto;
        }
        .details-title { font-size: 1.9rem; font-weight: 700; color: #647a0b; margin-bottom: 20px; text-align: center; }
        .details-box { margin-bottom: 20px; }
        .details-label { font-weight: 700; color: #647a0b; display:block; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #854f38; border-radius: 5px; box-sizing: border-box; }
        .form-control:focus { border-color:#647a0b; outline: none; box-shadow: 0 0 5px rgba(100,122,11,.5); }
        .btn-primary, .btn-secondary {
            background-color:#647a0b; border:none; color:#fff; padding:10px 20px; border-radius:5px;
            display:inline-flex; align-items:center; cursor:pointer; transition:.3s; font-size:1rem;
        }
        .btn-primary:hover, .btn-secondary:hover { background-color:#854f38; }
        .text-red-500 { color:#e3342f; font-size:.875rem; margin-top:5px; }

        .time-slots-grid { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .time-slot-btn {
            border: 1px solid #854f38; background: #ffffff; color: #854f38;
            padding: 6px 10px; border-radius: 4px; cursor: pointer; min-width: 72px;
            text-align: center; font-size: 0.95rem;
        }
        .time-slot-btn.active,
        .time-slot-btn:hover {
            background: #647a0b; color: #ffffff; border-color: #647a0b;
        }

        /* Flatpickr override */
        .flatpickr-calendar { border:1px solid #647a0b; }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange { background:#647a0b; color:#fff; }
        .flatpickr-day:hover { background:#854f38; color:#fff; }
        .flatpickr-day.disabled { background:#e9ecef; color:#6c757d; cursor:not-allowed; }
        .flatpickr-day.disabled:hover { background:#e9ecef; color:#6c757d; }

        /* --- Availability / conflict UI (non-blocking) --- */
        .legend-dot {
            display:inline-block; width:10px; height:10px; border-radius:999px; margin-right:6px; vertical-align:middle;
        }
        .legend-ok { background:#647a0b; }
        .legend-warn { background:#f0ad4e; }
        .legend-bad { background:#d9534f; }
        .legend-out { background:#854f38; }

        .time-slot-btn.status-ok { border-color:#647a0b; color:#647a0b; }
        .time-slot-btn.status-ok.active, .time-slot-btn.status-ok:hover { background:#647a0b; color:#fff; border-color:#647a0b; }

        .time-slot-btn.status-warn { border-color:#f0ad4e; color:#8a5a00; background:#fff8e6; }
        .time-slot-btn.status-warn.active, .time-slot-btn.status-warn:hover { background:#f0ad4e; color:#fff; border-color:#f0ad4e; }

        .time-slot-btn.status-bad { border-color:#d9534f; color:#b02a37; background:#ffe9ea; }
        .time-slot-btn.status-bad.active, .time-slot-btn.status-bad:hover { background:#d9534f; color:#fff; border-color:#d9534f; }

        .time-slot-btn.status-out { border-color:#854f38; color:#854f38; background:#fff3ef; }
        .time-slot-btn.status-out.active, .time-slot-btn.status-out:hover { background:#854f38; color:#fff; border-color:#854f38; }

        .slot-inline-reasons { font-size: 0.82rem; color: #6c757d; margin-top: 6px; }
        .slot-inline-reasons ul { margin: 0; padding-left: 18px; }
        .slot-actions { display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-top:10px; }
        .slot-actions .btn-mini {
            background:#f3f3f3; border:1px solid #d1d5db; color:#111827; padding:6px 10px; border-radius:6px;
            cursor:pointer; font-size:0.92rem;
        }
        .slot-actions .btn-mini:hover { background:#e8e8e8; }

        /* Legend block placed near time slots */
        .slots-legend {
            display:flex;
            flex-wrap:wrap;
            gap:10px 18px;
            align-items:center;
            justify-content:flex-start;
            font-size:0.92rem;
            background:#ffffff;
            border:1px solid rgba(133,79,56,.25);
            border-radius:10px;
            padding:10px 12px;
            margin-top:10px;
        }
        .slots-legend .legend-item { white-space:nowrap; }

        /* Tooltip readability */
        .tooltip-inner { max-width: 360px; text-align: left; white-space: pre-line; }

        @media (max-width: 600px) {
            .details-container { padding:20px; }
            .details-title { font-size:1.5rem; }
            .btn-primary, .btn-secondary { width:100%; margin-bottom:10px; }
            .d-flex.justify-content-center.mt-4 { flex-direction:column; }
            .slots-legend { font-size:0.88rem; }
        }
    </style>

    @php
        $therapist = auth()->user();
        $practiceLocations = $therapist->practiceLocations ?? collect();

        // Group all products by prestation name
        $productsByName = ($products ?? collect())->groupBy('name');

        /**
         * Build a structure:
         * PRODUCT_VARIANTS[name][slug] = [
         *   'mode_label' => 'À Domicile',
         *   'products' => [ ['id'=>..., 'duration'=>..., 'price'=>...], ... ]
         * ]
         *
         * Also build display data for the prestation dropdown:
         * - show "à partir de" when different prices exist across variants
         */
        $productVariants = [];
        $productDisplay = []; // name => ['min_price'=>, 'has_multi_price'=>bool]

        foreach ($productsByName as $productName => $group) {
            $allPrices = $group->map(function($p){
                // adapt to your field name if needed
                return $p->price ?? $p->price_ttc ?? $p->price_new ?? null;
            })->filter(fn($v) => $v !== null)->map(fn($v) => (float)$v)->values();

            $hasMultiPrice = $allPrices->unique()->count() > 1;
            $minPrice = $allPrices->count() ? $allPrices->min() : null;

            $productDisplay[$productName] = [
                'min_price' => $minPrice,
                'has_multi_price' => $hasMultiPrice,
            ];

            $modes = [];

            foreach ($group as $p) {
                $price = $p->price ?? $p->price_ttc ?? $p->price_new ?? null;
                $duration = $p->duration ?? $p->duration_minutes ?? null;

                $add = function(string $slug, string $label) use (&$modes, $p, $duration, $price) {
                    if (!isset($modes[$slug])) {
                        $modes[$slug] = [
                            'slug' => $slug,
                            'mode_label' => $label,
                            'products' => [],
                        ];
                    }
                    $modes[$slug]['products'][] = [
                        'id' => $p->id,
                        'duration' => $duration,
                        'price' => $price,
                    ];
                };

                if (!empty($p->adomicile)) {
                    $add('domicile', 'À Domicile');
                }
                if (!empty($p->en_entreprise)) {
                    $add('entreprise', 'En entreprise');
                }
                if (!empty($p->dans_le_cabinet)) {
                    $add('cabinet', 'Dans le Cabinet');
                }
                if (!empty($p->visio) || !empty($p->en_visio)) {
                    $add('visio', 'En Visio');
                }
            }

            // sort products inside each mode by duration then price for nicer dropdown
            foreach ($modes as $slug => $modeData) {
                $productsList = collect($modeData['products'])
                    ->sortBy([
                        fn($a) => (int)($a['duration'] ?? 0),
                        fn($a) => (float)($a['price'] ?? 0),
                    ])
                    ->values()
                    ->all();

                $modes[$slug]['products'] = $productsList;
            }

            if (!empty($modes)) {
                $productVariants[$productName] = array_values($modes); // keep deterministic array for JS
            }
        }
    @endphp

    <div class="container mt-5">
        <div class="details-container">

            <h1 class="details-title">Créer un Rendez-vous</h1>

            {{-- ⚠️ Avertissement disponibilité (non bloquant) --}}
            <div id="slot-warning-banner" class="alert alert-warning text-center" style="display:none;">
                ⚠ Ce créneau présente un ou plusieurs conflits. Vous pouvez tout de même créer le rendez-vous.
                <div id="slot-warning-details" class="mt-2 text-start" style="max-width: 640px; margin: 0 auto;"></div>
            </div>

            @if(session('success'))
                <div class="alert alert-success text-center">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger text-center">
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('appointments.store') }}" method="POST">
                @csrf

                {{-- Therapist id hidden --}}
                <input type="hidden" name="therapist_id" value="{{ $therapist->id }}">

                {{-- Client selection --}}
                <div class="details-box">
                    <label class="details-label">Client</label>
                    @php
                        $selectedClientId = old('client_profile_id', request()->query('client_profile_id'));
                    @endphp

                    <select name="client_profile_id" id="client_profile_id" class="form-control js-client-select" required data-placeholder="Rechercher un client…">
                        <option value="" {{ $selectedClientId ? '' : 'selected' }}>
                            -- Sélectionner un client --
                        </option>

                        @foreach(($clientProfiles ?? collect())->sortBy(fn($c) => mb_strtolower(trim(($c->last_name ?? '').' '.($c->first_name ?? '')))) as $client)
                            <option value="{{ $client->id }}" @selected((string)$selectedClientId === (string)$client->id)>
                                {{ $client->first_name }} {{ $client->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Prestation --}}
                <div class="details-box">
                    <label class="details-label">Prestation</label>
                    <select id="product_name" name="product_name" class="form-control" required>
                        <option value="" disabled selected>Sélectionner une prestation</option>
                        @foreach($productVariants as $productName => $modes)
                            @php
                                $disp = $productDisplay[$productName] ?? null;
                                $minPrice = $disp['min_price'] ?? null;
                                $hasMulti = $disp['has_multi_price'] ?? false;

                                $priceLabel = '';
                                if ($minPrice !== null) {
                                    $priceLabel = $hasMulti
                                        ? ' — à partir de ' . rtrim(rtrim(number_format($minPrice, 2, '.', ''), '0'), '.') . '€'
                                        : ' — ' . rtrim(rtrim(number_format($minPrice, 2, '.', ''), '0'), '.') . '€';
                                }
                            @endphp
                            <option value="{{ $productName }}">{{ $productName }}{!! $priceLabel ? e($priceLabel) : '' !!}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Consultation mode --}}
                <div class="details-box" id="consultation-mode-section" style="display:none;">
                    <label class="details-label">Mode de Consultation</label>
                    <select id="consultation_mode" class="form-control" required>
                        <option value="" disabled selected>Sélectionner le mode</option>
                    </select>
                </div>

                {{-- Format (variant) --}}
                <div class="details-box" id="format-section" style="display:none;">
                    <label class="details-label">Format</label>
                    <select id="format_variant" class="form-control">
                        <option value="" disabled selected>Sélectionner un format</option>
                    </select>
                </div>

                {{-- Hidden final values --}}
                <input type="hidden" name="product_id" id="product_id">
                <input type="hidden" name="type" id="selected_mode_slug"> {{-- cabinet/visio/domicile/entreprise --}}
                <input type="hidden" name="force_availability_override" id="force_availability_override" value="0">

                {{-- Cabinet location --}}
                <div class="details-box" id="cabinet-location-section" style="display:none;">
                    <label class="details-label">Cabinet</label>
                    <select id="practice_location_id" name="practice_location_id" class="form-control">
                        <option value="" disabled selected>Choisir un cabinet</option>
                        @foreach($practiceLocations as $loc)
                            <option value="{{ $loc->id }}"
                                    data-address="{{ $loc->full_address ?? ($loc->address_line1 . ', ' . $loc->postal_code . ' ' . $loc->city) }}">
                                {{ $loc->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Cabinet address preview --}}
                <div class="details-box" id="therapist-address-section" style="display:none;">
                    <label class="details-label">Adresse du Cabinet</label>
                    <p id="therapist-address" class="form-control-static"></p>
                </div>

                {{-- Domicile address --}}
                <div class="details-box" id="client-address-section" style="display:none;">
                    <label class="details-label">Adresse (domicile / entreprise)</label>
                    <input type="text" id="address" name="address" class="form-control" placeholder="Adresse complète">
                </div>

                {{-- Date --}}
                <div class="details-box">
                    <label class="details-label">Date</label>
                    <input type="text" id="appointment_date" name="appointment_date" class="form-control" placeholder="Sélectionner une date" required>
                    <p id="date-loading-message" class="text-muted mt-1" style="display:none;">Chargement des dates…</p>

                    <div class="mt-2 flex items-center gap-2">
                        <input type="checkbox" id="backfill_past" name="backfill_past" value="1" class="rounded border-gray-300">
                        <label for="backfill_past" class="text-sm text-gray-600">
                            Saisie d’un rendez-vous passé (aucun e-mail, pas de contrôle de disponibilité)
                        </label>
                    </div>
                    <p id="backfill-hint" class="text-muted mt-1" style="display:none;">
                        Mode rendez-vous passé activé : choisissez une date passée et un horaire, puis enregistrez.
                    </p>
                </div>

                {{-- Time --}}
                <div class="details-box">
                    <label class="details-label">Horaire</label>
                    <input type="hidden" id="appointment_time" name="appointment_time">

                    {{-- Légende au bon endroit : juste à côté des créneaux --}}
                    <div class="slots-legend" aria-label="Légende des créneaux">
                        <span class="legend-item"><span class="legend-dot legend-ok"></span> Disponible</span>
                        <span class="legend-item"><span class="legend-dot legend-warn"></span> Attention</span>
                        <span class="legend-item"><span class="legend-dot legend-bad"></span> Conflit</span>
                        <span class="legend-item"><span class="legend-dot legend-out"></span> Hors dispo</span>
                    </div>

                    <div id="time-slots-container" class="mt-2 text-muted">Sélectionnez prestation, mode et date.</div>
                    <p id="no-slots-message" class="text-red-500 mt-1" style="display:none;"></p>
                </div>

                {{-- Status --}}
                <input type="hidden" name="status" value="Confirmé">

                {{-- Notes --}}
                <div class="details-box">
                    <label class="details-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-control"></textarea>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn-primary">Créer le RDV</button>
                    <a href="{{ url()->previous() }}" class="btn-secondary ms-3">Retour</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const PRODUCT_VARIANTS = @json($productVariants);

        let allowedDates = [];
        let currentSlotsRequestId = 0;

        $(function () {
            // Client dropdown: searchable + tri alpha (nom puis prénom)
            const $clientSelect = $('.js-client-select');
            if ($clientSelect.length) {
                $clientSelect.select2({
                    width: '100%',
                    placeholder: $clientSelect.data('placeholder') || 'Rechercher un client…'
                });
            }

            const fp = flatpickr("#appointment_date", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d-m-Y",
                minDate: "today",
                locale: "fr",
                disableMobile: true,
                enable: [() => true], // therapist can pick any future date; we'll warn if not recommended
            });

            function isBackfillMode() {
                return $('#backfill_past').is(':checked');
            }

            function escapeHtml(str) {
                return String(str ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function formatPrice(v) {
                if (v === null || v === undefined || v === '') return '';
                const n = Number(v);
                if (Number.isNaN(n)) return String(v);
                return (Math.round(n * 100) / 100).toString().replace('.', ',') + '€';
            }

            function conflictLabel(code) {
                switch (code) {
                    case 'overlap_internal': return 'Conflit avec un autre rendez-vous';
                    case 'overlap_external': return 'Conflit avec un agenda externe';
                    case 'outside_opening_hours': return 'En dehors des horaires d’ouverture';
                    case 'temporary_unavailability': return 'Indisponibilité temporaire';
                    case 'outside_dispo': return 'En dehors de vos disponibilités ponctuelles';
                    default: return 'Conflit / contrainte détectée';
                }
            }

            function showSlotWarning(conflicts, explanations) {
                const $banner = $('#slot-warning-banner');
                const $details = $('#slot-warning-details');

                if (!conflicts || !conflicts.length) {
                    $banner.hide();
                    $details.empty();
                    return;
                }

                const uniq = [...new Set(
                    (explanations && explanations.length ? explanations : conflicts.map(c => conflictLabel(c)))
                )];

                let html = '<div class="slot-inline-reasons"><strong>Détails :</strong><ul>';
                uniq.forEach(t => { html += `<li>${escapeHtml(t)}</li>`; });
                html += '</ul></div>';

                $details.html(html);
                $banner.show();
            }

            function hideSlotWarning() {
                $('#slot-warning-banner').hide();
                $('#slot-warning-details').empty();
            }

            function disposeSlotTooltips() {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    const t = bootstrap.Tooltip.getInstance(el);
                    if (t) t.dispose();
                });
            }

            function initSlotTooltips() {
                disposeSlotTooltips();

                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    new bootstrap.Tooltip(el, {
                        trigger: 'hover focus',
                        container: 'body',
                        html: true
                    });
                });
            }

            function resetSlotsUI() {
                $('#appointment_time').val('');
                $('#force_availability_override').val('0');
                $('#time-slots-container').html('<span class="text-muted">Sélectionnez prestation, mode et date.</span>');
                $('#no-slots-message').hide().text('');
                hideSlotWarning();
                disposeSlotTooltips();
            }

            function enableBackfillModeUI() {
                fp.set('minDate', null);
                fp.set('maxDate', 'today');
                fp.set('enable', [() => true]);
                fp.clear();

                resetSlotsUI();
                $('#date-loading-message').hide();
                $('#backfill-hint').show();
            }

            function disableBackfillModeUIAndRefresh() {
                fp.set('maxDate', null);
                fp.set('minDate', 'today');
                fp.set('enable', [() => true]);
                $('#backfill-hint').hide();
                refreshDates();
            }

            function buildTooltipHtml(conflicts, explanations) {
                const list = (explanations && explanations.length)
                    ? explanations
                    : (conflicts || []).map(c => conflictLabel(c));

                const uniq = [...new Set(list)].filter(Boolean);
                if (!uniq.length) return '';

                return uniq.map(t => escapeHtml(t)).join('<br>');
            }

            function normalizeSlots(slots) {
                return (slots || []).map(s => {
                    if (typeof s === 'string') {
                        return { start: s, conflicts: [], explanations: [], status: null };
                    }

                    let start = s.start || s.time || '';
                    if (start && start.includes('T')) start = start.substring(11, 16);

                    let conflicts = Array.isArray(s.conflicts) ? s.conflicts : [];
                    let explanations = Array.isArray(s.explanations) ? s.explanations : [];

                    if (!explanations.length && Array.isArray(s.reasons)) explanations = s.reasons;
                    if (!explanations.length && typeof s.reason === 'string' && s.reason.trim().length) explanations = [s.reason.trim()];

                    const status = (typeof s.status === 'string' && s.status) ? s.status : null;

                    const has_conflict =
                        !!(s.has_conflict || s.is_conflict || s.conflict || s.blocked || conflicts.length || explanations.length);

                    if (has_conflict && !conflicts.length && !status) {
                        conflicts = ['overlap_internal'];
                        if (!explanations.length) explanations = [conflictLabel('overlap_internal')];
                    }

                    return { start, conflicts, explanations, status };
                });
            }

            function computeStatus(conflicts, explicitStatus = null) {
                if (explicitStatus && ['ok','warn','bad','out'].includes(explicitStatus)) return explicitStatus;

                if (!conflicts || !conflicts.length) return 'ok';

                if (conflicts.includes('overlap_internal') || conflicts.includes('overlap_external')) return 'bad';

                if (
                    conflicts.includes('outside_opening_hours') ||
                    conflicts.includes('temporary_unavailability') ||
                    conflicts.includes('outside_dispo')
                ) return 'out';

                return 'warn';
            }

            function renderSlots(slots, options = {}) {
                if (!slots || !slots.length) {
                    $('#time-slots-container').html('<span class="text-muted">Aucun créneau.</span>');
                    $('#no-slots-message').text('Pas de créneaux pour ce jour').show();
                    disposeSlotTooltips();
                    return;
                }

                const normalized = normalizeSlots(slots);
                const forceConflictType = options.forceConflictType || null;
                const forceStatus = options.forceStatus || null;

                let html = '<div class="time-slots-grid">';
                normalized.forEach(s => {
                    let conflicts = Array.isArray(s.conflicts) ? [...s.conflicts] : [];
                    let explanations = Array.isArray(s.explanations) ? [...s.explanations] : [];

                    if (forceConflictType && !conflicts.includes(forceConflictType)) {
                        conflicts.push(forceConflictType);
                    }

                    if (!explanations.length && conflicts.length) {
                        explanations = conflicts.map(c => conflictLabel(c));
                    }

                    const status = forceStatus ? forceStatus : computeStatus(conflicts, s.status);
                    const tooltipHtml = buildTooltipHtml(conflicts, explanations);
                    const hasConflict = conflicts.length ? 1 : 0;

                    html += `
                        <button type="button"
                                class="time-slot-btn status-${status}"
                                data-time="${escapeHtml(s.start)}"
                                data-has-conflict="${hasConflict}"
                                data-conflicts="${encodeURIComponent(JSON.stringify(conflicts))}"
                                data-explanations="${encodeURIComponent(JSON.stringify(explanations))}"
                                ${tooltipHtml ? `data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="${tooltipHtml}"` : ''}>
                            ${escapeHtml(s.start)}
                        </button>
                    `;
                });
                html += '</div>';

                html += `
                    <div class="slot-actions">
                        <button type="button" id="show-all-hours" class="btn-mini">
                            Afficher tous les horaires (toutes les 15 min)
                        </button>
                        <span class="text-muted" style="font-size:0.9rem;">
                            Astuce : vous pouvez créer un RDV même en cas de conflit (un avertissement s’affichera).
                        </span>
                    </div>
                `;

                $('#time-slots-container').html(html);
                $('#no-slots-message').hide();

                initSlotTooltips();
            }

            function renderManualSlotsEvery15Min(forceOutsideDispo = false) {
                const slots = [];
                for (let h = 0; h < 24; h++) {
                    for (let m = 0; m < 60; m += 15) {
                        const hh = String(h).padStart(2, '0');
                        const mm = String(m).padStart(2, '0');
                        slots.push(`${hh}:${mm}`);
                    }
                }

                if (forceOutsideDispo) {
                    renderSlots(slots, { forceConflictType: 'outside_dispo', forceStatus: 'out' });
                } else {
                    renderSlots(slots);
                }
            }

            /**
             * THERAPIST endpoints
             * IMPORTANT: These routes must exist:
             * - appointments.available-dates-concrete-therapist
             * - appointments.available-slots-therapist
             */
            function fetchDates(productId, modeSlug, locationId = null) {
                $('#date-loading-message').show();

                $.post('{{ route("appointments.available-dates-concrete-therapist") }}', {
                    product_id: productId,
                    mode: modeSlug,
                    location_id: locationId,
                    days: 60,
                    _token: '{{ csrf_token() }}'
                })
                .done(res => {
                    allowedDates = res.dates || [];
                    fp.set('enable', [() => true]); // warning-only mode
                    fp.clear();
                    resetSlotsUI();
                })
                .fail(() => alert('Erreur lors du chargement des dates.'))
                .always(() => $('#date-loading-message').hide());
            }

            function fetchSlots(date, productId, modeSlug, locationId = null) {
                currentSlotsRequestId++;
                const reqId = currentSlotsRequestId;

                $('#time-slots-container').html('<span class="text-muted">Chargement des créneaux…</span>');
                disposeSlotTooltips();

                $.post('{{ route("appointments.available-slots-therapist") }}', {
                    date,
                    product_id: productId,
                    mode: modeSlug,
                    location_id: locationId,
                    include_conflicts: 1,
                    _token: '{{ csrf_token() }}'
                })
                .done(res => {
                    if (reqId !== currentSlotsRequestId) return;

                    if (!res.slots || !res.slots.length) {
                        $('#no-slots-message')
                            .text('Aucun créneau recommandé pour ce jour. Vous pouvez sélectionner un horaire manuellement.')
                            .show();

                        renderManualSlotsEvery15Min(true);
                        $('#force_availability_override').val('1');
                        showSlotWarning(['outside_dispo'], [conflictLabel('outside_dispo')]);
                        return;
                    }

                    renderSlots(res.slots);
                })
                .fail(() => {
                    $('#time-slots-container').html('<span class="text-red-500">Erreur lors de la récupération.</span>');
                });
            }

            function refreshDates() {
                if (isBackfillMode()) {
                    enableBackfillModeUI();
                    return;
                }

                const productId = $('#product_id').val();
                const slug      = $('#selected_mode_slug').val();
                const loc       = slug === 'cabinet' ? $('#practice_location_id').val() : null;

                if (!productId || !slug || (slug === 'cabinet' && !loc)) {
                    allowedDates = [];
                    fp.set('enable', [() => true]);
                    fp.clear();
                    resetSlotsUI();
                    return;
                }

                fetchDates(productId, slug, loc);
            }

            // Backfill toggle
            $('#backfill_past').on('change', function () {
                if (isBackfillMode()) {
                    enableBackfillModeUI();
                } else {
                    disableBackfillModeUIAndRefresh();
                }
            });

            function resetModeAndFormat() {
                $('#consultation_mode').empty().append('<option value="" disabled selected>Sélectionner le mode</option>');
                $('#consultation-mode-section').hide();

                $('#format_variant').empty().append('<option value="" disabled selected>Sélectionner un format</option>');
                $('#format-section').hide();
                $('#format_variant').prop('required', false);

                $('#product_id').val('');
                $('#selected_mode_slug').val('');
            }

            function applyModeUI(slug) {
                if (slug === 'cabinet') {
                    $('#cabinet-location-section').show();
                    $('#client-address-section').hide();
                } else if (slug === 'domicile' || slug === 'entreprise') {
                    $('#cabinet-location-section').hide();
                    $('#client-address-section').show();
                } else {
                    $('#cabinet-location-section').hide();
                    $('#client-address-section').hide();
                }

                if (slug !== 'cabinet') {
                    $('#practice_location_id').val('');
                    $('#therapist-address-section').hide();
                    $('#therapist-address').text('');
                }
            }

            function buildFormatOptionText(prestationName, modeLabel, duration, price) {
                const d = duration ? `${duration}min` : '';
                const p = price !== null && price !== undefined && price !== '' ? formatPrice(price) : '';
                // "Nom – Mode – Durée – Prix"
                return [prestationName, modeLabel, d, p].filter(Boolean).join(' – ');
            }

            // PRODUCT change
            $('#product_name').on('change', function () {
                const name = $(this).val();
                const modes = PRODUCT_VARIANTS[name] || [];

                resetSlotsUI();
                fp.clear();
                hideSlotWarning();

                // reset downstream UI
                $('#cabinet-location-section').hide();
                $('#practice_location_id').val('');
                $('#therapist-address-section').hide();
                $('#therapist-address').text('');
                $('#client-address-section').hide();

                // rebuild modes (unique by slug)
                const $mode = $('#consultation_mode').empty();
                $mode.append('<option value="" disabled selected>Sélectionner le mode</option>');

                modes.forEach(m => {
                    $mode.append(`<option value="${escapeHtml(m.slug)}">${escapeHtml(m.mode_label)}</option>`);
                });

                $('#consultation-mode-section').show();

                // reset hidden
                $('#product_id').val('');
                $('#selected_mode_slug').val('');

                // reset format
                $('#format_variant').empty().append('<option value="" disabled selected>Sélectionner un format</option>');
                $('#format-section').hide();
                $('#format_variant').prop('required', false);

                refreshDates();
            });

            // MODE change
            $('#consultation_mode').on('change', function () {
                const slug = $(this).val();
                const name = $('#product_name').val();
                const modes = PRODUCT_VARIANTS[name] || [];
                const modeData = modes.find(m => m.slug === slug) || null;

                $('#selected_mode_slug').val(slug);
                applyModeUI(slug);

                resetSlotsUI();
                fp.clear();
                hideSlotWarning();

                // reset product id until decided
                $('#product_id').val('');

                // build format options if multiple variants for this mode
                const $format = $('#format_variant').empty();
                $format.append('<option value="" disabled selected>Sélectionner un format</option>');

                if (modeData && Array.isArray(modeData.products) && modeData.products.length > 1) {
                    modeData.products.forEach(p => {
                        const label = buildFormatOptionText(name, modeData.mode_label, p.duration, p.price);
                        $format.append(`<option value="${p.id}">${escapeHtml(label)}</option>`);
                    });

                    $('#format-section').show();
                    $('#format_variant').prop('required', true);

                    // wait for selection
                    refreshDates();
                    return;
                }

                // single variant => auto-select product_id and hide format
                $('#format-section').hide();
                $('#format_variant').prop('required', false);

                if (modeData && Array.isArray(modeData.products) && modeData.products.length === 1) {
                    $('#product_id').val(modeData.products[0].id);
                }

                refreshDates();
            });

            // FORMAT change
            $('#format_variant').on('change', function () {
                const pid = $(this).val();
                $('#product_id').val(pid || '');

                resetSlotsUI();
                fp.clear();
                hideSlotWarning();

                refreshDates();
            });

            // CABINET change
            $('#practice_location_id').on('change', function () {
                const $opt = $(this).find(':selected');
                $('#therapist-address-section').show();
                $('#therapist-address').text($opt.data('address') || '—');
                refreshDates();
            });

            // DATE change
            $('#appointment_date').on('change', function () {
                const date      = $(this).val();
                const productId = $('#product_id').val();
                const slug      = $('#selected_mode_slug').val();
                const loc       = slug === 'cabinet' ? $('#practice_location_id').val() : null;

                hideSlotWarning();

                if (!date) {
                    resetSlotsUI();
                    return;
                }

                if (isBackfillMode()) {
                    renderManualSlotsEvery15Min(false);
                    $('#force_availability_override').val('0');
                    return;
                }

                if (Array.isArray(allowedDates) && allowedDates.length && !allowedDates.includes(date)) {
                    showSlotWarning(['outside_dispo'], [conflictLabel('outside_dispo')]);
                }

                if (date && productId && slug && (slug !== 'cabinet' || loc)) {
                    fetchSlots(date, productId, slug, loc);
                } else {
                    resetSlotsUI();
                }
            });

            // Show all hours
            $(document).on('click', '#show-all-hours', function () {
                hideSlotWarning();
                renderManualSlotsEvery15Min(isBackfillMode() ? false : true);

                if (!isBackfillMode()) {
                    $('#force_availability_override').val('1');
                    showSlotWarning(['outside_dispo'], [conflictLabel('outside_dispo')]);
                } else {
                    $('#force_availability_override').val('0');
                }

                initSlotTooltips();
            });

            // Slot click
            $(document).on('click', '.time-slot-btn', function () {
                $('.time-slot-btn').removeClass('active');
                $(this).addClass('active');
                $('#appointment_time').val($(this).data('time'));
                $('#no-slots-message').hide();

                const hasConflict = String($(this).attr('data-has-conflict') || '0') === '1';
                $('#force_availability_override').val(hasConflict ? '1' : '0');

                if (!hasConflict) {
                    hideSlotWarning();
                    return;
                }

                let conflicts = [];
                let explanations = [];
                try { conflicts = JSON.parse(decodeURIComponent($(this).attr('data-conflicts') || '[]')); } catch (e) {}
                try { explanations = JSON.parse(decodeURIComponent($(this).attr('data-explanations') || '[]')); } catch (e) {}

                showSlotWarning(conflicts, explanations);
            });
        });
    </script>
</x-app-layout>

{{-- resources/views/appointments/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Modifier le Rendez-vous') }}
        </h2>
    </x-slot>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Flatpickr --}}
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

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

        @media (max-width: 600px) {
            .details-container { padding:20px; }
            .details-title { font-size:1.5rem; }
            .btn-primary, .btn-secondary { width:100%; margin-bottom:10px; }
            .d-flex.justify-content-center.mt-4 { flex-direction:column; }
        }
    </style>

    @php
        $therapist = auth()->user();
        $practiceLocations = $practiceLocations ?? $therapist->practiceLocations ?? collect();

        // Group products by name with modes (same logic as create_therapist)
        $productsByName = $products->groupBy('name');
        $productModes = [];
        foreach ($productsByName as $productName => $group) {
            $modes = [];
            foreach ($group as $p) {
                if ($p->adomicile)        $modes[] = ['mode' => 'À Domicile',     'slug' => 'domicile', 'product' => $p];
                if ($p->dans_le_cabinet)  $modes[] = ['mode' => 'Dans le Cabinet', 'slug' => 'cabinet',  'product' => $p];
                if ($p->visio || $p->en_visio) $modes[] = ['mode' => 'En Visio', 'slug' => 'visio',    'product' => $p];
            }
            if (!empty($modes)) $productModes[$productName] = $modes;
        }

        $appointmentDate = $appointment->appointment_date
            ? \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d')
            : null;

        $appointmentTime = $appointment->appointment_date
            ? \Carbon\Carbon::parse($appointment->appointment_date)->format('H:i')
            : null;

        $initialProductId        = old('product_id', $appointment->product_id);
        $initialModeSlug         = old('type', $appointment->type);
        $initialLocationId       = old('practice_location_id', $appointment->practice_location_id);
        $initialAppointmentDate  = old('appointment_date', $appointmentDate);
        $initialAppointmentTime  = old('appointment_time', $appointmentTime);

        $initialProduct      = $products->firstWhere('id', $initialProductId);
        $initialProductName  = old('product_name', $initialProduct ? $initialProduct->name : null);

        $editingAppointmentId = $appointment->id;
    @endphp

    <div class="container mt-5">
        <div class="details-container">

            <h1 class="details-title">Modifier le Rendez-vous</h1>

            <div id="slot-warning-banner" class="alert alert-warning text-center" style="display:none;">
                ⚠ Ce créneau présente un ou plusieurs conflits. Vous pouvez tout de même mettre le rendez-vous à jour.
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

            <form action="{{ route('appointments.update', $appointment->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Important: helps server-side skip self-conflict and/or bypass buffer rules --}}
                <input type="hidden" name="editing_appointment_id" value="{{ $editingAppointmentId }}">

                {{-- Client selection --}}
                <div class="details-box">
                    <label class="details-label">Client</label>
                    <select name="client_profile_id" class="form-control" required>
                        <option value="" disabled>-- Sélectionner un client --</option>
                        @foreach($clientProfiles as $client)
                            <option value="{{ $client->id }}"
                                {{ old('client_profile_id', $appointment->client_profile_id) == $client->id ? 'selected' : '' }}>
                                {{ $client->first_name }} {{ $client->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_profile_id')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Prestation (by name) --}}
                <div class="details-box">
                    <label class="details-label">Prestation</label>
                    <select id="product_name" name="product_name" class="form-control" required>
                        <option value="" disabled {{ $initialProductName ? '' : 'selected' }}>Sélectionner une prestation</option>
                        @foreach($productModes as $productName => $modes)
                            <option value="{{ $productName }}" {{ $initialProductName === $productName ? 'selected' : '' }}>
                                {{ $productName }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_name')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Mode --}}
                <div class="details-box" id="consultation-mode-section" style="display:none;">
                    <label class="details-label">Mode de Consultation</label>
                    <select id="consultation_mode" class="form-control" required>
                        <option value="" disabled selected>Sélectionner le mode</option>
                    </select>
                    <p class="text-red-500 mt-1 d-none" id="mode-error"></p>
                </div>

                {{-- Hidden final values --}}
                <input type="hidden" name="product_id" id="product_id" value="{{ $initialProductId }}">
                <input type="hidden" name="type" id="selected_mode_slug" value="{{ $initialModeSlug }}">
                <input type="hidden" name="force_availability_override" id="force_availability_override" value="0">

                {{-- Cabinet --}}
                <div class="details-box" id="cabinet-location-section" style="display:none;">
                    <label class="details-label">Cabinet</label>
                    <select id="practice_location_id" name="practice_location_id" class="form-control">
                        <option value="" disabled {{ $initialLocationId ? '' : 'selected' }}>Choisir un cabinet</option>
                        @foreach($practiceLocations as $loc)
                            <option value="{{ $loc->id }}"
                                    data-address="{{ $loc->full_address ?? ($loc->address_line1 . ', ' . $loc->postal_code . ' ' . $loc->city) }}"
                                    {{ (string) $initialLocationId === (string) $loc->id ? 'selected' : '' }}>
                                {{ $loc->label }}@if($loc->is_shared) — {{ __('Cabinet partagé') }} @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-red-500 mt-1 d-none" id="location-error"></p>
                </div>

                <div class="details-box" id="therapist-address-section" style="display:none;">
                    <label class="details-label">Adresse du Cabinet</label>
                    <p id="therapist-address" class="form-control-static"></p>
                </div>

                {{-- Domicile --}}
                <div class="details-box" id="client-address-section" style="display:none;">
                    <label class="details-label">Adresse du Domicile</label>
                    <input type="text" id="address" name="address" class="form-control"
                           value="{{ old('address', $appointment->address) }}">
                    @error('address')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Date --}}
                <div class="details-box">
                    <label class="details-label">Date</label>
                    <input type="text" id="appointment_date" name="appointment_date" class="form-control"
                           placeholder="Sélectionner une date"
                           value="{{ $initialAppointmentDate }}" required>
                    <p id="date-loading-message" class="text-muted mt-1" style="display:none;">Chargement des dates…</p>
                    @error('appointment_date')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Time --}}
                <div class="details-box">
                    <label class="details-label">Horaire</label>
                    <input type="hidden" id="appointment_time" name="appointment_time" value="{{ $initialAppointmentTime }}" required>
                    <div id="time-slots-container" class="mt-2 text-muted">Sélectionnez prestation, mode et date.</div>
                    <p id="no-slots-message" class="text-red-500 mt-1" style="display:none;"></p>
                    @error('appointment_time')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Status --}}
                <div class="details-box">
                    <label class="details-label" for="status">{{ __('Statut') }}</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Programmé" {{ old('status', $appointment->status) == 'Programmé' ? 'selected' : '' }}>Programmé</option>
                        <option value="Confirmé" {{ old('status', $appointment->status) == 'Confirmé' ? 'selected' : '' }}>Confirmé</option>
                        <option value="Complété" {{ old('status', $appointment->status) == 'Complété' ? 'selected' : '' }}>Complété</option>
                        <option value="Annulé"   {{ old('status', $appointment->status) == 'Annulé' ? 'selected' : '' }}>Annulé</option>
                    </select>
                    @error('status')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Notes --}}
                <div class="details-box">
                    <label class="details-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-control">{{ old('notes', $appointment->notes) }}</textarea>
                    @error('notes')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i> Mettre à jour le rendez-vous
                    </button>
                    <a href="{{ route('appointments.index') }}" class="btn-secondary ms-3">
                        <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
const PRODUCT_MODES        = @json($productModes);
const OLD_TIME             = @json($initialAppointmentTime);
const INITIAL_PRODUCT_NAME = @json($initialProductName);
const INITIAL_MODE_SLUG    = @json($initialModeSlug);
const INITIAL_LOCATION_ID  = @json($initialLocationId);
const INITIAL_DATE         = @json($initialAppointmentDate);
const therapistId          = '{{ $therapist->id }}';

const EDITING_APPOINTMENT_ID = @json($editingAppointmentId);

let allowedDates = [];
let currentSlotsRequestId = 0;

$(function() {

    const fp = flatpickr("#appointment_date", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d-m-Y",
        minDate: "today",
        locale: "fr",
        disableMobile: true,
        enable: []
    });

    function resetSlotsUI() {
        $('#appointment_time').val('');
        $('#force_availability_override').val('0');
        $('#time-slots-container').html('<span class="text-muted">Sélectionnez prestation, mode et date.</span>');
        $('#no-slots-message').hide().text('');
        hideSlotWarning();
    }

    /**
     * ✅ Use THERAPIST endpoints (same as create_therapist)
     * + pass exclude_appointment_id so the current appointment doesn't block itself
     */
    function conflictLabel(type) {
        switch (type) {
            case 'overlap_internal': return 'Conflit avec un autre rendez-vous';
            case 'overlap_external': return 'Conflit avec un événement externe';
            case 'outside_dispo': return 'En dehors des disponibilités';
            case 'outside_opening_hours': return 'En dehors des horaires habituels';
            case 'temporary_unavailability': return 'Période d’indisponibilité';
            default: return 'Conflit détecté';
        }
    }

    function hideSlotWarning() {
        $('#slot-warning-banner').hide();
        $('#slot-warning-details').html('');
    }

    function showSlotWarning(conflicts = [], explanations = []) {
        const details = (Array.isArray(explanations) && explanations.length)
            ? explanations
            : (Array.isArray(conflicts) ? conflicts.map(conflictLabel) : []);

        const uniq = [...new Set(details)].filter(Boolean);
        const html = uniq.length
            ? `<ul class="mb-0">${uniq.map(d => `<li>${d}</li>`).join('')}</ul>`
            : '<div>Ce créneau est en dehors des recommandations habituelles.</div>';

        $('#slot-warning-details').html(html);
        $('#slot-warning-banner').show();
    }

    function normalizeSlots(slots) {
        return (slots || []).map(s => {
            if (typeof s === 'string') {
                return { start: s, hasConflict: false, conflicts: [], explanations: [] };
            }

            let start = s.start || s.time || '';
            if (start && start.includes('T')) {
                start = start.substring(11, 16);
            }

            const conflicts = Array.isArray(s.conflicts) ? s.conflicts : [];
            const explanations = Array.isArray(s.explanations)
                ? s.explanations
                : (Array.isArray(s.reasons) ? s.reasons : []);

            const hasConflict = !!(s.has_conflict || s.is_conflict || s.conflict || s.blocked || conflicts.length || explanations.length);

            return { start, hasConflict, conflicts, explanations };
        });
    }

    function renderSlots(slots) {
        const normalized = normalizeSlots(slots);

        if (!normalized.length) {
            $('#time-slots-container').html('<span class="text-muted">Aucun crÇ¸neau disponible.</span>');
            $('#no-slots-message').text('Pas de crÇ¸neaux pour ce jour.').show();
            renderManualSlotsEvery15Min();
            $('#force_availability_override').val('1');
            showSlotWarning(['outside_dispo'], [conflictLabel('outside_dispo')]);
            return;
        }

        let html = '<div class="time-slots-grid">';
        normalized.forEach(s => {
            const explanations = s.explanations.length ? s.explanations : s.conflicts.map(conflictLabel);
            html += `<button type="button" class="time-slot-btn"
                             data-time="${s.start}"
                             data-has-conflict="${s.hasConflict ? 1 : 0}"
                             data-conflicts="${encodeURIComponent(JSON.stringify(s.conflicts || []))}"
                             data-explanations="${encodeURIComponent(JSON.stringify(explanations || []))}">
                        ${s.start}
                    </button>`;
        });
        html += '</div>';
        html += '<div class="mt-2"><button type="button" id="show-all-hours" class="btn-secondary" style="padding:6px 10px;font-size:0.9rem;">Afficher tous les horaires</button></div>';
        $('#time-slots-container').html(html);
        $('#no-slots-message').hide().text('');
    }

    function renderManualSlotsEvery15Min() {
        let html = '<div class="time-slots-grid">';
        for (let h = 0; h < 24; h++) {
            for (let m = 0; m < 60; m += 15) {
                const hh = String(h).padStart(2, '0');
                const mm = String(m).padStart(2, '0');
                const value = `${hh}:${mm}`;
                html += `<button type="button" class="time-slot-btn"
                                 data-time="${value}"
                                 data-has-conflict="1"
                                 data-manual="1"
                                 data-conflicts="${encodeURIComponent(JSON.stringify(['outside_dispo']))}"
                                 data-explanations="${encodeURIComponent(JSON.stringify([conflictLabel('outside_dispo')]))}">
                            ${value}
                        </button>`;
            }
        }
        html += '</div>';
        $('#time-slots-container').html(html);
    }

    function fetchDates(productId, modeSlug, locationId = null) {
        $('#date-loading-message').show().text('Chargement des dates…');

        $.post('{{ route("appointments.available-dates-concrete-therapist") }}', {
            product_id: productId,
            mode: modeSlug,
            location_id: locationId,
            include_conflicts: 1,
            days: 90,

            // ⭐ crucial for edit
            exclude_appointment_id: EDITING_APPOINTMENT_ID,

            _token: '{{ csrf_token() }}'
        })
        .done(res => {
            allowedDates = res.dates || [];
            // Warning-only mode: allow selecting any date, even outside availability.
            fp.set('enable', [() => true]);

            // Don't nuke the edit date if it exists (avoid fp.clear() here)
            resetSlotsUI();
        })
        .fail(() => {
            allowedDates = [];
            fp.set('enable', []);
            fp.clear();
            resetSlotsUI();
            alert('Erreur lors du chargement des dates.');
        })
        .always(() => $('#date-loading-message').hide().text(''));
    }

    function fetchSlots(date, productId, modeSlug, locationId = null) {
        currentSlotsRequestId++;
        const reqId = currentSlotsRequestId;

        $('#appointment_time').val('');
        $('#force_availability_override').val('0');
        hideSlotWarning();
        $('#time-slots-container').html('<span class="text-muted">Chargement des créneaux…</span>');
        $('#no-slots-message').hide().text('');

        $.post('{{ route("appointments.available-slots-therapist") }}', {
            date: date,
            product_id: productId,
            mode: modeSlug,
            location_id: locationId,
            include_conflicts: 1,

            // ⭐ crucial for edit
            exclude_appointment_id: EDITING_APPOINTMENT_ID,

            _token: '{{ csrf_token() }}'
        })
        .done(res => {
            if (reqId !== currentSlotsRequestId) return;

            if (!res.slots || !res.slots.length) {
                $('#no-slots-message')
                    .text('Aucun créneau recommandé pour ce jour. Vous pouvez sélectionner un horaire manuellement.')
                    .show();
                renderManualSlotsEvery15Min();
                $('#force_availability_override').val('1');
                showSlotWarning(['outside_dispo'], [conflictLabel('outside_dispo')]);
                return;
            }

            renderSlots(res.slots);

            if (OLD_TIME) {
                $('#appointment_time').val(OLD_TIME);
                $('#time-slots-container .time-slot-btn').each(function () {
                    if ($(this).data('time') === OLD_TIME) {
                        $(this).addClass('active');

                        const hasConflict = String($(this).attr('data-has-conflict') || '0') === '1';
                        $('#force_availability_override').val(hasConflict ? '1' : '0');

                        if (hasConflict) {
                            let conflicts = [];
                            let explanations = [];
                            try { conflicts = JSON.parse(decodeURIComponent($(this).attr('data-conflicts') || '[]')); } catch (e) {}
                            try { explanations = JSON.parse(decodeURIComponent($(this).attr('data-explanations') || '[]')); } catch (e) {}
                            showSlotWarning(conflicts, explanations);
                        } else {
                            hideSlotWarning();
                        }
                    }
                });
            }
        })
        .fail(() => {
            $('#time-slots-container').html('<span class="text-red-500">Erreur lors de la récupération.</span>');
        });
    }

    function refreshDates() {
        const productId = $('#product_id').val();
        const slug      = $('#selected_mode_slug').val();
        const loc       = (slug === 'cabinet') ? $('#practice_location_id').val() : null;

        if (!productId || !slug || (slug === 'cabinet' && !loc)) {
            allowedDates = [];
            fp.set('enable', []);
            fp.clear();
            resetSlotsUI();
            return;
        }
        fetchDates(productId, slug, loc);
    }

    // show all hours (manual, outside dispo)
    $(document).on('click', '#show-all-hours', function() {
        renderManualSlotsEvery15Min();
        $('#force_availability_override').val('1');
        showSlotWarning(['outside_dispo'], [conflictLabel('outside_dispo')]);
    });

    // slot click
    $(document).on('click', '.time-slot-btn', function() {
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

    // product change -> populate mode
    $('#product_name').on('change', function () {
        const name  = $(this).val();
        const modes = PRODUCT_MODES[name] || [];
        const $mode = $('#consultation_mode').empty();

        $mode.append('<option value="" disabled selected>Sélectionner le mode</option>');
        modes.forEach(m => $mode.append(`<option value="${m.product.id}" data-slug="${m.slug}">${m.mode}</option>`));
        $('#consultation-mode-section').toggle(modes.length > 0);

        $('#product_id').val('');
        $('#selected_mode_slug').val('');

        $('#cabinet-location-section').hide();
        $('#therapist-address-section').hide();
        $('#client-address-section').hide();
        $('#mode-error').addClass('d-none').text('');
        $('#location-error').addClass('d-none').text('');

        allowedDates = [];
        fp.set('enable', []);
        fp.clear();
        resetSlotsUI();
    });

    // mode change -> show sections + refresh
    $('#consultation_mode').on('change', function () {
        const productId = $(this).val();
        const slug      = $(this).find(':selected').data('slug');

        $('#product_id').val(productId);
        $('#selected_mode_slug').val(slug);

        resetSlotsUI();
        fp.set('enable', []);
        fp.clear();

        if (slug === 'cabinet') {
            $('#cabinet-location-section').show();
            $('#client-address-section').hide();
        } else if (slug === 'domicile') {
            $('#cabinet-location-section').hide();
            $('#client-address-section').show();
        } else {
            $('#cabinet-location-section').hide();
            $('#client-address-section').hide();
        }

        refreshDates();
    });

    // cabinet change -> update address + refresh
    $('#practice_location_id').on('change', function () {
        const $opt = $(this).find(':selected');
        $('#therapist-address-section').show();
        $('#therapist-address').text($opt.data('address') || '—');
        refreshDates();
    });

    // date change -> fetch slots
    $('#appointment_date').on('change', function () {
        const date      = $(this).val();
        const productId = $('#product_id').val();
        const slug      = $('#selected_mode_slug').val();
        const loc       = (slug === 'cabinet') ? $('#practice_location_id').val() : null;
        hideSlotWarning();

        if (date && productId && slug) fetchSlots(date, productId, slug, loc);
        else resetSlotsUI();
    });

    // ---- init edit values ----
    if (INITIAL_PRODUCT_NAME) {
        $('#product_name').val(INITIAL_PRODUCT_NAME).trigger('change');

        setTimeout(function () {
            // fill mode select & select current mode/product
            const modes = PRODUCT_MODES[INITIAL_PRODUCT_NAME] || [];
            const match = modes.find(m => String(m.product.id) === String(@json($initialProductId)) && m.slug === INITIAL_MODE_SLUG)
                       || modes.find(m => m.slug === INITIAL_MODE_SLUG)
                       || modes[0];

            if (match) {
                $('#consultation_mode option').each(function () {
                    if (String($(this).val()) === String(match.product.id) && $(this).data('slug') === match.slug) {
                        $(this).prop('selected', true);
                    }
                });
                $('#consultation_mode').trigger('change');
            }

            if (INITIAL_MODE_SLUG === 'cabinet' && INITIAL_LOCATION_ID) {
                $('#practice_location_id').val(INITIAL_LOCATION_ID).trigger('change');
            }

            // set date + fetch slots
            if (INITIAL_DATE) {
                setTimeout(function () {
                    fp.setDate(INITIAL_DATE, true);

                    const slug = $('#selected_mode_slug').val();
                    const loc  = (slug === 'cabinet') ? $('#practice_location_id').val() : null;
                    const pid  = $('#product_id').val();

                    if (pid && slug) {
                        // important: refresh dates first so the date is enabled, then fetch slots
                        refreshDates();
                        setTimeout(() => fetchSlots(INITIAL_DATE, pid, slug, loc), 350);
                    }
                }, 450);
            }
        }, 250);
    }

});
</script>

</x-app-layout>

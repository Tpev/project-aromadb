{{-- resources/views/appointments/create_patient_partner.blade.php --}}
<x-app-layout>
@php
    $therapistName = $therapist->company_name
        ?? $therapist->business_name
        ?? $therapist->name
        ?? __('Thérapeute');

    $pageTitle = "{$therapistName} — Réservation partenaire";
@endphp

@push('head')
    <meta name="robots" content="noindex, nofollow">
    <meta property="og:title" content="{{ $pageTitle }}">
@endpush

<x-slot name="header">
    <h2 class="font-semibold text-xl" style="color: #647a0b;">
        {{ __('Réservation partenaire') }}
    </h2>
</x-slot>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .container { max-width: 820px; }
    .details-container {
        background-color: #f9f9f9;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        margin: 0 auto;
    }
    .details-title {
        font-size: 1.8rem;
        font-weight: 800;
        color: #647a0b;
        margin-bottom: 16px;
        text-align: center;
    }
    .details-box { margin-bottom: 15px; }
    .details-label {
        font-weight: bold;
        color: #647a0b;
        display: block;
        margin-bottom: 6px;
    }
    .subtle { color:#6b7280; font-size:.92rem; }
    .badge-mode {
        display:inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(100,122,11,.12);
        color:#647a0b;
        font-weight:700;
        font-size:.85rem;
    }
    .price-chip{
        display:inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #e5e7eb;
        color:#111827;
        font-weight:700;
        font-size:.85rem;
        margin-left:6px;
    }
    .earlier-slot-choice {
        display:flex;
        align-items:flex-start;
        gap:12px;
        padding:14px;
        border:1px solid #dfe5c9;
        border-radius:8px;
        background:#f8faef;
        cursor:pointer;
    }
    .earlier-slot-choice input { width:18px; height:18px; margin-top:2px; accent-color:#647a0b; }
    .earlier-slot-choice strong { display:block; color:#26351f; }
    .earlier-slot-choice small { display:block; margin-top:4px; color:#687064; line-height:1.45; }
    .booking-errors {
        margin: 0 0 20px;
        padding: 14px 16px;
        border: 1px solid #fecaca;
        border-radius: 8px;
        background: #fef2f2;
        color: #991b1b;
    }
    .booking-errors p { margin: 0 0 6px; font-weight: 700; }
    .booking-errors ul { margin: 0; padding-left: 20px; }
</style>

<div class="container mt-5">
    <div class="details-container">
        <h1 class="details-title">{{ __('Réserver une prestation (lien partenaire)') }}</h1>

        @if ($errors->any())
            <div class="booking-errors" role="alert" aria-live="polite">
                <p>{{ __('La réservation n’a pas pu être enregistrée.') }}</p>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('bookingLinks.store', ['token' => $bookingLink->token]) }}" method="POST">
            @csrf

            {{-- Prestation name --}}
            <div class="details-box">
                <label class="details-label" for="prestation_name">{{ __('Prestation') }}</label>
                <select id="prestation_name" class="form-select" required>
                    <option value="">{{ __('Choisir une prestation') }}</option>
                    @foreach($productsByName as $name => $items)
                        <option value="{{ $name }}">{{ $name }}</option>
                    @endforeach
                </select>
                <small class="subtle" id="prestationHelp" style="display:none;">
                    {{ __("Plusieurs formats existent pour cette prestation. Choisissez celui qui vous convient.") }}
                </small>
            </div>

            {{-- Variant selector --}}
            <div class="details-box" id="variantBox" style="display:none;">
                <label class="details-label" for="variant_select">{{ __('Format') }}</label>
                <select id="variant_select" class="form-select">
                    <option value="">{{ __('Choisir un format') }}</option>
                </select>
                <div class="subtle mt-2" id="variantMeta" style="display:none;"></div>
            </div>

            <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id') }}">
            <input type="hidden" name="type" id="type" value="{{ old('type') }}">

            {{-- Cabinet selector (only for cabinet mode) --}}
            <div class="details-box" id="practiceLocationBox" style="display:none;">
                <label class="details-label" for="practice_location_id">{{ __('Cabinet') }}</label>
                @if(app(\App\Support\BookingV2Access::class)->enabledFor($therapist))
                    <p id="autoLocationSummary" class="subtle" style="display:none;text-align:left;">
                        <strong id="autoLocationLabel"></strong><br>
                        <span id="autoLocationAddress"></span>
                    </p>
                    <p id="noCompatibleLocation" class="subtle" style="display:none;text-align:left;color:#b91c1c;">
                        {{ __('Aucun cabinet n’est configuré pour cette prestation. Contactez directement le praticien.') }}
                    </p>
                @endif
                <select id="practice_location_id" name="practice_location_id" class="form-select">
                    <option value="">{{ __('Choisir un cabinet') }}</option>
                    @foreach($practiceLocations as $loc)
                        <option value="{{ $loc->id }}" @selected((string) old('practice_location_id') === (string) $loc->id)>
                            {{ $loc->full_address ?? ($loc->address ?? ('Cabinet #' . $loc->id)) }}
                        </option>
                    @endforeach
                </select>
                <p class="text-danger mt-2 d-none" id="location-error"></p>
            </div>

            {{-- Address (only for domicile/entreprise) --}}
            <div class="details-box" id="addressBox" style="display:none;">
                <label class="details-label" for="address">{{ __('Adresse') }}</label>
                <input type="text" id="address" name="address" class="form-control" value="{{ old('address') }}"
                       placeholder="{{ __('Adresse complète (rue, code postal, ville)') }}">
                <small class="subtle">{{ __("Requis pour une prestation à domicile / en entreprise.") }}</small>
            </div>

            <div class="subtle mt-2" id="date-loading-message" style="display:none;"></div>
            <p class="text-danger mt-2 d-none" id="no-slots-message"></p>

            {{-- Date --}}
            <div class="details-box">
                <label class="details-label" for="appointment_date">{{ __('Date') }}</label>
                <input type="text" id="appointment_date" name="appointment_date" class="form-control"
                       value="{{ old('appointment_date') }}" placeholder="{{ __('Choisir une date') }}" required>
            </div>

            {{-- Time --}}
            <div class="details-box">
                <label class="details-label" for="appointment_time">{{ __('Heure') }}</label>
                <select id="appointment_time" name="appointment_time" class="form-select" required>
                    <option value="">{{ __('Choisir une heure') }}</option>
                </select>
            </div>

            {{-- Patient info --}}
            <div class="details-box">
                <label class="details-label" for="first_name">{{ __('Prénom') }}</label>
                <input type="text" id="first_name" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
            </div>

            <div class="details-box">
                <label class="details-label" for="last_name">{{ __('Nom') }}</label>
                <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
            </div>

            <div class="details-box">
                <label class="details-label" for="email">{{ __('Email') }}</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>

            <div class="details-box">
                <label class="details-label" for="phone">{{ __('Téléphone') }}</label>
                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" required>
            </div>

            <div class="details-box">
                <label class="details-label" for="notes">{{ __('Informations complémentaires (facultatif)') }}</label>
                <textarea id="notes" name="notes" class="form-control"
                          placeholder="{{ $therapist->resolvedBookingNotesPlaceholder() }}">{{ old('notes') }}</textarea>
                <small class="subtle">{{ __('Ces informations seront transmises au praticien avec votre rendez-vous.') }}</small>
                @error('notes')<p class="text-danger mt-2">{{ $message }}</p>@enderror
            </div>

            @if(config('appointments.earlier_slots.enabled', false))
                <div class="details-box">
                    <label class="earlier-slot-choice" for="wants_earlier_slot">
                        <input type="checkbox" id="wants_earlier_slot" name="wants_earlier_slot" value="1" @checked(old('wants_earlier_slot'))>
                        <span>
                            <strong>{{ __('Me prévenir si un rendez-vous plus tôt se libère') }}</strong>
                            <small>{{ __('Vous recevrez un email uniquement pour un créneau compatible. Votre rendez-vous actuel restera réservé jusqu’à votre confirmation.') }}</small>
                        </span>
                    </label>
                </div>
            @endif

            <div class="details-box">
                <label class="details-label" for="gift_voucher_code">{{ __('Code bon cadeau (optionnel)') }}</label>
                <input type="text" id="gift_voucher_code" name="gift_voucher_code" class="form-control"
                       value="{{ old('gift_voucher_code') }}" placeholder="{{ __('Ex: AM-ABCD-EFGH-IJKL') }}">
            </div>

            <button type="submit" class="btn btn-success w-100 mt-3">
                {{ __('Réserver') }}
            </button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function () {
    const therapistId = @json($therapist->id);
    const catalog = @json($catalog);
    const bookingV2Locations = @json($compatibleLocationsByProduct ?? []);
    const bookingV2Active = @json(app(\App\Support\BookingV2Access::class)->enabledFor($therapist));
    const defaultBookingNotesPlaceholder = @json($therapist->resolvedBookingNotesPlaceholder());
    const oldProductId = @json((string) old('product_id', ''));
    const oldLocationId = @json((string) old('practice_location_id', ''));
    let pendingOldDate = @json(old('appointment_date'));
    let pendingOldTime = @json(old('appointment_time'));

    const $prestationSelect = $('#prestation_name');
    const $variantSelect = $('#variant_select');
    const $practiceLocationSelect = $('#practice_location_id');

    const variantBox = document.getElementById('variantBox');
    const practiceLocationBox = document.getElementById('practiceLocationBox');

    const productIdInput = document.getElementById('product_id');
    const typeInput = document.getElementById('type');
    const addressBox = document.getElementById('addressBox');
    const addressInput = document.getElementById('address');

    const prestationHelp = document.getElementById('prestationHelp');
    const variantMeta = document.getElementById('variantMeta');
    const notesInput = document.getElementById('notes');

    const dateLoadingMessage = document.getElementById('date-loading-message');
    const noSlotsMessage = document.getElementById('no-slots-message');

    const timeSelect = document.getElementById('appointment_time');

    let internalUpdate = false;

    $prestationSelect.select2({ width: '100%' });
    $variantSelect.select2({ width: '100%' });
    $practiceLocationSelect.select2({ width: '100%' });

    function setVariantMeta(v) {
        if (notesInput) {
            notesInput.placeholder = v?.booking_notes_placeholder || defaultBookingNotesPlaceholder;
        }

        if (!v) {
            variantMeta.style.display = 'none';
            variantMeta.innerHTML = '';
            return;
        }

        const modes = [];
        if (v.visio) modes.push("En visio");
        if (v.dans_le_cabinet) modes.push("Dans le cabinet");
        if (v.adomicile) modes.push("À domicile");
        if (v.en_entreprise) modes.push("En entreprise");

        const modeLabel = modes.length ? modes.join(" • ") : "—";
        const duration = v.duration ? `${v.duration} min` : "—";
        const price = (typeof v.price === 'number') ? `${v.price.toFixed(2)} €` : "—";

        variantMeta.innerHTML =
            `<span class="badge-mode">${modeLabel}</span> ` +
            `<span class="price-chip">${duration} • ${price}</span>`;
        variantMeta.style.display = 'block';
    }

    function inferModeSlug(v) {
        if (!v) return null;
        if (v.dans_le_cabinet) return 'cabinet';
        if (v.visio) return 'visio';
        if (v.adomicile) return 'domicile';
        if (v.en_entreprise) return 'entreprise';
        return null;
    }

    function resetTimeSelect() {
        timeSelect.innerHTML = `<option value="">{{ __('Choisir une heure') }}</option>`;
    }

    function toggleAddressBox(mode) {
        const needsAddress = (mode === 'domicile' || mode === 'entreprise');
        if (addressBox) addressBox.style.display = needsAddress ? 'block' : 'none';
        if (addressInput) addressInput.required = needsAddress;
    }

    function getSelectedVariant() {
        const pid = parseInt(productIdInput.value || "0", 10);
        if (!pid) return null;

        for (const entry of catalog) {
            const found = (entry.variants || []).find(v => parseInt(v.id, 10) === pid);
            if (found) return found;
        }
        return null;
    }

    // Flatpickr
    const fp = flatpickr("#appointment_date", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        minDate: "today",
        locale: "fr",
        disableMobile: true,
        enable: [],
        onChange: function(selectedDates, dateStr) {
            if (!dateStr) return;

            const v = getSelectedVariant();
            const mode = inferModeSlug(v);
            const productId = parseInt(productIdInput.value || "0", 10);
            const locationId = (mode === 'cabinet') ? ($practiceLocationSelect.val() || null) : null;

            if (!productId || !mode) return;
            if (mode === 'cabinet' && !locationId) return;

            fetchAvailableSlots(dateStr, productId, mode, locationId);
        }
    });

    function fetchDates(productId, mode, locationId) {
        dateLoadingMessage.textContent = "{{ __('Chargement des jours disponibles...') }}";
        dateLoadingMessage.style.display = 'block';

        $.ajax({
            url: '{{ route("appointments.available-dates-concrete-patient") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_id: productId,
                therapist_id: therapistId,
                mode: mode,
                location_id: (mode === 'cabinet' ? locationId : undefined),
            },
            success: function (res) {
                dateLoadingMessage.style.display = 'none';

                const dates = (res && Array.isArray(res.dates)) ? res.dates : [];
                fp.clear();
                fp.set('enable', dates);

                if (!dates.length) {
                    resetTimeSelect();
                    noSlotsMessage.classList.remove('d-none');
                    noSlotsMessage.textContent = "{{ __('Aucune date disponible pour cette prestation.') }}";
                } else {
                    noSlotsMessage.classList.add('d-none');
                    noSlotsMessage.textContent = "";

                    if (pendingOldDate && dates.includes(pendingOldDate)) {
                        const dateToRestore = pendingOldDate;
                        pendingOldDate = null;
                        fp.setDate(dateToRestore, true);
                    }
                }
            },
            error: function (xhr) {
                console.error('Error fetching dates:', xhr.responseText);
                dateLoadingMessage.style.display = 'none';
                fp.set('enable', []);
                resetTimeSelect();
                noSlotsMessage.classList.remove('d-none');
                noSlotsMessage.textContent = "{{ __('Une erreur est survenue lors de la récupération des dates.') }}";
            }
        });
    }

    function fetchAvailableSlots(dateStr, productId, mode, locationId) {
        resetTimeSelect();

        $.ajax({
            url: '{{ route("appointments.available-slots-patient") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                therapist_id: therapistId,
                date: dateStr,
                product_id: productId,
                mode: mode,
                location_id: (mode === 'cabinet' ? locationId : undefined),
            },
            success: function (res) {
                const slots = (res && Array.isArray(res.slots)) ? res.slots : [];

                if (!slots.length) {
                    noSlotsMessage.classList.remove('d-none');
                    noSlotsMessage.textContent = "{{ __('Aucun créneau disponible pour cette date.') }}";
                    return;
                }

                noSlotsMessage.classList.add('d-none');
                noSlotsMessage.textContent = "";

                slots.forEach(s => {
                    // res.slots can be array of objects {start,end} or strings; support both
                    const start = (typeof s === 'string') ? s : (s.start || '');
                    if (!start) return;

                    const opt = document.createElement('option');
                    opt.value = start;
                    opt.textContent = start;
                    timeSelect.appendChild(opt);
                });

                if (pendingOldTime && Array.from(timeSelect.options).some(option => option.value === pendingOldTime)) {
                    timeSelect.value = pendingOldTime;
                    pendingOldTime = null;
                }
            },
            error: function (xhr) {
                console.error('Error fetching slots:', xhr.responseText);
                noSlotsMessage.classList.remove('d-none');
                noSlotsMessage.textContent = "{{ __('Une erreur est survenue lors de la récupération des créneaux.') }}";
            }
        });
    }

    function configureCabinetLocations(productId) {
        if (!bookingV2Active) return;

        if (!productId) {
            $practiceLocationSelect.find('option[value]').prop('disabled', false);
            $practiceLocationSelect.val('').trigger('change.select2');
            $practiceLocationSelect.next('.select2-container').show();
            $('#autoLocationSummary, #noCompatibleLocation').hide();
            return;
        }

        const locations = bookingV2Locations[String(productId)] || [];
        const allowedIds = locations.map(location => String(location.id));
        $practiceLocationSelect.find('option[value]').each(function () {
            $(this).prop('disabled', !allowedIds.includes(String($(this).val())));
        });
        $('#autoLocationSummary, #noCompatibleLocation').hide();

        if (locations.length === 0) {
            $practiceLocationSelect.val('').trigger('change.select2');
            $practiceLocationSelect.next('.select2-container').hide();
            $('#noCompatibleLocation').show();
            return;
        }

        if (locations.length === 1) {
            const location = locations[0];
            $practiceLocationSelect.val(String(location.id)).trigger('change.select2');
            $practiceLocationSelect.next('.select2-container').hide();
            $('#autoLocationLabel').text(location.label);
            $('#autoLocationAddress').text(location.address);
            $('#autoLocationSummary').show();
            return;
        }

        if (!allowedIds.includes(String($practiceLocationSelect.val() || ''))) {
            $practiceLocationSelect.val('').trigger('change.select2');
        } else {
            $practiceLocationSelect.trigger('change.select2');
        }
        $practiceLocationSelect.next('.select2-container').show();
    }

    function refreshAvailability() {
        const v = getSelectedVariant();
        const mode = inferModeSlug(v);
        const productId = parseInt(productIdInput.value || "0", 10);

        if (typeInput) typeInput.value = mode || '';
        toggleAddressBox(mode);

        fp.clear();
        fp.set('enable', []);
        resetTimeSelect();

        if (!productId || !mode) return;

        if (mode === 'cabinet') {
            practiceLocationBox.style.display = 'block';
            configureCabinetLocations(productId);
            const locationId = $practiceLocationSelect.val();
            if (!locationId) return;
            fetchDates(productId, mode, locationId);
        } else {
            practiceLocationBox.style.display = 'none';
            $practiceLocationSelect.val("").trigger('change.select2');
            fetchDates(productId, mode, null);
        }
    }

    function onPrestationChange() {
        if (internalUpdate) return;

        const selectedName = $prestationSelect.val();

        productIdInput.value = '';
        if (typeInput) typeInput.value = '';
        setVariantMeta(null);
        toggleAddressBox(null);

        fp.clear();
        fp.set('enable', []);
        resetTimeSelect();
        configureCabinetLocations(null);

        if (!selectedName) {
            variantBox.style.display = 'none';
            prestationHelp.style.display = 'none';

            internalUpdate = true;
            $variantSelect.empty().append(new Option("{{ __('Choisir un format') }}", "")).val("").trigger('change.select2');
            $practiceLocationSelect.val("").trigger('change.select2');
            internalUpdate = false;

            practiceLocationBox.style.display = 'none';
            return;
        }

        const entry = catalog.find(c => c.name === selectedName);
        const variants = entry ? entry.variants : [];

        if (variants.length === 1) {
            variantBox.style.display = 'none';
            prestationHelp.style.display = 'none';

            productIdInput.value = variants[0].id;
            setVariantMeta(variants[0]);

            internalUpdate = true;
            $variantSelect.empty().append(new Option("{{ __('Choisir un format') }}", "")).val("").trigger('change.select2');
            internalUpdate = false;

            refreshAvailability();
            return;
        }

        variantBox.style.display = 'block';
        prestationHelp.style.display = 'block';

        internalUpdate = true;
        $variantSelect.empty().append(new Option("{{ __('Choisir un format') }}", ""));
        variants.forEach(v => {
            const parts = [];
            if (v.dans_le_cabinet) parts.push("Dans le cabinet");
            if (v.visio) parts.push("En visio");
            if (v.adomicile) parts.push("À domicile");
            if (v.en_entreprise) parts.push("En entreprise");
            const modeTxt = parts.length ? parts.join(" • ") : "—";
            const dur = v.duration ? `${v.duration} min` : "—";
            const pr = (typeof v.price === 'number') ? `${v.price.toFixed(2)} €` : "—";
            $variantSelect.append(new Option(`${v.name} — ${modeTxt} • ${dur} • ${pr}`, v.id));
        });
        $variantSelect.val("").trigger('change.select2');
        internalUpdate = false;

        practiceLocationBox.style.display = 'none';
        toggleAddressBox(null);
    }

    function onVariantChange() {
        if (internalUpdate) return;

        const selectedId = parseInt($variantSelect.val() || "0", 10);

        if (!selectedId) {
            productIdInput.value = '';
            if (typeInput) typeInput.value = '';
            setVariantMeta(null);
            practiceLocationBox.style.display = 'none';
            toggleAddressBox(null);

            fp.clear();
            fp.set('enable', []);
            resetTimeSelect();
            return;
        }

        productIdInput.value = selectedId;

        const v = getSelectedVariant();
        setVariantMeta(v || null);

        refreshAvailability();
    }

    $prestationSelect.on('change', onPrestationChange);
    $variantSelect.on('change', onVariantChange);
    $practiceLocationSelect.on('change', function() {
        if (internalUpdate) return;
        refreshAvailability();
    });

    if (oldProductId) {
        const oldEntry = catalog.find(entry => (entry.variants || []).some(
            variant => String(variant.id) === oldProductId
        ));

        if (oldEntry) {
            $prestationSelect.val(oldEntry.name).trigger('change');

            if ((oldEntry.variants || []).length > 1) {
                $variantSelect.val(oldProductId).trigger('change');
            }

            if (oldLocationId && !$practiceLocationSelect.find(`option[value="${oldLocationId}"]`).prop('disabled')) {
                $practiceLocationSelect.val(oldLocationId).trigger('change');
            }
        } else {
            onPrestationChange();
        }
    } else {
        onPrestationChange();
    }
})();
</script>

</x-app-layout>

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
        $practiceLocations = $therapist->practiceLocations ?? collect();

        // Group products by name with modes, same as create_therapist
        $productsByName = $products->groupBy('name');
        $productModes = [];
        foreach ($productsByName as $productName => $group) {
            $modes = [];
            foreach ($group as $p) {
                if ($p->adomicile) {
                    $modes[] = ['mode' => 'À Domicile', 'slug' => 'domicile', 'product' => $p];
                }
                if ($p->dans_le_cabinet) {
                    $modes[] = ['mode' => 'Dans le Cabinet', 'slug' => 'cabinet', 'product' => $p];
                }
                if ($p->visio || $p->en_visio) {
                    $modes[] = ['mode' => 'En Visio', 'slug' => 'visio', 'product' => $p];
                }
            }
            if (!empty($modes)) {
                $productModes[$productName] = $modes;
            }
        }

        $appointmentDate = $appointment->appointment_date
            ? \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d')
            : null;

        $appointmentTime = $appointment->appointment_date
            ? \Carbon\Carbon::parse($appointment->appointment_date)->format('H:i')
            : null;

        $initialProductId    = old('product_id', $appointment->product_id);
        $initialModeSlug     = old('type', $appointment->type);
        $initialLocationId   = old('practice_location_id', $appointment->practice_location_id);
        $initialAppointmentDate = old('appointment_date', $appointmentDate);
        $initialAppointmentTime = old('appointment_time', $appointmentTime);

        $initialProduct      = $products->firstWhere('id', $initialProductId);
        $initialProductName  = old('product_name', $initialProduct ? $initialProduct->name : null);
    @endphp

    <div class="container mt-5">
        <div class="details-container">

            <h1 class="details-title">Modifier le Rendez-vous</h1>

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

                {{-- Prestation (by name, like patient/therapist create) --}}
                <div class="details-box">
                    <label class="details-label">Prestation</label>
                    <select id="product_name" name="product_name" class="form-control" required>
                        <option value="" disabled {{ $initialProductName ? '' : 'selected' }}>Sélectionner une prestation</option>
                        @foreach($productModes as $productName => $modes)
                            <option value="{{ $productName }}"
                                {{ $initialProductName === $productName ? 'selected' : '' }}>
                                {{ $productName }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_name')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Mode de consultation --}}
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

                {{-- Cabinet location --}}
                <div class="details-box" id="cabinet-location-section" style="display:none;">
                    <label class="details-label">Cabinet</label>
                    <select id="practice_location_id" name="practice_location_id" class="form-control">
                        <option value="" disabled {{ $initialLocationId ? '' : 'selected' }}>Choisir un cabinet</option>
                        @foreach($practiceLocations as $loc)
                            <option value="{{ $loc->id }}"
                                    data-address="{{ $loc->full_address ?? ($loc->address_line1 . ', ' . $loc->postal_code . ' ' . $loc->city) }}"
                                {{ (string) $initialLocationId === (string) $loc->id ? 'selected' : '' }}>
                                {{ $loc->label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-red-500 mt-1 d-none" id="location-error"></p>
                </div>

                {{-- Cabinet address preview --}}
                <div class="details-box" id="therapist-address-section" style="display:none;">
                    <label class="details-label">Adresse du Cabinet</label>
                    <p id="therapist-address" class="form-control-static"></p>
                </div>

                {{-- Domicile address --}}
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
                           value="{{ $initialAppointmentDate }}">
                    <p id="date-loading-message" class="text-muted mt-1" style="display:none;">Chargement des dates…</p>
                    @error('appointment_date')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Time (grid) --}}
                <div class="details-box">
                    <label class="details-label">Horaire</label>
                    <input type="hidden" id="appointment_time" name="appointment_time" value="{{ $initialAppointmentTime }}">
                    <div id="time-slots-container" class="mt-2 text-muted">
                        Sélectionnez prestation, mode et date.
                    </div>
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
        enable: [],
        onChange: function(selectedDates, dateStr) {
            const productId = $('#product_id').val();
            const modeSlug  = $('#selected_mode_slug').val();
            const locId     = (modeSlug === 'cabinet') ? $('#practice_location_id').val() : null;

            if (dateStr && productId) {
                fetchSlots(dateStr, productId, modeSlug, locId);
            } else {
                resetSlotsUI();
            }
        }
    });

    function resetSlotsUI() {
        $('#appointment_time').val('');
        $('#time-slots-container').html('<span class="text-muted">Sélectionnez prestation, mode et date.</span>');
        $('#no-slots-message').hide().text('');
    }

    function fetchDates(productId, modeSlug, locationId = null) {
        $('#date-loading-message').show().text('Chargement des dates…');

        $.post('{{ route("appointments.available-dates-concrete-patient") }}', {
            product_id: productId,
            therapist_id: therapistId,
            mode: modeSlug,
            location_id: (modeSlug === 'cabinet' ? locationId : null),
            days: 60,
            _token: '{{ csrf_token() }}'
        })
        .done(res => {
            allowedDates = res.dates || [];
            if (!allowedDates.length) {
                fp.set('enable', []);
                fp.set('disable', [true]);
                fp.clear();
                resetSlotsUI();
                alert('Aucune date disponible pour cette prestation.');
                return;
            }
            fp.set('enable', allowedDates);
            fp.set('disable', []);
            // don't clear INITIAL_DATE if it exists and is valid – flatpickr will handle it
            resetSlotsUI();
        })
        .fail(() => {
            allowedDates = [];
            fp.set('enable', []);
            fp.set('disable', [true]);
            fp.clear();
            resetSlotsUI();
            alert('Une erreur est survenue lors du chargement des dates.');
        })
        .always(() => $('#date-loading-message').hide().text(''));
    }

    function fetchSlots(date, productId, modeSlug, locationId = null) {
        if (modeSlug === 'cabinet' && !locationId) {
            resetSlotsUI();
            return;
        }

        currentSlotsRequestId++;
        const reqId = currentSlotsRequestId;

        $('#appointment_time').val('');
        $('#time-slots-container').html('<span class="text-muted">Chargement des créneaux…</span>');
        $('#no-slots-message').hide().text('');

        $.post('{{ route("appointments.available-slots-patient") }}', {
            date: date,
            product_id: productId,
            therapist_id: therapistId,
            mode: modeSlug,
            location_id: (modeSlug === 'cabinet' ? locationId : null),
            _token: '{{ csrf_token() }}'
        })
        .done(res => {
            if (reqId !== currentSlotsRequestId) return;

            if (!Array.isArray(res.slots) || !res.slots.length) {
                $('#time-slots-container').html('<span class="text-muted">Aucun créneau disponible pour cette date.</span>');
                $('#no-slots-message')
                    .text('Aucun créneau n’est disponible pour ce jour. Merci de choisir une autre date.')
                    .show();
                return;
            }

            let html = '<div class="time-slots-grid">';
            res.slots.forEach(slot => {
                const t = slot.start;
                html += `<button type="button" class="time-slot-btn" data-time="${t}">${t}</button>`;
            });
            html += '</div>';
            $('#time-slots-container').html(html);

            if (OLD_TIME) {
                $('#appointment_time').val(OLD_TIME);
                $('#time-slots-container .time-slot-btn').each(function () {
                    if ($(this).data('time') === OLD_TIME) {
                        $(this).addClass('active');
                    }
                });
            }
        })
        .fail(() => {
            $('#time-slots-container').html('<span class="text-red-500">Erreur lors de la récupération des créneaux disponibles.</span>');
        });
    }

    // Time slot click
    $(document).on('click', '.time-slot-btn', function () {
        $('.time-slot-btn').removeClass('active');
        $(this).addClass('active');
        const time = $(this).data('time');
        $('#appointment_time').val(time);
        $('#no-slots-message').hide().text('');
    });

    // PRODUCT --> update mode dropdown
    $('#product_name').on('change', function () {
        const name  = $(this).val();
        const modes = PRODUCT_MODES[name] || [];
        const $mode = $('#consultation_mode').empty();

        $mode.append('<option value="" disabled selected>Sélectionner le mode</option>');
        $('#consultation-mode-section').toggle(modes.length > 0);

        modes.forEach(m => {
            $mode.append(`<option value="${m.product.id}" data-slug="${m.slug}">${m.mode}</option>`);
        });

        $('#product_id').val('');
        $('#selected_mode_slug').val('');
        $('#cabinet-location-section').hide();
        $('#therapist-address-section').hide();
        $('#client-address-section').hide();
        $('#mode-error').addClass('d-none').text('');
        $('#location-error').addClass('d-none').text('');

        allowedDates = [];
        fp.set('enable', []);
        fp.set('disable', []);
        fp.clear();
        resetSlotsUI();
    });

    // MODE --> update UI + fetch dates
    $('#consultation_mode').on('change', function () {
        const productId = $(this).val();
        const slug      = $(this).find(':selected').data('slug');

        $('#product_id').val(productId);
        $('#selected_mode_slug').val(slug);

        resetSlotsUI();
        fp.set('enable', []);
        fp.set('disable', []);
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

        if (slug !== 'cabinet') {
            fetchDates(productId, slug, null);
        }
    });

    // CABINET --> fetch dates + show address
    $('#practice_location_id').on('change', function () {
        const locId   = $(this).val();
        const address = $(this).find(':selected').data('address') || '';
        $('#therapist-address').text(address || 'Adresse non disponible.');
        $('#therapist-address-section').show();

        const productId = $('#product_id').val();
        const slug      = $('#selected_mode_slug').val();
        if (productId && slug === 'cabinet') {
            fetchDates(productId, slug, locId);
            $('#location-error').addClass('d-none').text('');
        }
    });

    // ---------- Initialisation (edit mode) ----------
    if (INITIAL_PRODUCT_NAME) {
        $('#product_name').val(INITIAL_PRODUCT_NAME).trigger('change');

        // attendre que le dropdown mode soit rempli
        setTimeout(function () {
            if (INITIAL_MODE_SLUG && INITIAL_PRODUCT_NAME in PRODUCT_MODES) {
                const modes = PRODUCT_MODES[INITIAL_PRODUCT_NAME] || [];
                const target = modes.find(m => m.product.id == @json($initialProductId) && m.slug === INITIAL_MODE_SLUG)
                           || modes.find(m => m.slug === INITIAL_MODE_SLUG)
                           || modes[0];

                if (target) {
                    $('#consultation_mode option').each(function () {
                        if ($(this).val() == target.product.id && $(this).data('slug') === target.slug) {
                            $(this).prop('selected', true);
                        }
                    });
                    $('#consultation_mode').trigger('change');
                }
            }

            if (INITIAL_MODE_SLUG === 'cabinet' && INITIAL_LOCATION_ID) {
                $('#practice_location_id').val(INITIAL_LOCATION_ID).trigger('change');
            }

            if (INITIAL_DATE) {
                setTimeout(function () {
                    fp.setDate(INITIAL_DATE, true);
                    const slug  = $('#selected_mode_slug').val();
                    const locId = (slug === 'cabinet') ? $('#practice_location_id').val() : null;
                    const productId = $('#product_id').val();
                    if (productId) {
                        fetchSlots(INITIAL_DATE, productId, slug, locId);
                    }
                }, 600);
            }
        }, 300);
    }

});
</script>

</x-app-layout>

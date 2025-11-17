{{-- resources/views/appointments/create_patient.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Demander un Rendez-vous') }}
        </h2>
    </x-slot>

    <!-- Font Awesome (icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Bootstrap (optional) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Flatpickr -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <style>
        .container { max-width: 800px; }
        .details-container {
            background-color: #f9f9f9; border-radius: 10px; padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,.1); margin: 0 auto;
        }
        .details-title { font-size: 2rem; font-weight: 700; color: #647a0b; margin-bottom: 20px; text-align: center; }
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
        @media (max-width: 600px) {
            .details-container { padding:20px; }
            .details-title { font-size:1.5rem; }
            .d-flex.justify-content-center.mt-4 { flex-direction:column; align-items:stretch; }
            .btn-primary, .btn-secondary { width:100%; margin-bottom:10px; }
        }

        /* Flatpickr theming */
        .flatpickr-calendar { border:1px solid #647a0b; }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange { background:#647a0b; color:#fff; }
        .flatpickr-day:hover { background:#854f38; color:#fff; }
        .flatpickr-day.disabled { background:#e9ecef; color:#6c757d; cursor:not-allowed; }
        .flatpickr-day.disabled:hover { background:#e9ecef; color:#6c757d; }

        /* Time slots grid */
        .time-slots-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .time-slot-btn {
            border: 1px solid #854f38;
            background: #ffffff;
            color: #854f38;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            min-width: 72px;
            text-align: center;
            font-size: 0.95rem;
        }
        .time-slot-btn.active,
        .time-slot-btn:hover {
            background: #647a0b;
            color: #ffffff;
            border-color: #647a0b;
        }
    </style>

    @php
        // Ensure we have locations (lazy load if not provided)
        $practiceLocations = $therapist->practiceLocations ?? collect();

        // Keep only products bookable online
        $onlineProducts = $products->filter(fn($p) => $p->can_be_booked_online);

        // Group by "display name" and expose available modes per name
        $productsByName = $onlineProducts->groupBy('name');

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
    @endphp

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Demander un Rendez-vous') }}</h1>

            @if(session('success'))
                <div class="alert alert-success text-center">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger text-center">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('appointments.storePatient') }}" method="POST">
                @csrf

                <!-- Hidden therapist -->
                <input type="hidden" name="therapist_id" value="{{ $therapist->id }}">

                <!-- Selected therapist -->
                <div class="details-box">
                    <label class="details-label">{{ __('Thérapeute Sélectionné') }}</label>
                    <p>{{ $therapist->company_name ?? $therapist->name }}</p>
                </div>

                <!-- Prestation -->
                @if(count($productModes) > 0)
                    <div class="details-box">
                        <label class="details-label" for="product_name">{{ __('Prestation') }}</label>
                        <select id="product_name" name="product_name" class="form-control" required>
                            <option value="" disabled selected>{{ __('Sélectionner une prestation') }}</option>
                            @foreach($productModes as $productName => $modes)
                                @php
                                    $p = $modes[0]['product'];
                                    $total = $p->price + ($p->price * $p->tax_rate / 100);
                                    $price = rtrim(rtrim(number_format($total, 2, '.', ''), '0'), '.');
                                @endphp
                                <option value="{{ $productName }}" {{ old('product_name') == $productName ? 'selected' : '' }}>
                                    {{ $productName }} - {{ $price }}€
                                </option>
                            @endforeach
                        </select>
                        @error('product_name')<p class="text-red-500">{{ $message }}</p>@enderror
                    </div>
                @else
                    <p>{{ __('Aucune prestation disponible pour la réservation en ligne.') }}</p>
                @endif

                <!-- Mode de consultation (populated after Prestation) -->
                <div class="details-box" id="consultation-mode-section" style="display:none;">
                    <label class="details-label" for="consultation_mode">{{ __('Mode de Consultation') }}</label>
                    <select id="consultation_mode" class="form-control" required>
                        <option value="" disabled selected>{{ __('Sélectionner un mode de consultation') }}</option>
                    </select>
                    <small class="text-muted">{{ __('Sélectionnez le mode (cabinet, visio, domicile).') }}</small>
                    <p class="text-red-500 mt-1 d-none" id="mode-error"></p>
                </div>

                <!-- Hidden "final" product_id and mode -->
                <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id') }}">
                <input type="hidden" name="type" id="selected_mode_slug" value="{{ old('type') }}"><!-- mode -->

                <!-- Cabinet: choose a practice location -->
                <div class="details-box" id="cabinet-location-section" style="display:none;">
                    <label class="details-label" for="practice_location_id">{{ __('Sélectionnez le Cabinet') }}</label>
                    <select id="practice_location_id" name="practice_location_id" class="form-control">
                        <option value="" disabled selected>{{ __('Choisir un lieu') }}</option>
                        @foreach($practiceLocations as $loc)
                            <option value="{{ $loc->id }}"
                                    data-address="{{ $loc->full_address ?? ($loc->address_line1 . ', ' . $loc->postal_code . ' ' . $loc->city) }}"
                                    {{ old('practice_location_id') == $loc->id ? 'selected':'' }}>
                                {{ $loc->label }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">{{ __('Le choix du lieu est requis pour les consultations au cabinet.') }}</small>
                    <p class="text-red-500 mt-1 d-none" id="location-error"></p>
                </div>

                <!-- Cabinet address preview -->
                <div class="details-box" id="therapist-address-section" style="display:none;">
                    <label class="details-label">{{ __('Adresse du Cabinet') }}</label>
                    <p id="therapist-address" class="form-control-static"></p>
                </div>

                <!-- Domicile: patient's address -->
                <div class="details-box" id="client-address-section" style="display:none;">
                    <label class="details-label" for="address">{{ __('Votre adresse') }}</label>
                    <input type="text" id="address" name="address" class="form-control"
                           value="{{ old('address', $clientProfile->address ?? '') }}">
                    @error('address')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Identity -->
                <div class="details-box">
                    <label class="details-label" for="first_name">{{ __('Votre Prénom') }}</label>
                    <input type="text" id="first_name" name="first_name" class="form-control"
                           value="{{ old('first_name') }}" required>
                    @error('first_name')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="details-box">
                    <label class="details-label" for="last_name">{{ __('Votre Nom') }}</label>
                    <input type="text" id="last_name" name="last_name" class="form-control"
                           value="{{ old('last_name') }}" required>
                    @error('last_name')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="details-box">
                    <label class="details-label" for="email">{{ __('Votre Email') }}</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="{{ old('email') }}" required>
                    @error('email')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="details-box">
                    <label class="details-label" for="phone">{{ __('Votre Numéro de Téléphone') }}</label>
                    <input type="text" id="phone" name="phone" class="form-control"
                           value="{{ old('phone') }}" required>
                    @error('phone')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Date -->
                <div class="details-box">
                    <label class="details-label" for="appointment_date">{{ __('Date du Rendez-vous') }}</label>
                    <input type="text" id="appointment_date" name="appointment_date" class="form-control"
                           value="{{ old('appointment_date') }}" required placeholder="{{ __('Sélectionner une date') }}">
                    <p id="date-loading-message" class="text-muted mt-1" style="display:none;">
                        {{ __('Chargement des jours disponibles...') }}
                    </p>
                    @error('appointment_date')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Hour (time slots grid + hidden input) -->
                <div class="details-box">
                    <label class="details-label" for="appointment_time">{{ __('Heure du Rendez-vous') }}</label>

                    <input type="hidden" id="appointment_time" name="appointment_time" value="{{ old('appointment_time') }}">

                    <div id="time-slots-container" class="mt-2">
                        <span class="text-muted">{{ __('Veuillez d’abord sélectionner une prestation, un mode et une date.') }}</span>
                    </div>

                    <p id="no-slots-message" class="text-red-500 mt-1" style="display:none;"></p>

                    @error('appointment_time')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Notes -->
                <div class="details-box">
                    <label class="details-label" for="notes">{{ __('Notes') }}</label>
                    <textarea id="notes" name="notes" class="form-control">{{ old('notes') }}</textarea>
                    @error('notes')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Submit -->
                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i> {{ __('Demander le Rendez-vous') }}
                    </button>
                    <a href="{{ url()->previous() }}" class="btn-secondary ms-3">
                        <i class="fas fa-arrow-left mr-2"></i> {{ __('Retour') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const PRODUCT_MODES = @json($productModes);
    const OLD_TIME      = @json(old('appointment_time'));

    $(function () {
        let allowedDates = []; // concrete Y-m-d dates with at least one slot
        let currentSlotsRequestId = 0;

        const fp = flatpickr("#appointment_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y",
            minDate: "today",
            locale: "fr",
            disableMobile: true,
            enable: [],     // will be set dynamically
            onChange: function(selectedDates, dateStr) {
                const therapistId = $('input[name="therapist_id"]').val();
                const productId   = $('#product_id').val();
                const modeSlug    = $('#selected_mode_slug').val();
                const locationId  = ($('#practice_location_id').is(':visible') ? $('#practice_location_id').val() : null);

                if (dateStr && productId && therapistId) {
                    fetchAvailableSlots(dateStr, productId, therapistId, modeSlug, locationId);
                } else {
                    resetTimeSelect();
                }
            }
        });

        function resetTimeSelect() {
            $('#appointment_time').val('');
            $('#time-slots-container').html(
                '<span class="text-muted">{{ __("Veuillez d’abord sélectionner une prestation, un mode et une date.") }}</span>'
            );
            $('#no-slots-message').hide().text('');
        }

        function requireCabinetLocationIfNeeded() {
            const modeSlug = $('#selected_mode_slug').val();
            if (modeSlug === 'cabinet') {
                const locId = $('#practice_location_id').val();
                if (!locId) {
                    $('#location-error').text('{{ __("Veuillez sélectionner un cabinet.") }}').removeClass('d-none');
                    return false;
                }
                $('#location-error').addClass('d-none');
            }
            return true;
        }

        /**
         * Ask the backend: "which concrete dates in the next N days have at least one slot?"
         */
        function loadAvailableDates(productId, therapistId, modeSlug, locationId) {
            $('#date-loading-message')
                .text('{{ __("Chargement des jours disponibles...") }}')
                .show();

            $.ajax({
                url: '{{ route("appointments.available-dates-concrete-patient") }}',
                method: 'POST',
                data: {
                    product_id:  productId,
                    therapist_id: therapistId,
                    mode:        modeSlug || undefined,
                    location_id: (modeSlug === 'cabinet' ? (locationId || undefined) : undefined),
                    days:        60,
                    _token:      '{{ csrf_token() }}'
                },
                success: function(response) {
                    allowedDates = Array.isArray(response.dates) ? response.dates : [];

                    if (allowedDates.length === 0) {
                        fp.set('enable', []);
                        fp.set('disable', [true]); // disable everything
                        fp.clear();
                        resetTimeSelect();
                        alert('{{ __("Aucune date disponible pour cette prestation.") }}');
                    } else {
                        fp.set('enable', allowedDates);
                        fp.set('disable', []); // only enabled dates are clickable
                        fp.clear();
                        resetTimeSelect();
                    }

                    $('#date-loading-message').hide().text('');
                },
                error: function(xhr) {
                    console.error('Error fetching available dates:', xhr.responseText);
                    allowedDates = [];
                    fp.set('enable', []);
                    fp.set('disable', [true]);
                    fp.clear();
                    resetTimeSelect();
                    $('#date-loading-message').hide().text('');
                    alert('{{ __("Une erreur est survenue lors de la récupération des jours disponibles.") }}');
                }
            });
        }

        /**
         * Fetch slots for a specific date (uses the same logic as date precomputation).
         */
        function fetchAvailableSlots(date, productId, therapistId, modeSlug, locationId) {
            if (modeSlug === 'cabinet' && !locationId) {
                resetTimeSelect();
                return;
            }

            currentSlotsRequestId++;
            const thisRequestId = currentSlotsRequestId;

            $('#appointment_time').val('');
            $('#time-slots-container').html(
                '<span class="text-muted">{{ __("Chargement des créneaux disponibles...") }}</span>'
            );
            $('#no-slots-message').hide().text('');

            const payload = {
                date:         date,
                product_id:   productId,
                therapist_id: therapistId,
                _token:       '{{ csrf_token() }}',
                mode:         modeSlug || undefined,
                location_id:  (modeSlug === 'cabinet' ? (locationId || undefined) : undefined)
            };

            $.ajax({
                url: '{{ route("appointments.available-slots-patient") }}',
                method: 'POST',
                data: payload,
                success: function (response) {
                    if (thisRequestId !== currentSlotsRequestId) return;

                    const hasSlots = Array.isArray(response.slots) && response.slots.length > 0;

                    if (hasSlots) {
                        let html = '<div class="time-slots-grid">';
                        response.slots.forEach(function (slot) {
                            const t = slot.start;
                            html += `<button type="button" class="time-slot-btn" data-time="${t}">${t}</button>`;
                        });
                        html += '</div>';
                        $('#time-slots-container').html(html);
                        $('#no-slots-message').hide().text('');

                        if (OLD_TIME) {
                            $('#appointment_time').val(OLD_TIME);
                            $('#time-slots-container .time-slot-btn').each(function () {
                                if ($(this).data('time') === OLD_TIME) {
                                    $(this).addClass('active');
                                }
                            });
                        }
                    } else {
                        $('#time-slots-container').html(
                            '<span class="text-muted">{{ __("Aucun créneau disponible pour cette date.") }}</span>'
                        );
                        $('#no-slots-message')
                            .text('{{ __("Aucun créneau n’est disponible pour ce jour. Merci de choisir une autre date.") }}')
                            .show();
                    }
                },
                error: function (xhr) {
                    if (thisRequestId !== currentSlotsRequestId) return;
                    console.error('Error fetching available slots:', xhr.responseText);
                    $('#time-slots-container').html(
                        '<span class="text-red-500">{{ __("Une erreur est survenue lors de la récupération des créneaux disponibles.") }}</span>'
                    );
                }
            });
        }

        // Click on time slot
        $(document).on('click', '.time-slot-btn', function () {
            $('.time-slot-btn').removeClass('active');
            $(this).addClass('active');
            const time = $(this).data('time');
            $('#appointment_time').val(time);
            $('#no-slots-message').hide().text('');
        });

        // When Prestation changes → populate Mode
        $('#product_name').on('change', function () {
            const name  = $(this).val();
            const modes = PRODUCT_MODES[name] || [];
            const $mode = $('#consultation_mode');

            $mode.empty().append('<option value="" disabled selected>{{ __("Sélectionner un mode de consultation") }}</option>');
            $('#consultation-mode-section').toggle(modes.length > 0);

            modes.forEach(function (m) {
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
            resetTimeSelect();
        });

        // When Mode changes
        $('#consultation_mode').on('change', function () {
            const productId = $(this).val();
            const modeSlug  = $(this).find(':selected').data('slug');

            $('#product_id').val(productId);
            $('#selected_mode_slug').val(modeSlug);

            if (modeSlug === 'cabinet') {
                $('#cabinet-location-section').show();
                $('#therapist-address-section').hide();
                $('#client-address-section').hide();
            } else if (modeSlug === 'domicile') {
                $('#cabinet-location-section').hide();
                $('#therapist-address-section').hide();
                $('#client-address-section').show();
            } else {
                $('#cabinet-location-section').hide();
                $('#therapist-address-section').hide();
                $('#client-address-section').hide();
            }

            const therapistId = $('input[name="therapist_id"]').val();

            if (modeSlug === 'cabinet') {
                allowedDates = [];
                fp.set('enable', []);
                fp.set('disable', []);
                fp.clear();
                resetTimeSelect();
            } else if (productId && therapistId) {
                loadAvailableDates(productId, therapistId, modeSlug, null);
            }
        });

        // When Cabinet location changes
        $('#practice_location_id').on('change', function () {
            const locId   = $(this).val();
            const address = $(this).find(':selected').data('address') || '';
            $('#therapist-address').text(address || '{{ __("Adresse non disponible.") }}');
            $('#therapist-address-section').show();

            const productId   = $('#product_id').val();
            const therapistId = $('input[name="therapist_id"]').val();
            const modeSlug    = $('#selected_mode_slug').val();

            if (productId && therapistId && modeSlug === 'cabinet') {
                loadAvailableDates(productId, therapistId, modeSlug, locId);
                $('#location-error').addClass('d-none').text('');
            }
        });

        // Restore old input (if any)
        @if(old('product_name'))
            $('#product_name').val(@json(old('product_name'))).trigger('change');
            @if(old('product_id'))
                setTimeout(function () {
                    const productId = @json(old('product_id'));

                    $('#consultation_mode option').each(function(){
                        if ($(this).val() == productId) $(this).prop('selected', true);
                    });
                    $('#consultation_mode').trigger('change');

                    @if(old('type'))
                        $('#selected_mode_slug').val(@json(old('type')));
                    @endif

                    @if(old('practice_location_id'))
                        $('#practice_location_id').val(@json(old('practice_location_id'))).trigger('change');
                    @endif

                    @if(old('appointment_date'))
                        setTimeout(function(){
                            const therapistId = $('input[name="therapist_id"]').val();
                            const modeSlug    = $('#selected_mode_slug').val();
                            const locationId  = ($('#practice_location_id').is(':visible') ? $('#practice_location_id').val() : null);

                            // load available dates then set date + slots
                            loadAvailableDates(productId, therapistId, modeSlug, locationId);

                            setTimeout(function() {
                                fp.setDate(@json(old('appointment_date')), true);
                                fetchAvailableSlots(@json(old('appointment_date')), productId, therapistId, modeSlug, locationId);
                            }, 600);
                        }, 300);
                    @endif
                }, 300);
            @endif
        @endif

        // Validate before submit (ensure cabinet has location)
        $('form').on('submit', function (e) {
            if (!requireCabinetLocationIfNeeded()) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>

</x-app-layout>

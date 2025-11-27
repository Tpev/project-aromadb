{{-- resources/views/mobile/appointments/create.blade.php --}}
<x-mobile-layout>

    <!-- Simple mobile header -->
    <div class="px-4 pt-4">
        <h2 class="text-xl font-semibold text-[#647a0b]">
            {{ __('Demander un Rendez-vous') }}
        </h2>
    </div>

    {{-- Icons (for small icons in buttons) --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    @php
        // Ensure we have locations (lazy load if not provided)
        $practiceLocations = $practiceLocations ?? ($therapist->practiceLocations ?? collect());

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

    <!-- Main container (mobile friendly) -->
    <div class="container mt-4 px-3 pb-6">
        <div class="details-container mx-auto p-4">

            {{-- Wizard progress --}}
            <div class="wizard-progress mb-4">
                <div class="wizard-steps">
                    <div class="wizard-step" id="wizard-step-1">
                        <div class="circle">1</div>
                        <span class="label text-xs">{{ __('Choix du créneau') }}</span>
                    </div>
                    <div class="wizard-line"></div>
                    <div class="wizard-step" id="wizard-step-2">
                        <div class="circle">2</div>
                        <span class="label text-xs">{{ __('Vos informations') }}</span>
                    </div>
                </div>
            </div>

            <h1 class="details-title text-center mb-4">{{ __('Demander un Rendez-vous') }}</h1>

            @if(session('success'))
                <div class="alert alert-success text-center text-sm">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger text-center text-sm">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('mobile.appointments.store') }}" method="POST">
                @csrf

                <!-- Hidden therapist -->
                <input type="hidden" name="therapist_id" value="{{ $therapist->id }}">

                <!-- STEP 1: PRESTA / MODE / DATE / TIME -->
                <div id="step-1">
                    <!-- Selected therapist -->
                    <div class="details-box mb-3">
                        <label class="details-label">{{ __('Thérapeute Sélectionné') }}</label>
                        <p class="text-sm">
                            {{ $therapist->company_name ?? $therapist->name }}
                        </p>
                    </div>

                    <!-- Prestation -->
                    @if(count($productModes) > 0)
                        <div class="details-box mb-3">
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
                            @error('product_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    @else
                        <p class="text-sm mb-3">{{ __('Aucune prestation disponible pour la réservation en ligne.') }}</p>
                    @endif

                    <!-- Mode de consultation -->
                    <div class="details-box mb-3" id="consultation-mode-section" style="display:none;">
                        <label class="details-label" for="consultation_mode">{{ __('Mode de Consultation') }}</label>
                        <select id="consultation_mode" class="form-control" required>
                            <option value="" disabled selected>{{ __('Sélectionner un mode de consultation') }}</option>
                        </select>
                        <small class="text-[11px] text-gray-500">
                            {{ __('Sélectionnez le mode (cabinet, visio, domicile).') }}
                        </small>
                        <p class="text-red-500 text-xs mt-1 d-none" id="mode-error"></p>
                    </div>

                    <!-- Hidden product_id & type (mode) -->
                    <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id') }}">
                    <input type="hidden" name="type" id="selected_mode_slug" value="{{ old('type') }}">

                    <!-- Cabinet location -->
                    <div class="details-box mb-3" id="cabinet-location-section" style="display:none;">
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
                        <small class="text-[11px] text-gray-500">
                            {{ __('Le choix du lieu est requis pour les consultations au cabinet.') }}
                        </small>
                        <p class="text-red-500 text-xs mt-1 d-none" id="location-error"></p>
                    </div>

                    <!-- Cabinet address preview -->
                    <div class="details-box mb-3" id="therapist-address-section" style="display:none;">
                        <label class="details-label">{{ __('Adresse du Cabinet') }}</label>
                        <p id="therapist-address"
                           class="text-sm border rounded px-2 py-1 bg-gray-50"></p>
                    </div>

                    <!-- Domicile: patient's address (Step1 just so we know if required, but you can keep the real input in Step2) -->
                    <div class="details-box mb-3" id="client-address-helper" style="display:none;">
                        <p class="text-xs text-gray-600">
                            {{ __('Vous pourrez indiquer votre adresse à l’étape suivante.') }}
                        </p>
                    </div>

                    <!-- Date (Flatpickr) -->
                    <div class="details-box mb-3">
                        <label class="details-label" for="appointment_date">{{ __('Date du Rendez-vous') }}</label>
                        <input type="text" id="appointment_date" name="appointment_date" class="form-control"
                               value="{{ old('appointment_date') }}" required
                               placeholder="{{ __('Sélectionner une date') }}">
                        <p id="date-loading-message" class="text-muted mt-1 text-xs" style="display:none;">
                            {{ __('Chargement des jours disponibles...') }}
                        </p>
                        @error('appointment_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Time slots -->
                    <div class="details-box mb-3">
                        <label class="details-label" for="appointment_time">{{ __('Heure du Rendez-vous') }}</label>

                        <input type="hidden" id="appointment_time" name="appointment_time"
                               value="{{ old('appointment_time') }}">

                        <div id="time-slots-container" class="mt-2">
                            <span class="text-muted text-xs">
                                {{ __('Veuillez d’abord sélectionner une prestation, un mode et une date.') }}
                            </span>
                        </div>

                        <p id="no-slots-message" class="text-red-500 text-xs mt-1" style="display:none;"></p>

                        @error('appointment_time')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Step 1 actions -->
                    <div class="mt-4 flex flex-col gap-2">
                        <button type="button"
                                id="btn-step1-next"
                                class="btn-primary w-100"
                                disabled>
                            {{ __('Continuer') }}
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>

                        <button type="button"
                                class="btn-secondary-outline w-100"
                                onclick="window.history.back()">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('Annuler') }}
                        </button>
                    </div>
                </div>

                <!-- STEP 2: PERSONAL INFO -->
                <div id="step-2" style="display:none;">
                    <!-- Summary of chosen slot -->
                    <div class="details-box mb-3 rounded-md p-3 bg-[#fff9f6] border border-[#e4e8d5]">
                        <p class="text-xs text-gray-500 mb-1 uppercase tracking-wide">
                            {{ __('Récapitulatif du rendez-vous') }}
                        </p>
                        <p class="text-sm">
                            <strong>{{ $therapist->company_name ?? $therapist->name }}</strong><br>
                            <span id="summary-product" class="text-xs text-gray-700"></span><br>
                            <span id="summary-mode" class="text-xs text-gray-700"></span><br>
                            <span id="summary-date" class="text-xs text-gray-700"></span><br>
                            <span id="summary-time" class="text-xs text-gray-700"></span>
                        </p>
                    </div>

                    <!-- Identity -->
                    <div class="details-box mb-3">
                        <label class="details-label" for="first_name">{{ __('Votre Prénom') }}</label>
                        <input type="text" id="first_name" name="first_name" class="form-control"
                               value="{{ old('first_name') }}" required>
                        @error('first_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="details-box mb-3">
                        <label class="details-label" for="last_name">{{ __('Votre Nom') }}</label>
                        <input type="text" id="last_name" name="last_name" class="form-control"
                               value="{{ old('last_name') }}" required>
                        @error('last_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="details-box mb-3">
                        <label class="details-label" for="email">{{ __('Votre Email') }}</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="{{ old('email') }}" required>
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="details-box mb-3">
                        <label class="details-label" for="phone">{{ __('Votre Numéro de Téléphone') }}</label>
                        <input type="text" id="phone" name="phone" class="form-control"
                               value="{{ old('phone') }}" required>
                        @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Domicile: patient's address (real input here) -->
                    <div class="details-box mb-3" id="client-address-section" style="display:none;">
                        <label class="details-label" for="address">{{ __('Votre adresse (pour les rendez-vous à domicile)') }}</label>
                        <input type="text" id="address" name="address" class="form-control"
                               value="{{ old('address') }}">
                        @error('address')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Notes -->
                    <div class="details-box mb-3">
                        <label class="details-label" for="notes">{{ __('Notes') }}</label>
                        <textarea id="notes" name="notes" class="form-control"
                                  rows="3">{{ old('notes') }}</textarea>
                        @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Step 2 actions -->
                    <div class="mt-4 flex flex-col gap-2">
                        <div class="flex gap-2">
                            <button type="button"
                                    id="btn-step2-back"
                                    class="btn-secondary w-1/2">
                                <i class="fas fa-arrow-left mr-2"></i>
                                {{ __('Retour') }}
                            </button>

                            <button type="submit"
                                    class="btn-primary w-1/2">
                                <i class="fas fa-check mr-2"></i>
                                {{ __('Confirmer le Rendez-vous') }}
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1 text-center">
                            {{ __('En confirmant, votre demande sera envoyée au thérapeute.') }}
                        </p>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Styles -->
    <style>
        .container { max-width: 480px; }
        .details-container {
            background-color: #f9f9f9; border-radius: 10px; padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,.06); margin: 0 auto;
        }
        .details-title { font-size: 1.5rem; font-weight: 700; color: #647a0b; }
        .details-box { margin-bottom: 14px; }
        .details-label { font-weight: 700; color: #647a0b; display:block; margin-bottom: 5px; font-size: 0.9rem; }
        .form-control { width: 100%; padding: 8px 10px; border: 1px solid #854f38; border-radius: 6px; box-sizing: border-box; font-size: 0.9rem; }
        .form-control:focus { border-color:#647a0b; outline: none; box-shadow: 0 0 5px rgba(100,122,11,.4); }
        .btn-primary, .btn-secondary, .btn-secondary-outline {
            border:none; color:#fff; padding:10px 16px; border-radius:6px;
            display:inline-flex; align-items:center; justify-content:center; cursor:pointer;
            transition:.3s; font-size:0.95rem;
        }
        .btn-primary { background-color:#647a0b; }
        .btn-secondary { background-color:#854f38; }
        .btn-secondary-outline {
            background-color:transparent; color:#854f38; border:1px solid #854f38;
        }
        .btn-primary[disabled] {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .btn-primary:hover:not([disabled]) { background-color:#4f5e08; }
        .btn-secondary:hover { background-color:#6b3b2c; }
        .btn-secondary-outline:hover {
            background-color:#854f38; color:#fff;
        }
        .text-red-500 { color:#e3342f; font-size:.8rem; margin-top:5px; }

        /* Time slots grid */
        .time-slots-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }
        .time-slot-btn {
            border: 1px solid #854f38;
            background: #ffffff;
            color: #854f38;
            padding: 5px 10px;
            border-radius: 999px;
            cursor: pointer;
            min-width: 64px;
            text-align: center;
            font-size: 0.85rem;
        }
        .time-slot-btn.active,
        .time-slot-btn:hover {
            background: #647a0b;
            color: #ffffff;
            border-color: #647a0b;
        }

        /* Wizard progress */
        .wizard-steps {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .wizard-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }
        .wizard-step .circle {
            width: 26px;
            height: 26px;
            border-radius: 999px;
            border: 2px solid #e4e8d5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            background-color: #fff;
            color: #9ca3af;
        }
        .wizard-step.active .circle {
            border-color: #647a0b;
            background-color: #647a0b;
            color: #fff;
        }
        .wizard-step.completed .circle {
            border-color: #647a0b;
            background-color: #647a0b;
            color: #fff;
        }
        .wizard-step .label {
            margin-top: 4px;
            color: #6b7280;
        }
        .wizard-step.active .label,
        .wizard-step.completed .label {
            color: #374151;
            font-weight: 600;
        }
        .wizard-line {
            height: 2px;
            background-color: #e4e8d5;
            flex: 0 0 28px;
            margin: 0 4px;
        }
        .wizard-line.active {
            background-color: #647a0b;
        }
    </style>

    <!-- Scripts: Flatpickr + jQuery (same logic as web version) -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        const PRODUCT_MODES = @json($productModes);
        const OLD_TIME      = @json(old('appointment_time'));

        let allowedDates = [];
        let currentSlotsRequestId = 0;
        let currentStep = 1;

        $(function () {
            const therapistId = $('input[name="therapist_id"]').val();

            const fp = flatpickr("#appointment_date", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d-m-Y",
                minDate: "today",
                locale: "fr",
                disableMobile: true,
                enable: []
            });

            function setStep(step) {
                currentStep = step;

                if (step === 1) {
                    $('#step-1').show();
                    $('#step-2').hide();
                    $('#wizard-step-1').addClass('active').removeClass('completed');
                    $('#wizard-step-2').removeClass('active completed');
                    $('.wizard-line').removeClass('active');
                } else {
                    $('#step-1').hide();
                    $('#step-2').show();
                    $('#wizard-step-1').addClass('completed').removeClass('active');
                    $('#wizard-step-2').addClass('active');
                    $('.wizard-line').addClass('active');
                    refreshSummary();
                    toggleDomicileAddress();
                }
            }

            function isStep1Complete() {
                const productId = $('#product_id').val();
                const modeSlug  = $('#selected_mode_slug').val();
                const date      = $('#appointment_date').val();
                const time      = $('#appointment_time').val();

                if (!productId || !modeSlug || !date || !time) {
                    return false;
                }
                if (modeSlug === 'cabinet' && !$('#practice_location_id').val()) {
                    return false;
                }
                return true;
            }

            function refreshNextButton() {
                $('#btn-step1-next').prop('disabled', !isStep1Complete());
            }

            function resetTimeSelect() {
                $('#appointment_time').val('');
                $('#time-slots-container').html(
                    '<span class="text-muted text-xs">{{ __("Veuillez d’abord sélectionner une prestation, un mode et une date.") }}</span>'
                );
                $('#no-slots-message').hide().text('');
                refreshNextButton();
            }

            function requireCabinetLocationIfNeeded() {
                const modeSlug = $('#selected_mode_slug').val();
                if (modeSlug === 'cabinet') {
                    const locId = $('#practice_location_id').val();
                    if (!locId) {
                        $('#location-error').text('{{ __("Veuillez sélectionner un cabinet.") }}').removeClass('d-none');
                        return false;
                    }
                    $('#location-error').addClass('d-none').text('');
                }
                return true;
            }

            function fetchDates(productId, modeSlug, locationId = null) {
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
                            fp.clear();
                            resetTimeSelect();
                            alert('{{ __("Aucune date disponible pour cette prestation.") }}');
                        } else {
                            fp.set('enable', allowedDates);
                            fp.clear();
                            resetTimeSelect();
                        }

                        $('#date-loading-message').hide().text('');
                    },
                    error: function(xhr) {
                        console.error('Error fetching available dates:', xhr.responseText);
                        allowedDates = [];
                        fp.set('enable', []);
                        fp.clear();
                        resetTimeSelect();
                        $('#date-loading-message').hide().text('');
                        alert('{{ __("Une erreur est survenue lors de la récupération des jours disponibles.") }}');
                    }
                });
            }

            function fetchAvailableSlots(date, productId, modeSlug, locationId) {
                if (!date || !productId || !modeSlug) {
                    resetTimeSelect();
                    return;
                }
                if (modeSlug === 'cabinet' && !locationId) {
                    resetTimeSelect();
                    return;
                }

                currentSlotsRequestId++;
                const thisRequestId = currentSlotsRequestId;

                $('#appointment_time').val('');
                $('#time-slots-container').html(
                    '<span class="text-muted text-xs">{{ __("Chargement des créneaux disponibles...") }}</span>'
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
                                '<span class="text-muted text-xs">{{ __("Aucun créneau disponible pour cette date.") }}</span>'
                            );
                            $('#no-slots-message')
                                .text('{{ __("Aucun créneau n’est disponible pour ce jour. Merci de choisir une autre date.") }}')
                                .show();
                        }

                        refreshNextButton();
                    },
                    error: function (xhr) {
                        if (thisRequestId !== currentSlotsRequestId) return;
                        console.error('Error fetching available slots:', xhr.responseText);
                        $('#time-slots-container').html(
                            '<span class="text-red-500 text-xs">{{ __("Une erreur est survenue lors de la récupération des créneaux disponibles.") }}</span>'
                        );
                    }
                });
            }

            // central refresh used by product / mode / cabinet
            function refreshDates() {
                const productId = $('#product_id').val();
                const modeSlug  = $('#selected_mode_slug').val();
                const locId     = (modeSlug === 'cabinet' ? $('#practice_location_id').val() : null);

                if (!productId || !modeSlug || (modeSlug === 'cabinet' && !locId)) {
                    allowedDates = [];
                    fp.set('enable', []);
                    fp.clear();
                    resetTimeSelect();
                    return;
                }

                fetchDates(productId, modeSlug, locId);
            }

            function refreshSummary() {
                const productName = $('#product_name').val() || '';
                const modeSlug    = $('#selected_mode_slug').val() || '';
                const dateRaw     = $('#appointment_date').val() || '';
                const timeRaw     = $('#appointment_time').val() || '';

                let modeLabel = '';
                if (modeSlug === 'cabinet')  modeLabel = '{{ __("Au cabinet") }}';
                if (modeSlug === 'visio')    modeLabel = '{{ __("En visio") }}';
                if (modeSlug === 'domicile') modeLabel = '{{ __("À domicile") }}';

                $('#summary-product').text(productName ? productName : '');
                $('#summary-mode').text(modeLabel);
                $('#summary-date').text(dateRaw ? dateRaw : '');
                $('#summary-time').text(timeRaw ? timeRaw : '');
            }

            function toggleDomicileAddress() {
                const modeSlug = $('#selected_mode_slug').val();
                if (modeSlug === 'domicile') {
                    $('#client-address-section').show();
                } else {
                    $('#client-address-section').hide();
                }
            }

            // Click on time slot
            $(document).on('click', '.time-slot-btn', function () {
                $('.time-slot-btn').removeClass('active');
                $(this).addClass('active');
                const time = $(this).data('time');
                $('#appointment_time').val(time);
                $('#no-slots-message').hide().text('');
                refreshNextButton();
            });

            // When Prestation changes → populate Mode & refresh dates
            $('#product_name').on('change', function () {
                const name  = $(this).val();
                const modes = PRODUCT_MODES[name] || [];
                const $mode = $('#consultation_mode');

                $mode.empty().append('<option value="" disabled selected>{{ __("Sélectionner un mode de consultation") }}</option>');
                $('#consultation-mode-section').toggle(modes.length > 0);

                modes.forEach(function (m) {
                    $mode.append(`<option value="${m.product.id}" data-slug="${m.slug}">${m.mode}</option>`);
                });

                // reset hidden state & UI
                $('#product_id').val('');
                $('#selected_mode_slug').val('');
                $('#cabinet-location-section').hide();
                $('#therapist-address-section').hide();
                $('#therapist-address').text('');
                $('#client-address-helper').hide();
                $('#mode-error').addClass('d-none').text('');
                $('#location-error').addClass('d-none').text('');

                allowedDates = [];
                fp.set('enable', []);
                fp.clear();
                resetTimeSelect();
                refreshNextButton();
            });

            // Mode change
            $('#consultation_mode').on('change', function () {
                const productId = $(this).val();
                const modeSlug  = $(this).find(':selected').data('slug');

                $('#product_id').val(productId);
                $('#selected_mode_slug').val(modeSlug);

                resetTimeSelect();
                fp.set('enable', []);
                fp.clear();

                if (modeSlug === 'cabinet') {
                    $('#cabinet-location-section').show();
                    $('#client-address-helper').hide();
                } else if (modeSlug === 'domicile') {
                    $('#cabinet-location-section').hide();
                    $('#therapist-address-section').hide();
                    $('#client-address-helper').show();
                } else {
                    $('#cabinet-location-section').hide();
                    $('#therapist-address-section').hide();
                    $('#client-address-helper').hide();
                }

                refreshDates();
                refreshNextButton();
            });

            // Cabinet location change
            $('#practice_location_id').on('change', function () {
                const locId   = $(this).val();
                const address = $(this).find(':selected').data('address') || '';
                $('#therapist-address').text(address || '{{ __("Adresse non disponible.") }}');
                $('#therapist-address-section').show();
                $('#location-error').addClass('d-none').text('');

                refreshDates();
                refreshNextButton();
            });

            // Date change
            $('#appointment_date').on('change', function () {
                const date      = $(this).val();
                const productId = $('#product_id').val();
                const modeSlug  = $('#selected_mode_slug').val();
                const locId     = (modeSlug === 'cabinet' ? $('#practice_location_id').val() : null);

                if (date && productId && modeSlug) {
                    fetchAvailableSlots(date, productId, modeSlug, locId);
                } else {
                    resetTimeSelect();
                }
                refreshNextButton();
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
                                const modeSlug   = $('#selected_mode_slug').val();
                                const locationId = (modeSlug === 'cabinet' ? $('#practice_location_id').val() : null);
                                const oldDate    = @json(old('appointment_date'));

                                fp.setDate(oldDate, true);
                                fetchAvailableSlots(oldDate, productId, modeSlug, locationId);
                            }, 600);
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

            // Step navigation
            $('#btn-step1-next').on('click', function () {
                if (!isStep1Complete()) return;
                if (!requireCabinetLocationIfNeeded()) return;
                setStep(2);
            });

            $('#btn-step2-back').on('click', function () {
                setStep(1);
            });

            // Initial state
            setStep(1);
            refreshNextButton();
        });
    </script>

</x-mobile-layout>

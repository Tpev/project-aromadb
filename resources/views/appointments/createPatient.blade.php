{{-- resources/views/appointments/create_patient.blade.php --}}
<x-app-layout>


{{-- ───────── SEO: META DESCRIPTION (Booking page) ───────── --}}
@php
    // 1) Location fragment
    $city  = trim($therapist->city_setByAdmin  ?? '');
    $state = trim($therapist->state_setByAdmin ?? '');
    $location = $city
        ? ($state ? "$city, $state" : $city)
        : ($state ?: __('votre région'));

    // 2) Services (up to 3)
    $servicesRaw = json_decode($therapist->services, true) ?? [];
    $servicesArr = is_array($servicesRaw) ? array_filter($servicesRaw) : [];
    $services    = collect($servicesArr)->unique()->take(3)->implode(', ');

    // 3) Label fallback
    $label = $services ?: __('Thérapeute');

    // 4) Short “about/profile” snippet (prefer profile_description if you have it)
    $rawAbout = $therapist->profile_description ?: ($therapist->about ?? '');
    $aboutSnippet = \Illuminate\Support\Str::limit(trim(strip_tags((string) $rawAbout)), 110);

    // 5) Final meta (aim ~155 chars)
    $meta = \Illuminate\Support\Str::limit(
        trim(sprintf(
            'Prendre rendez-vous avec %s – %s à %s. %s',
            $therapist->company_name ?? $therapist->name,
            $label,
            $location,
            $aboutSnippet
        )),
        155,
        '…'
    );
@endphp

@section('meta_description', $meta)




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
        :root {
            --brand: #647a0b;
            --brown: #854f38;
            --bg: #f9f9f9;
        }

        .container { max-width: 900px; }
        .details-container {
            background-color: var(--bg);
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 10px 25px rgba(0,0,0,.08);
            margin: 0 auto;
            border: 1px solid rgba(0,0,0,.04);
        }

        .details-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--brand);
            margin-bottom: 10px;
            text-align: center;
            letter-spacing: -0.02em;
        }

        .subtle {
            color: #64748b;
            font-size: .92rem;
            text-align: center;
            margin: -4px 0 14px;
        }

        .details-box { margin-bottom: 18px; }
        .details-label {
            font-weight: 800;
            color: var(--brand);
            display:block;
            margin-bottom: 6px;
            font-size: .95rem;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid rgba(133,79,56,.55);
            border-radius: 10px;
            box-sizing: border-box;
            background: #fff;
        }

        .form-control:focus {
            border-color: var(--brand);
            outline: none;
            box-shadow: 0 0 0 4px rgba(100,122,11,.14);
        }

        .btn-primary, .btn-secondary, .btn-outline {
            border:none;
            padding: 10px 16px;
            border-radius: 12px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            transition:.2s;
            font-size: 1rem;
            font-weight: 700;
            gap: .5rem;
            text-decoration: none;
            user-select: none;
        }
        .btn-primary {
            background-color: var(--brand);
            color:#fff;
        }
        .btn-primary:hover { background-color: var(--brown); color:#fff; }

        .btn-secondary {
            background: #fff;
            color: var(--brand);
            border: 1px solid rgba(100,122,11,.35);
        }
        .btn-secondary:hover { background: rgba(100,122,11,.08); color: var(--brand); }

        .btn-outline {
            background: transparent;
            color: var(--brown);
            border: 1px solid rgba(133,79,56,.45);
        }
        .btn-outline:hover {
            background: rgba(133,79,56,.06);
            color: var(--brown);
        }

        .text-red-500 { color:#e3342f; font-size:.9rem; margin-top:6px; }

        /* Wizard */
        .wizard-top {
            display:flex;
            gap: 10px;
            align-items:center;
            justify-content: center;
            padding: 10px;
            border-radius: 14px;
            background: rgba(100,122,11,.08);
            border: 1px solid rgba(100,122,11,.14);
            margin: 0 auto 18px;
        }
        .step-pill {
            display:flex;
            align-items:center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.75);
            border: 1px solid rgba(0,0,0,.06);
            min-width: 220px;
            justify-content:center;
            font-weight: 800;
            color: #334155;
        }
        .step-pill .dot {
            width: 26px; height: 26px;
            border-radius: 999px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size: .9rem;
            background: rgba(100,122,11,.12);
            color: var(--brand);
            border: 1px solid rgba(100,122,11,.25);
        }
        .step-pill.active {
            background: #fff;
            border-color: rgba(100,122,11,.35);
            box-shadow: 0 10px 22px rgba(0,0,0,.06);
        }
        .step-pill.active .dot {
            background: var(--brand);
            color: #fff;
            border-color: var(--brand);
        }
        .step-pill.done .dot {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
        }

        .wizard-section { display:none; }
        .wizard-section.active { display:block; }

        .card-soft {
            background: rgba(255,255,255,.85);
            border: 1px solid rgba(0,0,0,.05);
            border-radius: 14px;
            padding: 14px;
        }

        .summary-row {
            display:flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items:center;
            justify-content: space-between;
            background: rgba(100,122,11,.06);
            border: 1px dashed rgba(100,122,11,.35);
            border-radius: 12px;
            padding: 10px 12px;
            margin-top: 10px;
        }
        .summary-row .item {
            font-size: .92rem;
            color:#334155;
        }
        .summary-row .item b { color:#0f172a; }

        /* Flatpickr theming */
        .flatpickr-calendar { border:1px solid var(--brand); }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange { background:var(--brand); color:#fff; }
        .flatpickr-day:hover { background:var(--brown); color:#fff; }
        .flatpickr-day.disabled { background:#e9ecef; color:#6c757d; cursor:not-allowed; }
        .flatpickr-day.disabled:hover { background:#e9ecef; color:#6c757d; }

        /* Time slots grid */
        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 8px;
        }
        .time-slot-btn {
            border: 1px solid rgba(133,79,56,.55);
            background: #ffffff;
            color: var(--brown);
            padding: 9px 10px;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            font-size: 0.95rem;
            font-weight: 800;
            transition: .15s;
            min-height: 42px;
        }
        .time-slot-btn.active,
        .time-slot-btn:hover {
            background: var(--brand);
            color: #ffffff;
            border-color: var(--brand);
            transform: translateY(-1px);
            box-shadow: 0 10px 18px rgba(0,0,0,.08);
        }

        .hint {
            font-size: .9rem;
            color:#64748b;
        }

        .divider {
            height: 1px;
            background: rgba(0,0,0,.06);
            margin: 14px 0;
        }

        @media (max-width: 900px) {
            .time-slots-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        @media (max-width: 600px) {
            .details-container { padding: 16px; border-radius: 16px; }
            .details-title { font-size: 1.55rem; }
            .wizard-top { position: sticky; top: 0; z-index: 10; backdrop-filter: blur(8px); }
            .step-pill { min-width: 0; flex: 1; padding: 8px 10px; font-size: .92rem; }
            .summary-row { flex-direction: column; align-items: flex-start; }
            .time-slots-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .btn-row { flex-direction: column; align-items: stretch !important; }
            .btn-row .btn-primary, .btn-row .btn-secondary, .btn-row .btn-outline { width:100%; }
        }
    </style>

    @php
        $practiceLocations = $therapist->practiceLocations ?? collect();

        $onlineProducts = $products->filter(fn($p) => $p->can_be_booked_online);
        $productsByName = $onlineProducts->groupBy('name');

        $productModes = [];
        foreach ($productsByName as $productName => $group) {
            $modes = [];
            foreach ($group as $p) {
                if ($p->adomicile) {
                    $modes[] = ['mode' => 'À Domicile', 'slug' => 'domicile', 'product' => $p];
                }

                if (!empty($p->en_entreprise)) {
                    $modes[] = ['mode' => 'En entreprise', 'slug' => 'entreprise', 'product' => $p];
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
        <div class="details-container mx-auto">

            <h1 class="details-title">{{ __('Demander un Rendez-vous') }}</h1>
            <div class="subtle">{{ __('Choisissez votre créneau, puis renseignez vos informations.') }}</div>

            {{-- Wizard header --}}
            <div class="wizard-top">
                <div class="step-pill active" id="pill-step-1">
                    <span class="dot" id="dot-step-1">1</span>
                    <span>{{ __('Créneau') }}</span>
                </div>
                <div class="step-pill" id="pill-step-2">
                    <span class="dot" id="dot-step-2">2</span>
                    <span>{{ __('Vos infos') }}</span>
                </div>
            </div>

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

            <form id="patientBookingForm" action="{{ route('appointments.storePatient') }}" method="POST">
                @csrf

                {{-- Hidden therapist --}}
                <input type="hidden" name="therapist_id" value="{{ $therapist->id }}">

                {{-- Hidden "final" product_id and mode --}}
                <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id') }}">
                <input type="hidden" name="type" id="selected_mode_slug" value="{{ old('type') }}">
                <input type="hidden" id="appointment_time" name="appointment_time" value="{{ old('appointment_time') }}">

                {{-- =========================
                   STEP 1: SLOT SELECTION
                ========================== --}}
                <div class="wizard-section active" id="step-1">

                    <div class="card-soft">
                        <div class="details-box mb-2">
                            <label class="details-label">{{ __('Thérapeute') }}</label>
                            <div class="hint">
                                <b style="color:#0f172a;">{{ $therapist->company_name ?? $therapist->name }}</b>
                            </div>
                        </div>

                        <div class="divider"></div>

                        {{-- Prestation --}}
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
                            <p class="hint">{{ __('Aucune prestation disponible pour la réservation en ligne.') }}</p>
                        @endif

                        {{-- Mode de consultation --}}
                        <div class="details-box" id="consultation-mode-section" style="display:none;">
                            <label class="details-label" for="consultation_mode">{{ __('Mode de Consultation') }}</label>
                            <select id="consultation_mode" class="form-control" required>
                                <option value="" disabled selected>{{ __('Sélectionner un mode de consultation') }}</option>
                            </select>
                            <p class="text-red-500 mt-1 d-none" id="mode-error"></p>
                        </div>

                        {{-- Cabinet: choose a practice location --}}
                        <div class="details-box" id="cabinet-location-section" style="display:none;">
                            <label class="details-label" for="practice_location_id">{{ __('Cabinet') }}</label>
                            <select id="practice_location_id" class="form-control">
                                <option value="" disabled selected>{{ __('Choisir un lieu') }}</option>
                                @foreach($practiceLocations as $loc)
                                    <option value="{{ $loc->id }}"
                                            data-address="{{ $loc->full_address ?? ($loc->address_line1 . ', ' . $loc->postal_code . ' ' . $loc->city) }}"
                                            {{ old('practice_location_id') == $loc->id ? 'selected':'' }}>
                                        {{ $loc->label }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- IMPORTANT: keep the field name for backend --}}
                            <input type="hidden" name="practice_location_id" id="practice_location_id_hidden" value="{{ old('practice_location_id') }}">

                            <p class="text-red-500 mt-1 d-none" id="location-error"></p>
                        </div>

                        {{-- Cabinet address preview --}}
                        <div class="details-box" id="therapist-address-section" style="display:none;">
                            <label class="details-label">{{ __('Adresse du Cabinet') }}</label>
                            <p id="therapist-address" class="hint mb-0"></p>
                        </div>

                        <div class="divider"></div>

                        {{-- Date --}}
                        <div class="details-box">
                            <label class="details-label" for="appointment_date">{{ __('Date') }}</label>
                            <input type="text" id="appointment_date" name="appointment_date" class="form-control"
                                   value="{{ old('appointment_date') }}"
                                   required
                                   placeholder="{{ __('Sélectionner une date') }}">
                            <p id="date-loading-message" class="hint mt-2" style="display:none;">
                                {{ __('Chargement des jours disponibles...') }}
                            </p>
                            @error('appointment_date')<p class="text-red-500">{{ $message }}</p>@enderror
                        </div>

                        {{-- Time slots --}}
                        <div class="details-box">
                            <label class="details-label">{{ __('Horaire') }}</label>
                            <div id="time-slots-container" class="mt-2">
                                <span class="hint">{{ __('Veuillez d’abord sélectionner une prestation, un mode et une date.') }}</span>
                            </div>
                            <p id="no-slots-message" class="text-red-500 mt-2" style="display:none;"></p>
                            @error('appointment_time')<p class="text-red-500">{{ $message }}</p>@enderror

                            {{-- small summary --}}
                            <div class="summary-row" id="booking-summary" style="display:none;">
                                <div class="item">{{ __('Prestation') }} : <b id="sum-presta">—</b></div>
                                <div class="item">{{ __('Mode') }} : <b id="sum-mode">—</b></div>
                                <div class="item">{{ __('Date') }} : <b id="sum-date">—</b></div>
                                <div class="item">{{ __('Heure') }} : <b id="sum-time">—</b></div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center mt-4 gap-2 btn-row" style="align-items:center;">
                        <a href="{{ url()->previous() }}" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('Retour') }}
                        </a>

                        <button type="button" id="toStep2Btn" class="btn-primary" disabled>
                            {{ __('Continuer') }} <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>

                    <div class="hint text-center mt-2" id="step1-hint">
                        {{ __('Sélectionnez un créneau pour continuer.') }}
                    </div>
                </div>

                {{-- =========================
                   STEP 2: PATIENT INFO
                ========================== --}}
                <div class="wizard-section" id="step-2">
                    <div class="card-soft">

                        {{-- Domicile: patient's address (required server-side when domicile) --}}
                        <div class="details-box" id="client-address-section" style="display:none;">
                            <label class="details-label" for="address">{{ __('Votre adresse') }}</label>
                            <input type="text" id="address" name="address" class="form-control"
                                   value="{{ old('address', $clientProfile->address ?? '') }}">
                            @error('address')<p class="text-red-500">{{ $message }}</p>@enderror
                        </div>

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

                        <div class="details-box">
                            <label class="details-label" for="notes">{{ __('Notes') }}</label>
                            <textarea id="notes" name="notes" class="form-control" placeholder="{{ __('Ex: infos importantes, contexte, objectif…') }}">{{ old('notes') }}</textarea>
                            @error('notes')<p class="text-red-500">{{ $message }}</p>@enderror
                        </div>

                        {{-- status (kept as before if you need it in storePatient) --}}
                        {{-- if not used, you can remove --}}
                        <input type="hidden" name="status" value="pending">

                        <div class="summary-row" id="final-summary">
                            <div class="item">{{ __('Prestation') }} : <b id="f-presta">—</b></div>
                            <div class="item">{{ __('Mode') }} : <b id="f-mode">—</b></div>
                            <div class="item">{{ __('Date') }} : <b id="f-date">—</b></div>
                            <div class="item">{{ __('Heure') }} : <b id="f-time">—</b></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center mt-4 gap-2 btn-row" style="align-items:center;">
                        <button type="button" id="backToStep1Btn" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('Modifier le créneau') }}
                        </button>

                        <button type="submit" class="btn-primary" id="submitBtn">
                            <i class="fas fa-check"></i> {{ __('Demander le Rendez-vous') }}
                        </button>
                    </div>
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

        let allowedDates = [];
        let currentSlotsRequestId = 0;

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

            // -----------------------------
            // Wizard helpers
            // -----------------------------
            function setWizardStep(step) {
                const isStep1 = (step === 1);

                $('#step-1').toggleClass('active', isStep1);
                $('#step-2').toggleClass('active', !isStep1);

                // Pills
                $('#pill-step-1').toggleClass('active', isStep1);
                $('#pill-step-2').toggleClass('active', !isStep1);

                // done indicator for step 1 if slot chosen
                const slotChosen = !!$('#appointment_time').val();
                $('#pill-step-1').toggleClass('done', slotChosen);
                $('#dot-step-1').html(slotChosen ? '<i class="fas fa-check"></i>' : '1');

                if (!isStep1) {
                    // copy summary
                    $('#f-presta').text($('#sum-presta').text() || '—');
                    $('#f-mode').text($('#sum-mode').text() || '—');
                    $('#f-date').text($('#sum-date').text() || '—');
                    $('#f-time').text($('#sum-time').text() || '—');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }

            function updateSummary() {
                const presta = $('#product_name').val() || '—';
                const mode   = $('#selected_mode_slug').val() || '—';
                const date   = $('#appointment_date').val() || '—';
                const time   = $('#appointment_time').val() || '—';

                const modeLabel = (function() {
                    if (mode === 'cabinet') return 'Cabinet';
                    if (mode === 'visio') return 'Visio';
                    if (mode === 'domicile') return 'Domicile';
                    if (mode === 'entreprise') return 'Entreprise';
                    return mode;
                })();

                $('#sum-presta').text(presta);
                $('#sum-mode').text(modeLabel);
                $('#sum-date').text(date);
                $('#sum-time').text(time);

                const show = (presta !== '—' || mode !== '—' || date !== '—' || time !== '—');
                $('#booking-summary').toggle(show);

                // enable continue only if slot chosen
                const slotChosen = !!$('#appointment_time').val();
                $('#toStep2Btn').prop('disabled', !slotChosen);
                $('#step1-hint').text(slotChosen
                    ? '{{ __("Parfait. Vous pouvez continuer.") }}'
                    : '{{ __("Sélectionnez un créneau pour continuer.") }}'
                );

                // domicile address section only step2
                const slug = $('#selected_mode_slug').val();
                $('#client-address-section').toggle(slug === 'domicile' || slug === 'entreprise');

                // keep hidden location name used by backend
                const locId = $('#practice_location_id').val();
                $('#practice_location_id_hidden').val(locId || '');
            }

            function resetTimeSelect() {
                $('#appointment_time').val('');
                $('#time-slots-container').html(
                    '<span class="hint">{{ __("Veuillez d’abord sélectionner une prestation, un mode et une date.") }}</span>'
                );
                $('#no-slots-message').hide().text('');
                $('.time-slot-btn').removeClass('active');
                updateSummary();
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

            // -----------------------------
            // Ajax: dates / slots
            // -----------------------------
            function fetchDates(productId, modeSlug, locationId = null) {
                $('#date-loading-message')
                    .text('{{ __("Chargement des jours disponibles...") }}')
                    .show();

                $.ajax({
                    url: '{{ route("appointments.available-dates-concrete-patient") }}',
                    method: 'POST',
                    data: {
                        product_id:   productId,
                        therapist_id: therapistId,
                        mode:         modeSlug || undefined,
                        location_id:  (modeSlug === 'cabinet' ? (locationId || undefined) : undefined),
                        days:         60,
                        _token:       '{{ csrf_token() }}'
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
                        updateSummary();
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
                    '<span class="hint">{{ __("Chargement des créneaux disponibles...") }}</span>'
                );
                $('#no-slots-message').hide().text('');
                updateSummary();

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

                            updateSummary();
                        } else {
                            $('#time-slots-container').html(
                                '<span class="hint">{{ __("Aucun créneau disponible pour cette date.") }}</span>'
                            );
                            $('#no-slots-message')
                                .text('{{ __("Aucun créneau n’est disponible pour ce jour. Merci de choisir une autre date.") }}')
                                .show();

                            updateSummary();
                        }
                    },
                    error: function (xhr) {
                        if (thisRequestId !== currentSlotsRequestId) return;
                        console.error('Error fetching available slots:', xhr.responseText);
                        $('#time-slots-container').html(
                            '<span class="text-red-500">{{ __("Une erreur est survenue lors de la récupération des créneaux disponibles.") }}</span>'
                        );
                        updateSummary();
                    }
                });
            }

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

            // -----------------------------
            // Events
            // -----------------------------
            // Slot click
            $(document).on('click', '.time-slot-btn', function () {
                $('.time-slot-btn').removeClass('active');
                $(this).addClass('active');
                const time = $(this).data('time');
                $('#appointment_time').val(time);
                $('#no-slots-message').hide().text('');
                updateSummary();
            });

            // Product change
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
                $('#mode-error').addClass('d-none').text('');
                $('#location-error').addClass('d-none').text('');
                $('#practice_location_id').val('');
                $('#practice_location_id_hidden').val('');

                fp.set('enable', []);
                fp.clear();
                resetTimeSelect();

                updateSummary();
                refreshDates();
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
                } else {
                    $('#cabinet-location-section').hide();
                    $('#therapist-address-section').hide();
                    $('#practice_location_id').val('');
                    $('#practice_location_id_hidden').val('');
                }

                refreshDates();
                updateSummary();
            });

            // Cabinet change
            $('#practice_location_id').on('change', function () {
                const address = $(this).find(':selected').data('address') || '';
                $('#therapist-address').text(address || '{{ __("Adresse non disponible.") }}');
                $('#therapist-address-section').show();
                $('#location-error').addClass('d-none').text('');

                $('#practice_location_id_hidden').val($(this).val() || '');

                refreshDates();
                updateSummary();
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
                updateSummary();
            });

            // Wizard nav
            $('#toStep2Btn').on('click', function () {
                // Ensure we have a slot
                if (!$('#appointment_time').val()) return;

                // Ensure cabinet has location (if needed)
                if (!requireCabinetLocationIfNeeded()) return;

                // Ensure date exists
                const date = $('#appointment_date').val();
                if (!date) {
                    alert('{{ __("Veuillez sélectionner une date.") }}');
                    return;
                }

                updateSummary();
                setWizardStep(2);
            });

            $('#backToStep1Btn').on('click', function () {
                setWizardStep(1);
            });

            // Final validation before submit
            $('#patientBookingForm').on('submit', function (e) {
                // Must have slot
                if (!$('#appointment_time').val()) {
                    e.preventDefault();
                    setWizardStep(1);
                    alert('{{ __("Veuillez sélectionner un créneau.") }}');
                    return false;
                }
                if (!requireCabinetLocationIfNeeded()) {
                    e.preventDefault();
                    setWizardStep(1);
                    return false;
                }
                return true;
            });

            // Restore old input (if validation errors)
            @if(old('product_name'))
                $('#product_name').val(@json(old('product_name'))).trigger('change');

                @if(old('product_id'))
                    setTimeout(function () {
                        const productId = @json(old('product_id'));

                        $('#consultation_mode option').each(function(){
                            if ($(this).val() == productId) $(this).prop('selected', true);
                        });
                        $('#consultation_mode').trigger('change');

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
                                updateSummary();

                                // If user already had a time selected, go to step 2 directly (nice UX)
                                setTimeout(function(){
                                    if ($('#appointment_time').val()) {
                                        setWizardStep(2);
                                    }
                                }, 250);
                            }, 650);
                        @endif
                    }, 350);
                @endif
            @endif

            // Initial summary state
            updateSummary();
        });
    </script>

</x-app-layout>

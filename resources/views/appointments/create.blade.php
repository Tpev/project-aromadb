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
    @endphp

    <div class="container mt-5">
        <div class="details-container">

            <h1 class="details-title">Créer un Rendez-vous</h1>

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

					<select name="client_profile_id" class="form-control" required>
						<option value="" disabled {{ $selectedClientId ? '' : 'selected' }}>
							-- Sélectionner un client --
						</option>

						@foreach($clientProfiles as $client)
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
                        @foreach($productModes as $productName => $modes)
                            <option value="{{ $productName }}">{{ $productName }}</option>
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

                {{-- Hidden final values --}}
                <input type="hidden" name="product_id" id="product_id">
                <input type="hidden" name="type" id="selected_mode_slug"> {{-- cabinet/visio/domicile --}}

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
                    <label class="details-label">Adresse du Domicile</label>
                    <input type="text" id="address" name="address" class="form-control">
                </div>

                {{-- Date --}}
                <div class="details-box">
                    <label class="details-label">Date</label>
                    <input type="text" id="appointment_date" name="appointment_date" class="form-control" placeholder="Sélectionner une date" required>
                    <p id="date-loading-message" class="text-muted mt-1" style="display:none;">Chargement des dates…</p>
                </div>

                {{-- Time --}}
                <div class="details-box">
                    <label class="details-label">Horaire</label>
                    <input type="hidden" id="appointment_time" name="appointment_time">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    const PRODUCT_MODES = @json($productModes);
    let allowedDates = [];
    let currentSlotsRequestId = 0;

    $(function() {
        const therapistId = '{{ $therapist->id }}';

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
            $('#time-slots-container').html('<span class="text-muted">Sélectionnez prestation, mode et date.</span>');
            $('#no-slots-message').hide().text('');
        }

        /**
         * THERAPIST endpoints (no minimum notice / allow last-minute)
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
                fp.set('enable', allowedDates);
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

            $.post('{{ route("appointments.available-slots-therapist") }}', {
                date,
                product_id: productId,
                mode: modeSlug,
                location_id: locationId,
                _token: '{{ csrf_token() }}'
            })
            .done(res => {
                if (reqId !== currentSlotsRequestId) return;

                if (!res.slots || !res.slots.length) {
                    $('#time-slots-container').html('<span class="text-muted">Aucun créneau disponible.</span>');
                    $('#no-slots-message').text('Pas de créneaux pour ce jour').show();
                    return;
                }

                let html = '<div class="time-slots-grid">';
                res.slots.forEach(s => {
                    html += `<button type="button" class="time-slot-btn" data-time="${s.start}">${s.start}</button>`;
                });
                html += '</div>';
                $('#time-slots-container').html(html);
            })
            .fail(() => {
                $('#time-slots-container').html('<span class="text-red-500">Erreur lors de la récupération.</span>');
            });
        }

        /**
         * Recompute allowed dates anytime product / mode / cabinet changes.
         * If we don't have enough info yet, just clear the calendar & slots.
         */
        function refreshDates() {
            const productId = $('#product_id').val();
            const slug      = $('#selected_mode_slug').val();
            const loc       = slug === 'cabinet' ? $('#practice_location_id').val() : null;

            if (!productId || !slug || (slug === 'cabinet' && !loc)) {
                allowedDates = [];
                fp.set('enable', []);
                fp.clear();
                resetSlotsUI();
                return;
            }

            fetchDates(productId, slug, loc);
        }

        // PRODUCT --> update mode dropdown + reset downstream state + refreshDates
        $('#product_name').change(function() {
            const name  = $(this).val();
            const modes = PRODUCT_MODES[name] || [];

            const $mode = $('#consultation_mode').empty();
            $mode.append('<option value="" disabled selected>Sélectionner le mode</option>');
            modes.forEach(m => {
                $mode.append(`<option value="${m.product.id}" data-slug="${m.slug}">${m.mode}</option>`);
            });

            $('#consultation-mode-section').show();

            // Reset hidden & dependent fields
            $('#product_id').val('');
            $('#selected_mode_slug').val('');

            $('#cabinet-location-section').hide();
            $('#practice_location_id').val('');
            $('#therapist-address-section').hide();
            $('#therapist-address').text('');
            $('#client-address-section').hide();

            allowedDates = [];
            fp.set('enable', []);
            fp.clear();
            resetSlotsUI();

            refreshDates();
        });

        // MODE --> update UI + refreshDates
        $('#consultation_mode').change(function() {
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

        // CABINET --> update address + refreshDates
        $('#practice_location_id').change(function () {
            const $opt = $(this).find(':selected');
            $('#therapist-address-section').show();
            $('#therapist-address').text($opt.data('address') || '—');

            refreshDates();
        });

        // DATE --> fetch slots from current state
        $('#appointment_date').change(function() {
            const date      = $(this).val();
            const productId = $('#product_id').val();
            const slug      = $('#selected_mode_slug').val();
            const loc       = slug === 'cabinet' ? $('#practice_location_id').val() : null;

            if (date && productId && slug) {
                fetchSlots(date, productId, slug, loc);
            } else {
                resetSlotsUI();
            }
        });

        // SLOT click
        $(document).on('click', '.time-slot-btn', function() {
            $('.time-slot-btn').removeClass('active');
            $(this).addClass('active');
            $('#appointment_time').val($(this).data('time'));
            $('#no-slots-message').hide();
        });
    });
    </script>

</x-app-layout>

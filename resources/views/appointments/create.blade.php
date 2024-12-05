{{-- resources/views/appointments/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer un Rendez-vous') }}
        </h2>
    </x-slot>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Bootstrap CSS for better styling (optional but recommended) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Flatpickr CSS -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 800px;
        }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .details-title {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .details-box {
            margin-bottom: 20px;
        }

        .details-label {
            font-weight: bold;
            color: #647a0b;
            display: block;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #854f38;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: #647a0b;
            outline: none;
            box-shadow: 0 0 5px rgba(100, 122, 11, 0.5);
        }

        .btn-primary {
            background-color: #647a0b;
            border: none;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .btn-secondary {
            background-color: #647a0b;
            border: none;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 1rem;
        }

        .btn-secondary:hover {
            background-color: #854f38;
        }

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .loading-spinner {
            display: none;
            margin-left: 10px;
        }

        .form-section {
            text-align: left;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .details-container {
                padding: 20px;
            }

            .details-title {
                font-size: 1.5rem;
            }
        }

        /* Flatpickr Theme Customization */
        .flatpickr-calendar {
            border: 1px solid #647a0b;
        }

        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange {
            background: #647a0b;
            color: white;
        }

        .flatpickr-day:hover {
            background: #854f38;
            color: white;
        }

        .flatpickr-day.disabled {
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }

        .flatpickr-day.disabled:hover {
            background: #e9ecef;
            color: #6c757d;
        }
    </style>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouveau Rendez-vous') }}</h1>

            <!-- Success Message -->
            @if(session('success'))
                <div class="alert alert-success text-center">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Error Message -->
            @if ($errors->any())
                <div class="alert alert-danger text-center">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Appointment Creation Form -->
            <form action="{{ route('appointments.store') }}" method="POST">
                @csrf

                <!-- Client Profile -->
                <div class="details-box form-section">
                    <label class="details-label" for="client_profile_id">{{ __('Sélectionner le Client') }}</label>
                    <select id="client_profile_id" name="client_profile_id" class="form-control" required>
                        <option value="" disabled selected>{{ __('Sélectionner un client') }}</option>
                        @foreach($clientProfiles as $clientProfile)
                            <option value="{{ $clientProfile->id }}" {{ old('client_profile_id') == $clientProfile->id ? 'selected' : '' }}>
                                {{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
                            </option>
                        @endforeach
                        <option value="new" {{ old('client_profile_id') == 'new' ? 'selected' : '' }}>{{ __('Créer un nouveau client') }}</option>
                    </select>
                    @error('client_profile_id')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Client Fields -->
                <div id="new-client-fields" style="display: none;">
                    <!-- First Name -->
                    <div class="details-box">
                        <label class="details-label" for="first_name">{{ __('Prénom') }}</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" value="{{ old('first_name') }}">
                        @error('first_name')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div class="details-box">
                        <label class="details-label" for="last_name">{{ __('Nom') }}</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name') }}">
                        @error('last_name')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="details-box">
                        <label class="details-label" for="email">{{ __('Email') }}</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}">
                        @error('email')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="details-box">
                        <label class="details-label" for="phone">{{ __('Téléphone') }}</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}">
                        @error('phone')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Birthdate -->
                    <div class="details-box">
                        <label class="details-label" for="birthdate">{{ __('Date de naissance') }}</label>
                        <input type="date" id="birthdate" name="birthdate" class="form-control" value="{{ old('birthdate') }}">
                        @error('birthdate')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div class="details-box">
                        <label class="details-label" for="address">{{ __('Adresse') }}</label>
                        <input type="text" id="address" name="address" class="form-control" value="{{ old('address') }}">
                        @error('address')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Prepare product modes -->
                @php
                    // Group products by name
                    $productsByName = $products->groupBy('name');

                    // Prepare an array to map product names to their available consultation modes and products
                    $productModes = [];
                    foreach($productsByName as $productName => $productsGroup) {
                        $modes = [];
                        foreach($productsGroup as $product) {
                            if($product->adomicile) {
                                $modes[] = [
                                    'mode' => 'À Domicile',
                                    'product' => $product
                                ];
                            }
                            if($product->dans_le_cabinet) {
                                $modes[] = [
                                    'mode' => 'Dans le Cabinet',
                                    'product' => $product
                                ];
                            }
                            if($product->visio || $product->en_visio) {
                                $modes[] = [
                                    'mode' => 'En Visio',
                                    'product' => $product
                                ];
                            }
                        }
                        if (!empty($modes)) {
                            $productModes[$productName] = $modes;
                        }
                    }
                @endphp

                <!-- Prestation (Unique products by name) -->
                @if(count($productModes) > 0)
                    <div class="details-box form-section">
                        <label class="details-label" for="product_name">{{ __('Prestation') }}</label>
                        <select id="product_name" name="product_name" class="form-control" required>
                            <option value="" disabled selected>{{ __('Sélectionner une prestation') }}</option>
                            @foreach($productModes as $productName => $modes)
                                @php
                                    $product = $modes[0]['product']; // Get the first product for price display
                                    $totalPrice = $product->price + ($product->price * $product->tax_rate / 100);
                                    $formattedPrice = rtrim(rtrim(number_format($totalPrice, 2, '.', ''), '0'), '.');
                                @endphp
                                <option value="{{ $productName }}"
                                        {{ old('product_name') == $productName ? 'selected' : '' }}>
                                    {{ $productName }} - {{ $formattedPrice }}€
                                </option>
                            @endforeach
                        </select>
                        @error('product_name')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <p>{{ __('Aucune prestation disponible.') }}</p>
                @endif

                <!-- Mode de Consultation -->
                <div class="details-box form-section" id="consultation-mode-section" style="display: none;">
                    <label class="details-label" for="consultation_mode">{{ __('Mode de Consultation') }}</label>
                    <select id="consultation_mode" name="consultation_mode" class="form-control" required>
                        <option value="" disabled selected>{{ __('Sélectionner un mode de consultation') }}</option>
                    </select>
                    @error('consultation_mode')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Hidden input to store the selected product ID -->
                <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id') }}">

                <!-- Therapist Address (only show if the product is 'Dans le Cabinet') -->
                <div class="details-box form-section" id="therapist-address-section" style="display: none;">
                    <label class="details-label">{{ __('Adresse du Cabinet') }}</label>
                    <p class="form-control-static">{{ $therapist->company_address ?? __('Adresse non disponible.') }}</p>
                </div>

                <!-- Client Address (only show if the product is 'À Domicile') -->
                <div class="details-box form-section" id="client-address-section" style="display: none;">
                    <label class="details-label" for="client_address">{{ __('Adresse du Client') }}</label>
                    <input type="text" id="client_address" name="client_address" class="form-control" value="{{ old('client_address') }}">
                    @error('client_address')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Appointment Date -->
                <div class="details-box form-section">
                    <label class="details-label" for="appointment_date">{{ __('Date du Rendez-vous') }}</label>
                    <input type="text" id="appointment_date" name="appointment_date" class="form-control" value="{{ old('appointment_date') }}" required
                        placeholder="Sélectionner une date">
                    @error('appointment_date')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Available Time Slots -->
                <div class="details-box form-section">
                    <label class="details-label" for="appointment_time">{{ __('Heure du Rendez-vous') }}</label>
                    <div class="d-flex align-items-center">
                        <select id="appointment_time" name="appointment_time" class="form-control" required disabled>
                            <option value="" disabled selected>{{ __('Sélectionner une heure') }}</option>
                        </select>
                        <div class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </div>
                    @error('appointment_time')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="details-box form-section">
                    <label class="details-label" for="status">{{ __('Statut') }}</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Programmé" {{ old('status') == 'Programmé' ? 'selected' : '' }}>{{ __('Programmé') }}</option>
                        <option value="Complété" {{ old('status') == 'Complété' ? 'selected' : '' }}>{{ __('Complété') }}</option>
                        <option value="Annulé" {{ old('status') == 'Annulé' ? 'selected' : '' }}>{{ __('Annulé') }}</option>
                    </select>
                    @error('status')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="details-box form-section">
                    <label class="details-label" for="notes">{{ __('Notes') }}</label>
                    <textarea id="notes" name="notes" class="form-control">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit and Back Buttons -->
                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i> {{ __('Créer le Rendez-vous') }}
                    </button>
                    <a href="{{ route('appointments.index') }}" class="btn-secondary ml-3">
                        <i class="fas fa-arrow-left mr-2"></i> {{ __('Retour à la liste') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- jQuery (required for AJAX requests) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Flatpickr French Locale -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>

    <!-- Pass the product modes to JavaScript -->
    <script>
        var productModes = @json($productModes);
    </script>

    <!-- Custom Scripts -->
    <script>
    $(document).ready(function() {
        // Show/hide the new client fields based on selection
        $('#client_profile_id').change(function() {
            let selectedValue = $(this).val();
            if (selectedValue === 'new') {
                $('#new-client-fields').show();
            } else {
                $('#new-client-fields').hide();
            }
        });

        // If old input is 'new', make sure the new-client-fields are visible on page load
        @if(old('client_profile_id') == 'new')
            $('#new-client-fields').show();
        @endif

        // Initialize Flatpickr
        let availableDays = []; // Array to store available days (0 = Monday, 6 = Sunday)

        const fp = flatpickr("#appointment_date", {
            dateFormat: "Y-m-d", // Submission format
            altInput: true, // Enable alternative input for display
            altFormat: "d-m-Y", // Display format (dd-mm-yyyy)
            minDate: "today",
            locale: "fr",
            disable: [], // Initially, no dates are disabled
            onChange: function(selectedDates, dateStr, instance) {
                // Trigger fetching available slots
                var productId = $('#product_id').val();
                if (dateStr && productId) {
                    fetchAvailableSlots(dateStr, productId);
                } else {
                    $('#appointment_time').html('<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>');
                    $('#appointment_time').prop('disabled', true);
                }
            }
        });

        // Function to fetch available days based on selected product
        function loadAvailableDays(productId) {
            $.ajax({
                url: '{{ route("appointments.available-dates") }}',
                method: 'POST',
                data: {
                    product_id: productId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.available_days && response.available_days.length > 0) {
                        availableDays = response.available_days;
                        console.log('Available Days:', availableDays); // For debugging

                        // Update Flatpickr to disable days not available
                        fp.set('disable', [
                            function(date) {
                                // 0 = Monday, 6 = Sunday
                                let dayOfWeek = (date.getDay() + 6) % 7; // Convert JS day to your format
                                return !availableDays.includes(dayOfWeek);
                            }
                        ]);
                    } else {
                        // If no dates are available, disable all dates
                        fp.set('disable', [true]);
                        alert('{{ __("Aucune date disponible pour cette prestation.") }}');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching available days:', error, xhr.responseText);
                    alert('{{ __("Une erreur est survenue lors de la récupération des jours disponibles. Veuillez réessayer.") }}');
                }
            });
        }

        // Function to fetch available time slots based on selected date and product
        function fetchAvailableSlots(date, productId) {
            $('#appointment_time').prop('disabled', true); // Disable time dropdown while fetching
            $('.loading-spinner').show(); // Show loading spinner

            $.ajax({
                url: '{{ route("appointments.available-slots") }}',
                method: 'POST',
                data: {
                    date: date,
                    product_id: productId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('.loading-spinner').hide(); // Hide spinner after success
                    if (response.slots && response.slots.length > 0) {
                        let options = '<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>';
                        response.slots.forEach(function(slot) {
                            options += `<option value="${slot.start}">${slot.start} - ${slot.end}</option>`;
                        });
                        $('#appointment_time').html(options); // Populate time dropdown
                        $('#appointment_time').prop('disabled', false); // Enable time dropdown
                    } else {
                        $('#appointment_time').html('<option value="" disabled selected>{{ __("Aucun créneau disponible pour cette date.") }}</option>');
                        $('#appointment_time').prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    $('.loading-spinner').hide(); // Hide spinner on error
                    console.error('Error fetching available slots:', error, xhr.responseText);
                    alert('{{ __("Une erreur est survenue lors de la récupération des créneaux disponibles. Veuillez réessayer.") }}');
                }
            });
        }

        // When product_name changes
        $('#product_name').on('change', function() {
            var productName = $(this).val();
            if(productName) {
                var modes = productModes[productName];
                if(modes && modes.length > 0) {
                    // Clear and populate the consultation_mode dropdown
                    var consultationModeSelect = $('#consultation_mode');
                    consultationModeSelect.empty();
                    consultationModeSelect.append('<option value="" disabled selected>{{ __("Sélectionner un mode de consultation") }}</option>');
                    modes.forEach(function(modeData) {
                        var mode = modeData.mode;
                        var productId = modeData.product.id;
                        consultationModeSelect.append('<option value="'+productId+'">'+mode+'</option>');
                    });
                    $('#consultation-mode-section').show();
                    // Reset product_id hidden input
                    $('#product_id').val('');
                    // Hide address sections
                    $('#client-address-section').hide();
                    $('#therapist-address-section').hide();
                } else {
                    $('#consultation-mode-section').hide();
                    $('#product_id').val('');
                }
            } else {
                $('#consultation-mode-section').hide();
                $('#product_id').val('');
            }
            // Reset other fields if necessary
            fp.clear();
            fp.setDate(null);
            $('#appointment_time').html('<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>');
            $('#appointment_time').prop('disabled', true);
        });

        // When consultation_mode changes
        $('#consultation_mode').on('change', function() {
            var productId = $(this).val();
            // Set the hidden product_id input
            $('#product_id').val(productId);
            if (productId) {
                loadAvailableDays(productId);
                // Reset date and time fields
                fp.clear();
                fp.setDate(null);
                $('#appointment_time').html('<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>');
                $('#appointment_time').prop('disabled', true);
            }
            // Show or hide address sections based on the selected mode
            checkProductMode();
        });

        // Function to check the selected mode and show/hide address sections
        function checkProductMode() {
            var consultationModeSelect = document.getElementById('consultation_mode');
            var selectedOption = consultationModeSelect.options[consultationModeSelect.selectedIndex];
            var addressSection = document.getElementById('client-address-section');
            var therapistAddressSection = document.getElementById('therapist-address-section');
            if (selectedOption) {
                var mode = selectedOption.text;
                // Show client address section if 'À Domicile'
                if (mode === 'À Domicile') {
                    addressSection.style.display = 'block';
                    therapistAddressSection.style.display = 'none';
                } else if (mode === 'Dans le Cabinet') {
                    therapistAddressSection.style.display = 'block';
                    addressSection.style.display = 'none';
                } else {
                    // Hide both address sections for 'En Visio' and other modes
                    addressSection.style.display = 'none';
                    therapistAddressSection.style.display = 'none';
                }
            } else {
                // Hide both sections if no mode is selected
                addressSection.style.display = 'none';
                therapistAddressSection.style.display = 'none';
            }
        }

        // If old input exists (e.g., after validation error), populate the consultation_mode dropdown
        @if(old('product_name'))
            var oldProductName = "{{ old('product_name') }}";
            var modes = productModes[oldProductName];
            if(modes && modes.length > 0) {
                var consultationModeSelect = $('#consultation_mode');
                consultationModeSelect.empty();
                consultationModeSelect.append('<option value="" disabled selected>{{ __("Sélectionner un mode de consultation") }}</option>');
                modes.forEach(function(modeData) {
                    var mode = modeData.mode;
                    var productId = modeData.product.id;
                    var selected = '';
                    if(productId == "{{ old('product_id') }}") {
                        selected = 'selected';
                    }
                    consultationModeSelect.append('<option value="'+productId+'" '+selected+'>'+mode+'</option>');
                });
                $('#consultation-mode-section').show();
                // Set product_id hidden input
                $('#product_id').val("{{ old('product_id') }}");
                // Call checkProductMode to show/hide address sections
                checkProductMode();
            }
        @endif

        // If old appointment_date exists, set it
        @if(old('appointment_date') && old('product_id'))
            setTimeout(function() {
                fp.setDate('{{ old('appointment_date') }}', true);
                fetchAvailableSlots('{{ old('appointment_date') }}', '{{ old('product_id') }}');
            }, 500);
        @endif
    });
    </script>

    <!-- Bootstrap JS (optional but recommended) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</x-app-layout>

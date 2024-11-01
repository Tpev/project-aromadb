{{-- resources/views/appointments/create_patient.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Demander un Rendez-vous') }}
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
            border-color: #647a0b; /* Highlight border on focus */
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

            .d-flex.justify-content-center.mt-4 {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
                margin-bottom: 10px;
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
            <h1 class="details-title">{{ __('Demander un Rendez-vous') }}</h1>

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

            <!-- Appointment Request Form -->
            <form action="{{ route('appointments.storePatient') }}" method="POST">
                @csrf

                <!-- Therapist Information (Hidden) -->
                <input type="hidden" name="therapist_id" value="{{ $therapist->id }}">

                <!-- Display Selected Therapist -->
                <div class="details-box form-section">
                    <label class="details-label">{{ __('Thérapeute Sélectionné') }}</label>
                    <p>{{ $therapist->company_name }}</p>
                </div>

                <!-- Prestation (Only prestations that can be booked online) -->
                @if($products->count() > 0)
                    <div class="details-box form-section">
                        <label class="details-label" for="product_id">{{ __('Prestation') }}</label>
                        <select id="product_id" name="product_id" class="form-control" required>
                            <option value="" disabled selected>{{ __('Sélectionner une prestation') }}</option>
                            @foreach($products as $product)
                                @if($product->can_be_booked_online)
                                    @php
                                        $totalPrice = $product->price + ($product->price * $product->tax_rate / 100);
                                    @endphp
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }} data-duration="{{ $product->duration }}">
                                        {{ $product->name }} - {{ rtrim(rtrim(number_format($totalPrice, 2, '.', ''), '0'), '.') }}€
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('product_id')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Patient First Name -->
                <div class="details-box form-section">
                    <label class="details-label" for="first_name">{{ __('Votre Prénom') }}</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                    @error('first_name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Patient Last Name -->
                <div class="details-box form-section">
                    <label class="details-label" for="last_name">{{ __('Votre Nom') }}</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                    @error('last_name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Patient Email -->
                <div class="details-box form-section">
                    <label class="details-label" for="email">{{ __('Votre Email') }}</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    @error('email')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Patient Phone -->
                <div class="details-box form-section">
                    <label class="details-label" for="phone">{{ __('Votre Numéro de Téléphone') }}</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" required>
                    @error('phone')
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
                        <select id="appointment_time" name="appointment_time" class="form-control" required>
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
                        <i class="fas fa-plus mr-2"></i> {{ __('Demander le Rendez-vous') }}
                    </button>
                    <a href="{{ url()->previous() }}" class="btn-secondary ml-3">
                        <i class="fas fa-arrow-left mr-2"></i> {{ __('Retour') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Flatpickr French Locale -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>

    <!-- jQuery (required for AJAX requests) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Font Awesome JS (for icons) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>

    <!-- Bootstrap JS (optional but recommended) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts for Slot Fetching -->
    <script>
    $(document).ready(function() {
        // Initialize Flatpickr with disableMobile set to true
        let availableDays = []; // Array to store available days (0 = Monday, 6 = Sunday)

        const fp = flatpickr("#appointment_date", {
            dateFormat: "Y-m-d", // Format to submit to server
            altInput: true, // Enable alternative input for display
            altFormat: "d-m-Y", // Display format (dd-mm-yyyy)
            minDate: "today",
            locale: "fr",
            disableMobile: true, // Force Flatpickr to use its own calendar on mobile devices
            disable: [], // Initially, no dates are disabled
            onChange: function(selectedDates, dateStr, instance) {
                // Trigger fetching available slots
                let therapistId = $('input[name="therapist_id"]').val();
                let productId = $('#product_id').val();
                if (dateStr && productId && therapistId) {
                    fetchAvailableSlots(dateStr, productId, therapistId);
                } else {
                    $('#appointment_time').html('<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>');
                    $('#appointment_time').prop('disabled', true);
                }
            }
        });

        // Function to fetch available days based on selected product and therapist
        function loadAvailableDays(productId, therapistId) {
            $.ajax({
                url: '{{ route("appointments.available-dates-patient") }}',
                method: 'POST',
                data: {
                    product_id: productId,
                    therapist_id: therapistId,
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

        // Function to fetch available time slots based on selected date, product, and therapist
        function fetchAvailableSlots(date, productId, therapistId) {
            $('#appointment_time').prop('disabled', true); // Disable time dropdown while fetching
            $('.loading-spinner').show(); // Show loading spinner

            $.ajax({
                url: '{{ route("appointments.available-slots-patient") }}',
                method: 'POST',
                data: {
                    date: date,
                    product_id: productId,
                    therapist_id: therapistId,
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

        // Trigger fetching available days when the product changes
        $('#product_id').change(function() {
            let productId = $(this).val();
            let therapistId = $('input[name="therapist_id"]').val();
            if (productId && therapistId) {
                loadAvailableDays(productId, therapistId);
                // Reset date and time fields
                fp.clear();
                fp.setDate(null);
                $('#appointment_time').html('<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>');
                $('#appointment_time').prop('disabled', true);
            } else {
                // If no product or therapist is selected, reset Flatpickr and time dropdown
                fp.set('disable', []);
                $('#appointment_time').html('<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>');
                $('#appointment_time').prop('disabled', true);
            }
        });

        // Trigger fetching slots if old input exists (e.g., after validation error)
        @if(old('product_id') && old('therapist_id'))
            loadAvailableDays('{{ old('product_id') }}', '{{ old('therapist_id') }}');
            @if(old('appointment_date'))
                // Delay to ensure Flatpickr is initialized
                setTimeout(function() {
                    fp.setDate('{{ old('appointment_date') }}', true);
                    fetchAvailableSlots('{{ old('appointment_date') }}', '{{ old('product_id') }}', '{{ old('therapist_id') }}');
                }, 500);
            @endif
        @endif
    });
    </script>
</x-app-layout>

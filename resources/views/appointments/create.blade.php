<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer un Rendez-vous') }}
        </h2>
    </x-slot>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouveau Rendez-vous') }}</h1>

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
                    </select>
                    @error('client_profile_id')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Prestation (required) -->
                <div class="details-box form-section">
                    <label class="details-label" for="product_id">{{ __('Prestation') }}</label>
                    <select id="product_id" name="product_id" class="form-control" required>
                        <option value="" disabled selected>{{ __('Sélectionner une prestation') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Appointment Date -->
                <div class="details-box form-section">
                    <label class="details-label" for="appointment_date">{{ __('Date du Rendez-vous') }}</label>
                    <input type="date" id="appointment_date" name="appointment_date" class="form-control" value="{{ old('appointment_date') }}" required>
                    @error('appointment_date')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Available Time Slots -->
                <div class="details-box form-section">
                    <label class="details-label" for="appointment_time">{{ __('Heure du Rendez-vous') }}</label>
                    <select id="appointment_time" name="appointment_time" class="form-control" required>
                        <option value="" disabled selected>{{ __('Sélectionner une heure') }}</option>
                    </select>
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    @error('appointment_time')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>



                <!-- Status -->
                <div class="details-box form-section">
                    <label class="details-label" for="status">{{ __('Statut') }}</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>{{ __('Programmé') }}</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>{{ __('Complété') }}</option>
                        <option value="canceled" {{ old('status') == 'canceled' ? 'selected' : '' }}>{{ __('Annulé') }}</option>
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

    <!-- Custom Scripts -->
    <script>
$(document).ready(function() {
    $('#product_id, #appointment_date').change(function() {
        let selectedDate = $('#appointment_date').val();
        let productId = $('#product_id').val();

        if (selectedDate && productId) {
            fetchAvailableSlots(selectedDate, productId);
        } else {
            $('#appointment_time').html('<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>');
        }
    });

    function fetchAvailableSlots(date, productId) {
        $('#appointment_time').prop('disabled', true); // Disable time dropdown while fetching
        $('.loading-spinner').show(); // Show loading spinner

        $.ajax({
            url: '{{ route("appointments.available-slots") }}', // Correct route
            method: 'GET',
            data: { date: date, product_id: productId }, // Pass the correct data
            success: function(response) {
                $('.loading-spinner').hide(); // Hide spinner on success
                if (response.slots && response.slots.length > 0) {
                    let options = '<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>';
                    response.slots.forEach(function(slot) {
                        options += `<option value="${slot.start}">${slot.start} - ${slot.end}</option>`;
                    });
                    $('#appointment_time').html(options); // Populate the time dropdown
                    $('#appointment_time').prop('disabled', false); // Enable dropdown
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

    // Fetch available slots if old input exists (for example after form validation error)
    @if(old('appointment_date') && old('product_id'))
        fetchAvailableSlots('{{ old('appointment_date') }}', '{{ old('product_id') }}');
    @endif
});


    </script>
	
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
    </style>
</x-app-layout>

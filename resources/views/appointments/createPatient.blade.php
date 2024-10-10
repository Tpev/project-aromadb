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
                            <option value="">{{ __('Sélectionner une prestation') }}</option>
                            @foreach($products as $product)
                                @if($product->can_be_booked_online)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }} data-duration="{{ $product->duration }}">
                                        {{ $product->name }}
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
                    <input type="date" id="appointment_date" name="appointment_date" class="form-control" value="{{ old('appointment_date') }}" required min="{{ date('Y-m-d') }}">
                    @error('appointment_date')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Appointment Time -->
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

    <!-- Bootstrap JS (optional but recommended) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (required for AJAX requests) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Font Awesome JS (for icons) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>

    <!-- Custom Scripts -->
    <script>

    $(document).ready(function() {
        // Fonction pour récupérer les créneaux disponibles
        function fetchAvailableSlots() {
            let therapistId = $('input[name="therapist_id"]').val();
            let date = $('#appointment_date').val();
            let productId = $('#product_id').val();
            // let duration = $('#product_id option:selected').data('duration'); // Pas nécessaire d'envoyer la durée

            if (therapistId && date && productId) {
                $('#appointment_time').prop('disabled', true);
                $('.loading-spinner').show();
                $('#appointment_time').html('<option value="" disabled selected>Chargement...</option>');

                $.ajax({
                    url: '{{ route("appointments.available-slots-patient") }}',
                    method: 'POST', // Changer de GET à POST
                    data: {
                        therapist_id: therapistId,
                        date: date,
                        product_id: productId, // Envoyer product_id au lieu de duration
                        _token: '{{ csrf_token() }}' // Inclure le token CSRF
                    },
                    success: function(response) {
                        $('.loading-spinner').hide();
                        if(response.slots.length > 0){
                            let options = '<option value="" disabled selected>Sélectionner une heure</option>';
                            response.slots.forEach(function(slot){
                                options += `<option value="${slot.start}">${slot.start} - ${slot.end}</option>`;
                            });
                            $('#appointment_time').html(options);
                            $('#appointment_time').prop('disabled', false);
                        } else {
                            $('#appointment_time').html('<option value="" disabled selected>Aucun créneau disponible pour cette date.</option>');
                            $('#appointment_time').prop('disabled', true);
                        }
                    },
                    error: function(xhr){
                        $('.loading-spinner').hide();
                        console.error(xhr.responseText);
                        alert('Une erreur est survenue lors de la récupération des créneaux disponibles.');
                    }
                });
            } else {
                $('#appointment_time').html('<option value="" disabled selected>Sélectionner une heure</option>');
                $('#appointment_time').prop('disabled', true);
            }
        }

        // Déclencher la récupération des créneaux lorsqu'il y a un changement dans la date ou la prestation
        $('#product_id, #appointment_date').on('change', fetchAvailableSlots);

        // Récupérer les créneaux si des données anciennes existent (par exemple, après une erreur de validation)
        @if(old('product_id') && old('appointment_date'))
            fetchAvailableSlots();
        @endif
    });
</script>


</x-app-layout>

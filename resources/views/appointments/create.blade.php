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

                <!-- Duration -->
                <div class="details-box form-section">
                    <label class="details-label" for="duration">{{ __('Durée (minutes)') }}</label>
                    <input type="number" id="duration" name="duration" class="form-control" value="{{ old('duration') ?? 30 }}" min="1" required>
                    @error('duration')
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

                <!-- Type -->
                <div class="details-box form-section">
                    <label class="details-label" for="type">{{ __('Type de Rendez-vous') }}</label>
                    <input type="text" id="type" name="type" class="form-control" value="{{ old('type') }}">
                    @error('type')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Product (Optional) -->
                <div class="details-box form-section">
                    <label class="details-label" for="product_id">{{ __('Produit (Optionnel)') }}</label>
                    <select id="product_id" name="product_id" class="form-control">
                        <option value="">{{ __('Sélectionner un produit') }}</option>
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

    <!-- Bootstrap JS (optional but recommended) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (required for AJAX requests) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Custom Scripts -->
    <script>
        $(document).ready(function() {
            $('#duration, #appointment_date').change(function() {
                let selectedDate = $('#appointment_date').val();
                let duration = $('#duration').val();

                if (selectedDate && duration) {
                    fetchAvailableSlots(selectedDate, duration);
                } else {
                    $('#appointment_time').html('<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>');
                }
            });

            function fetchAvailableSlots(date, duration) {
                $('#appointment_time').prop('disabled', true);
                $('.loading-spinner').show();

                $.ajax({
                    url: '{{ route("appointments.available-slots") }}',
                    method: 'GET',
                    data: { date: date, duration: duration },
                    success: function(response) {
                        $('.loading-spinner').hide();
                        if (response.slots.length > 0) {
                            let options = '<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>';
                            response.slots.forEach(function(slot) {
                                options += `<option value="${slot.start}">${slot.start} - ${slot.end}</option>`;
                            });
                            $('#appointment_time').html(options);
                            $('#appointment_time').prop('disabled', false);
                        } else {
                            $('#appointment_time').html('<option value="" disabled selected>{{ __("Aucun créneau disponible pour cette date.") }}</option>');
                            $('#appointment_time').prop('disabled', true);
                        }
                    },
                    error: function(xhr) {
                        $('.loading-spinner').hide();
                        console.error(xhr);
                        alert('{{ __("Une erreur est survenue. Veuillez réessayer.") }}');
                    }
                });
            }

            // If old input exists (e.g., after validation error), fetch available slots
            @if(old('appointment_date') && old('duration'))
                fetchAvailableSlots('{{ old('appointment_date') }}', '{{ old('duration') }}');
            @endif
        });
    </script>
</x-app-layout>

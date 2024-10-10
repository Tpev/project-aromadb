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

            <!-- Appointment Request Form -->
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
                    <input type="text" id="appointment_date" name="appointment_date" class="form-control" value="{{ old('appointment_date') }}" required
                        placeholder="Sélectionner une date">
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

    <!-- Custom Scripts -->
<!-- Custom Scripts -->
<!-- Custom Scripts -->
<!-- Custom Scripts -->
<script>
$(document).ready(function() {
    // Initialisation de Flatpickr sur le champ de date
    let availableDays = []; // Tableau pour stocker les jours disponibles (0 = Lundi, 6 = Dimanche)

    const fp = flatpickr("#appointment_date", {
        dateFormat: "Y-m-d", // Format de soumission (envoyé au serveur)
        altInput: true, // Activer l'input alternatif pour l'affichage
        altFormat: "d-m-Y", // Format d'affichage (dd mm yyyy)
        minDate: "today",
        locale: "fr",
        disable: [], // Initialement, aucune date n'est désactivée
        onChange: function(selectedDates, dateStr, instance) {
            // Déclencher la récupération des créneaux disponibles
            let productId = $('#product_id').val();
            if (dateStr && productId) {
                fetchAvailableSlots(dateStr, productId);
            } else {
                $('#appointment_time').html('<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>');
                $('#appointment_time').prop('disabled', true);
            }
        }
    });

    // Fonction pour récupérer les jours disponibles en fonction de la prestation sélectionnée
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
                    console.log('Available Days:', availableDays); // Pour débogage

                    // Mettre à jour Flatpickr pour désactiver les jours non disponibles
                    fp.set('disable', [
                        function(date) {
                            // 0 = Lundi, 6 = Dimanche
                            let dayOfWeek = (date.getDay() + 6) % 7; // Convertir les jours de JS en day_of_week
                            return !availableDays.includes(dayOfWeek);
                        }
                    ]);
                } else {
                    // Si aucune date n'est disponible, désactiver toutes les dates
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

    // Fonction pour récupérer les créneaux disponibles
    function fetchAvailableSlots(date, productId) {
        $('#appointment_time').prop('disabled', true); // Désactiver le dropdown des heures pendant la récupération
        $('.loading-spinner').show(); // Afficher le spinner de chargement

        $.ajax({
            url: '{{ route("appointments.available-slots") }}',
            method: 'POST',
            data: {
                date: date,
                product_id: productId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('.loading-spinner').hide(); // Masquer le spinner après le succès
                if (response.slots && response.slots.length > 0) {
                    let options = '<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>';
                    response.slots.forEach(function(slot) {
                        options += `<option value="${slot.start}">${slot.start} - ${slot.end}</option>`;
                    });
                    $('#appointment_time').html(options); // Remplir le dropdown des heures
                    $('#appointment_time').prop('disabled', false); // Activer le dropdown
                } else {
                    $('#appointment_time').html('<option value="" disabled selected>{{ __("Aucun créneau disponible pour cette date.") }}</option>');
                    $('#appointment_time').prop('disabled', true);
                }
            },
            error: function(xhr, status, error) {
                $('.loading-spinner').hide(); // Masquer le spinner en cas d'erreur
                console.error('Error fetching available slots:', error, xhr.responseText);
                alert('{{ __("Une erreur est survenue lors de la récupération des créneaux disponibles. Veuillez réessayer.") }}');
            }
        });
    }

    // Déclencher le chargement des jours disponibles lorsque la prestation change
    $('#product_id').change(function() {
        let productId = $(this).val();
        if (productId) {
            loadAvailableDays(productId);
            // Réinitialiser le champ date et les créneaux disponibles
            fp.clear();
            fp.setDate(null);
            $('#appointment_time').html('<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>');
            $('#appointment_time').prop('disabled', true);
        } else {
            // Si aucune prestation n'est sélectionnée, réactiver toutes les dates
            fp.set('disable', []);
            $('#appointment_time').html('<option value="" disabled selected>{{ __("Sélectionner une heure") }}</option>');
            $('#appointment_time').prop('disabled', true);
        }
    });

    // Charger les jours disponibles si une prestation et une date sont déjà sélectionnées (par exemple après une erreur de validation)
    @if(old('product_id'))
        loadAvailableDays('{{ old('product_id') }}');
        @if(old('appointment_date'))
            // Retarder l'appel pour s'assurer que Flatpickr est initialisé
            setTimeout(function() {
                fp.setDate('{{ old('appointment_date') }}', true);
                fetchAvailableSlots('{{ old('appointment_date') }}', '{{ old('product_id') }}');
            }, 500);
        @endif
    @endif
});
</script>


    <!-- Bootstrap JS (optional but recommended) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</x-app-layout>

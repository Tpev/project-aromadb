<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer une Indisponibilité') }}
        </h2>
    </x-slot>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Flatpickr CSS -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

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
        }
        .btn-primary {
            background-color: #647a0b;
            color: #ffffff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #854f38;
        }
        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
            margin-top: 5px;
        }
    </style>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Définir vos périodes d\'indisponibilité') }}</h1>

            <p>{{ __('Ici, vous pouvez indiquer les périodes pendant lesquelles vous ne serez pas disponible pour prendre des rendez-vous. Cela inclut des vacances, des engagements personnels ou toute autre raison.') }}</p>

            <!-- Success Message -->
            @if(session('success'))
                <div class="alert alert-success text-center">{{ session('success') }}</div>
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

            <!-- Unavailability Form -->
            <form action="{{ route('unavailabilities.store') }}" method="POST">
                @csrf
				<div class="details-box">
    <label class="details-label" for="reason">{{ __('Raison (optionnelle)') }}</label>
    <textarea id="reason" name="reason" class="form-control" placeholder="{{ __('Indiquez une raison (facultatif)') }}">{{ old('reason') }}</textarea>
    @error('reason')
        <p class="text-red-500">{{ $message }}</p>
    @enderror
</div>
                <div class="details-box">
                    <label class="details-label" for="start_date">{{ __('Date de début') }}</label>
                    <input type="text" id="start_date" name="start_date" class="form-control" required placeholder="Sélectionner une date">
                    @error('start_date')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="details-box">
                    <label class="details-label" for="start_time">{{ __('Heure de début') }}</label>
                    <input type="text" id="start_time" name="start_time" class="form-control" required placeholder="Sélectionner une heure">
                    @error('start_time')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="details-box">
                    <label class="details-label" for="end_date">{{ __('Date de fin') }}</label>
                    <input type="text" id="end_date" name="end_date" class="form-control" required placeholder="Sélectionner une date">
                    @error('end_date')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="details-box">
                    <label class="details-label" for="end_time">{{ __('Heure de fin') }}</label>
                    <input type="text" id="end_time" name="end_time" class="form-control" required placeholder="Sélectionner une heure">
                    @error('end_time')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-calendar-times mr-2"></i> {{ __('Ajouter Indisponibilité') }}
                    </button>
                    <a href="{{ route('appointments.index') }}" class="btn-secondary ml-3">
                        <i class="fas fa-arrow-left mr-2"></i> {{ __('Retour à la liste') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        $(document).ready(function() {
            // Initialize Flatpickr for date inputs
        const startDatePicker = flatpickr("#start_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y",
            locale: "fr",
            minDate: "today",
            onChange: function(selectedDates) {
                // Set the minimum date of end_date to the selected start_date
                const selectedDate = selectedDates[0];
                endDatePicker.set('minDate', selectedDate); // Update the minDate for end_date
            }
        });

        // Initialize Flatpickr for end date
        const endDatePicker = flatpickr("#end_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y",
            locale: "fr",
            minDate: "today",
        });

            // Initialize Flatpickr for time inputs
            flatpickr("#start_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                altInput: true,
                altFormat: "H:i",
                locale: "fr",
				time_24hr: true
            });

            flatpickr("#end_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                altInput: true,
                altFormat: "H:i",
                locale: "fr",
				time_24hr: true
            });
        });
    </script>

</x-app-layout>

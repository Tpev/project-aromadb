<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Modifier une Indisponibilité') }}
        </h2>
    </x-slot>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <style>
        .error-message {
            color: #e3342f;
            font-size: 0.875rem;
            margin-top: 5px;
        }
        .container { max-width: 800px; }
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
        .details-box { margin-bottom: 20px; }
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
        .btn-primary:hover { background-color: #854f38; }
    </style>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Modifier votre indisponibilité') }}</h1>

            @if(session('success'))
                <div class="alert alert-success text-center">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger text-center">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="unavailability-form" action="{{ route('unavailabilities.update', $unavailability->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="details-box">
                    <label class="details-label" for="reason">{{ __('Raison (optionnelle)') }}</label>
                    <textarea id="reason" name="reason" class="form-control" placeholder="{{ __('Indiquez une raison (facultatif)') }}">{{ old('reason', $unavailability->reason) }}</textarea>
                    @error('reason')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="details-box">
                    <label class="details-label" for="start_date">{{ __('Date de début') }}</label>
                    <input
                        type="text"
                        id="start_date"
                        name="start_date"
                        class="form-control"
                        required
                        value="{{ old('start_date', \Carbon\Carbon::parse($unavailability->start_date)->format('Y-m-d')) }}"
                        placeholder="{{ __('Sélectionner une date') }}"
                    >
                    @error('start_date')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="details-box">
                    <label class="details-label" for="start_time">{{ __('Heure de début') }}</label>
                    <input
                        type="text"
                        id="start_time"
                        name="start_time"
                        class="form-control"
                        required
                        value="{{ old('start_time', \Carbon\Carbon::parse($unavailability->start_date)->format('H:i')) }}"
                        placeholder="{{ __('Sélectionner une heure') }}"
                    >
                    @error('start_time')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="details-box">
                    <label class="details-label" for="end_date">{{ __('Date de fin') }}</label>
                    <input
                        type="text"
                        id="end_date"
                        name="end_date"
                        class="form-control"
                        required
                        value="{{ old('end_date', \Carbon\Carbon::parse($unavailability->end_date)->format('Y-m-d')) }}"
                        placeholder="{{ __('Sélectionner une date') }}"
                    >
                    @error('end_date')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="details-box">
                    <label class="details-label" for="end_time">{{ __('Heure de fin') }}</label>
                    <input
                        type="text"
                        id="end_time"
                        name="end_time"
                        class="form-control"
                        required
                        value="{{ old('end_time', \Carbon\Carbon::parse($unavailability->end_date)->format('H:i')) }}"
                        placeholder="{{ __('Sélectionner une heure') }}"
                    >
                    @error('end_time')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div id="custom-error" class="error-message text-center"></div>

                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i> {{ __('Mettre à jour') }}
                    </button>
                    <a href="{{ route('unavailabilities.index') }}" class="btn btn-secondary ml-3">
                        <i class="fas fa-arrow-left mr-2"></i> {{ __('Retour à la liste') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        $(document).ready(function() {
            const startDateValue = $('#start_date').val();
            const endDateValue = $('#end_date').val();
            const startTimeValue = $('#start_time').val();
            const endTimeValue = $('#end_time').val();

            const endDatePicker = flatpickr("#end_date", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d-m-Y",
                locale: "fr",
                defaultDate: endDateValue || null,
            });

            flatpickr("#start_date", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d-m-Y",
                locale: "fr",
                defaultDate: startDateValue || null,
                onChange: function(selectedDates) {
                    const selectedDate = selectedDates[0];
                    endDatePicker.set('minDate', selectedDate);
                }
            });

            flatpickr("#start_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                altInput: true,
                altFormat: "H:i",
                locale: "fr",
                time_24hr: true,
                defaultDate: startTimeValue || null
            });

            flatpickr("#end_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                altInput: true,
                altFormat: "H:i",
                locale: "fr",
                time_24hr: true,
                defaultDate: endTimeValue || null
            });

            $('#unavailability-form').on('submit', function(e) {
                $('#custom-error').text('');

                const startDate = $('#start_date').val();
                const startTime = $('#start_time').val();
                const endDate = $('#end_date').val();
                const endTime = $('#end_time').val();

                if (!startDate || !startTime || !endDate || !endTime) {
                    $('#custom-error').text('Veuillez remplir toutes les dates et heures.');
                    e.preventDefault();
                    return;
                }

                const startDateTime = new Date(`${startDate}T${startTime}`);
                const endDateTime = new Date(`${endDate}T${endTime}`);

                if (endDateTime <= startDateTime) {
                    $('#custom-error').text('La date et l\'heure de fin doivent être après la date et l\'heure de début.');
                    e.preventDefault();
                }
            });
        });
    </script>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Ajouter une Disponibilité') }}
        </h2>
    </x-slot>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Timepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouvelle Disponibilité') }}</h1>

            <form action="{{ route('availabilities.store') }}" method="POST">
                @csrf

                <!-- Jour de la Semaine -->
                <div class="details-box">
                    <label class="details-label" for="day_of_week">{{ __('Jour de la Semaine') }}</label>
                    <select id="day_of_week" name="day_of_week" class="form-control" required>
                        <option value="" disabled selected>{{ __('Sélectionner un jour') }}</option>
                        @foreach(['0' => 'Lundi', '1' => 'Mardi', '2' => 'Mercredi', '3' => 'Jeudi', '4' => 'Vendredi', '5' => 'Samedi', '6' => 'Dimanche'] as $key => $day)
                            <option value="{{ $key }}" {{ old('day_of_week') === (string)$key ? 'selected' : '' }}>{{ $day }}</option>
                        @endforeach
                    </select>
                    @error('day_of_week')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Heure de Début -->
                <div class="details-box">
                    <label class="details-label" for="start_time">{{ __('Heure de Début') }}</label>
                    <input type="text" id="start_time" name="start_time" class="form-control timepicker" value="{{ old('start_time') }}" required autocomplete="off">
                    @error('start_time')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Heure de Fin -->
                <div class="details-box">
                    <label class="details-label" for="end_time">{{ __('Heure de Fin') }}</label>
                    <input type="text" id="end_time" name="end_time" class="form-control timepicker" value="{{ old('end_time') }}" required autocomplete="off">
                    @error('end_time')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i> {{ __('Ajouter la Disponibilité') }}
                    </button>
                    <a href="{{ route('availabilities.index') }}" class="btn-secondary ml-3">
                        <i class="fas fa-arrow-left mr-2"></i> {{ __('Retour à la Liste') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- jQuery and Timepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

    <script>
        $(document).ready(function(){
            $('.timepicker').timepicker({
                timeFormat: 'HH:mm',
                interval: 15,
                minTime: '00:00',
                maxTime: '23:45',
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });
        });
    </script>

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
            text-align: left;
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

        .d-flex {
            display: flex;
            align-items: center;
        }

        .justify-content-center {
            justify-content: center;
        }

        .ml-3 {
            margin-left: 15px;
        }

        .mr-2 {
            margin-right: 8px;
        }
    </style>
</x-app-layout>

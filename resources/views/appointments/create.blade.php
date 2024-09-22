<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer un Rendez-vous') }}
        </h2>
    </x-slot>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouveau Rendez-vous') }}</h1>

            <form action="{{ route('appointments.store') }}" method="POST">
                @csrf

                <!-- Client Profile -->
                <div class="details-box">
                    <label class="details-label" for="client_profile_id">{{ __('Sélectionner le Client') }}</label>
                    <select id="client_profile_id" name="client_profile_id" class="form-control" required>
                        <option value="" disabled selected>{{ __('Sélectionner un client') }}</option>
                        @foreach($clientProfiles as $clientProfile)
                            <option value="{{ $clientProfile->id }}">{{ $clientProfile->first_name }} {{ $clientProfile->last_name }}</option>
                        @endforeach
                    </select>
                    @error('client_profile_id')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Appointment Date -->
                <div class="details-box">
                    <label class="details-label" for="appointment_date">{{ __('Date et Heure du Rendez-vous') }}</label>
                    <input type="datetime-local" id="appointment_date" name="appointment_date" class="form-control" value="{{ old('appointment_date') }}" required>
                    @error('appointment_date')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="details-box">
                    <label class="details-label" for="status">{{ __('Statut') }}</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="scheduled">{{ __('Programmé') }}</option>
                        <option value="completed">{{ __('Complété') }}</option>
                        <option value="canceled">{{ __('Annulé') }}</option>
                    </select>
                    @error('status')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="details-box">
                    <label class="details-label" for="notes">{{ __('Notes') }}</label>
                    <textarea id="notes" name="notes" class="form-control">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary mt-4">{{ __('Créer le Rendez-vous') }}</button>
                <a href="{{ route('appointments.index') }}" class="btn-secondary mt-4">{{ __('Retour à la liste') }}</a>
            </form>
        </div>
    </div>

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
            margin-bottom: 15px;
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
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            padding: 10px 20px;
            border: 1px solid #854f38;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
        }
    </style>
</x-app-layout>

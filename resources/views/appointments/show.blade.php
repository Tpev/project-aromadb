<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails du Rendez-vous') }} - {{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Rendez-vous avec ') }}{{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}</h1>

            <div class="row mt-4">
                <!-- Client Name -->
                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label">{{ __('Nom du Client') }}</label>
                        <p class="details-value">{{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}</p>
                    </div>
                </div>

                <!-- Appointment Date -->
                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label">{{ __('Date et Heure du Rendez-vous') }}</label>
                        <p class="details-value">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Status -->
                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label">{{ __('Statut') }}</label>
                        <p class="details-value">{{ ucfirst($appointment->status) }}</p>
                    </div>
                </div>

                <!-- Notes -->
                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label">{{ __('Notes') }}</label>
                        <p class="details-value">{{ $appointment->notes ?? 'Aucune note' }}</p>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <a href="{{ route('appointments.index') }}" class="btn-primary">{{ __('Retour à la liste') }}</a>
                    <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn-secondary">{{ __('Modifier le Rendez-vous') }}</a>
                </div>
            </div>
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
            margin-bottom: 20px;
        }

        .details-label {
            font-weight: bold;
            color: #647a0b;
            display: block;
            margin-bottom: 5px;
        }

        .details-value {
            color: #333333;
            font-size: 1rem;
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
    </style>
</x-app-layout>

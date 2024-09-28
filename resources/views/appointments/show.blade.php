<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails du Rendez-vous') }} - {{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">
                <i class="fas fa-calendar-alt"></i> 
                {{ __('Rendez-vous avec ') }}{{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}
            </h1>

            <!-- Client Information Section -->
            <div class="info-box">
                <h2 class="section-title">
                    <i class="fas fa-user"></i> {{ __('Informations du Client') }}
                </h2>
                <div class="row">
                    <div class="col-md-6">
                        <label class="details-label">{{ __('Nom') }}</label>
                        <p class="details-value">{{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="details-label">{{ __('Date et Heure') }}</label>
                        <p class="details-value">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Appointment Details Section -->
            <div class="info-box mt-4">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i> {{ __('Détails du Rendez-vous') }}
                </h2>
                <div class="row">
                    <div class="col-md-4">
                        <label class="details-label">{{ __('Durée') }}</label>
                        <p class="details-value">{{ $appointment->duration }} {{ __('minutes') }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="details-label">{{ __('Statut') }}</label>
                        <p class="details-value">{{ ucfirst($appointment->status) }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="details-label">{{ __('Type') }}</label>
                        <p class="details-value">{{ $appointment->type ?? __('Non spécifié') }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <label class="details-label">{{ __('Produit') }}</label>
                        <p class="details-value">{{ $appointment->product->name ?? __('Aucun produit') }}</p>
                    </div>
                    <div class="col-md-8">
                        <label class="details-label">{{ __('Notes') }}</label>
                        <p class="details-value">{{ $appointment->notes ?? __('Aucune note') }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-4 text-center">
                <div class="col-md-12">
                    <a href="{{ route('appointments.index') }}" class="btn-primary mx-2">
                        <i class="fas fa-arrow-left"></i> {{ __('Retour à la liste') }}
                    </a>
                    <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn-secondary mx-2">
                        <i class="fas fa-edit"></i> {{ __('Modifier le Rendez-vous') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 900px;
        }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .details-title {
            font-size: 1.75rem;
            font-weight: bold;
            color: #647a0b;
            text-align: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 15px;
        }

        .info-box {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .details-label {
            font-weight: 600;
            color: #647a0b;
            display: block;
            margin-bottom: 5px;
        }

        .details-value {
            font-size: 1rem;
            color: #333;
        }

        .btn-primary, .btn-secondary {
            padding: 10px 20px;
            font-size: 0.95rem;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            border: 1px solid #854f38;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }

        /* Responsive styling */
        @media (max-width: 768px) {
            .details-label, .details-value, .section-title {
                text-align: center;
            }
        }
    </style>
</x-app-layout>

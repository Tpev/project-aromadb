<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails du profil client') }} - {{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
        </h2>
    </x-slot>
<a href="{{ route('session_notes.index', $clientProfile->id) }}">View Session Notes</a>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Profil de ') }}{{ $clientProfile->first_name }} {{ $clientProfile->last_name }}</h1>

            <!-- Compact Boxed Profile Information -->
            <div class="profile-info-boxes row mt-4">
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-user-circle icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Prénom') }}</p>
                            <p class="profile-value">{{ $clientProfile->first_name }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-user icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Nom') }}</p>
                            <p class="profile-value">{{ $clientProfile->last_name }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-envelope icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Email') }}</p>
                            <p class="profile-value">{{ $clientProfile->email ?? 'Non spécifié' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-phone icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Téléphone') }}</p>
                            <p class="profile-value">{{ $clientProfile->phone ?? 'Non spécifié' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-birthday-cake icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Date de naissance') }}</p>
                            <p class="profile-value">{{ $clientProfile->birthdate ? $clientProfile->birthdate : 'Non spécifié' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-map-marker-alt icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Adresse') }}</p>
                            <p class="profile-value">{{ $clientProfile->address ?? 'Non spécifié' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointments Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-title">{{ __('Rendez-vous de ce client') }}</h2>
                    
                    @if($appointments->isEmpty())
                        <p>Aucun rendez-vous trouvé pour ce client.</p>
                    @else
                        <div class="table-responsive mx-auto">
                            <table class="table table-bordered table-hover" id="appointmentsTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($appointments as $appointment)
                                        <tr>
                                            <td>{{ $appointment->appointment_date }}</td>
                                            <td>{{ ucfirst($appointment->status) }}</td>
                                            <td>{{ $appointment->notes ?? 'Pas de notes' }}</td>
                                            <td>
                                                <a href="{{ route('appointments.show', $appointment->id) }}" class="btn btn-primary">Voir</a>
                                                <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn btn-secondary">Modifier</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <a href="{{ route('client_profiles.index') }}" class="btn-primary">{{ __('Retour à la liste') }}</a>
                    <a href="{{ route('client_profiles.edit', $clientProfile->id) }}" class="btn-secondary">{{ __('Modifier le profil') }}</a>
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

        .profile-info-boxes {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .profile-box {
            display: flex;
            align-items: center;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: transform 0.3s;
        }

        .profile-box:hover {
            transform: scale(1.05);
        }

        .icon {
            font-size: 2rem;
            color: #854f38;
            margin-right: 15px;
        }

        .profile-details {
            text-align: left;
        }

        .profile-label {
            font-weight: bold;
            color: #647a0b;
            margin: 0;
        }

        .profile-value {
            color: #333333;
            font-size: 1rem;
        }

        .table-responsive {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .table {
            width: 100%;
            max-width: 1000px;
            text-align: center;
        }

        .table thead {
            background-color: #647a0b;
            color: #ffffff;
        }

        .table tbody tr {
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s, transform 0.3s;
        }

        .table tbody tr:hover {
            background-color: #854f38;
            color: #ffffff;
            transform: scale(1.02);
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

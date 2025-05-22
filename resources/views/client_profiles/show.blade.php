<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails du profil client') }} - {{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">
                {{ __('Profil de ') }}{{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
            </h1>
<form action="{{ route('client.invite', $clientProfile) }}" method="POST">
    @csrf <button class="btn btn-primary">Envoyer l’invitation</button>
</form>

            <!-- Compact Boxed Profile Information -->
            <div class="profile-info-boxes row mt-4">
                <!-- First Name -->
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-user-circle icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Prénom') }}</p>
                            <p class="profile-value">{{ $clientProfile->first_name }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Last Name -->
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-user icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Nom') }}</p>
                            <p class="profile-value">{{ $clientProfile->last_name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-envelope icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Email') }}</p>
                            <p class="profile-value">{{ $clientProfile->email ?? 'Non spécifié' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Phone -->
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-phone icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Téléphone') }}</p>
                            <p class="profile-value">{{ $clientProfile->phone ?? 'Non spécifié' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Birthdate -->
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-birthday-cake icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Date de naissance') }}</p>
                            <p class="profile-value">
                                {{ $clientProfile->birthdate ? $clientProfile->birthdate : 'Non spécifié' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-map-marker-alt icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Adresse') }}</p>
                            <p class="profile-value">{{ $clientProfile->address ?? 'Non spécifié' }}</p>
                        </div>
                    </div>
                </div>
            </div> <!-- End first row of info boxes -->

            <!-- New row for Billing Names -->
            <div class="profile-info-boxes row mt-4">
                <!-- First Name (Billing) -->
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-file-invoice icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Prénom (Facturation)') }}</p>
                            <p class="profile-value">
                                {{ $clientProfile->first_name_billing ?? 'Non spécifié' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Last Name (Billing) -->
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-file-invoice-dollar icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Nom (Facturation)') }}</p>
                            <p class="profile-value">
                                {{ $clientProfile->last_name_billing ?? 'Non spécifié' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div> <!-- End row for Billing Names -->

            @can('requestTestimonial', $clientProfile)
                @if(is_null($testimonialRequest))
                    <!-- Aucun demande envoyée -->
                    <form action="{{ route('testimonial.request', ['clientProfile' => $clientProfile->id]) }}" method="POST" class="mt-6">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            {{ __('Demander un Témoignage') }}
                        </button>
                    </form>
                @elseif($testimonialRequest->status === 'pending')
                    <!-- Demande envoyée -->
                    <p class="mt-6 text-lg text-gray-600">
                        {{ __('Demande envoyée le') }} {{ $testimonialRequest->created_at->format('d/m/Y') }}.
                    </p>
                @elseif($testimonialRequest->status === 'completed')
                    <!-- Témoignage fait -->
                    <p class="mt-6 text-lg text-gray-600">
                        {{ __('Témoignage fait le') }} {{ $testimonialRequest->updated_at->format('d/m/Y') }}.
                    </p>
                @endif
            @endcan

            <!-- Appointments Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-title">{{ __('Rendez-vous de ce client') }}</h2>
                    <a href="{{ route('appointments.create', $clientProfile->id) }}" class="btn btn-primary mb-3">
                        {{ __('Créer un Rendez-vous') }}
                    </a>
                    
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

            <!-- Session Notes Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-title">{{ __('Notes de Séance de ce client') }}</h2>
                    <a href="{{ route('session_notes.create', $clientProfile->id) }}" class="btn btn-primary mb-3">
                        {{ __('Créer une Note de Séance') }}
                    </a>
                    
                    @if($sessionNotes->isEmpty())
                        <p>Aucune note trouvée pour ce client.</p>
                    @else
                        <div class="table-responsive mx-auto">
                            <table class="table table-bordered table-hover" id="sessionNotesTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Note</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sessionNotes as $sessionNote)
                                        <tr>
                                            <td>{{ $sessionNote->created_at }}</td>
                                            <td>{!! Str::limit(strip_tags($sessionNote->note), 50) !!}</td>
                                            <td>
                                                <a href="{{ route('session_notes.show', $sessionNote->id) }}" class="btn btn-primary">Voir</a>
                                                <a href="{{ route('session_notes.edit', $sessionNote->id) }}" class="btn btn-secondary">Modifier</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Questionnaire Responses Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-title">{{ __('Réponses aux Questionnaires de ce client') }}</h2>
                    <a href="{{ route('questionnaires.send.show', $clientProfile->id) }}" class="btn btn-primary mb-3">
                        {{ __('Remplir / Envoyer un questionnaire') }}
                    </a>
                    
                    @if($responses->isEmpty())
                        <p>Aucune réponse trouvée pour ce client.</p>
                    @else
                        <div class="table-responsive mx-auto">
                            <table class="table table-bordered table-hover" id="responsesTable">
                                <thead>
                                    <tr>
                                        <th>Questionnaire</th>
                                        <th>Date de Soumission</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($responses as $response)
                                        <tr>
                                            <td>{{ $response->questionnaire->title }}</td>
                                            <td>{{ $response->created_at }}</td>
                                            <td>
                                                @if($response->answers === '[]')
                                                    <span class="text-muted">{{ __('Pas encore rempli') }}</span>
                                                @else
                                                    <a href="{{ route('questionnaires.responses.show', $response->id) }}" class="btn btn-primary">Voir Réponse</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Conseils Envoyés Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-title">{{ __('Conseils Envoyés à ce client') }}</h2>
                    <!-- Bouton pour envoyer un nouveau conseil -->
                    <a href="{{ route('client_profiles.conseils.sendform', $clientProfile->id) }}" class="btn btn-primary mb-3">
                        {{ __('Envoyer un Conseil') }}
                    </a>
                    
                    @php
                        // Relationship name may vary:
                        $conseilsSent = $clientProfile->conseilsSent ?? collect();
                    @endphp

                    @if($conseilsSent->isEmpty())
                        <p>Aucun conseil envoyé à ce client pour le moment.</p>
                    @else
                        <div class="table-responsive mx-auto">
                            <table class="table table-bordered table-hover" id="conseilsSentTable">
                                <thead>
                                    <tr>
                                        <th>Nom du Conseil</th>
                                        <th>Tag</th>
                                        <th>Date d'Envoi</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($conseilsSent as $conseil)
                                        <tr>
                                            <td>{{ $conseil->name }}</td>
                                            <td>{{ $conseil->tag ?? '—' }}</td>
                                            <td>
                                                {{ optional($conseil->pivot)->created_at
                                                    ? $conseil->pivot->created_at->format('d/m/Y')
                                                    : '—' 
                                                }}
                                            </td>
                                            <td>
                                                <a href="{{ route('conseils.show', $conseil->id) }}" class="btn btn-primary">Voir Conseil</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

<!-- ======================= METRICS SECTION ADDED HERE ======================= -->
<div class="row mt-4">
    <div class="col-md-12">
        <h2 class="details-title">{{ __('Mesures du client') }}</h2>

        <a href="{{ route('client_profiles.metrics.create', $clientProfile->id) }}" class="btn btn-primary mb-3">
            {{ __('Créer une Nouvelle Mesure') }}
        </a>

        @if($clientProfile->metrics->isEmpty())
            <p>Aucune mesure trouvée pour ce client.</p>
        @else
            <div class="table-responsive mx-auto">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Objectif</th>
                          
                            <th>Dernière Entrée</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientProfile->metrics as $metric)
                            @php
                                // Get the last entry by sorting descending or using a relationship method
                                // Here we'll do a quick sort in Blade:
                                $lastEntry = $metric->entries->sortByDesc('entry_date')->first();
                            @endphp

                            <tr>
                                <td>{{ $metric->name }}</td>
                                <td>{{ $metric->goal ?? 'N/A' }}</td>
                    

                                <td>
                                    @if($lastEntry)
                                        <!-- Show the date and value of the most recent entry -->
                                         {{ $lastEntry->value }}  ( {{ $lastEntry->entry_date }} )
                                    @else
                                        Aucune entrée
                                    @endif
                                </td>

                                <td>
                                    <!-- "Show" route for viewing entries for this metric -->
                                    <a href="{{ route('client_profiles.metrics.show', [$clientProfile->id, $metric->id]) }}"
                                       class="btn btn-primary">
                                        Voir
                                    </a>

                                    <!-- "Edit" route for this metric -->
                                    <a href="{{ route('client_profiles.metrics.edit', [$clientProfile->id, $metric->id]) }}"
                                       class="btn btn-secondary">
                                        Modifier
                                    </a>

                                    <!-- Delete form -->
                                    <form action="{{ route('client_profiles.metrics.destroy', [$clientProfile->id, $metric->id]) }}"
                                          method="POST"
                                          style="display: inline-block;"
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette mesure ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
<!-- ======================= END METRICS SECTION ======================= -->
<!-- ======================= FILE UPLOAD SECTION STARTS ======================= -->
<div class="row mt-4">
    <div class="col-md-12">
        <h2 class="details-title">Fichiers du Client</h2>

        <!-- Form to upload a new file -->
        <form 
            action="{{ route('client_profiles.files.store', $clientProfile->id) }}" 
            method="POST" 
            enctype="multipart/form-data"
            class="mb-4"
        >
            @csrf
            <div class="form-group">
                <label for="file">Choisir un fichier</label>
                <input 
                    type="file" 
                    name="file" 
                    id="file"
                    class="form-control" 
                    required
                >
            </div>
            <button type="submit" class="btn btn-primary mt-2">
                Importer
            </button>
        </form>

        @if (session('success'))
            <div class="mb-4 text-green-600 font-bold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Table of existing files -->
        @if($clientProfile->clientFiles->isEmpty())
            <p>Aucun fichier n’a encore été téléchargé pour ce client.</p>
        @else
            <div class="table-responsive mx-auto">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Nom du Fichier</th>
                            <th>Taille</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientProfile->clientFiles as $file)
                            <tr>
                                <td>{{ $file->original_name }}</td>
                                <td>
                                    @if($file->size)
                                        {{ round($file->size / 1024, 2) }} KB
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <!-- Download link (if you have a download route) -->
                                    <a 
                                        href="{{ route('client_profiles.files.download', [$clientProfile->id, $file->id]) }}" 
                                        class="btn btn-primary"
                                    >
                                        Télécharger
                                    </a>

                                    <!-- Delete form -->
                                    <form 
                                        action="{{ route('client_profiles.files.destroy', [$clientProfile->id, $file->id]) }}"
                                        method="POST"
                                        style="display:inline-block;"
                                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>
<!-- ======================= FILE UPLOAD SECTION ENDS ======================= -->

            <!-- Action Buttons (Bottom) -->
            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <a href="{{ route('client_profiles.index') }}" class="btn-primary">
                        {{ __('Retour à la liste') }}
                    </a>
                    <a href="{{ route('client_profiles.edit', $clientProfile->id) }}" class="btn-secondary">
                        {{ __('Modifier le profil') }}
                    </a>
                    <!-- Delete Button -->
                    <form action="{{ route('client_profiles.destroy', $clientProfile->id) }}"
                          method="POST"
                          style="display: inline-block;"
                          onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer ce profil client ? Cette action est irréversible.') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">{{ __('Supprimer le profil') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        /* Delete Button */
        .btn-danger {
            background-color: #e3342f;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            margin-bottom: 10px;
        }
        .btn-danger:hover {
            background-color: #cc1f1a;
        }

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

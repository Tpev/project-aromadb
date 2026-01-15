<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails du Rendez-vous') }} - {{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}
        </h2>
    </x-slot>

<div class="container-fluid mt-5">
    <div class="details-container p-4">
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
                        <label class="details-label">{{ __('Téléphone') }}</label>
                        <p class="details-value">{{ $appointment->clientProfile->phone ?? __('Non renseigné') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="details-label">{{ __('Email') }}</label>
                        <p class="details-value">{{ $appointment->clientProfile->email ?? __('Non renseigné') }}</p>
                    </div>
                </div>
            </div>

            <!-- Appointment Details Section -->
            <div class="info-box mt-4">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i> {{ __('Détails du Rendez-vous') }}
                </h2>
                <div class="row">
                    <div class="col-md-6">
                        <label class="details-label">{{ __('Prestation (Produit)') }}</label>
                        <p class="details-value">{{ $appointment->product->name ?? __('Aucune prestation') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="details-label">{{ __('Date et Heure') }}</label>
                        <p class="details-value">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

@php
    $modeSlug = $appointment->type ?? (method_exists($appointment, 'getResolvedMode') ? $appointment->getResolvedMode() : null);
    $isDomicileLike = in_array($modeSlug, ['domicile','entreprise'], true);

    $modeLabel = method_exists($appointment, 'getResolvedModeLabel')
        ? $appointment->getResolvedModeLabel()
        : ($modeSlug ? ucfirst($modeSlug) : __('Non spécifié'));
@endphp


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
                        <label class="details-label">{{ __('Mode') }}</label>
                        <p class="details-value">{{ $modeLabel }}</p>
                    </div>
                </div>

                {{-- Adresse (cabinet / domicile / entreprise) --}}
                @if($modeSlug === 'cabinet')
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <label class="details-label">{{ __('Cabinet') }}</label>
                            <p class="details-value">
                                @php
                                    $loc = $appointment->practiceLocation ?? null;
                                @endphp
                                @if($loc)
                                    {{ $loc->label }}
                                    <br>
                                    <span class="text-muted">
                                        {{ $loc->full_address ?? trim(($loc->address_line1 ?? '') . ', ' . ($loc->postal_code ?? '') . ' ' . ($loc->city ?? '')) }}
                                    </span>
                                @else
                                    {{ __('Non renseigné') }}
                                @endif
                            </p>
                        </div>
                    </div>
                @elseif($isDomicileLike)
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <label class="details-label">{{ $modeSlug === 'entreprise' ? __('Adresse de l’entreprise') : __('Adresse du domicile') }}</label>
                            <p class="details-value">{{ $appointment->address ?: ($appointment->clientProfile->address ?? __('Non renseigné')) }}</p>
                        </div>
                    </div>
                @endif
        @if($meetingLink)
            <div class="mt-4">
                <strong>Votre lien de connexion :</strong>
                <p><a href="{{ $meetingLink }}" class="text-blue-500 hover:underline">{{ $meetingLink }}</a></p>
            </div>
        @endif
		@if($meetingLink)
            <div class="mt-4">
                <strong>Le lien de connexion de votre client :</strong>
                <p><a href="{{ $meetingLinkPatient }}" class="text-blue-500 hover:underline">{{ $meetingLinkPatient }}</a></p>
            </div>
        @endif
                <div class="row">
                    <div class="col-md-12">
                        <label class="details-label">{{ __('Notes') }}</label>
                        <p class="details-value">{{ $appointment->notes ?? __('Aucune note') }}</p>
                    </div>
                </div>
            </div>
<!-- Action Buttons -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="button-group">
            <a href="{{ route('appointments.index') }}" class="btn-primary">
                <i class="fas fa-arrow-left"></i> {{ __('Retour à la liste') }}
            </a>
            <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn-secondary">
                <i class="fas fa-edit"></i> {{ __('Modifier le Rendez-vous') }}
            </a>
            <!-- Mark as Completed Button -->
            @if($appointment->status !== 'Complété')
                <form action="{{ route('appointments.complete', $appointment->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn-secondary" onclick="return confirm('{{ __('Marquer ce rendez-vous comme complété?') }}')">
                        <i class="fas fa-check-circle"></i> {{ __('Marquer comme Complété') }}
                    </button>
                </form>
            @endif
            <!-- Delete Button -->
            <form action="{{ route('appointments.destroy', $appointment->id) }}" method="POST" style="display: inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-secondary" onclick="return confirm('{{ __('Êtes-vous sûr de vouloir annuler ce rendez-vous?') }}')">
                    <i class="fas fa-trash"></i> {{ __('Annuler le Rendez-vous') }}
                </button>
            </form>
        </div>
    </div>
</div>
@can('update', $appointment)
@if($appointment->product?->requires_emargement && !$appointment->emargement_sent)
<form action="{{ route('emargement.send', $appointment->id) }}" method="POST" style="display:inline-block">
    @csrf
    <button class="btn-primary" type="submit">📄 Envoyer la feuille d’émargement</button>
</form>
@endif
@endcan


        </div>
    </div>

    <!-- Custom Styles -->
    <style>
/* Remove the max-width constraint */
.container {
    /* max-width: 900px; */
}

/* Adjust .details-container if necessary */
.details-container {
    width: 100%;
    /* Adjust padding and margins as needed */
    padding: 25px;
    margin: 0;
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

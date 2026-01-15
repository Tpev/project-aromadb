<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #6d9c2e;">
            {{ __('Confirmation de Rendez-vous') }}
        </h2>
    </x-slot>

    @php
        // Derive the actual consultation mode for this appointment
        // Prefer stored type / resolved mode to avoid mis-detecting "entreprise"
        $mode = $appointment->type
            ?? (method_exists($appointment, 'getResolvedMode') ? $appointment->getResolvedMode() : null);

        // Backward compatibility fallback (old appointments)
        if (!$mode) {
            if ($appointment->practiceLocation) {
                $mode = 'cabinet';
            } elseif ($appointment->product?->visio) {
                $mode = 'visio';
            } elseif ($appointment->product?->adomicile) {
                $mode = 'domicile';
            } elseif (!empty($appointment->product?->en_entreprise)) {
                $mode = 'entreprise';
            } else {
                $mode = 'cabinet';
            }
        }

        $modeLabel = [
            'cabinet'     => __('Dans le Cabinet'),
            'visio'       => __('En Visio'),
            'domicile'    => __('À Domicile'),
            'entreprise'  => __('En entreprise'),
        ][$mode] ?? __('Non spécifié');

        // Address strings
        $cabinetLabel = $appointment->practiceLocation?->label;
        $cabinetFullAddress = $appointment->practiceLocation?->full_address
            ?? trim(collect([
                $appointment->practiceLocation?->address_line1,
                $appointment->practiceLocation?->address_line2,
                trim(
                    ($appointment->practiceLocation?->postal_code ? $appointment->practiceLocation?->postal_code.' ' : '')
                    . ($appointment->practiceLocation?->city ?? '')
                ),
                $appointment->practiceLocation?->country,
            ])->filter()->implode("\n"));

        $fallbackCompanyAddress = $appointment->user?->company_address;
        $clientAddress = $appointment->address ?? $appointment->clientProfile?->address ?? null;

        // Cancellation rules (therapist setting)
        $cutoffHours = max(0, (int) ($appointment->user?->cancellation_notice_hours ?? 0));
        $isCancelled = in_array($appointment->status, ['cancelled', 'canceled', 'Annulée', 'Annulee'], true);

        $canCancel = true;
        $latestCancelAt = null;

        if ($appointment->appointment_date) {
            $latestCancelAt = $appointment->appointment_date->copy()->subHours($cutoffHours);
            if ($cutoffHours > 0) {
                $canCancel = now()->lte($latestCancelAt);
            }
            // Also prevent cancel if appointment is in the past
            if ($appointment->appointment_date->isPast()) {
                $canCancel = false;
            }
        } else {
            $canCancel = false;
        }
    @endphp

    <style>
        .confirmation-container {
            background-color: #f0f8ec;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 20px auto;
            transition: box-shadow 0.3s ease-in-out;
        }
        .confirmation-container:hover { box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15); }
        .confirmation-title { font-size: 2rem; font-weight: bold; color: #6d9c2e; text-align: center; margin-bottom: 25px; }
        .confirmation-label { font-weight: bold; color: #6d9c2e; font-size: 1.1rem; }
        .confirmation-content { font-size: 1rem; color: #4f4f4f; margin-bottom: 15px; line-height: 1.5; }

        .btn-home {
            background-color: #6d9c2e; color: #fff; font-size: 1.2rem; padding: 10px 25px; border-radius: 30px; border: none;
            text-align: center; cursor: pointer; display: inline-block; transition: background-color 0.3s ease-in-out; text-decoration: none;
        }
        .btn-home:hover { background-color: #56781f; }

        .btn-danger {
            background-color: #d9534f; color: #fff; font-size: 1.1rem; padding: 10px 25px; border-radius: 30px; border: none;
            text-align: center; cursor: pointer; display: inline-block; transition: background-color 0.3s ease-in-out; text-decoration: none;
        }
        .btn-danger:hover { background-color: #c9302c; }

        .btn-disabled {
            background-color: #cfd8c3 !important;
            color: #ffffff !important;
            cursor: not-allowed !important;
            opacity: 0.9;
        }

        .alert {
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 0.95rem;
        }
        .alert-success { background: #e7f6e7; color: #2f6b2f; border: 1px solid #bfe6bf; }
        .alert-error { background: #fdecec; color: #8a2b2b; border: 1px solid #f3b6b6; }

        .icon-success { color: #6d9c2e; font-size: 3rem; text-align: center; display: block; margin: 0 auto 20px auto; }
        .icon-pending { color: #ffc107; font-size: 3rem; text-align: center; display: block; margin: 0 auto 20px auto; animation: spin 2s linear infinite; }
        .icon-cancel  { color: #d9534f; font-size: 3rem; text-align: center; display: block; margin: 0 auto 20px auto; }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        @media (max-width: 768px) {
            .confirmation-container { padding: 20px; }
            .confirmation-title { font-size: 1.8rem; }
            .confirmation-content { font-size: 0.95rem; }
            .btn-home, .btn-danger { font-size: 1rem; padding: 10px 20px; }
            .icon-success, .icon-pending, .icon-cancel { font-size: 2.5rem; }
        }
    </style>

    <div class="confirmation-container">

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if($isCancelled)
            <i class="fas fa-times-circle icon-cancel"></i>
            <h1 class="confirmation-title">{{ __('Rendez-vous Annulé') }}</h1>

            <div class="confirmation-content">
                <p>{{ __('Ce rendez-vous a été annulé.') }}</p>
            </div>

            <div class="text-center mt-4">
                <a href="{{ url('/') }}" class="btn-home">
                    <i class="fas fa-home mr-2"></i> {{ __('Retour à l\'Accueil') }}
                </a>
            </div>

        @elseif($appointment->status === 'Payée' || $appointment->status === 'confirmed')
            <i class="fas fa-check-circle icon-success"></i>
            <h1 class="confirmation-title">{{ __('Rendez-vous Confirmé') }}</h1>

            {{-- Core infos --}}
            <div class="confirmation-content">
                <p><span class="confirmation-label">{{ __('Thérapeute') }}:</span> {{ $appointment->user->company_name ?? $appointment->user->name }}</p>
            </div>

            <div class="confirmation-content">
                <p><span class="confirmation-label">{{ __('Prénom du Client') }}:</span> {{ $appointment->clientProfile->first_name }}</p>
            </div>

            <div class="confirmation-content">
                <p><span class="confirmation-label">{{ __('Nom du Client') }}:</span> {{ $appointment->clientProfile->last_name }}</p>
            </div>

            <div class="confirmation-content">
                <p><span class="confirmation-label">{{ __('Date du Rendez-vous') }}:</span> {{ $appointment->appointment_date->format('d/m/Y') }}</p>
            </div>

            <div class="confirmation-content">
                <p><span class="confirmation-label">{{ __('Heure du Rendez-vous') }}:</span> {{ $appointment->appointment_date->format('H:i') }}</p>
            </div>

            <div class="confirmation-content">
                <p><span class="confirmation-label">{{ __('Durée') }}:</span> {{ $appointment->duration }} {{ __('minutes') }}</p>
            </div>

            @if($appointment->product)
                <div class="confirmation-content">
                    <p><span class="confirmation-label">{{ __('Prestation') }}:</span> {{ $appointment->product->name }}</p>
                </div>

                <div class="confirmation-content">
                    <p><span class="confirmation-label">{{ __('Mode de consultation') }}:</span> {{ $modeLabel }}</p>
                </div>

                {{-- Mode-specific info --}}
                @if($mode === 'visio')
                    <div class="confirmation-content">
                        <p>{{ __('Vous recevrez le lien visio par email.') }}</p>
                    </div>

                @elseif($mode === 'cabinet')
                    <div class="confirmation-content">
                        <p>
                            <span class="confirmation-label">{{ __('Adresse du cabinet') }}:</span><br>
                            @if($appointment->practiceLocation)
                                <strong>{{ $cabinetLabel }}</strong><br>
                                {!! nl2br(e($cabinetFullAddress)) !!}
                            @elseif(!empty($fallbackCompanyAddress))
                                {!! nl2br(e($fallbackCompanyAddress)) !!}
                            @else
                                {{ __('Adresse non disponible') }}
                            @endif
                        </p>
                    </div>

                @elseif($mode === 'domicile' || $mode === 'entreprise')
                    <div class="confirmation-content">
                        <p>
                            <span class="confirmation-label">
                                {{ $mode === 'entreprise' ? __('Adresse de l’entreprise') : __('Votre adresse') }}:
                            </span><br>
                            {!! nl2br(e($clientAddress ?? __('Adresse non disponible'))) !!}
                        </p>
                    </div>
                @endif
            @endif

            <div class="confirmation-content">
                <p><span class="confirmation-label">{{ __('Notes') }}:</span> {{ $appointment->notes ?? __('Aucune note ajoutée') }}</p>
            </div>

            {{-- ICS --}}
            <div class="text-center mt-4">
                <a href="{{ route('appointments.downloadICS', $appointment->token) }}" class="btn-home">
                    <i class="fas fa-calendar-plus mr-2"></i> {{ __('Ajouter à votre calendrier') }}
                </a>
            </div>

            {{-- Cancel (disabled if too late) --}}
            <div class="text-center mt-4">
                @if($canCancel)
                    <form method="POST"
                          action="{{ route('appointment.confirmation.cancel', $appointment->token) }}"
                          onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir annuler ce rendez-vous ?') }}');">
                        @csrf
                        <button type="submit" class="btn-danger">
                            <i class="fas fa-times mr-2"></i> {{ __('Annuler le rendez-vous') }}
                        </button>
                    </form>
                @else
                    <button type="button" class="btn-danger btn-disabled" disabled>
                        <i class="fas fa-lock mr-2"></i> {{ __('Annulation non disponible') }}
                    </button>

                    <div class="alert alert-error mt-3">
                        @if($cutoffHours > 0 && $latestCancelAt)
                            {{ __('L’annulation en ligne n’est plus possible à moins de :hours heure(s) du rendez-vous.', ['hours' => $cutoffHours]) }}
                        @else
                            {{ __('L’annulation en ligne n’est pas disponible pour ce rendez-vous.') }}
                        @endif
                    </div>
                @endif
            </div>

            {{-- Home --}}
            <div class="text-center mt-4">
                <a href="{{ url('/') }}" class="btn-home">
                    <i class="fas fa-home mr-2"></i> {{ __('Retour à l\'Accueil') }}
                </a>
            </div>

        @elseif($appointment->status === 'pending')
            <i class="fas fa-spinner fa-spin icon-pending"></i>
            <h1 class="confirmation-title">{{ __('Rendez-vous en Attente de Paiement') }}</h1>

            <div class="confirmation-content">
                <p>{{ __('Votre rendez-vous a été créé avec succès. Veuillez procéder au paiement pour confirmer votre réservation.') }}</p>
            </div>

            <div class="confirmation-content">
                <p>{{ __('Vous serez redirigé vers la page de paiement. Si la redirection ne se produit pas automatiquement, cliquez sur le bouton ci-dessous.') }}</p>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('checkout.resume', $appointment->stripe_session_id) }}" class="btn-home">
                    <i class="fas fa-credit-card mr-2"></i> {{ __('Procéder au Paiement') }}
                </a>
            </div>

            {{-- Cancel (disabled if too late) --}}
            <div class="text-center mt-4">
                @if(!$isCancelled)
                    @if($canCancel)
                        <form method="POST"
                              action="{{ route('appointment.confirmation.cancel', $appointment->token) }}"
                              onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir annuler ce rendez-vous ?') }}');">
                            @csrf
                            <button type="submit" class="btn-danger">
                                <i class="fas fa-times mr-2"></i> {{ __('Annuler le rendez-vous') }}
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn-danger btn-disabled" disabled>
                            <i class="fas fa-lock mr-2"></i> {{ __('Annulation non disponible') }}
                        </button>

                        <div class="alert alert-error mt-3">
                            @if($cutoffHours > 0 && $latestCancelAt)
                                {{ __('L’annulation en ligne n’est plus possible à moins de :hours heure(s) du rendez-vous.', ['hours' => $cutoffHours]) }}
                            @else
                                {{ __('L’annulation en ligne n’est pas disponible pour ce rendez-vous.') }}
                            @endif
                        </div>
                    @endif
                @endif
            </div>

            <div class="text-center mt-4">
                <a href="{{ url('/') }}" class="btn-home">
                    <i class="fas fa-home mr-2"></i> {{ __('Retour à l\'Accueil') }}
                </a>
            </div>
        @endif
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
</x-app-layout>

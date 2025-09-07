<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #6d9c2e;">
            {{ __('Confirmation de Rendez-vous') }}
        </h2>
    </x-slot>

    @php
        // Derive the actual consultation mode for this appointment
        $mode = null;
        if ($appointment->practiceLocation) {
            $mode = 'cabinet';
        } elseif ($appointment->product?->visio) {
            $mode = 'visio';
        } elseif ($appointment->product?->adomicile) {
            $mode = 'domicile';
        } else {
            // default fallback
            $mode = 'cabinet';
        }

        // Helper labels
        $modeLabel = [
            'cabinet'  => __('Dans le Cabinet'),
            'visio'    => __('En Visio'),
            'domicile' => __('À Domicile'),
        ][$mode] ?? __('Non spécifié');

        // Address strings
        $cabinetLabel = $appointment->practiceLocation?->label;
        // Prefer model accessor `full_address` if you have it (as in your PracticeLocation model),
        // otherwise manually reconstruct the address here.
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

        // Fallback company address if no specific practice location
        $fallbackCompanyAddress = $appointment->user?->company_address;
        // Client address (for domicile)
        $clientAddress = $appointment->clientProfile?->address ?? $appointment->address ?? null;
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
        .icon-success { color: #6d9c2e; font-size: 3rem; text-align: center; display: block; margin: 0 auto 20px auto; }
        .icon-pending { color: #ffc107; font-size: 3rem; text-align: center; display: block; margin: 0 auto 20px auto; animation: spin 2s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @media (max-width: 768px) {
            .confirmation-container { padding: 20px; }
            .confirmation-title { font-size: 1.8rem; }
            .confirmation-content { font-size: 0.95rem; }
            .btn-home { font-size: 1rem; padding: 10px 20px; }
            .icon-success, .icon-pending { font-size: 2.5rem; }
        }
    </style>

    <div class="confirmation-container">
        @if($appointment->status === 'Payée' || $appointment->status === 'confirmed')
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

                @elseif($mode === 'domicile')
                    <div class="confirmation-content">
                        <p>
                            <span class="confirmation-label">{{ __('Votre adresse') }}:</span><br>
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

            <div class="text-center mt-4">
                <a href="{{ url('/') }}" class="btn-home">
                    <i class="fas fa-home mr-2"></i> {{ __('Retour à l\'Accueil') }}
                </a>
            </div>
        @endif
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
</x-app-layout>

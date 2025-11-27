{{-- resources/views/mobile/appointments/show.blade.php --}}
<x-mobile-layout>

    @php
        // Derive the actual consultation mode for this appointment
        if ($appointment->practiceLocation) {
            $mode = 'cabinet';
        } elseif ($appointment->product?->visio) {
            $mode = 'visio';
        } elseif ($appointment->product?->adomicile) {
            $mode = 'domicile';
        } else {
            $mode = 'cabinet';
        }

        $modeLabel = [
            'cabinet'  => __('Dans le Cabinet'),
            'visio'    => __('En Visio'),
            'domicile' => __('À Domicile'),
        ][$mode] ?? __('Non spécifié');

        // Cabinet infos
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

        // Fallback company address
        $fallbackCompanyAddress = $appointment->user?->company_address;

        // Client address (domicile)
        $clientAddress = $appointment->clientProfile?->address ?? $appointment->address ?? null;

        $isConfirmed = in_array($appointment->status, ['Payée', 'confirmed'], true);
        $isPending   = $appointment->status === 'pending';
    @endphp

    {{-- Header --}}
    <div class="px-4 pt-4 pb-2 flex items-center justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500">
                @if($isConfirmed)
                    {{ __('Rendez-vous confirmé') }}
                @elseif($isPending)
                    {{ __('En attente de paiement') }}
                @else
                    {{ __('Détails du rendez-vous') }}
                @endif
            </p>
            <h2 class="text-lg font-semibold text-[#647a0b]">
                {{ $appointment->user->company_name ?? $appointment->user->name }}
            </h2>
        </div>

        <button type="button"
                onclick="window.location.href='{{ url('/') }}'"
                class="text-xs text-gray-500 underline">
            {{ __('Fermer') }}
        </button>
    </div>

    <div class="px-4 pb-6 space-y-4">

        {{-- STATUS CARD --}}
        <x-ts-card class="space-y-2">
            <div class="flex items-center gap-3">
                @if($isConfirmed)
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-emerald-100 text-emerald-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-emerald-700">
                            {{ __('Votre rendez-vous est confirmé 🎉') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ __('Un email de confirmation vous a été envoyé (si une adresse email a été fournie).') }}
                        </p>
                    </div>
                @elseif($isPending)
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 text-amber-500">
                        <i class="fas fa-spinner text-xl animate-spin-slow"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-amber-700">
                            {{ __('Rendez-vous en attente de paiement') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ __('Veuillez finaliser le paiement pour confirmer votre réservation.') }}
                        </p>
                    </div>
                @else
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 text-gray-500">
                        <i class="fas fa-info-circle text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-700">
                            {{ __('Détails du rendez-vous') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ __('Le statut actuel est : :status', ['status' => $appointment->status ?? '—']) }}
                        </p>
                    </div>
                @endif
            </div>
        </x-ts-card>

        {{-- CORE INFOS CARD --}}
        <x-ts-card class="space-y-3">
            {{-- Date & time --}}
            <div class="flex items-start gap-3">
                <div class="mt-0.5">
                    <i class="fas fa-calendar-alt text-[#647a0b] text-base"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        {{ __('Date & heure') }}
                    </p>
                    <p class="text-sm font-medium text-gray-800">
                        {{ $appointment->appointment_date->format('d/m/Y') }}
                        — {{ $appointment->appointment_date->format('H:i') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ __('Durée : :minutes min', ['minutes' => $appointment->duration]) }}
                    </p>
                </div>
            </div>

            {{-- Prestation --}}
            @if($appointment->product)
                <div class="h-px bg-gray-100"></div>

                <div class="flex items-start gap-3">
                    <div class="mt-0.5">
                        <i class="fas fa-spa text-[#854f38] text-base"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs uppercase tracking-wide text-gray-500">
                            {{ __('Prestation') }}
                        </p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $appointment->product->name }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ __('Mode : :mode', ['mode' => $modeLabel]) }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Patient --}}
            <div class="h-px bg-gray-100"></div>

            <div class="flex items-start gap-3">
                <div class="mt-0.5">
                    <i class="fas fa-user text-gray-500 text-base"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        {{ __('Client') }}
                    </p>
                    <p class="text-sm font-medium text-gray-800">
                        {{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}
                    </p>
                    @if($appointment->clientProfile->email)
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $appointment->clientProfile->email }}
                        </p>
                    @endif
                    @if($appointment->clientProfile->phone)
                        <p class="text-xs text-gray-500">
                            {{ $appointment->clientProfile->phone }}
                        </p>
                    @endif
                </div>
            </div>
        </x-ts-card>

        {{-- LOCATION / MODE SPECIFIC CARD --}}
        @if($mode === 'visio' || $mode === 'cabinet' || $mode === 'domicile')
            <x-ts-card class="space-y-2">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fas fa-map-marker-alt text-[#647a0b] text-base"></i>
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        @if($mode === 'visio')
                            {{ __('Consultation en ligne') }}
                        @elseif($mode === 'cabinet')
                            {{ __('Adresse du cabinet') }}
                        @else
                            {{ __('Adresse du rendez-vous') }}
                        @endif
                    </p>
                </div>

                @if($mode === 'visio')
                    <p class="text-sm text-gray-700">
                        {{ __('Vous recevrez le lien de visio par email avant le rendez-vous.') }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ __('Vérifiez également vos courriers indésirables (spam) si vous ne le trouvez pas.') }}
                    </p>

                @elseif($mode === 'cabinet')
                    @if($appointment->practiceLocation)
                        <p class="text-sm font-medium text-gray-800">
                            {{ $cabinetLabel }}
                        </p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">
                            {!! nl2br(e($cabinetFullAddress)) !!}
                        </p>
                    @elseif(!empty($fallbackCompanyAddress))
                        <p class="text-sm text-gray-700 whitespace-pre-line">
                            {!! nl2br(e($fallbackCompanyAddress)) !!}
                        </p>
                    @else
                        <p class="text-sm text-gray-500">
                            {{ __('Adresse non disponible') }}
                        </p>
                    @endif

                @elseif($mode === 'domicile')
                    <p class="text-sm text-gray-700 whitespace-pre-line">
                        {!! nl2br(e($clientAddress ?? __('Adresse non disponible'))) !!}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ __('Assurez-vous que votre adresse est correcte pour que le thérapeute puisse se déplacer.') }}
                    </p>
                @endif
            </x-ts-card>
        @endif

        {{-- NOTES --}}
        <x-ts-card>
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">
                {{ __('Notes') }}
            </p>
            <p class="text-sm text-gray-700">
                {{ $appointment->notes ?: __('Aucune note ajoutée') }}
            </p>
        </x-ts-card>

        {{-- ACTIONS --}}
        <div class="space-y-3 mt-2">
            @if($isConfirmed)
                {{-- Use existing web route for ICS download --}}
                <a href="{{ route('appointments.downloadICS', $appointment->token) }}"
                   class="w-full inline-flex items-center justify-center px-4 py-2 rounded-full bg-primary-600 text-white text-sm font-semibold shadow-sm active:scale-[0.98] transition-transform">
                    <i class="fas fa-calendar-plus mr-2 text-xs"></i>
                    {{ __('Ajouter à mon agenda') }}
                </a>
            @endif

            @if($isPending && $appointment->stripe_session_id)
                <a href="{{ route('checkout.resume', $appointment->stripe_session_id) }}"
                   class="w-full inline-flex items-center justify-center px-4 py-2 rounded-full bg-amber-500 text-white text-sm font-semibold shadow-sm active:scale-[0.98] transition-transform">
                    <i class="fas fa-credit-card mr-2 text-xs"></i>
                    {{ __('Procéder au paiement') }}
                </a>
            @endif

            <a href="{{ url('/mobile/recherche-praticien') }}"
               class="w-full inline-flex items-center justify-center px-4 py-2 rounded-full bg-gray-100 text-gray-800 text-sm font-semibold shadow-sm active:scale-[0.98] transition-transform">
                <i class="fas fa-home mr-2 text-xs"></i>
                {{ __('Retour à l’accueil AromaMade') }}
            </a>
        </div>

        <p class="text-[11px] text-gray-400 text-center mt-2">
            {{ __('En cas de question ou de modification, veuillez contacter directement votre thérapeute.') }}
        </p>
    </div>

    <style>
        .animate-spin-slow {
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            0%   { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

</x-mobile-layout>

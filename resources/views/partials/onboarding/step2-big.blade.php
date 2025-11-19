{{-- resources/views/partials/onboarding/step2-big.blade.php --}}
<div id="step2" class="border border-[#e4e8d5] rounded-xl p-5 bg-[#fdfbf8]">
    <div class="flex items-start justify-between gap-3 mb-4">
        <div class="flex items-start gap-3">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#647a0b] text-white text-sm font-semibold">
                2
            </span>
            <div>
                <h4 class="text-sm sm:text-base font-semibold text-gray-900">
                    Étape 2 — Prêt pour les réservations en ligne
                </h4>
                <p class="text-xs text-gray-500 mt-1">
                    Configurez vos lieux, vos horaires et vos prestations pour ouvrir les réservations.
                </p>
            </div>
        </div>

        <div class="text-right">
            <div class="text-xs font-semibold text-gray-700">
                {{ $step2Completion }}% complété
            </div>
            @if($step2Completion == 100)
                <div class="text-[11px] text-green-700 mt-0.5">✔ Réservations en ligne prêtes</div>
            @else
                <div class="text-[11px] text-gray-500 mt-0.5">Activez la réservation en complétant ces points</div>
            @endif
        </div>
    </div>

    {{-- Barre de progression --}}
    <div class="w-full bg-gray-100 rounded-full h-1.5 mb-4 overflow-hidden">
        <div class="h-1.5 rounded-full bg-[#647a0b] transition-all duration-300"
             style="width: {{ $step2Completion }}%;"></div>
    </div>

    {{-- Checklist --}}
    <ul class="space-y-2 text-xs text-gray-600 mb-5">
        @php $ok = $step2Checks['location'] ?? false; @endphp
        <li class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[11px]
                         {{ $ok ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                {{ $ok ? '✓' : '•' }}
            </span>
            <span>Ajouter au moins un lieu de consultation (cabinet, visio, domicile)</span>
        </li>

        @php $ok = $step2Checks['availabilities'] ?? false; @endphp
        <li class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[11px]
                         {{ $ok ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                {{ $ok ? '✓' : '•' }}
            </span>
            <span>Définir vos plages de disponibilités dans l’agenda</span>
        </li>

        @php $ok = $step2Checks['bookable'] ?? false; @endphp
        <li class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[11px]
                         {{ $ok ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                {{ $ok ? '✓' : '•' }}
            </span>
            <span>Marquer au moins une prestation comme “réservable en ligne”</span>
        </li>
    </ul>

    {{-- CTAs --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('practice-locations.index') }}"
           class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold rounded-lg
                  bg-white text-[#647a0b] border border-[#647a0b] hover:bg-[#f5f7eb] transition-colors">
            Gérer mes lieux
        </a>

        <a href="{{ route('availabilities.index') }}"
           class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold rounded-lg
                  bg-white text-[#647a0b] border border-[#647a0b] hover:bg-[#f5f7eb] transition-colors">
            Définir mes horaires
        </a>

        <a href="{{ route('products.index') }}"
           class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold rounded-lg
                  bg-[#647a0b] text-white hover:bg-[#8ea633] transition-colors">
            Rendre mes prestations réservables
        </a>
    </div>
</div>

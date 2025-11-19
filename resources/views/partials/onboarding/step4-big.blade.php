<div id="step4" class="border border-[#e4e8d5] rounded-xl p-5 bg-[#f7fbff]">
    <div class="flex items-start justify-between gap-3 mb-4">
        <div class="flex items-start gap-3">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#647a0b] text-white text-sm font-semibold">
                4
            </span>
            <div>
                <h4 class="text-sm sm:text-base font-semibold text-gray-900">
                    Étape 4 — Parrainer un confrère / une consœur (bonus)
                </h4>
                <p class="text-xs text-gray-500 mt-1">
                    Invitez un autre praticien à découvrir AromaMade PRO. Lorsqu’il s’abonne, vous gagnez 1 mois de licence PRO offert.
                </p>
            </div>
        </div>

        <div class="text-right">
            <div class="text-xs font-semibold text-gray-700">
                {{ $step4Completion }}% complété
            </div>
            @if($skipStep4)
                <div class="text-[11px] text-gray-500 mt-0.5">Étape ignorée</div>
            @elseif(($step4Checks['referral'] ?? false) === true)
                <div class="text-[11px] text-green-700 mt-0.5">✔ Parrainage validé</div>
            @else
                <div class="text-[11px] text-gray-500 mt-0.5">Bonus : 1 mois offert</div>
            @endif
        </div>
    </div>

    {{-- Barre de progression --}}
    <div class="w-full bg-gray-100 rounded-full h-1.5 mb-4 overflow-hidden">
        <div class="h-1.5 rounded-full bg-[#647a0b] transition-all duration-300"
             style="width: {{ $step4Completion }}%;"></div>
    </div>

    <ul class="space-y-2 text-xs text-gray-600 mb-5">
        <li class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[11px]
                         {{ ($step4Checks['referral'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                {{ ($step4Checks['referral'] ?? false) ? '✓' : '•' }}
            </span>
            <span>Inviter au moins un praticien (et qu’il s’abonne) pour débloquer le mois offert</span>
        </li>
    </ul>

    <div class="flex flex-wrap gap-2 items-center">
        {{-- Lien vers ta page de parrainage (à adapter) --}}
        @if(Route::has('referrals.index'))
            <a href="{{ route('referrals.index') }}"
               class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold rounded-lg
                      bg-[#647a0b] text-white hover:bg-[#8ea633] transition-colors">
                Inviter un thérapeute
            </a>
        @endif

        {{-- Bouton "Marquer comme fait" (temporaire) --}}
        @if(!($step4Checks['referral'] ?? false) && !$skipStep4)
            <form action="{{ route('onboarding.referralDone') }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold rounded-lg
                               text-gray-500 border border-gray-300 hover:bg-gray-50 transition-colors">
                    Marquer comme fait
                </button>
            </form>
        @endif

        {{-- Bouton pour ignorer l’étape 4 --}}
        @if(!$skipStep4 && !($step4Checks['referral'] ?? false))
            <form action="{{ route('onboarding.skipStep4') }}" method="POST" class="ml-auto">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold rounded-lg
                               text-gray-400 border border-gray-200 hover:bg-gray-50 transition-colors">
                    Ignorer pour le moment
                </button>
            </form>
        @endif
    </div>
</div>

{{-- resources/views/partials/onboarding/step1-big.blade.php --}}
<div id="step1" class="border border-[#e4e8d5] rounded-xl p-5 bg-[#f9faf5]">
    <div class="flex items-start justify-between gap-3 mb-4">
        <div class="flex items-start gap-3">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#647a0b] text-white text-sm font-semibold">
                1
            </span>
            <div>
                <h4 class="text-sm sm:text-base font-semibold text-gray-900">
                    Étape 1 — Profil & informations de base
                </h4>
                <p class="text-xs text-gray-500 mt-1">
                    Donnez confiance à vos futurs clients avec une fiche claire et complète.
                </p>
            </div>
        </div>

        <div class="text-right">
            <div class="text-xs font-semibold text-gray-700">
                {{ $step1Completion }}% complété
            </div>
            @if($step1Completion == 100)
                <div class="text-[11px] text-green-700 mt-0.5">✔ Profil de base complété</div>
            @else
                <div class="text-[11px] text-gray-500 mt-0.5">Complétez les éléments ci-dessous</div>
            @endif
        </div>
    </div>

    {{-- Barre de progression --}}
    <div class="w-full bg-gray-100 rounded-full h-1.5 mb-4 overflow-hidden">
        <div class="h-1.5 rounded-full bg-[#647a0b] transition-all duration-300"
             style="width: {{ $step1Completion }}%;"></div>
    </div>

    {{-- Checklist basée sur les champs company_* + about + profile_description + services --}}
    <ul class="space-y-2 text-xs text-gray-600 mb-5">
        @php $ok = $step1Checks['company_name'] ?? false; @endphp
        <li class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[11px]
                         {{ $ok ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                {{ $ok ? '✓' : '•' }}
            </span>
            <span>Nom de votre activité / structure renseigné</span>
        </li>

        @php $ok = $step1Checks['company_address'] ?? false; @endphp
        <li class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[11px]
                         {{ $ok ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                {{ $ok ? '✓' : '•' }}
            </span>
            <span>Adresse professionnelle complétée</span>
        </li>

        @php $ok = $step1Checks['company_email'] ?? false; @endphp
        <li class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[11px]
                         {{ $ok ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                {{ $ok ? '✓' : '•' }}
            </span>
            <span>Email professionnel renseigné</span>
        </li>

        @php $ok = $step1Checks['company_phone'] ?? false; @endphp
        <li class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[11px]
                         {{ $ok ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                {{ $ok ? '✓' : '•' }}
            </span>
            <span>Téléphone professionnel renseigné</span>
        </li>

        @php $ok = $step1Checks['about'] ?? false; @endphp
        <li class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[11px]
                         {{ $ok ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                {{ $ok ? '✓' : '•' }}
            </span>
            <span>Texte “À propos” complété</span>
        </li>

        @php $ok = $step1Checks['profile_description'] ?? false; @endphp
        <li class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[11px]
                         {{ $ok ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                {{ $ok ? '✓' : '•' }}
            </span>
            <span>Description de profil remplie</span>
        </li>

        @php $ok = $step1Checks['services'] ?? false; @endphp
        <li class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[11px]
                         {{ $ok ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                {{ $ok ? '✓' : '•' }}
            </span>
            <span>Services / accompagnements principaux ajoutés</span>
        </li>
    </ul>

    {{-- CTA --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('profile.editCompanyInfo') }}"
           class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold rounded-lg
                  bg-[#647a0b] text-white hover:bg-[#8ea633] transition-colors">
            Compléter mes informations professionnelles
        </a>
    </div>
</div>

{{-- resources/views/mobile/therapists/results.blade.php --}}
@php
    // === SEO TITLE / DESCRIPTION ===
    if (isset($specialty) && isset($region)) {
        $pageTitle = "Résultats : " . ucfirst(str_replace('-', ' ', $specialty)) . " en " . ucfirst(str_replace('-', ' ', $region));
        $pageDescription = "Trouvez un(e) " . ucfirst(str_replace('-', ' ', $specialty)) .
            " en " . ucfirst(str_replace('-', ' ', $region)) . " sur AromaMade.";
    } elseif (isset($specialty)) {
        $pageTitle = "Résultats : " . ucfirst(str_replace('-', ' ', $specialty));
        $pageDescription = "Découvrez les praticiens spécialisés en " . ucfirst(str_replace('-', ' ', $specialty)) . ".";
    } elseif (isset($region)) {
        $pageTitle = "Résultats en " . ucfirst(str_replace('-', ' ', $region));
        $pageDescription = "Recherchez des praticiens en " . ucfirst(str_replace('-', ' ', $region)) . ".";
    } else {
        $pageTitle = "Résultats de recherche | AromaMade";
        $pageDescription = "Trouvez des praticiens en médecines douces sur AromaMade.";
    }

    // === HEADING ===
    if (isset($specialty) && isset($region)) {
        $heading = "Les meilleurs " . ucfirst(str_replace('-', ' ', $specialty)) .
            " en " . ucfirst(str_replace('-', ' ', $region));
    } elseif (isset($specialty)) {
        $heading = "Les meilleurs " . ucfirst(str_replace('-', ' ', $specialty));
    } elseif (isset($region)) {
        $heading = "Praticiens en " . ucfirst(str_replace('-', ' ', $region));
    } else {
        $heading = "Résultats de votre recherche";
    }

    $count = $therapists->count();
@endphp

<x-mobile-layout :title="$pageTitle">

    @section('title', $pageTitle)
    @section('meta_description', $pageDescription)

    <div
        class="min-h-screen flex flex-col px-5 py-6"
        style="background: radial-gradient(circle at top, #fffaf3 0, #f7f4ec 40%, #eee7dc 100%);"
    >
        <div class="w-full max-w-lg mx-auto space-y-7">

            {{-- ───────────────────── TOP BAR ───────────────────── --}}
            <div class="flex items-center justify-between">
                <a
                    href="{{ route('mobile.therapists.index') }}"
                    class="inline-flex items-center gap-1.5 text-base text-gray-700 font-medium"
                >
                    <i class="fas fa-chevron-left text-sm"></i>
                    <span class="break-words">{{ __('Modifier la recherche') }}</span>
                </a>

                <span class="text-sm text-gray-500 font-medium">
                    {{ $count }} {{ \Illuminate\Support\Str::plural('praticien', $count) }}
                </span>
            </div>

            {{-- ───────────────────── HEADER / CONTEXT ───────────────────── --}}
            <div class="text-left space-y-3">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-semibold max-w-full break-words"
                >
                    <i class="fas fa-map-marker-alt mr-2 text-[10px]"></i>

                    @if(isset($specialty) || isset($region))
                        <span class="break-words">
                            {{ trim(($specialty ?? __('Toutes spécialités')) . ' • ' . ($region ?? __('Tous lieux'))) }}
                        </span>
                    @else
                        <span class="break-words">
                            {{ __('Toutes spécialités • Tous lieux') }}
                        </span>
                    @endif
                </span>

                <h1 class="text-2xl font-extrabold text-[#647a0b] leading-snug break-words">
                    {{ $heading }}
                </h1>

                <p class="text-base text-gray-700 leading-relaxed break-words">
                    @if ($count > 0)
                        {{ __('Parcourez les profils, découvrez les spécialités et réservez en quelques taps.') }}
                    @else
                        {{ __('Aucun résultat ne correspond à votre recherche. Essayez d’élargir les filtres.') }}
                    @endif
                </p>
            </div>

            {{-- ───────────────────── RESULTS LIST ───────────────────── --}}
            <div class="space-y-5 pb-6">
                @forelse($therapists as $therapist)

                    <x-ts-card class="rounded-3xl shadow-lg border border-primary-100 px-4 py-4 bg-white/95">

                        <div class="flex items-start gap-4">

                            {{-- AVATAR --}}
                            <div class="shrink-0">
                                <div class="w-20 h-20 rounded-3xl overflow-hidden bg-primary-50 border border-primary-100 flex items-center justify-center">
                                    @php
                                        $avatar = $therapist->profile_picture
                                            ? asset('storage/' . $therapist->profile_picture)
                                            : null;
                                    @endphp

                                    @if($avatar)
                                        <img
                                            src="{{ $avatar }}"
                                            alt="{{ $therapist->name }}"
                                            class="w-full h-full object-cover"
                                        >
                                    @else
                                        <span class="text-xl font-bold text-primary-700">
                                            {{ mb_substr($therapist->name ?? 'A', 0, 1) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- MAIN INFO --}}
                            <div class="flex-1 min-w-0 space-y-2">

                                {{-- Name + badge --}}
                                <div class="flex items-start gap-2">
                                    <div class="flex-1 min-w-0 space-y-1">
                                        <div class="text-lg font-semibold text-gray-900 leading-snug break-words">
                                            {{ $therapist->name }}
                                        </div>

                                        @if($therapist->company_name)
                                            <div class="text-sm text-gray-700 break-words">
                                                {{ $therapist->company_name }}
                                            </div>
                                        @endif

                                        @if(!empty($therapist->profile_description))
                                            <div class="text-sm text-[#854f38] italic leading-snug break-words">
                                                {{ $therapist->profile_description }}
                                            </div>
                                        @endif
                                    </div>

                                    @if($therapist->verified ?? false)
                                        <span class="shrink-0 inline-flex items-center px-2 py-[4px]
                                                     rounded-full bg-green-50 text-green-700 text-xs font-semibold">
                                            <i class="fas fa-shield-check mr-1 text-[10px]"></i>
                                            {{ __('Vérifié') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- LOCATION --}}
                                @if($therapist->city_setByAdmin || $therapist->state_setByAdmin)
                                    <div class="flex items-center gap-1.5 text-sm text-gray-600 mt-1">
                                        <i class="fas fa-map-marker-alt text-[11px] text-secondary-600"></i>
                                        <span class="break-words">
                                            {{ $therapist->city_setByAdmin }}
                                            @if($therapist->state_setByAdmin)
                                                – {{ $therapist->state_setByAdmin }}
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                {{-- SERVICES --}}
                                @php
                                    $services = is_array($therapist->services)
                                        ? $therapist->services
                                        : json_decode($therapist->services, true);
                                @endphp

                                @if(!empty($services))
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach(array_slice($services, 0, 3) as $service)
                                            <span class="inline-flex items-center px-3 py-[4px]
                                                         rounded-full bg-primary-50 text-primary-700
                                                         text-xs font-medium max-w-full break-words">
                                                {{ $service }}
                                            </span>
                                        @endforeach

                                        @if(count($services) > 3)
                                            <span class="inline-flex items-center px-2 py-[3px]
                                                         rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                                                +{{ count($services) - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- ABOUT --}}
                                @php
                                    $aboutPlain = strip_tags($therapist->about ?? '');
                                    $aboutPreview = $aboutPlain !== ''
                                        ? \Illuminate\Support\Str::limit($aboutPlain, 180)
                                        : __('Informations à venir.');
                                @endphp

                                <p class="mt-2 text-sm text-gray-700 leading-snug break-words">
                                    {{ $aboutPreview }}
                                </p>

                                {{-- FOOTER ROW --}}
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                        <i class="fas fa-comment-dots text-[11px] text-secondary-500"></i>
                                        @php
                                            $testimonialsCount = $therapist->testimonials()->count();
                                        @endphp
                                        <span class="break-words">
                                            {{ $testimonialsCount }} {{ \Illuminate\Support\Str::plural('avis', $testimonialsCount) }}
                                        </span>
                                    </div>

                                    <x-ts-button
                                        tag="a"
                                        href="{{ route('mobile.therapists.show', $therapist->slug) }}"
                                        size="sm"
                                        rounded
                                        class="!text-sm !px-4 !py-2 !bg-[#647a0b] !border-0 !text-white hover:!bg-[#8ea633]"
                                    >
                                        {{ __('Voir le profil') }}
                                        <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                                    </x-ts-button>
                                </div>

                            </div>
                        </div>
                    </x-ts-card>

                @empty

                    {{-- NO RESULTS --}}
                    <x-ts-card class="rounded-2xl bg-white/90 text-center py-8 border border-secondary-100">
                        <div class="space-y-3">
                            <div class="inline-flex items-center justify-center
                                        w-12 h-12 rounded-2xl bg-secondary-50 mx-auto">
                                <i class="fas fa-search-minus text-secondary-700 text-lg"></i>
                            </div>

                            <p class="text-base font-semibold text-gray-800">
                                {{ __('Aucun praticien trouvé') }}
                            </p>

                            <p class="text-sm text-gray-600 px-5 leading-relaxed break-words">
                                {{ __('Essayez de modifier la spécialité, le lieu ou réduisez les mots-clés.') }}
                            </p>

                            <x-ts-button
                                tag="a"
                                href="{{ route('mobile.therapists.index') }}"
                                size="sm"
                                rounded
                                class="!text-sm !px-5 !py-2 !bg-[#647a0b] !text-white hover:!bg-[#8ea633]"
                            >
                                {{ __('Revenir à la recherche') }}
                            </x-ts-button>
                        </div>
                    </x-ts-card>

                @endforelse
            </div>

            {{-- FOOTER HINT --}}
            @if($count > 0)
                <p class="text-sm text-gray-500 text-center leading-relaxed px-4 break-words">
                    {{ __('Vous pouvez ajuster vos critères à tout moment via “Modifier la recherche”.') }}
                </p>
            @endif
        </div>
    </div>

</x-mobile-layout>

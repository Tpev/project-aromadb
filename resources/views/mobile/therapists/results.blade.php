{{-- resources/views/mobile/therapists/results.blade.php --}}
@php
    // Dynamic SEO text for the mobile page
    if (isset($specialty) && isset($region)) {
        $pageTitle = "Résultats : " . ucfirst(str_replace('-', ' ', $specialty)) . " en " . ucfirst(str_replace('-', ' ', $region));
        $pageDescription = "Trouvez un(e) " . ucfirst(str_replace('-', ' ', $specialty)) . " en " . ucfirst(str_replace('-', ' ', $region)) . " sur AromaMade.";
    } elseif (isset($specialty)) {
        $pageTitle = "Résultats : " . ucfirst(str_replace('-', ' ', $specialty));
        $pageDescription = "Découvrez les praticiens spécialisés en " . ucfirst(str_replace('-', ' ', $specialty)) . " sur AromaMade.";
    } elseif (isset($region)) {
        $pageTitle = "Résultats en " . ucfirst(str_replace('-', ' ', $region));
        $pageDescription = "Recherchez des praticiens en " . ucfirst(str_replace('-', ' ', $region)) . " sur AromaMade.";
    } else {
        $pageTitle = "Résultats de recherche | AromaMade";
        $pageDescription = "Trouvez des praticiens en médecines douces sur AromaMade.";
    }

    if (isset($specialty) && isset($region)) {
        $heading = "Les meilleurs " . ucfirst(str_replace('-', ' ', $specialty)) . " en " . ucfirst(str_replace('-', ' ', $region));
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

            {{-- Top bar: back + summary --}}
            <div class="flex items-center justify-between">
                <a
                    href="{{ route('mobile.therapists.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-700"
                >
                    <i class="fas fa-chevron-left text-xs"></i>
                    <span>{{ __('Modifier la recherche') }}</span>
                </a>

                <span class="text-xs text-gray-500">
                    {{ $count }} {{ \Illuminate\Support\Str::plural('praticien', $count) }}
                </span>
            </div>

            {{-- Header / context --}}
            <div class="text-left space-y-3">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700"
                >
                    <i class="fas fa-map-marker-alt mr-1.5 text-[11px]"></i>
                    @if(isset($specialty) || isset($region))
                        {{ trim(($specialty ?? __('Toutes spécialités')) . ' • ' . ($region ?? __('Tous lieux'))) }}
                    @else
                        {{ __('Toutes spécialités • Tous lieux') }}
                    @endif
                </span>

                <h1 class="text-xl font-extrabold text-[#647a0b] leading-snug">
                    {{ $heading }}
                </h1>

                <p class="text-sm text-gray-700 leading-relaxed">
                    @if($count > 0)
                        {{ __('Parcourez les profils, consultez les informations essentielles et réservez en quelques taps.') }}
                    @else
                        {{ __('Aucun résultat ne correspond exactement à votre recherche. Essayez d’élargir les filtres (lieu ou spécialité).') }}
                    @endif
                </p>
            </div>

            {{-- Results list --}}
            <div class="space-y-5 pb-4">
                @forelse($therapists as $therapist)
                    <x-ts-card class="rounded-3xl shadow-lg border border-primary-100 px-4 py-4 bg-white/95">
                        <div class="flex items-start gap-4">
                            {{-- Avatar (bigger) --}}
                            <div class="shrink-0">
                                <div class="w-16 h-16 rounded-2xl overflow-hidden bg-primary-50 flex items-center justify-center border border-primary-100">
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
                                        <span class="text-lg font-semibold text-primary-700">
                                            {{ mb_substr($therapist->name ?? 'A', 0, 1) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Main info --}}
                            <div class="flex-1 space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="space-y-1">
                                        <div class="text-base font-semibold text-gray-900 leading-tight break-words">
                                            {{ $therapist->name }}
                                        </div>

                                        @if($therapist->company_name)
                                            <div class="text-sm text-gray-700 break-words">
                                                {{ $therapist->company_name }}
                                            </div>
                                        @endif

                                        {{-- Profile description (specialty / tagline) --}}
                                        @if(!empty($therapist->profile_description))
                                            <div class="text-sm text-[#854f38] italic leading-snug break-words max-h-12 overflow-hidden">
                                                {{ $therapist->profile_description }}
                                            </div>
                                        @endif
                                    </div>

                                    @if($therapist->verified ?? false)
                                        <span class="inline-flex items-center px-2 py-[3px] rounded-full bg-green-50 text-[11px] text-green-700 font-medium">
                                            <i class="fas fa-shield-check mr-1 text-[10px]"></i>
                                            {{ __('Vérifié') }}
                                        </span>
                                    @endif
                                </div>

                                @if($therapist->city_setByAdmin || $therapist->state_setByAdmin)
                                    <div class="flex items-center gap-1.5 text-xs text-gray-600 mt-1">
                                        <i class="fas fa-map-marker-alt text-[11px] text-secondary-600"></i>
                                        <span class="break-words">
                                            {{ $therapist->city_setByAdmin }}
                                            @if($therapist->state_setByAdmin)
                                                – {{ $therapist->state_setByAdmin }}
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                {{-- Services / spécialités --}}
                                @php
                                    $services = null;
                                    if (isset($therapist->services)) {
                                        if (is_array($therapist->services)) {
                                            $services = $therapist->services;
                                        } else {
                                            $decoded = json_decode($therapist->services, true);
                                            $services = is_array($decoded) ? $decoded : null;
                                        }
                                    }
                                @endphp

                                @if(!empty($services))
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach(array_slice($services, 0, 3) as $service)
                                            <span class="inline-flex items-center px-2 py-[3px] rounded-full bg-primary-50 text-primary-700 text-[11px] font-medium break-words">
                                                {{ $service }}
                                            </span>
                                        @endforeach

                                        @if(count($services) > 3)
                                            <span class="inline-flex items-center px-2 py-[3px] rounded-full bg-gray-100 text-gray-600 text-[11px] font-medium">
                                                +{{ count($services) - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- About preview --}}
                                @php
                                    $aboutPlain = strip_tags($therapist->about ?? '');
                                    $aboutPreview = $aboutPlain !== ''
                                        ? \Illuminate\Support\Str::limit($aboutPlain, 160)
                                        : __('Informations à venir pour ce praticien.');
                                @endphp

                                <p class="mt-2 text-sm text-gray-700 leading-snug break-words max-h-20 overflow-hidden">
                                    {{ $aboutPreview }}
                                </p>

                                {{-- Bottom row: testimonials + CTA --}}
                                <div class="mt-3 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                        <i class="fas fa-comment-dots text-[11px] text-secondary-500"></i>
                                        <span>
                                            @php
                                                $testimonialsCount = method_exists($therapist, 'testimonials')
                                                    ? $therapist->testimonials()->count()
                                                    : 0;
                                            @endphp
                                            {{ $testimonialsCount }} {{ \Illuminate\Support\Str::plural('avis', $testimonialsCount) }}
                                        </span>
                                    </div>

                                    <x-ts-button
                                        tag="a"
                                        href="{{ route('mobile.therapists.show', $therapist->slug) }}"
                                        size="sm"
                                        rounded
                                        class="!text-xs !px-3 !py-1.5 !bg-[#647a0b] !border-0 !text-white hover:!bg-[#8ea633]"
                                    >
                                        {{ __('Voir le profil') }}
                                        <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                                    </x-ts-button>
                                </div>
                            </div>
                        </div>
                    </x-ts-card>
                @empty
                    <x-ts-card class="rounded-2xl bg-white/90 text-center py-7 border border-secondary-100">
                        <div class="space-y-3">
                            <div class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-secondary-50 mx-auto">
                                <i class="fas fa-search-minus text-secondary-700 text-sm"></i>
                            </div>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ __('Aucun praticien trouvé') }}
                            </p>
                            <p class="text-xs text-gray-600 px-4 leading-relaxed">
                                {{ __('Essayez de modifier la spécialité, le lieu ou de saisir moins de mots-clés pour élargir la recherche.') }}
                            </p>

                            <div class="pt-1">
                                <x-ts-button
                                    tag="a"
                                    href="{{ route('mobile.therapists.index') }}"
                                    size="sm"
                                    rounded
                                    class="!text-xs !px-4 !py-1.5 !bg-[#647a0b] !border-0 !text-white hover:!bg-[#8ea633]"
                                >
                                    {{ __('Revenir à la recherche') }}
                                </x-ts-button>
                            </div>
                        </div>
                    </x-ts-card>
                @endforelse
            </div>

            {{-- Small footer hint --}}
            @if($count > 0)
                <p class="text-[11px] text-gray-500 text-center leading-relaxed px-4 mt-1">
                    {{ __('Vous pourrez toujours ajuster vos critères depuis le bouton “Modifier la recherche”.') }}
                </p>
            @endif
        </div>
    </div>
</x-mobile-layout>

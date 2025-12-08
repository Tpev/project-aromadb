{{-- resources/views/public/therapist/show.blade.php --}}
<x-app-layout>
    {{-- En-tête de la page --}}
    <x-slot name="header">

@once
    @if ($therapist->banner)
        <link rel="preload"
              as="image"
              href="{{ asset("storage/banners/{$therapist->id}/banner-1280.webp") }}"
              imagesrcset="
                  {{ asset("storage/banners/{$therapist->id}/banner-1280.webp") }} 1280w,
                  {{ asset("storage/banners/{$therapist->id}/banner-1920.webp") }} 1920w"
              imagesizes="100vw">
    @endif
@endonce
    </x-slot>
{{-- ─────────────── SEO PAGE TITLE (uses services) ─────────────── --}}
@php
    // 1) Location bits
    $city  = trim($therapist->city_setByAdmin  ?? '');
    $state = trim($therapist->state_setByAdmin ?? '');
    $loc   = $city ? "à $city" . ($state ? ", $state" : '')
                   : ($state ? "en $state" : '');

    // 2) Services list (decode JSON → array, grab first 2 unique names)
    $servicesRaw = json_decode($therapist->services, true) ?? [];
    $servicesArr = is_array($servicesRaw) ? array_filter($servicesRaw) : [];
    $services    = collect($servicesArr)->unique()->take(2)->implode(', ');

    // 3) Fallback label
    $label = $services ?: 'Thérapeute';

    // 4) Assemble final title (max ~60 chars)
    $brand = config('app.name', 'AromaMade');
    $title = \Illuminate\Support\Str::limit(
                trim(sprintf('%s • %s | %s', $label, $loc ?: __('près de chez vous'), $brand)),
                60,
                '…'
            );
@endphp
@php
    $imgVer = $therapist->updated_at?->timestamp ?? time();
@endphp

{{-- ───────── META DESCRIPTION (uses services list) ───────── --}}
@php
    // 1) Build location fragment
    $city  = trim($therapist->city_setByAdmin  ?? '');
    $state = trim($therapist->state_setByAdmin ?? '');
    $location = $city
        ? ($state ? "$city, $state" : $city)
        : ($state ?: __('votre région'));

    // 2) Extract up to three unique service names
    $servicesRaw = json_decode($therapist->services, true) ?? [];
    $servicesArr = is_array($servicesRaw) ? array_filter($servicesRaw) : [];
    $services    = collect($servicesArr)->unique()->take(3)->implode(', ');

    // 3) Fallback label
    $label = $services ?: 'Thérapeute';

    // 4) Trimmed “À propos” snippet
    $aboutSnippet = \Illuminate\Support\Str::limit(strip_tags($therapist->about), 120);

    // 5) Compose final meta sentence (~155 chars)
    $meta = \Illuminate\Support\Str::limit(
                trim(sprintf(
                    '%s – %s à %s. %s',
                    $therapist->business_name ?? $therapist->company_name,
                    $label,
                    $location,
                    $aboutSnippet
                )),
                155,
                '…'
            );
@endphp

@section('meta_description', $meta)


    {{-- Contenu principal --}}
<div>
        <div>
            @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
        {{ session('success') }}
    </div>
@endif

{{-- FULL-WIDTH HERO – CLS 0.00 -------------------------------------------- --}}
<section class="relative overflow-hidden isolate">

    {{-- optional banner (painted once size is known) ----------------------- --}}
    @if ($therapist->banner)
        <picture class="absolute inset-0 -z-10">
            <source type="image/webp"
                    srcset="{{ asset("storage/banners/{$therapist->id}/banner-1280.webp") }} 1280w,
                            {{ asset("storage/banners/{$therapist->id}/banner-1920.webp") }} 1920w"
                    sizes="100vw">
            <img  src="{{ asset("storage/banners/{$therapist->id}/banner-1280.webp") }}"
                  width="1920" height="720"
                  class="w-full h-full object-cover opacity-30"
                  alt="">
        </picture>
    @endif

    {{-- green overlay – spans full viewport width ------------------------- --}}
    <div class="bg-[#8ea633]/90 backdrop-blur-sm text-white shadow-lg w-full">

        {{-- FLEX container -------------------------------------------------- --}}
        <div class="max-w-7xl mx-auto px-6
                    flex flex-col md:flex-row items-center gap-10
                    py-12 md:py-20
                    min-h-[380px] sm:min-h-[420px]">

            {{-- Avatar ------------------------------------------------------ --}}
            <div class="shrink-0">
                @if ($therapist->profile_picture)
<img  src="{{ asset("storage/avatars/{$therapist->id}/avatar-320.webp") }}?v={{ $imgVer }}"
      srcset="{{ asset("storage/avatars/{$therapist->id}/avatar-320.webp") }}?v={{ $imgVer }} 320w,
              {{ asset("storage/avatars/{$therapist->id}/avatar-640.webp") }}?v={{ $imgVer }} 640w,
              {{ asset("storage/avatars/{$therapist->id}/avatar-1024.webp") }}?v={{ $imgVer }} 1024w"
      sizes="(min-width: 768px) 224px, 192px"
      width="224" height="224"
      class="block w-48 h-48 md:w-56 md:h-56 rounded-full object-cover
             ring-4 ring-white shadow-md"
      alt="{{ __('Photo de Profil') }}"
      fetchpriority="high"
      loading="eager" decoding="async">

                @else
                    <div class="w-48 h-48 md:w-56 md:h-56 rounded-full bg-white flex items-center
                                justify-center text-[#8ea633] text-4xl font-bold ring-4 ring-white
                                select-none">
                        {{ strtoupper(substr($therapist->company_name, 0, 1)) }}
                    </div>
                @endif
            </div>

            {{-- Copy & CTAs -------------------------------------------------- --}}
            <div class="text-center md:text-left max-w-2xl">
                <h1 class="text-3xl md:text-5xl font-extrabold leading-tight tracking-tight break-words">
                    {{ $therapist->company_name }}
                </h1>

                @if ($therapist->profile_description)
                    <p class="mt-4 text-xl leading-relaxed">
                        {{ $therapist->profile_description }}
                    </p>
                @endif

                @if ($therapist->accept_online_appointments)
                    <nav aria-label="{{ __('Liens de prise de contact') }}"
                         class="mt-8 flex flex-wrap md:flex-nowrap gap-4
                                justify-center md:justify-start">

                        <a  href="{{ route('appointments.createPatient', $therapist->id) }}"
                            class="inline-block whitespace-nowrap bg-white text-[#8ea633] font-semibold
                                   text-lg px-8 py-3 rounded-full hover:bg-[#e8f0d8]
                                   transition-colors duration-200">
                            {{ __('Prendre Rendez-vous') }}
                        </a>

                        <button type="button"
                                class="inline-block whitespace-nowrap bg-[#854f38] text-white font-semibold
                                       text-lg px-8 py-3 rounded-full hover:bg-[#6a3f2c]
                                       transition-colors duration-200"
                                x-data
                                x-on:click.prevent="$dispatch('open-request-modal')">
                            {{ __('Demander des informations') }}
                        </button>

                        <a  href="{{ route('client.login') }}"
                            class="inline-block whitespace-nowrap bg-white text-[#8ea633] font-semibold
                                   text-lg px-8 py-3 rounded-full hover:bg-[#e8f0d8]
                                   transition-colors duration-200">
                            {{ __('Accès Client') }}
                        </a>
                    </nav>
                @endif
            </div>
        </div>
    </div>
</section>





{{-- STICKY CTA BAR – CLS-SAFE ───────────────────────────────--}}
@php
    // bar height = 56 px (14 * 4) → Tailwind h-14
    $barH = 'h-14';
@endphp

{{-- 1. Desktop placeholder – keeps layout stable --}}
<div x-data="{show:false}"
     x-init="window.addEventListener('scroll', ()=>show = window.scrollY > 450)"
     :class="show ? '{{ $barH }}' : 'h-0'"
     class="hidden md:block w-full transition-all duration-300"></div>

{{-- 2. Real bar – fixed, fades in/out --}}
<div x-data="{show:false}"
     x-init="window.addEventListener('scroll', ()=>show = window.scrollY > 450)"
     x-show="show"
     x-transition.opacity.duration.300ms
     class="fixed inset-x-0
            bottom-0 md:top-0 md:bottom-auto
            z-40 bg-[#8ea633] text-white {{ $barH }}
            shadow-lg flex items-center">

    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between w-full">
        <span class="font-medium truncate">{{ $therapist->company_name }}</span>

        <div class="flex gap-3">
            <a href="{{ route('appointments.createPatient', $therapist->id) }}"
               class="bg-white text-[#8ea633] font-semibold px-5 py-2 rounded-full hover:bg-[#e8f0d8]">
                {{ __('Prendre Rendez-vous') }}
            </a>
            <button type="button"
                    class="hidden md:inline bg-[#854f38] hover:bg-[#6a3f2c] px-5 py-2 rounded-full"
                    x-on:click="$dispatch('open-request-modal')">
                {{ __('Infos') }}
            </button>
        </div>
    </div>
</div>

{{-- À PROPOS + CONTACT (two-column band) -------------------------------- --}}
<section class="bg-[#f9fafb] shadow rounded-lg p-8">
    <div class="grid md:grid-cols-3 gap-12">
        {{-- Column 1-2 : À Propos --}}
        <div class="md:col-span-2">
            <h3 class="text-3xl font-semibold text-[#647a0b] flex items-center">
                <i class="fas fa-info-circle text-[#854f38] mr-3"></i> {{ __('À Propos') }}
            </h3>

            <article class="mt-6 text-gray-700 text-lg leading-relaxed prose max-w-none">
                {!! $therapist->about ?? __('Informations à propos non disponibles.') !!}
            </article>
        </div>

        {{-- Column 3 : Contact --}}
        <aside>
            <h3 class="text-3xl font-semibold text-[#647a0b] flex items-center">
                <i class="fas fa-address-book text-[#854f38] mr-3"></i> {{ __('Contact') }}
            </h3>

            <ul class="mt-6 space-y-6">
                @if ($therapist->share_address_publicly)
                    <li class="flex items-start">
                        <i class="fas fa-map-marker-alt text-2xl text-[#854f38] mr-4 mt-1"></i>
                        <div>
                            <h4 class="text-xl font-semibold text-[#647a0b]">{{ __('Adresse') }}</h4>
                            <p class="text-gray-700 mt-2">
                                {{ $therapist->company_address ?? __('Adresse non disponible.') }}
                            </p>
                        </div>
                    </li>
                @endif

                @if ($therapist->share_phone_publicly)
                    <li class="flex items-start">
                        <i class="fas fa-phone-alt text-2xl text-[#854f38] mr-4 mt-1"></i>
                        <div>
                            <h4 class="text-xl font-semibold text-[#647a0b]">{{ __('Téléphone') }}</h4>
                            <p class="text-gray-700 mt-2">
                                {{ $therapist->company_phone ?? __('Téléphone non disponible.') }}
                            </p>
                        </div>
                    </li>
                @endif

                @if ($therapist->share_email_publicly)
                    <li class="flex items-start">
                        <i class="fas fa-envelope text-2xl text-[#854f38] mr-4 mt-1"></i>
                        <div>
                            <h4 class="text-xl font-semibold text-[#647a0b]">{{ __('Email') }}</h4>
                            <p class="text-gray-700 mt-2">
                                <a href="mailto:{{ $therapist->company_email }}"
                                   class="text-[#854f38] hover:text-[#6a3f2c]">
                                    {{ $therapist->company_email }}
                                </a>
                            </p>
                        </div>
                    </li>
                @endif

                @if ($therapist->cgv_pdf_path)
                    <li class="flex items-start">
                        <i class="fas fa-file-pdf text-2xl text-[#854f38] mr-4 mt-1"></i>
                        <div>
                            <h4 class="text-xl font-semibold text-[#647a0b]">
                                {{ __('Conditions Générales de Vente') }}
                            </h4>
                            <p class="text-gray-700 mt-2">
                                <a href="{{ asset('storage/' . $therapist->cgv_pdf_path) }}"
                                   target="_blank"
                                   rel="noopener"
                                   class="text-[#854f38] hover:text-[#6a3f2c] underline">
                                    {{ __('Consulter les CGV du praticien') }}
                                </a>
                            </p>
                        </div>
                    </li>
                @endif

            </ul>
        </aside>
    </div>
</section>


            {{-- Section Services --}}
            <div class="bg-white shadow rounded-lg p-8">
                <h3 class="text-3xl font-semibold text-[#647a0b] flex items-center">
                    <i class="fas fa-concierge-bell text-[#854f38] mr-3"></i> {{ __('Services') }}
                </h3>
                
                {{-- Decode services JSON string into an array --}}
                @php
                    $services = json_decode($therapist->services, true) ?? [];
                @endphp

                @if(is_array($services) && count($services) > 0)
                    <div class="mt-6 flex flex-wrap gap-3">
                        @foreach($services as $service)
                            <span class="bg-[#647a0b] text-white px-4 py-2 rounded-full text-sm font-medium service-tag">
                                {{ $service }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="mt-6 text-gray-600">{{ __('Aucun service spécifié.') }}</p>
                @endif
            </div>



{{-- Section Prestations --}}
<div class="bg-white shadow rounded-lg p-8">
    <h3 class="text-3xl font-semibold text-[#854f38] flex items-center">
        <i class="fas fa-spa text-[#854f38] mr-3"></i> {{ __('Prestations') }}
    </h3>

    @php
        // Legacy-safe visibility: show if null or true; hide only if explicitly false
        $visiblePrestations = $prestations->filter(function ($p) {
            return $p->visible_in_portal !== false;
        });

        // On groupe par nom pour fusionner les différents "modes" d'une même prestation
        $groupedPrestations = $visiblePrestations->groupBy('name');
    @endphp

    @if($groupedPrestations->count() > 0)
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($groupedPrestations as $name => $group)
                @php
                    /** @var \App\Models\Product $prestation */
                    $prestation = $group->first(); // on prend le 1er comme "référence"

                    $truncatedDescription = \Illuminate\Support\Str::limit($prestation->description, 200);

                    // On agrège les modes sur l'ensemble du groupe
                    $hasCabinet = $group->contains(function ($p) {
                        return (bool) $p->dans_le_cabinet;
                    });
                    $hasDomicile = $group->contains(function ($p) {
                        return (bool) $p->adomicile;
                    });
                    $hasVisio = $group->contains(function ($p) {
                        return (bool) $p->visio;
                    });

                    // Prépare les badges de lieu à partir des flags agrégés
                    $locationBadges = [];
                    if ($hasCabinet) {
                        $locationBadges[] = ['📍', __('Cabinet')];
                    }
                    if ($hasDomicile) {
                        $locationBadges[] = ['🏠', __('À domicile')];
                    }
                    if ($hasVisio) {
                        $locationBadges[] = ['💻', __('Visio')];
                    }

                    // Paiement en ligne possible (basé sur au moins une variante qui le permet)
                    $canCollectOnline = $group->contains(function ($p) {
                        return (bool) $p->collect_payment;
                    });
                @endphp

                <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 prestation-item bg-[#f9fafb]">
                    @if($prestation->image)
                        <img src="{{ asset('storage/' . $prestation->image) }}" alt="{{ $prestation->name }}" class="w-full h-48 object-cover">
                    @endif
                    <div class="p-6">
                        <h4 class="text-2xl font-semibold text-[#647a0b]">{{ $prestation->name }}</h4>

                        {{-- Ligne de badges : lieux, durée, paiement en ligne --}}
                        @if(
                            count($locationBadges) > 0
                            || !is_null($prestation->duration)
                            || $canCollectOnline
                        )
                            <div class="mt-3 flex flex-wrap gap-2 text-xs sm:text-sm">
                                @foreach($locationBadges as [$icon, $label])
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-white border border-[#e4e8d5] text-[#647a0b]">
                                        <span class="mr-1">{{ $icon }}</span> {{ $label }}
                                    </span>
                                @endforeach

                                @if(!is_null($prestation->duration))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-white border border-[#e4e8d5] text-gray-700">
                                        <span class="mr-1">⏱</span> {{ $prestation->duration }} {{ __('min') }}
                                    </span>
                                @endif

                                @if($canCollectOnline)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-white border border-[#e4e8d5] text-[#854f38]">
                                        <span class="mr-1">💳</span> {{ __('Paiement en ligne possible') }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        @if($prestation->price_visible_in_portal && $prestation->price > 0)
                            <p class="mt-3 text-gray-600 font-semibold">
                                {{ __('Prix :') }}
                                {{ number_format($prestation->price_incl_tax ?? $prestation->price, 2, ',', ' ') }} €
                            </p>
                        @endif

                        <p class="mt-4 text-gray-700 prestation-description"
                           data-full-text="{{ e($prestation->description) }}"
                           data-truncated-text="{{ e($truncatedDescription) }}">
                            {!! nl2br(e($truncatedDescription)) !!}
                            @if(\Illuminate\Support\Str::length($prestation->description) > 200)
                                <span class="text-[#854f38] cursor-pointer voir-plus">{{ __('Voir plus') }}</span>
                            @endif
                        </p>

                        @if($prestation->brochure)
                            <a href="{{ asset('storage/' . $prestation->brochure) }}" target="_blank" class="mt-4 inline-block text-[#854f38] hover:text-[#6a3f2c]">
                                {{ __('Télécharger la brochure') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="mt-6 text-gray-600">{{ __('Aucune prestation disponible pour le moment.') }}</p>
    @endif
</div>





            {{-- Section Événements --}}
            <div class="bg-[#f9fafb] shadow rounded-lg p-8">
                <h3 class="text-3xl font-semibold text-[#854f38] flex items-center">
                    <i class="fas fa-calendar-alt text-[#854f38] mr-3"></i> {{ __('Événements à Venir') }}
                </h3>
                @if($events->count() > 0)
                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($events as $event)
                            <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 event-item bg-white">
                                @if($event->image)
                                    <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->name }}" class="w-full h-48 object-cover">
                                @endif
                                <div class="p-6">
                                    <h4 class="text-2xl font-semibold text-[#854f38]">{{ $event->name }}</h4>
                                    <p class="mt-2 text-gray-600">
                                        <i class="fas fa-calendar-alt mr-1 text-[#854f38]"></i> {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}
                                    </p>
                                    <p class="text-gray-600 mt-1">
                                        <i class="fas fa-map-marker-alt mr-1 text-[#854f38]"></i> {{ $event->location }}
                                    </p>
                                    @if($event->limited_spot)
                                        <p class="text-gray-600 mt-1">
                                            <i class="fas fa-users mr-1 text-[#854f38]"></i> {{ __('Places restantes :') }} {{ $event->number_of_spot - $event->reservations->count() }}
                                        </p>
                                    @endif
                                    
                                    @if($event->associatedProduct && $event->associatedProduct->price > 0)
                                        <p class="text-gray-600 mt-1">
                                            <i class="fas fa-tag mr-1 text-[#854f38]"></i> {{ __('Prix :') }} {{ number_format($event->associatedProduct->price_incl_tax, 2, ',', ' ') }} €
                                        </p>
                                    @endif
                                    
                                    <p class="mt-4 text-gray-700">{{ $event->description }}</p>

                                    @php
                                        $spotsLeft = $event->limited_spot ? $event->number_of_spot - $event->reservations->count() : null;

                                        // URL de réservation (page publique que l'on veut partager)
                                        $eventUrl = route('events.reserve.create', $event->id);

                                        // Texte par défaut pour le post Facebook
                                        $shareText = "Je participe à : {$event->name}";

                                        // URL du partage Facebook (click-to-share)
                                        $shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($eventUrl)
                                                  . '&quote=' . urlencode($shareText);
                                    @endphp

                                    <div class="mt-6 flex flex-wrap gap-3 items-center">
                                        @if($event->booking_required)
                                            @if(!$event->limited_spot || ($spotsLeft > 0))
                                                <a href="{{ route('events.reserve.create', $event->id) }}" class="inline-block bg-[#854f38] text-white text-sm px-6 py-2 rounded-full hover:bg-[#6a3f2c] transition-colors duration-300">
                                                    {{ __('Réserver') }}
                                                </a>
                                            @else
                                                <p class="text-red-500 font-semibold">{{ __('Complet') }}</p>
                                            @endif
                                        @endif

                                        {{-- Bouton "Partager sur Facebook" --}}
                                        <a href="{{ $shareUrl }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="inline-flex items-center bg-[#1877F2] text-white text-xs px-4 py-2 rounded-full hover:bg-[#145DBF] transition-colors duration-300">
                                            <i class="fab fa-facebook-f mr-2"></i>
                                            {{ __('Partager sur Facebook') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-6 text-gray-600">{{ __('Aucun événement à venir pour le moment.') }}</p>
                @endif
            </div>

{{-- Section Témoignages --}}
<div class="bg-white shadow rounded-lg p-8">
    <h3 class="text-3xl font-semibold text-[#647a0b] flex items-center">
        <i class="fas fa-comments text-[#854f38] mr-3"></i> {{ __('Témoignages') }}
    </h3>

    @if($testimonials->count() > 0)
        <div class="mt-8 space-y-6">
            @foreach($testimonials as $testimonial)
                @php
                    $isGoogle = $testimonial->source === 'google';

                    $author = $isGoogle
                        ? ($testimonial->reviewer_name ?? 'Client Google')
                        : optional($testimonial->clientProfile)->first_name;

                    $date = $testimonial->external_created_at
                        ? $testimonial->external_created_at->format('d/m/Y')
                        : $testimonial->created_at->format('d/m/Y');
                @endphp

                <div class="p-6 border-l-4 {{ $isGoogle ? 'border-[#8ea633]' : 'border-[#854f38]' }} bg-[#f9fafb] rounded-md">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2">
                            <p class="text-sm text-gray-800 font-semibold">
                                {{ $author ?? __('Client') }}
                            </p>

                            @if($isGoogle)
                                <span class="inline-flex items-center text-xs bg-[#e5f0c8] text-[#647a0b] px-2 py-0.5 rounded-full">
                                    <i class="fab fa-google mr-1"></i> Avis Google
                                </span>
                            @endif
                        </div>

                        @if($isGoogle && $testimonial->rating)
                            <div class="flex items-center gap-1 text-[#f6b400] text-xs">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $testimonial->rating)
                                        <i class="fas fa-star"></i>
                                    @else
                                        <i class="far fa-star text-gray-300"></i>
                                    @endif
                                @endfor
                            </div>
                        @endif
                    </div>

                    <p class="text-gray-700 italic text-lg whitespace-pre-line">
                        "{{ $testimonial->testimonial }}"
                    </p>

                    <p class="mt-4 text-sm text-gray-600">
                        {{ $date }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $testimonials->links() }}
        </div>
    @else
        <p class="mt-6 text-lg text-gray-600">
            {{ __('Les témoignages de mes clients seront bientôt disponibles ici.') }}
        </p>
    @endif
</div>


        </div>
    </div>
{{-- Modal de demande d’information --}}
<div
    x-data="{ open: false }"
    x-on:open-request-modal.window="open = true"
    x-on:close-request-modal.window="open = false"
    x-show="open"
    style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg mx-auto p-6 relative">
        {{-- Bouton pour fermer le modal --}}
        <button
            class="absolute top-3 right-3 text-gray-500 hover:text-gray-700"
            x-on:click="open = false"
        >
            <i class="fas fa-times"></i>
        </button>

        <h2 class="text-2xl font-semibold text-[#647a0b] mb-4">
            {{ __('Demande d\'information') }}
        </h2>

        {{-- Formulaire qui enverra les données à la route POST --}}
        <form method="POST" action="{{ route('therapist.sendInformationRequest', $therapist->slug) }}">
            @csrf

            <div class="mb-4">
                <label class="block font-medium text-gray-700" for="first_name">
                    {{ __('Prénom') }}
                </label>
                <input
                    type="text"
                    name="first_name"
                    id="first_name"
                    required
                    class="mt-1 w-full border border-gray-300 rounded-md p-2"
                >
            </div>

            <div class="mb-4">
                <label class="block font-medium text-gray-700" for="last_name">
                    {{ __('Nom') }}
                </label>
                <input
                    type="text"
                    name="last_name"
                    id="last_name"
                    required
                    class="mt-1 w-full border border-gray-300 rounded-md p-2"
                >
            </div>

            <div class="mb-4">
                <label class="block font-medium text-gray-700" for="email">
                    {{ __('Adresse Email') }}
                </label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    required
                    class="mt-1 w-full border border-gray-300 rounded-md p-2"
                >
            </div>

            <div class="mb-4">
			<label class="block font-medium text-gray-700" for="phone">
				{{ __('Téléphone') }}
			</label>
			<input
				type="tel"
				name="phone"
				id="phone"
				class="mt-1 w-full border border-gray-300 rounded-md p-2"
				pattern="^[0-9\-\+\(\)\s]+$"
				placeholder="0612345614"
			/>
			@error('phone')
				<p class="text-red-600 text-sm mt-1">{{ $message }}</p>
			@enderror
            </div>

            <div class="mb-4">
                <label class="block font-medium text-gray-700" for="message">
                    {{ __('Message') }}
                </label>
                <textarea
                    name="message"
                    id="message"
                    rows="4"
                    class="mt-1 w-full border border-gray-300 rounded-md p-2"
                    placeholder="{{ __('Décrivez brièvement votre demande...') }}"
                    required
                ></textarea>
            </div>
                <!-- Accept Terms & Privacy Policy -->
                <div class="mb-4">
                    <label for="terms" class="flex items-center text-sm text-gray-600">
                        <input id="terms" type="checkbox" class="form-checkbox h-4 w-4 text-[#647a0b]" name="terms" required>
                        <span class="ml-2">
                            {{ __('J\'accepte les') }} 
                            <a href="{{ route('cgu') }}" target="_blank" class="underline text-blue-600 hover:text-blue-800">
                                {{ __('Conditions Générales d’Utilisation') }}
                            </a>
                            {{ __('et la') }}
                            <a href="{{ route('privacypolicy') }}" target="_blank" class="underline text-blue-600 hover:text-blue-800">
                                {{ __('Politique de Confidentialité') }}
                            </a>.
                        </span>
                    </label>
                    <x-input-error :messages="$errors->get('terms')" class="mt-2 text-red-600" />
                </div>
            <div class="text-right">
                <button
                    type="submit"
                    class="bg-[#647a0b] text-white font-semibold py-2 px-4 rounded-full hover:bg-[#8ea633] transition-colors duration-300"
                >
                    {{ __('Envoyer') }}
                </button>
            </div>
        </form>
    </div>
	
</div>

    {{-- Styles personnalisés --}}
    @push('styles')
        <style>
            /* Styles personnalisés pour le profil du thérapeute */

            /* Service Tags */
            .service-tag {
                transition: transform 0.3s ease, background-color 0.3s ease;
                cursor: pointer;
            }
            .service-tag:hover {
                transform: translateY(-3px);
                background-color: #8ea633; /* Vert Secondaire */
            }

            /* Prestations et Événements */
            .prestation-item, .event-item {
                position: relative;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .prestation-item:hover, .event-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            }

            /* Boutons */
            .button {
                transition: background-color 0.3s ease, transform 0.3s ease;
            }
            .button:hover {
                background-color: #6a3f2c; /* Teinte plus foncée au survol */
                transform: translateY(-2px);
            }

  

            @keyframes fadeIn {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }



            /* Ajustements responsives */
            @media (max-width: 768px) {
                .md\:flex-row {
                    flex-direction: column;
                }
                .md\:mt-0 {
                    margin-top: 1.5rem;
                }
                .md\:ml-8 {
                    margin-left: 0;
                }
                .md\:text-left {
                    text-align: center;
                }
            }
	

        </style>
    @endpush

    {{-- Scripts pour les animations et la fonctionnalité "Voir plus" --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Définir les textes "Voir plus" et "Voir moins"
                const voirPlusText = '{{ __("Voir plus") }}';
                const voirMoinsText = '{{ __("Voir moins") }}';

                // Animation d'apparition
                const sections = document.querySelectorAll('.bg-white, .bg-\\[\\#f9fafb\\]');
                sections.forEach((section, index) => {
                    section.style.animationDelay = `${index * 0.2}s`;
                    section.classList.add('fade-in');
                });

                // Fonctionnalité "Voir plus" pour les Prestations
                const descriptions = document.querySelectorAll('.prestation-description');

                descriptions.forEach(function(description) {
                    description.addEventListener('click', function(event) {
                        if (event.target.classList.contains('voir-plus')) {
                            const fullText = description.getAttribute('data-full-text');
                            description.innerHTML = fullText.replace(/\n/g, '<br>') + ` <span class="text-[#854f38] cursor-pointer voir-moins">${voirMoinsText}</span>`;
                        } else if (event.target.classList.contains('voir-moins')) {
                            const truncatedText = description.getAttribute('data-truncated-text');
                            let voirPlusSpan = '';
                            if (description.getAttribute('data-full-text').length > 200) {
                                voirPlusSpan = ` <span class="text-[#854f38] cursor-pointer voir-plus">${voirPlusText}</span>`;
                            }
                            description.innerHTML = truncatedText.replace(/\n/g, '<br>') + voirPlusSpan;
                        }
                    });
                });
            });
        </script>
    @endpush
@php
    // Calcule un rating moyen seulement sur ceux qui ont une note
    $ratedTestimonials = $testimonials->filter(fn($t) => !is_null($t->rating));
    $averageRating = $ratedTestimonials->count() > 0
        ? round($ratedTestimonials->avg('rating'), 1)
        : null;

    $reviewItems = $testimonials->map(function ($t) use ($therapist) {
        $isGoogle = $t->source === 'google';
        $author   = $isGoogle
            ? ($t->reviewer_name ?? 'Client Google')
            : optional($t->clientProfile)->first_name;

        $dateIso = ($t->external_created_at ?? $t->created_at)->toAtomString();

        return [
            '@type'         => 'Review',
            'author'        => [
                '@type' => 'Person',
                'name'  => $author ?: 'Client',
            ],
            'datePublished' => $dateIso,
            'reviewBody'    => $t->testimonial,
            'reviewRating'  => $t->rating ? [
                '@type'       => 'Rating',
                'ratingValue' => $t->rating,
                'bestRating'  => 5,
                'worstRating' => 1,
            ] : null,
        ];
    })->values()->all();

    // Nettoyage : on enlève les reviewRating = null si pas de note
    $reviewItems = array_map(function ($item) {
        if (is_null($item['reviewRating'])) {
            unset($item['reviewRating']);
        }
        return $item;
    }, $reviewItems);

    // ───── Services / Offers pour schema.org ─────
    // On prend les prestations visibles sur le portail (comme dans la section Prestations)
    $visiblePrestationsForSchema = $prestations
        ->filter(fn($p) => $p->visible_in_portal !== false)
        ->unique('name')
        ->take(6); // évite un JSON énorme

    $serviceOffers = $visiblePrestationsForSchema->map(function ($p) {
        $desc = \Illuminate\Support\Str::limit(strip_tags($p->description ?? ''), 160);

        $offer = [
            '@type'       => 'Offer',
            'name'        => $p->name,
            'description' => $desc ?: null,
            'itemOffered' => [
                '@type'       => 'Service',
                'name'        => $p->name,
                'description' => $desc ?: null,
            ],
        ];

        // Prix uniquement si on a le droit de l’afficher
        if ($p->price_visible_in_portal && $p->price > 0) {
            $offer['price']         = (float) ($p->price_incl_tax ?? $p->price);
            $offer['priceCurrency'] = 'EUR';
        }

        // Nettoyage des clés null
        return array_filter($offer, fn($v) => !is_null($v));
    })->values()->all();

    // ───── Construction finale du LocalBusiness + Offers ─────
    $schemaData = [
        '@context'  => 'https://schema.org',
        '@type'     => 'LocalBusiness',
        '@id'       => url()->current(),
        'name'      => $therapist->company_name,
        'url'       => url()->current(),
        'image'     => $therapist->profile_picture
                        ? asset("storage/avatars/{$therapist->id}/avatar-640.webp")
                        : null,
        'address'   => ($therapist->share_address_publicly && $therapist->company_address)
                        ? [
                            '@type'           => 'PostalAddress',
                            'streetAddress'   => $therapist->company_address,
                            'addressLocality' => $therapist->city_setByAdmin,
                            'addressRegion'   => $therapist->state_setByAdmin,
                            'addressCountry'  => 'FR',
                          ]
                        : null,
        'telephone' => $therapist->share_phone_publicly ? $therapist->company_phone : null,
        'review'    => $reviewItems,
        'aggregateRating' => $averageRating ? [
            '@type'       => 'AggregateRating',
            'ratingValue' => $averageRating,
            'reviewCount' => $ratedTestimonials->count(),
        ] : null,
    ];

    // Ajoute les offres si on en a
    if (count($serviceOffers) > 0) {
        $schemaData['makesOffer'] = $serviceOffers;
    }

    // Nettoyage final des clés null top-level
    $schemaData = array_filter($schemaData, fn($v) => !is_null($v));
@endphp

@section('structured_data')
<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}
</script>
@endsection


</x-app-layout>

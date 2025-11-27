{{-- resources/views/mobile/therapists/show.blade.php --}}
@php
    // === LOCATION FRAGMENTS ===
    $city  = trim($therapist->city_setByAdmin  ?? '');
    $state = trim($therapist->state_setByAdmin ?? '');
    $locationLabel = $city
        ? ($state ? "$city, $state" : $city)
        : ($state ?: __('Votre région'));

    // === SERVICES LABEL FOR TITLE / META ===
    $servicesRaw = json_decode($therapist->services, true) ?? [];
    $servicesArr = is_array($servicesRaw) ? array_filter($servicesRaw) : [];
    $servicesSeo = collect($servicesArr)->unique()->take(3)->implode(', ');
    $labelSeo    = $servicesSeo ?: 'Thérapeute';

    // === PAGE TITLE (short for mobile) ===
    $brand = config('app.name', 'AromaMade');
    $pageTitle = \Illuminate\Support\Str::limit(
        trim(sprintf('%s – %s | %s', $therapist->company_name ?? $therapist->name, $labelSeo, $brand)),
        60,
        '…'
    );

    // === META DESCRIPTION ===
    $aboutSnippet = \Illuminate\Support\Str::limit(strip_tags($therapist->about ?? ''), 120);
    $meta = \Illuminate\Support\Str::limit(
        trim(sprintf(
            '%s – %s à %s. %s',
            $therapist->business_name ?? $therapist->company_name ?? $therapist->name,
            $labelSeo,
            $locationLabel,
            $aboutSnippet
        )),
        155,
        '…'
    );

    // === IMAGE VERSIONING ===
    $imgVer = $therapist->updated_at?->timestamp ?? time();

    // === RATING (GOOGLE + INTERNAL) ===
    $ratedTestimonials = $testimonials->filter(fn($t) => !is_null($t->rating));
    $averageRating = $ratedTestimonials->count() > 0
        ? round($ratedTestimonials->avg('rating'), 1)
        : null;
@endphp

<x-mobile-layout :title="$pageTitle">
    @section('title', $pageTitle)
    @section('meta_description', $meta)

    <div
        x-data="{ infoOpen: false }"
        class="min-h-screen flex flex-col px-5 py-6"
        style="background: radial-gradient(circle at top, #fffaf3 0, #f7f4ec 40%, #eee7dc 100%);"
    >
        <div class="w-full max-w-lg mx-auto space-y-6">

            {{-- Back link + rating / count --}}
            <div class="flex items-center justify-between">
                <a
                    href="{{ url()->previous() !== url()->current() ? url()->previous() : route('mobile.therapists.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-700"
                >
                    <i class="fas fa-chevron-left text-xs"></i>
                    <span>{{ __('Retour') }}</span>
                </a>

                <div class="flex items-center gap-2 text-xs text-gray-600">
                    @if($averageRating)
                        <div class="inline-flex items-center gap-1">
                            <i class="fas fa-star text-[#f6b400] text-sm"></i>
                            <span class="font-semibold">{{ $averageRating }}</span>
                        </div>
                        <span>•</span>
                    @endif
                    <span>
                        {{ $testimonials->count() }} {{ \Illuminate\Support\Str::plural('avis', $testimonials->count()) }}
                    </span>
                </div>
            </div>

            {{-- HERO CARD --}}
            <x-ts-card class="rounded-3xl shadow-xl border-0 bg-gradient-to-br from-primary-600 via-primary-500 to-secondary-600 text-white px-5 py-6">
                <div class="flex flex-col items-center text-center space-y-4">
                    {{-- Avatar --}}
                    <div class="w-24 h-24 rounded-full bg-white/10 border border-white/40 overflow-hidden flex items-center justify-center shadow-md">
                        @if ($therapist->profile_picture)
                            <img
                                src="{{ asset("storage/avatars/{$therapist->id}/avatar-320.webp") }}?v={{ $imgVer }}"
                                alt="{{ $therapist->name }}"
                                class="w-full h-full object-cover"
                            >
                        @else
                            <span class="text-2xl font-semibold">
                                {{ mb_substr($therapist->company_name ?? $therapist->name ?? 'A', 0, 1) }}
                            </span>
                        @endif
                    </div>

                    {{-- Main info --}}
                    <div class="space-y-1">
                        <h1 class="text-xl font-extrabold leading-snug break-words">
                            {{ $therapist->company_name ?? $therapist->name }}
                        </h1>

                        @if($therapist->profile_description)
                            <p class="text-sm leading-snug text-white/90 break-words">
                                {{ $therapist->profile_description }}
                            </p>
                        @endif

                        <div class="flex flex-wrap items-center justify-center gap-2 mt-1 text-xs text-white/90">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white/15">
                                <i class="fas fa-map-marker-alt mr-1 text-[11px]"></i>
                                {{ $locationLabel }}
                            </span>

                            @if(!empty($servicesSeo))
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white/15">
                                    <i class="fas fa-leaf mr-1 text-[11px]"></i>
                                    {{ $servicesSeo }}
                                </span>
                            @endif

                            @if($therapist->verified ?? false)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-green-500/20 border border-green-300/60 text-[11px]">
                                    <i class="fas fa-shield-check mr-1 text-[11px]"></i>
                                    {{ __('Praticien vérifié') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- CTAs --}}
                    @if($therapist->accept_online_appointments)
                        <div class="w-full flex flex-col gap-3 mt-2">
                            <x-ts-button
                                tag="a"
                                href="{{ route('appointments.createPatient', $therapist->id) }}"
                                class="w-full !bg-white !text-primary-700 !border-0 hover:!bg-primary-50 !text-[14px]"
                            >
                                <i class="fas fa-calendar-plus mr-2 text-xs"></i>
                                {{ __('Prendre rendez-vous') }}
                            </x-ts-button>

                            <div class="flex gap-3">
                                <x-ts-button
                                    tag="button"
                                    type="button"
                                    class="flex-1 !bg-secondary-700 !border-0 !text-[13px]"
                                    x-on:click="infoOpen = true"
                                >
                                    <i class="fas fa-envelope-open-text mr-2 text-xs"></i>
                                    {{ __('Demander une information') }}
                                </x-ts-button>

                                <x-ts-button
                                    tag="a"
                                    href="{{ route('client.login') }}"
                                    class="flex-1 !bg-white/15 !border border-white/40 !text-[13px]"
                                >
                                    <i class="fas fa-user-circle mr-2 text-xs"></i>
                                    {{ __('Accès client') }}
                                </x-ts-button>
                            </div>
                        </div>
                    @endif
                </div>
            </x-ts-card>

            {{-- À PROPOS --}}
            <x-ts-card class="rounded-3xl shadow-md border border-primary-50 bg-white px-5 py-5">
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="inline-flex items-center justify-center w-8 h-8 rounded-2xl bg-primary-50">
                            <i class="fas fa-info-circle text-primary-700 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('À propos du praticien') }}
                        </h2>
                    </div>

                    <div class="text-[14px] text-gray-700 leading-relaxed prose max-w-none break-words">
                        {!! $therapist->about ?? __('Informations à propos non disponibles pour le moment.') !!}
                    </div>
                </div>
            </x-ts-card>

            {{-- COORDONNÉES --}}
            <x-ts-card class="rounded-3xl shadow-md border border-primary-50 bg-white px-5 py-5">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="inline-flex items-center justify-center w-8 h-8 rounded-2xl bg-secondary-50">
                            <i class="fas fa-address-card text-secondary-700 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('Coordonnées & contact') }}
                        </h2>
                    </div>

                    <div class="space-y-3 text-[14px] text-gray-700">
                        @if($therapist->share_address_publicly)
                            <div class="flex items-start gap-2">
                                <i class="fas fa-map-marker-alt mt-1 text-secondary-600 text-sm"></i>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ __('Adresse') }}</p>
                                    <p class="leading-snug break-words">
                                        {{ $therapist->company_address ?? __('Adresse non disponible.') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($therapist->share_phone_publicly && $therapist->company_phone)
                            <div class="flex items-start gap-2">
                                <i class="fas fa-phone-alt mt-1 text-secondary-600 text-sm"></i>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ __('Téléphone') }}</p>
                                    <a href="tel:{{ $therapist->company_phone }}" class="text-primary-700 font-medium">
                                        {{ $therapist->company_phone }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if($therapist->share_email_publicly && $therapist->company_email)
                            <div class="flex items-start gap-2">
                                <i class="fas fa-envelope mt-1 text-secondary-600 text-sm"></i>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ __('Email') }}</p>
                                    <a href="mailto:{{ $therapist->company_email }}" class="text-primary-700 font-medium break-words">
                                        {{ $therapist->company_email }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </x-ts-card>

            {{-- SERVICES (tags) --}}
            @php
                $servicesTags = is_array($servicesArr) ? $servicesArr : [];
            @endphp
            @if(count($servicesTags) > 0)
                <x-ts-card class="rounded-3xl shadow-md border border-primary-50 bg-white px-5 py-5">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="inline-flex items-center justify-center w-8 h-8 rounded-2xl bg-primary-50">
                                <i class="fas fa-concierge-bell text-primary-700 text-sm"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ __('Types de soins proposés') }}
                            </h2>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @foreach($servicesTags as $service)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-primary-50 text-primary-800 text-[13px] font-medium">
                                    {{ $service }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </x-ts-card>
            @endif

            {{-- PRESTATIONS --}}
            @php
                $visiblePrestations = $prestations->filter(fn($p) => $p->visible_in_portal !== false);
                $groupedPrestations = $visiblePrestations->groupBy('name');
            @endphp

            @if($groupedPrestations->count() > 0)
                <x-ts-card class="rounded-3xl shadow-md border border-secondary-50 bg-white px-5 py-5">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="inline-flex items-center justify-center w-8 h-8 rounded-2xl bg-secondary-50">
                                <i class="fas fa-spa text-secondary-700 text-sm"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ __('Prestations proposées') }}
                            </h2>
                        </div>

                        <div class="space-y-4">
                            @foreach($groupedPrestations as $name => $group)
                                @php
                                    /** @var \App\Models\Product $prestation */
                                    $prestation = $group->first();

                                    $hasCabinet = $group->contains(fn($p) => (bool) $p->dans_le_cabinet);
                                    $hasDomicile = $group->contains(fn($p) => (bool) $p->adomicile);
                                    $hasVisio   = $group->contains(fn($p) => (bool) $p->visio);
                                    $canCollectOnline = $group->contains(fn($p) => (bool) $p->collect_payment);

                                    $truncated = \Illuminate\Support\Str::limit(strip_tags($prestation->description ?? ''), 120);
                                @endphp

                                <div class="border border-gray-100 rounded-2xl px-3 py-3 bg-[#fdfbf8]">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="space-y-1">
                                            <p class="text-[15px] font-semibold text-gray-900 break-words">
                                                {{ $prestation->name }}
                                            </p>

                                            <div class="flex flex-wrap gap-1.5 mt-1 text-[11px]">
                                                @if($hasCabinet)
                                                    <span class="inline-flex items-center px-2 py-[3px] rounded-full bg-white text-gray-800 border border-[#e4e8d5]">
                                                        📍 {{ __('Cabinet') }}
                                                    </span>
                                                @endif
                                                @if($hasDomicile)
                                                    <span class="inline-flex items-center px-2 py-[3px] rounded-full bg-white text-gray-800 border border-[#e4e8d5]">
                                                        🏠 {{ __('À domicile') }}
                                                    </span>
                                                @endif
                                                @if($hasVisio)
                                                    <span class="inline-flex items-center px-2 py-[3px] rounded-full bg-white text-gray-800 border border-[#e4e8d5]">
                                                        💻 {{ __('Visio') }}
                                                    </span>
                                                @endif
                                                @if(!is_null($prestation->duration))
                                                    <span class="inline-flex items-center px-2 py-[3px] rounded-full bg-white text-gray-800 border border-[#e4e8d5]">
                                                        ⏱ {{ $prestation->duration }} {{ __('min') }}
                                                    </span>
                                                @endif
                                                @if($canCollectOnline)
                                                    <span class="inline-flex items-center px-2 py-[3px] rounded-full bg-white text-[#854f38] border border-[#e4e8d5]">
                                                        💳 {{ __('Paiement en ligne possible') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($prestation->price_visible_in_portal && $prestation->price > 0)
                                            <p class="text-[14px] font-semibold text-[#854f38] whitespace-nowrap">
                                                {{ number_format($prestation->price_incl_tax ?? $prestation->price, 2, ',', ' ') }} €
                                            </p>
                                        @endif
                                    </div>

                                    @if($truncated)
                                        <p class="mt-2 text-[13px] text-gray-700 leading-snug break-words">
                                            {{ $truncated }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ts-card>
            @endif

            {{-- ÉVÉNEMENTS --}}
            @if($events->count() > 0)
                <x-ts-card class="rounded-3xl shadow-md border border-secondary-50 bg-white px-5 py-5">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="inline-flex items-center justify-center w-8 h-8 rounded-2xl bg-secondary-50">
                                <i class="fas fa-calendar-alt text-secondary-700 text-sm"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ __('Événements à venir') }}
                            </h2>
                        </div>

                        <div class="space-y-4">
                            @foreach($events as $event)
                                @php
                                    $spotsLeft = $event->limited_spot
                                        ? $event->number_of_spot - $event->reservations->count()
                                        : null;
                                @endphp
                                <div class="border border-gray-100 rounded-2xl px-3 py-3 bg-[#fdfbf8] space-y-2">
                                    <p class="text-[15px] font-semibold text-[#854f38] break-words">
                                        {{ $event->name }}
                                    </p>

                                    <div class="text-[13px] text-gray-700 space-y-1">
                                        <p>
                                            <i class="fas fa-calendar-alt mr-1 text-secondary-600 text-xs"></i>
                                            {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}
                                        </p>
                                        <p>
                                            <i class="fas fa-map-marker-alt mr-1 text-secondary-600 text-xs"></i>
                                            {{ $event->location }}
                                        </p>

                                        @if($event->limited_spot)
                                            <p>
                                                <i class="fas fa-users mr-1 text-secondary-600 text-xs"></i>
                                                {{ __('Places restantes :') }} {{ max($spotsLeft, 0) }}
                                            </p>
                                        @endif

                                        @if($event->associatedProduct && $event->associatedProduct->price > 0)
                                            <p>
                                                <i class="fas fa-tag mr-1 text-secondary-600 text-xs"></i>
                                                {{ __('Prix :') }} {{ number_format($event->associatedProduct->price_incl_tax, 2, ',', ' ') }} €
                                            </p>
                                        @endif
                                    </div>

                                    @if($event->booking_required)
                                        <div class="pt-1">
                                            @if(!$event->limited_spot || ($spotsLeft > 0))
                                                <x-ts-button
                                                    tag="a"
                                                    href="{{ route('events.reserve.create', $event->id) }}"
                                                    size="sm"
                                                    rounded
                                                    class="!text-[12px] !px-3 !py-1.5 !bg-[#854f38] !text-white !border-0 hover:!bg-[#6a3f2c]"
                                                >
                                                    {{ __('Réserver ma place') }}
                                                </x-ts-button>
                                            @else
                                                <span class="text-[12px] font-semibold text-red-500">
                                                    {{ __('Complet') }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ts-card>
            @endif

            {{-- TÉMOIGNAGES (mobile-friendly) --}}
            <x-ts-card class="rounded-3xl shadow-md border border-primary-50 bg-white px-5 py-5">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="inline-flex items-center justify-center w-8 h-8 rounded-2xl bg-primary-50">
                            <i class="fas fa-comments text-primary-700 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('Avis & témoignages') }}
                        </h2>
                    </div>

                    @if($testimonials->count() > 0)
                        <div class="space-y-4">
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

                                <div class="border-l-4 {{ $isGoogle ? 'border-[#8ea633]' : 'border-[#854f38]' }} bg-[#f9fafb] rounded-md px-3 py-3 space-y-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <p class="text-[13px] font-semibold text-gray-900">
                                                {{ $author ?? __('Client') }}
                                            </p>

                                            @if($isGoogle)
                                                <span class="inline-flex items-center text-[11px] bg-[#e5f0c8] text-[#647a0b] px-2 py-[2px] rounded-full">
                                                    <i class="fab fa-google mr-1 text-[10px]"></i>
                                                    {{ __('Avis Google') }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($isGoogle && $testimonial->rating)
                                            <div class="flex items-center gap-[2px] text-[#f6b400] text-[11px]">
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

                                    <p class="text-[13px] text-gray-700 italic leading-snug whitespace-pre-line">
                                        "{{ $testimonial->testimonial }}"
                                    </p>

                                    <p class="text-[11px] text-gray-500">
                                        {{ $date }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[13px] text-gray-600">
                            {{ __('Les témoignages des clients seront bientôt disponibles ici.') }}
                        </p>
                    @endif
                </div>
            </x-ts-card>

            {{-- Small hint --}}
            <p class="text-[11px] text-gray-500 text-center leading-relaxed px-4">
                {{ __('Vous pourrez à tout moment revenir sur cette fiche depuis vos favoris ou depuis une recherche de praticien.') }}
            </p>
        </div>

        {{-- MODAL: Demande d’information --}}
        <div
            x-show="infoOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40"
        >
            <div
                class="w-full max-w-md bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-5 pb-6 mx-auto"
                x-on:click.outside="infoOpen = false"
            >
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-[18px] font-semibold text-[#647a0b]">
                        {{ __('Demander une information') }}
                    </h2>
                    <button
                        type="button"
                        class="text-gray-400 hover:text-gray-600"
                        x-on:click="infoOpen = false"
                    >
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('therapist.sendInformationRequest', $therapist->slug) }}" class="space-y-3">
                    @csrf

                    <x-ts-input
                        name="first_name"
                        label="{{ __('Prénom') }}"
                        required
                        class="text-[14px]"
                    />

                    <x-ts-input
                        name="last_name"
                        label="{{ __('Nom') }}"
                        required
                        class="text-[14px]"
                    />

                    <x-ts-input
                        name="email"
                        type="email"
                        label="{{ __('Adresse email') }}"
                        required
                        class="text-[14px]"
                    />

                    <x-ts-input
                        name="phone"
                        label="{{ __('Téléphone (optionnel)') }}"
                        placeholder="06 12 34 56 78"
                        class="text-[14px]"
                    />

                    <div class="space-y-1">
                        <label for="message" class="block text-[13px] font-medium text-gray-800">
                            {{ __('Votre message') }}
                        </label>
                        <textarea
                            id="message"
                            name="message"
                            rows="4"
                            required
                            class="w-full rounded-2xl border-gray-300 text-[14px] px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="{{ __('Expliquez brièvement votre demande, vos besoins ou vos questions…') }}"
                        ></textarea>
                    </div>

                    <div class="text-[11px] text-gray-600 space-y-1">
                        <label class="flex items-start gap-2">
                            <input
                                type="checkbox"
                                name="terms"
                                class="mt-[3px] h-3.5 w-3.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                required
                            >
                            <span>
                                {{ __('J\'accepte les') }}
                                <a href="{{ route('cgu') }}" target="_blank" class="underline text-blue-600">
                                    {{ __('Conditions Générales d’Utilisation') }}
                                </a>
                                {{ __('et la') }}
                                <a href="{{ route('privacypolicy') }}" target="_blank" class="underline text-blue-600">
                                    {{ __('Politique de Confidentialité') }}
                                </a>.
                            </span>
                        </label>
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <x-ts-button
                            type="button"
                            color="secondary"
                            class="!text-[13px] !px-3 !py-1.5"
                            x-on:click="infoOpen = false"
                        >
                            {{ __('Annuler') }}
                        </x-ts-button>

                        <x-ts-button
                            type="submit"
                            color="primary"
                            class="!text-[13px] !px-4 !py-1.5"
                        >
                            {{ __('Envoyer la demande') }}
                        </x-ts-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Schema.org (reuses the same logic as your web view, but lighter) --}}
    @php
        $reviewItems = $testimonials->map(function ($t) {
            $isGoogle = $t->source === 'google';
            $author   = $isGoogle
                ? ($t->reviewer_name ?? 'Client Google')
                : optional($t->clientProfile)->first_name;

            $dateIso = ($t->external_created_at ?? $t->created_at)->toAtomString();

            $item = [
                '@type'         => 'Review',
                'author'        => [
                    '@type' => 'Person',
                    'name'  => $author ?: 'Client',
                ],
                'datePublished' => $dateIso,
                'reviewBody'    => $t->testimonial,
            ];

            if ($t->rating) {
                $item['reviewRating'] = [
                    '@type'       => 'Rating',
                    'ratingValue' => $t->rating,
                    'bestRating'  => 5,
                    'worstRating' => 1,
                ];
            }

            return $item;
        })->values()->all();

        $schemaData = [
            '@context'  => 'https://schema.org',
            '@type'     => 'LocalBusiness',
            '@id'       => url()->current(),
            'name'      => $therapist->company_name ?? $therapist->name,
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

        $schemaData = array_filter($schemaData, fn($v) => !is_null($v));
    @endphp

    @section('structured_data')
        <script type="application/ld+json">
            {!! json_encode($schemaData, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}
        </script>
    @endsection
</x-mobile-layout>

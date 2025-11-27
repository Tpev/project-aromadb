{{-- resources/views/mobile/therapists/show.blade.php --}}
@php
    use Illuminate\Support\Str;

    // === LOCATION ===
    $city  = trim($therapist->city_setByAdmin  ?? '');
    $state = trim($therapist->state_setByAdmin ?? '');
    $locationLabel = $city
        ? ($state ? "$city, $state" : $city)
        : ($state ?: __('Votre région'));

    // === SERVICES (SEO + tags) ===
    $servicesRaw = json_decode($therapist->services, true) ?? [];
    $servicesArr = is_array($servicesRaw) ? array_filter($servicesRaw) : [];
    $servicesSeo = collect($servicesArr)->unique()->take(3)->implode(', ');

    // === PAGE TITLE / META ===
    $brand     = config('app.name', 'AromaMade');
    $nameLabel = $therapist->company_name ?? $therapist->name;
    $labelSeo  = $servicesSeo ?: 'Thérapeute';

    $pageTitle = Str::limit("$nameLabel – $labelSeo | $brand", 60, '…');

    $aboutSnippet = Str::limit(strip_tags($therapist->about ?? ''), 120);
    $meta = Str::limit(
        trim(sprintf('%s – %s à %s. %s', $nameLabel, $labelSeo, $locationLabel, $aboutSnippet)),
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
        class="min-h-screen flex flex-col px-5 py-6 bg-[#f7f4ec]"
    >
        <div class="w-full max-w-lg mx-auto space-y-6">

            {{-- ─────────────────── BACK / RATING ─────────────────── --}}
            <div class="flex items-center justify-between">
                <a
                    href="{{ url()->previous() !== url()->current() ? url()->previous() : route('mobile.therapists.index') }}"
                    class="inline-flex items-center gap-1.5 text-base text-gray-700 font-medium"
                >
                    <i class="fas fa-chevron-left text-sm"></i>
                    {{ __('Retour') }}
                </a>

                <div class="flex items-center gap-2 text-sm text-gray-600">
                    @if($averageRating)
                        <div class="inline-flex items-center gap-1">
                            <i class="fas fa-star text-[#f6b400]"></i>
                            <span class="font-semibold">{{ $averageRating }}</span>
                        </div>
                        <span>•</span>
                    @endif

                    <span>
                        {{ $testimonials->count() }}
                        {{ \Illuminate\Support\Str::plural('avis', $testimonials->count()) }}
                    </span>
                </div>
            </div>

            {{-- ─────────────────── HERO CARD (PRIMARY GREEN, NO GRADIENT) ─────────────────── --}}
            <x-ts-card class="rounded-3xl shadow-xl border-0 bg-[#647a0b] text-white px-6 py-7">
                <div class="flex flex-col items-center text-center space-y-4">

                    {{-- Avatar --}}
                    <div class="w-28 h-28 rounded-full bg-white/10 border border-white/20 overflow-hidden shadow-md flex items-center justify-center">
                        @if ($therapist->profile_picture)
                            <img
                                src="{{ asset("storage/avatars/{$therapist->id}/avatar-320.webp") }}?v={{ $imgVer }}"
                                class="w-full h-full object-cover"
                                alt="{{ $therapist->name }}"
                            >
                        @else
                            <span class="text-3xl font-bold">
                                {{ mb_substr($nameLabel ?? 'A', 0, 1) }}
                            </span>
                        @endif
                    </div>

                    {{-- Name & tagline --}}
                    <div class="space-y-1">
                        <h1 class="text-2xl font-extrabold break-words leading-snug">
                            {{ $nameLabel }}
                        </h1>

                        @if($therapist->profile_description)
                            <p class="text-sm text-white/90 leading-snug break-words">
                                {{ $therapist->profile_description }}
                            </p>
                        @endif

                        <div class="flex flex-wrap items-center justify-center gap-2 mt-1">
                            <span class="px-3 py-1 rounded-full bg-white/15 text-xs inline-flex items-center">
                                <i class="fas fa-map-marker-alt mr-1 text-[10px]"></i>
                                {{ $locationLabel }}
                            </span>

                            @if($servicesSeo)
                                <span class="px-3 py-1 rounded-full bg-white/15 text-xs inline-flex items-center">
                                    <i class="fas fa-leaf mr-1 text-[10px]"></i>
                                    {{ $servicesSeo }}
                                </span>
                            @endif

                            @if($therapist->verified ?? false)
                                <span class="px-3 py-1 rounded-full bg-green-500/20 border border-green-300/60 text-xs inline-flex items-center">
                                    <i class="fas fa-shield-check mr-1 text-[10px]"></i>
                                    {{ __('Praticien vérifié') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- CTAs (kept same logic: only if accept_online_appointments) --}}
                    @if($therapist->accept_online_appointments)
                        <div class="w-full flex flex-col gap-3 mt-2">
                            <x-ts-button
                                tag="a"
                                href="{{ route('mobile.appointments.create_from_therapist', $therapist->slug) }}"
                                class="w-full !bg-white !text-[#647a0b] !font-semibold !border-0 hover:!bg-primary-50"
                            >
                                <i class="fas fa-calendar-plus mr-2"></i>
                                {{ __('Prendre rendez-vous') }}
                            </x-ts-button>

                            <div class="flex gap-3">
                                <x-ts-button
                                    type="button"
                                    class="flex-1 !bg-white/20 !border-0"
                                    x-on:click="infoOpen = true"
                                >
                                    <i class="fas fa-envelope-open-text mr-2"></i>
                                    {{ __('Demande d’information') }}
                                </x-ts-button>

                                <x-ts-button
                                    tag="a"
                                    href="{{ route('client.login') }}"
                                    class="flex-1 !bg-white/20 !border-0"
                                >
                                    <i class="fas fa-user-circle mr-2"></i>
                                    {{ __('Accès client') }}
                                </x-ts-button>
                            </div>
                        </div>
                    @endif
                </div>
            </x-ts-card>

            {{-- ─────────────────── À PROPOS ─────────────────── --}}
            <x-ts-card class="rounded-3xl shadow-md border border-primary-50 bg-white px-5 py-6">
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-2xl bg-primary-50 flex items-center justify-center">
                            <i class="fas fa-info-circle text-primary-700 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('À propos du praticien') }}
                        </h2>
                    </div>

                    <div class="text-[15px] text-gray-700 leading-relaxed break-words prose max-w-none">
                        {!! $therapist->about ?? __('Informations à propos non disponibles pour le moment.') !!}
                    </div>
                </div>
            </x-ts-card>

            {{-- ─────────────────── COORDONNÉES ─────────────────── --}}
            <x-ts-card class="rounded-3xl shadow-md border border-primary-50 bg-white px-5 py-6">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-2xl bg-secondary-50 flex items-center justify-center">
                            <i class="fas fa-address-card text-secondary-700 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('Coordonnées & contact') }}
                        </h2>
                    </div>

                    <div class="space-y-3 text-[15px] text-gray-700">
                        @if($therapist->share_address_publicly)
                            <div class="flex items-start gap-2">
                                <i class="fas fa-map-marker-alt mt-1 text-secondary-600"></i>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ __('Adresse') }}</p>
                                    <p class="break-words">
                                        {{ $therapist->company_address ?? __('Adresse non disponible.') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($therapist->share_phone_publicly && $therapist->company_phone)
                            <div class="flex items-start gap-2">
                                <i class="fas fa-phone-alt mt-1 text-secondary-600"></i>
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
                                <i class="fas fa-envelope mt-1 text-secondary-600"></i>
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

            {{-- ─────────────────── SERVICES TAGS ─────────────────── --}}
            @if(count($servicesArr) > 0)
                <x-ts-card class="rounded-3xl shadow-md border border-primary-50 bg-white px-5 py-6">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-2xl bg-primary-50 flex items-center justify-center">
                                <i class="fas fa-concierge-bell text-primary-700 text-sm"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ __('Types de soins proposés') }}
                            </h2>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @foreach($servicesArr as $service)
                                <span class="px-3 py-1.5 rounded-full bg-primary-50 text-primary-800 text-[13px] font-medium break-words">
                                    {{ $service }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </x-ts-card>
            @endif

            {{-- ─────────────────── PRESTATIONS ─────────────────── --}}
            @php
                $visiblePrestations = $prestations->filter(fn($p) => $p->visible_in_portal !== false);
                $groupedPrestations = $visiblePrestations->groupBy('name');
            @endphp

            @if($groupedPrestations->count() > 0)
                <x-ts-card class="rounded-3xl shadow-md border border-secondary-50 bg-white px-5 py-6">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-2xl bg-secondary-50 flex items-center justify-center">
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
                                    $prestation      = $group->first();
                                    $hasCabinet      = $group->contains(fn($p) => (bool) $p->dans_le_cabinet);
                                    $hasDomicile     = $group->contains(fn($p) => (bool) $p->adomicile);
                                    $hasVisio        = $group->contains(fn($p) => (bool) $p->visio);
                                    $canCollectOnline = $group->contains(fn($p) => (bool) $p->collect_payment);
                                    $truncated       = Str::limit(strip_tags($prestation->description ?? ''), 120);
                                @endphp

                                <div class="border border-gray-100 rounded-2xl px-3 py-3 bg-[#fdfbf8] space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="space-y-1 min-w-0">
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
                                        <p class="mt-1 text-[13px] text-gray-700 leading-snug break-words">
                                            {{ $truncated }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ts-card>
            @endif

            {{-- ─────────────────── ÉVÉNEMENTS ─────────────────── --}}
            @if($events->count() > 0)
                <x-ts-card class="rounded-3xl shadow-md border border-secondary-50 bg-white px-5 py-6">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-2xl bg-secondary-50 flex items-center justify-center">
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
                                                {{ __('Prix :') }}
                                                {{ number_format($event->associatedProduct->price_incl_tax, 2, ',', ' ') }} €
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

            {{-- ─────────────────── TÉMOIGNAGES ─────────────────── --}}
            <x-ts-card class="rounded-3xl shadow-md border border-primary-50 bg-white px-5 py-6">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-2xl bg-primary-50 flex items-center justify-center">
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
                                    $author   = $isGoogle
                                        ? ($testimonial->reviewer_name ?? 'Client Google')
                                        : optional($testimonial->clientProfile)->first_name;

                                    $date = $testimonial->external_created_at
                                        ? $testimonial->external_created_at->format('d/m/Y')
                                        : $testimonial->created_at->format('d/m/Y');
                                @endphp

                                <div class="border-l-4 {{ $isGoogle ? 'border-[#8ea633]' : 'border-[#854f38]' }} bg-[#f9fafb] rounded-md px-3 py-3 space-y-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <p class="text-[14px] font-semibold text-gray-900">
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

                                    <p class="text-[14px] text-gray-700 italic leading-snug whitespace-pre-line break-words">
                                        "{{ $testimonial->testimonial }}"
                                    </p>

                                    <p class="text-[11px] text-gray-500">
                                        {{ $date }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[14px] text-gray-600">
                            {{ __('Les témoignages des clients seront bientôt disponibles ici.') }}
                        </p>
                    @endif
                </div>
            </x-ts-card>

            <p class="text-[11px] text-gray-500 text-center leading-relaxed px-4">
                {{ __('Vous pourrez retrouver ce praticien via vos favoris ou une nouvelle recherche.') }}
            </p>
        </div>

        {{-- ─────────────────── MODAL: Demande d’information ─────────────────── --}}
        <div
            x-show="infoOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40"
        >
            <div
                class="w-full max-w-md bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-6 mx-auto"
                x-on:click.outside="infoOpen = false"
            >
                <div class="flex items-center justify-between mb-4">
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
                    />

                    <x-ts-input
                        name="last_name"
                        label="{{ __('Nom') }}"
                        required
                    />

                    <x-ts-input
                        name="email"
                        type="email"
                        label="{{ __('Adresse email') }}"
                        required
                    />

                    <x-ts-input
                        name="phone"
                        label="{{ __('Téléphone (optionnel)') }}"
                        placeholder="06 12 34 56 78"
                    />

                    <div class="space-y-1">
                        <label for="message" class="block text-sm font-medium text-gray-800">
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

    {{-- ─────────────────── SCHEMA.ORG (LocalBusiness + Reviews + Rating) ─────────────────── --}}
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
            'name'      => $nameLabel,
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

        // Nettoyage des clés null top-level
        $schemaData = array_filter($schemaData, fn($v) => !is_null($v));
    @endphp

    @section('structured_data')
        <script type="application/ld+json">
            {!! json_encode($schemaData, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}
        </script>
    @endsection
</x-mobile-layout>

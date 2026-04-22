{{-- resources/views/digital-trainings/public/show.blade.php --}}

@php
    $tags = $training->tags ?? [];
    if (is_string($tags)) {
        $tags = array_filter(array_map('trim', explode(',', $tags)));
    }

    $isFree   = (bool) ($training->is_free ?? false);
    $priceCts = $training->price_cents ?? null;
    $priceStr = null;

    if (!$isFree && $priceCts !== null) {
        $priceStr = number_format($priceCts / 100, 2, ',', ' ') . ' €';
    }

    $duration = $training->estimated_duration_minutes ?? null;

    // nicer duration label (minutes -> "1h30" or "45 min")
    $durationLabel = null;
    if ($duration) {
        $mins = (int) $duration;
        if ($mins >= 60) {
            $h = intdiv($mins, 60);
            $m = $mins % 60;
            $durationLabel = $m > 0 ? ($h . 'h' . str_pad((string)$m, 2, '0', STR_PAD_LEFT)) : ($h . 'h');
        } else {
            $durationLabel = $mins . ' min';
        }
    }

// Therapist bits (can be null)
$therapist = $therapist ?? null;
$therapistPublicUrl = $therapistPublicUrl ?? null;

$therapistName = null;
$therapistInit = null;
$therapistCity = null;
$buyUrl        = null;

if ($therapist) {
    $therapistName = $therapist->company_name ?: $therapist->name;
    $therapistInit = mb_strtoupper(mb_substr($therapistName, 0, 1));
    $therapistCity = $therapist->city_setByAdmin ?? $therapist->city ?? null;

    // New canonical checkout route: /pro/{slug}/checkout
    // Trainings are selected via ?item=training:{id}
    if (!empty($therapist->slug)) {
        $buyUrl = route('public.checkout.show', [
            'slug' => $therapist->slug,
        ]) . '?item=' . urlencode('training:' . $training->id);
    }
}



    // Modules (for “plan de formation”)
    $modules = $training->modules ?? collect();

    // Can we show a buy button?
    $canBuy = (!$isFree && !is_null($priceCts) && !empty($buyUrl));
    $canClaimFree = $isFree && ($training->free_access_requires_identity ?? false);
    $freeAccessUrl = $canClaimFree ? route('digital-trainings.public.free-access.store', $training) : null;
@endphp

<x-app-layout>
    <x-slot name="header">
        {{-- Empty on purpose, hero below is full-width like therapist page --}}
    </x-slot>

    <div>
        {{-- HERO – same spirit as therapist public page --}}
        <section class="relative overflow-hidden isolate">
            {{-- Optional blurred cover as background (low opacity) --}}
            @if($training->cover_image_path)
                <picture class="absolute inset-0 -z-10">
                    <img src="{{ asset('storage/'.$training->cover_image_path) }}"
                         alt="{{ $training->title }}"
                         class="w-full h-full object-cover opacity-25">
                </picture>
            @endif

            {{-- Green overlay band --}}
            <div class="bg-[#8ea633]/90 backdrop-blur-sm text-white w-full">
                <div class="max-w-7xl mx-auto px-6
                            flex flex-col md:flex-row items-center gap-10
                            py-12 md:py-16
                            min-h-[280px] sm:min-h-[320px]">

                    {{-- Left: cover or placeholder --}}
                    <div class="shrink-0 w-full max-w-sm md:max-w-xs">
                        @if($training->cover_image_path)
                            <div class="rounded-2xl overflow-hidden bg-white/10 ring-4 ring-white/30 shadow-md">
                                <img src="{{ asset('storage/'.$training->cover_image_path) }}"
                                     alt="{{ $training->title }}"
                                     class="w-full h-56 md:h-64 object-cover">
                            </div>
                        @else
                            <div class="w-full h-56 md:h-64 rounded-2xl bg-white/10 flex items-center justify-center text-3xl font-bold">
                                🎓
                            </div>
                        @endif
                    </div>

                    {{-- Right: copy + CTA --}}
                    <div class="text-center md:text-left max-w-2xl">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 text-xs font-semibold tracking-wide uppercase">
                            🎓 {{ __('Formation digitale') }}
                        </span>

                        <h1 class="mt-4 text-3xl md:text-4xl font-extrabold leading-tight tracking-tight break-words">
                            {{ $training->title }}
                        </h1>

                        <p class="mt-3 text-sm md:text-base text-white/90">
                            {{ __('Programme en ligne pour approfondir votre bien-être, à votre rythme, entre deux séances avec votre thérapeute.') }}
                        </p>

                        {{-- Meta pills: durée, prix/free --}}
                        <div class="mt-5 flex flex-wrap gap-2 justify-center md:justify-start text-xs md:text-sm">
                            @if($durationLabel)
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/40">
                                    ⏱ {{ __('Durée estimée : :d', ['d' => $durationLabel]) }}
                                </span>
                            @endif

                            @if($isFree)
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/40">
                                    💚 {{ $canClaimFree ? __('Accès gratuit après formulaire') : __('Accès offert à certains clients (voir avec le thérapeute).') }}
                                </span>
                            @elseif($priceStr)
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/40">
                                    💶 {{ __('Tarif : :price', ['price' => $priceStr]) }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/40">
                                    💻 {{ __('Programme en ligne – tarif communiqué par le thérapeute.') }}
                                </span>
                            @endif
                        </div>

                        {{-- Tags --}}
                        @if(!empty($tags))
                            <div class="mt-4 flex flex-wrap gap-2 justify-center md:justify-start">
                                @foreach($tags as $tag)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 text-xs">
                                        #{{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- CTA --}}
                        <div class="mt-8 flex flex-wrap gap-3 justify-center md:justify-start">
                            @if($canClaimFree)
                                <a href="#free-access-gate"
                                   class="inline-flex items-center justify-center whitespace-nowrap bg-white text-[#647a0b] font-extrabold
                                          text-sm md:text-base px-6 md:px-8 py-2.5 rounded-full hover:bg-[#e8f0d8]
                                          transition-colors duration-200">
                                    ✨ {{ __('Accéder gratuitement') }}
                                </a>
                            @endif

                            {{-- BUY button --}}
                            @if($canBuy)
                                <a href="{{ $buyUrl }}"
                                   class="inline-flex items-center justify-center whitespace-nowrap bg-white text-[#647a0b] font-extrabold
                                          text-sm md:text-base px-6 md:px-8 py-2.5 rounded-full hover:bg-[#e8f0d8]
                                          transition-colors duration-200">
                                    🔒 {{ __('Acheter la formation') }}
                                </a>
                            @endif

                            @if($therapistPublicUrl)
                                <a href="{{ $therapistPublicUrl }}"
                                   class="inline-flex items-center justify-center whitespace-nowrap bg-[#854f38] text-white font-semibold
                                          text-sm md:text-base px-6 md:px-8 py-2.5 rounded-full hover:bg-[#6a3f2c]
                                          transition-colors duration-200">
                                    🌿 {{ __('Voir la page du thérapeute') }}
                                </a>

                                <a href="{{ $therapistPublicUrl }}"
                                   class="inline-flex items-center justify-center whitespace-nowrap bg-white/10 text-white font-semibold
                                          text-sm md:text-base px-6 md:px-8 py-2.5 rounded-full hover:bg-white/15
                                          border border-white/30 transition-colors duration-200">
                                    📅 {{ __('Contacter / Prendre rendez-vous') }}
                                </a>
                            @else
                                <span class="text-xs md:text-sm text-white/80">
                                    {{ __('Adressez-vous directement à votre thérapeute pour obtenir l’accès à cette formation.') }}
                                </span>
                            @endif
                        </div>

                        @if($canBuy)
                            <p class="mt-3 text-[11px] text-white/80">
                                {{ __('Paiement sécurisé. Accès envoyé par email après confirmation du paiement.') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- MAIN CONTENT --}}
        <div class="max-w-7xl mx-auto px-6 py-10 space-y-8">
            {{-- À propos de la formation --}}
            <section class="bg-white shadow rounded-lg p-6 md:p-8">
                <h2 class="text-2xl md:text-3xl font-semibold text-[#647a0b] flex items-center">
                    <i class="fas fa-info-circle text-[#854f38] mr-3"></i>
                    {{ __('À propos de cette formation') }}
                </h2>

                <article class="mt-6 text-gray-700 text-sm md:text-base leading-relaxed prose max-w-none">
                    @if($training->description)
                        {!! nl2br(e($training->description)) !!}
                    @else
                        <p>
                            {{ __('Cette formation digitale vous permet de revoir les notions clés abordées en séance, et d’avancer pas à pas grâce à des supports clairs (textes, vidéos, audios, PDF, fiches pratiques…).') }}
                        </p>
                    @endif
                </article>

                {{-- Secondary buy CTA (below description) --}}
                @if($canBuy)
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ $buyUrl }}"
                           class="inline-flex items-center bg-[#647a0b] text-white font-extrabold
                                  px-6 py-2.5 rounded-full text-sm hover:bg-[#8ea633] transition-colors duration-200">
                            🔒 {{ __('Acheter maintenant (:price)', ['price' => $priceStr]) }}
                        </a>
                        @if($therapistPublicUrl)
                            <a href="{{ $therapistPublicUrl }}"
                               class="inline-flex items-center border border-[#e4e8d5] text-[#647a0b] font-medium
                                      px-6 py-2.5 rounded-full text-sm bg-white hover:bg-[#f4f6ec] transition-colors duration-200">
                                🌿 {{ __('Voir le thérapeute') }}
                            </a>
                        @endif
                    </div>
                @endif

                @if($canClaimFree)
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="#free-access-gate"
                           class="inline-flex items-center bg-[#647a0b] text-white font-extrabold
                                  px-6 py-2.5 rounded-full text-sm hover:bg-[#8ea633] transition-colors duration-200">
                            ✨ {{ __('Accéder gratuitement') }}
                        </a>
                    </div>
                @endif
            </section>

            @if($canClaimFree)
                <section id="free-access-gate" class="bg-white shadow rounded-lg p-6 md:p-8 border border-[#e4e8d5]">
                    <h2 class="text-2xl md:text-3xl font-semibold text-[#647a0b] flex items-center">
                        <i class="fas fa-unlock-alt text-[#854f38] mr-3"></i>
                        {{ __('Accéder gratuitement') }}
                    </h2>

                    <p class="mt-3 text-sm md:text-base text-gray-600 max-w-3xl">
                        {{ __('Renseignez simplement votre prénom, votre nom et votre email pour accéder immédiatement à ce contenu gratuit.') }}
                    </p>

                    @if($errors->any())
                        <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            <ul class="list-disc pl-4 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ $freeAccessUrl }}" method="POST" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">
                                {{ __('Prénom') }}
                            </label>
                            <input type="text"
                                   name="first_name"
                                   value="{{ old('first_name') }}"
                                   required
                                   class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">
                                {{ __('Nom') }}
                            </label>
                            <input type="text"
                                   name="last_name"
                                   value="{{ old('last_name') }}"
                                   required
                                   class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-800 mb-1">
                                {{ __('Email') }}
                            </label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40"
                                   placeholder="prenom.nom@email.com">
                        </div>

                        <div class="md:col-span-2">
                            <label class="inline-flex items-start gap-3 text-sm text-slate-700">
                                <input type="hidden" name="email_communication_consent" value="0">
                                <input type="checkbox"
                                       name="email_communication_consent"
                                       value="1"
                                       {{ old('email_communication_consent') ? 'checked' : '' }}
                                       class="mt-1 rounded border-slate-300 text-[#647a0b] focus:ring-[#647a0b]/40">
                                <span>
                                    {{ __('J’accepte de recevoir des communications par email de la part de :name.', ['name' => $therapistName ?: __('ce praticien')]) }}
                                </span>
                            </label>
                        </div>

                        <div class="md:col-span-2 flex flex-wrap items-center gap-3 pt-2">
                            <button type="submit"
                                    class="inline-flex items-center justify-center whitespace-nowrap bg-[#647a0b] text-white font-extrabold
                                           text-sm md:text-base px-6 md:px-8 py-2.5 rounded-full hover:bg-[#8ea633]
                                           transition-colors duration-200">
                                ✨ {{ __('Accéder gratuitement') }}
                            </button>

                            <p class="text-xs md:text-sm text-gray-500">
                                {{ __('Aucun paiement ne vous sera demandé pour ce contenu.') }}
                            </p>
                        </div>
                    </form>
                </section>
            @endif

            {{-- Plan de la formation (modules listing) --}}
            <section class="bg-[#f9fafb] shadow rounded-lg p-6 md:p-8">
                <h2 class="text-2xl md:text-3xl font-semibold text-[#647a0b] flex items-center">
                    <i class="fas fa-list-ul text-[#854f38] mr-3"></i>
                    {{ __('Plan de la formation') }}
                </h2>

                @if($modules->count())
                    <p class="mt-3 text-sm md:text-base text-gray-600">
                        {{ __('Découvrez le déroulé de la formation, module par module. Le thérapeute peut adapter le rythme et les supports selon vos besoins.') }}
                    </p>

                    <div class="mt-6 space-y-4">
                        @foreach($modules->sortBy('display_order') as $index => $module)
                            @php
                                $displayIndex = $loop->iteration;
                                $moduleTitle = $module->title ?: __('Module :num', ['num' => $displayIndex]);
                                $moduleDesc  = $module->description;

                                $blocks = $module->sorted_blocks ?? $module->blocks ?? collect();
                                $blocksCount = $blocks->count();

                                $hasText   = $blocks->contains(fn($b) => $b->type === 'text');
                                $hasVideo  = $blocks->contains(fn($b) => $b->type === 'video_url');
                                $hasAudio  = $blocks->contains(fn($b) => $b->type === 'audio');
                                $hasPdf    = $blocks->contains(fn($b) => $b->type === 'pdf');
                            @endphp

                            <div class="bg-white border border-[#e4e8d5] rounded-lg p-4 md:p-5 flex gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-[#647a0b] text-white flex items-center justify-center font-semibold text-sm">
                                        {{ $displayIndex }}
                                    </div>
                                </div>

                                <div class="flex-1">
                                    <div class="flex flex-col md:flex-row md:items-baseline md:justify-between gap-2">
                                        <h3 class="text-sm md:text-base font-semibold text-[#647a0b]">
                                            {{ $moduleTitle }}
                                        </h3>

                                        @if($blocksCount > 0)
                                            <div class="flex flex-wrap gap-2 text-[11px] md:text-xs text-gray-600">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#f9fafb] border border-[#e4e8d5]">
                                                    📚 {{ trans_choice(':count contenu|:count contenus', $blocksCount, ['count' => $blocksCount]) }}
                                                </span>

                                                @if($hasText)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#f9fafb] border border-[#e4e8d5]">
                                                        📝 {{ __('Textes & explications') }}
                                                    </span>
                                                @endif
                                                @if($hasVideo)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#f9fafb] border border-[#e4e8d5]">
                                                        🎬 {{ __('Vidéos') }}
                                                    </span>
                                                @endif
                                                @if($hasAudio)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#f9fafb] border border-[#e4e8d5]">
                                                        🎧 {{ __('Audios') }}
                                                    </span>
                                                @endif
                                                @if($hasPdf)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#f9fafb] border border-[#e4e8d5]">
                                                        📄 {{ __('PDF & fiches pratiques') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    @if($moduleDesc)
                                        <p class="mt-2 text-xs md:text-sm text-gray-700 leading-relaxed">
                                            {{ $moduleDesc }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm md:text-base text-gray-600">
                        {{ __('Le plan détaillé de cette formation sera bientôt disponible. N’hésitez pas à en parler avec votre thérapeute.') }}
                    </p>
                @endif
            </section>

            {{-- Proposé par le thérapeute --}}
            @if($therapist)
                <section class="bg-white shadow rounded-lg p-6 md:p-8">
                    <h2 class="text-2xl md:text-3xl font-semibold text-[#647a0b] flex items-center">
                        <i class="fas fa-user-md text-[#854f38] mr-3"></i>
                        {{ __('Proposé par votre thérapeute') }}
                    </h2>

                    <div class="mt-6 flex flex-col md:flex-row items-start gap-6">
                        <div class="shrink-0">
                            @if($therapist->profile_picture)
                                @php
                                    $imgVer = $therapist->updated_at?->timestamp ?? time();
                                @endphp
                                <img src="{{ asset("storage/avatars/{$therapist->id}/avatar-320.webp") }}?v={{ $imgVer }}"
                                     srcset="{{ asset("storage/avatars/{$therapist->id}/avatar-320.webp") }}?v={{ $imgVer }} 320w,
                                             {{ asset("storage/avatars/{$therapist->id}/avatar-640.webp") }}?v={{ $imgVer }} 640w"
                                     sizes="128px"
                                     width="128" height="128"
                                     class="block w-28 h-28 md:w-32 md:h-32 rounded-full object-cover
                                            ring-4 ring-white shadow-md bg-white"
                                     alt="{{ $therapistName }}">
                            @else
                                <div class="w-28 h-28 md:w-32 md:h-32 rounded-full bg-white flex items-center
                                            justify-center text-[#8ea633] text-3xl font-bold ring-4 ring-white
                                            select-none shadow-md">
                                    {{ $therapistInit }}
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 space-y-3 text-sm md:text-base text-gray-700">
                            <div>
                                <p class="text-lg md:text-xl font-semibold text-[#647a0b]">
                                    {{ $therapistName }}
                                </p>
                                @if($therapistCity)
                                    <p class="text-sm text-gray-500">
                                        {{ $therapistCity }}
                                    </p>
                                @endif
                            </div>

                            @if($therapist->profile_description)
                                <p class="leading-relaxed">
                                    {{ $therapist->profile_description }}
                                </p>
                            @else
                                <p class="leading-relaxed">
                                    {{ __('Ce thérapeute propose des accompagnements personnalisés, ainsi que des contenus digitaux complémentaires comme cette formation.') }}
                                </p>
                            @endif

                            <div class="pt-3 flex flex-wrap gap-3">
                                @if($canBuy)
                                    <a href="{{ $buyUrl }}"
                                       class="inline-flex items-center bg-[#647a0b] text-white font-extrabold
                                              px-5 py-2 rounded-full text-sm hover:bg-[#8ea633] transition-colors duration-200">
                                        🔒 {{ __('Acheter la formation') }}
                                    </a>
                                @endif

                                @if($therapistPublicUrl)
                                    <a href="{{ $therapistPublicUrl }}"
                                       class="inline-flex items-center border border-[#e4e8d5] text-[#647a0b] font-medium
                                              px-5 py-2 rounded-full text-sm bg-white hover:bg-[#f4f6ec] transition-colors duration-200">
                                        🌿 {{ __('Voir sa page publique') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <p class="mt-6 text-xs md:text-sm text-gray-500">
                        {{ $canClaimFree
                            ? __('L’accès gratuit se débloque immédiatement après le formulaire de contact.')
                            : __('L’accès à la formation se fait via un lien sécurisé envoyé par email une fois la commande confirmée.') }}
                    </p>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>

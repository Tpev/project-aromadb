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

    // Therapist bits (can be null)
    $therapist = $therapist ?? null;
    $therapistPublicUrl = $therapistPublicUrl ?? null;

    if ($therapist) {
        $therapistName = $therapist->company_name ?: $therapist->name;
        $therapistInit = mb_strtoupper(mb_substr($therapistName, 0, 1));
        $therapistCity = $therapist->city_setByAdmin ?? $therapist->city ?? null;
    }

    // Modules (for “plan de formation”)
    $modules = $training->modules ?? collect();
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
                            @if($duration)
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/40">
                                    ⏱ {{ __('Durée estimée : :min h', ['min' => $duration]) }}
                                </span>
                            @endif

                            @if($isFree)
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/40">
                                    💚 {{ __('Accès offert à certains clients (voir avec le thérapeute).') }}
                                </span>
                            @elseif($priceStr)
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/40">
                                    💶 {{ __('Tarif indicatif : :price', ['price' => $priceStr]) }}
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

                        {{-- CTA: go to therapist public page to book/contact --}}
                        <div class="mt-8 flex flex-wrap gap-3 justify-center md:justify-start">
                            @if($therapistPublicUrl)
                                <a href="{{ $therapistPublicUrl }}"
                                   class="inline-block whitespace-nowrap bg-white text-[#8ea633] font-semibold
                                          text-sm md:text-base px-6 md:px-8 py-2.5 rounded-full hover:bg-[#e8f0d8]
                                          transition-colors duration-200">
                                    📅 {{ __('Contacter / Prendre rendez-vous') }}
                                </a>

                                <a href="{{ $therapistPublicUrl }}"
                                   class="inline-block whitespace-nowrap bg-[#854f38] text-white font-semibold
                                          text-sm md:text-base px-6 md:px-8 py-2.5 rounded-full hover:bg-[#6a3f2c]
                                          transition-colors duration-200">
                                    🌿 {{ __('Voir la page du thérapeute') }}
                                </a>
                            @else
                                <span class="text-xs md:text-sm text-white/80">
                                    {{ __('Adressez-vous directement à votre thérapeute pour obtenir l’accès à cette formation.') }}
                                </span>
                            @endif
                        </div>
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
                            {{ __('Cette formation digitale vous permet de revoir les notions clés abordées en séance, et d’avancer pas à pas grâce à des supports clairs (vidéos, PDF, fiches pratiques…).') }}
                        </p>
                    @endif
                </article>
            </section>

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

                                // Small summary of content types
                                $hasText   = $blocks->contains(fn($b) => $b->type === 'text');
                                $hasVideo  = $blocks->contains(fn($b) => $b->type === 'video_url');
                                $hasPdf    = $blocks->contains(fn($b) => $b->type === 'pdf');
                            @endphp

                            <div class="bg-white border border-[#e4e8d5] rounded-lg p-4 md:p-5 flex gap-4">
                                {{-- Index --}}
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-[#647a0b] text-white flex items-center justify-center font-semibold text-sm">
                                        {{ $displayIndex }}
                                    </div>
                                </div>

                                {{-- Content --}}
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
                        {{-- Avatar --}}
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

                        {{-- Text + CTA --}}
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

                            @if($therapistPublicUrl)
                                <div class="pt-3 flex flex-wrap gap-3">
                                    <a href="{{ $therapistPublicUrl }}"
                                       class="inline-flex items-center bg-[#647a0b] text-white font-semibold
                                              px-5 py-2 rounded-full text-sm hover:bg-[#8ea633] transition-colors duration-200">
                                        📅 {{ __('Prendre rendez-vous') }}
                                    </a>
                                    <a href="{{ $therapistPublicUrl }}"
                                       class="inline-flex items-center border border-[#e4e8d5] text-[#647a0b] font-medium
                                              px-5 py-2 rounded-full text-sm bg-white hover:bg-[#f4f6ec] transition-colors duration-200">
                                        🌿 {{ __('Voir sa page publique') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <p class="mt-6 text-xs md:text-sm text-gray-500">
                        {{ __('L’accès à la formation se fait via un lien sécurisé envoyé par email une fois que votre thérapeute a créé votre accès dans AromaMade.') }}
                    </p>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>

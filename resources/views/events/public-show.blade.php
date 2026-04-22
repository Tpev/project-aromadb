@php
    $spotsLeft = $event->limited_spot
        ? max($event->number_of_spot - $event->reservations->whereIn('status', ['confirmed', 'pending_payment', 'paid'])->count(), 0)
        : null;

    $desc = $event->description;
    $descLooksHtml = $desc && preg_match('/<\/?[a-z][\s\S]*>/i', $desc);
    $descText = $desc ? trim(strip_tags($desc)) : '';
    $isVisio = ($event->event_type ?? 'in_person') === 'visio';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            <i class="fas fa-calendar-alt mr-2"></i>{{ __('Détails de l’événement') }}
        </h2>
    </x-slot>

    @once
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
        <style>
            .am-quill-view .ql-toolbar { display:none !important; }
            .am-quill-view.ql-snow { border: none !important; }
            .am-quill-view .ql-editor { padding: 0 !important; }
            .am-quill-view .ql-editor p { margin: .35rem 0; }
            .am-quill-view .ql-editor ul,
            .am-quill-view .ql-editor ol { padding-left: 1.25rem; margin: .35rem 0; }
            .am-quill-view .ql-editor h1,
            .am-quill-view .ql-editor h2,
            .am-quill-view .ql-editor h3 { margin: .55rem 0 .35rem; }
        </style>
    @endonce

    <div class="container max-w-4xl py-8 space-y-6">
        <div class="rounded-2xl border border-[#dbe3b8] bg-white shadow-md overflow-hidden">
            <div class="px-6 py-5 sm:px-8" style="background-color: #647a0b;">
                <div class="flex flex-col gap-3 text-white">
                    <div>
                        <h1 class="text-3xl font-bold">{{ $event->name }}</h1>
                        <p class="mt-1 text-sm text-white/85">
                            {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}
                            · {{ $event->location }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">
                            @if($isVisio)
                                <i class="fas fa-video mr-1 text-indigo-200"></i>{{ __('Visio') }}
                            @else
                                <i class="fas fa-map-marker-alt mr-1 text-amber-200"></i>{{ __('Présentiel') }}
                            @endif
                        </span>

                        @if($event->limited_spot && !is_null($spotsLeft))
                            <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">
                                <i class="fas fa-users mr-1 text-lime-200"></i>{{ __('Places restantes :') }} {{ $spotsLeft }}
                            </span>
                        @endif

                        <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">
                            <i class="fas fa-ticket-alt mr-1 text-amber-300"></i>{{ __('Sans réservation en ligne') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="px-6 py-6 sm:px-8 space-y-6">
                @if($event->image)
                    <img src="{{ asset('storage/' . $event->image) }}"
                         alt="{{ $event->name }}"
                         class="w-full h-72 object-cover rounded-xl border border-gray-200">
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-[#e2ecc3] bg-[#fdfaf3] px-4 py-3">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-[#854f38]">{{ __('Date & heure') }}</h3>
                        <p class="mt-2 text-sm font-medium text-slate-900">
                            {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-[#e2ecc3] bg-[#fdfaf3] px-4 py-3">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-[#854f38]">{{ __('Durée') }}</h3>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $event->duration }} {{ __('minutes') }}</p>
                    </div>

                    <div class="rounded-xl border border-[#e2ecc3] bg-[#fbfff6] px-4 py-3 md:col-span-2">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-[#647a0b]">
                            {{ $isVisio ? __('Visio') : __('Lieu') }}
                        </h3>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $event->location }}</p>
                    </div>

                    @if($event->associatedProduct && ($event->associatedProduct->price ?? 0) > 0)
                        <div class="rounded-xl border border-[#e2ecc3] bg-[#fdfaf3] px-4 py-3 md:col-span-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-[#854f38]">{{ __('Prix') }}</h3>
                            <p class="mt-2 text-sm font-medium text-slate-900">
                                {{ number_format($event->associatedProduct->price_incl_tax, 2, ',', ' ') }} €
                            </p>
                        </div>
                    @endif
                </div>

                @if($descText !== '')
                    <div class="rounded-xl border border-[#e2ecc3] bg-[#fbfff6] px-4 py-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-[#647a0b]">{{ __('Description') }}</h3>

                        <div class="mt-3 text-sm leading-relaxed text-slate-800">
                            @if($descLooksHtml)
                                <div class="ql-snow am-quill-view">
                                    <div class="ql-editor">
                                        {!! $desc !!}
                                    </div>
                                </div>
                            @else
                                {!! nl2br(e($desc)) !!}
                            @endif
                        </div>
                    </div>
                @endif

                <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-4 text-blue-900">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div>
                            <p class="font-semibold">{{ __('Cet événement est accessible sans réservation en ligne.') }}</p>
                            <p class="mt-1 text-sm">
                                {{ __('Si vous souhaitez en savoir plus, vous pouvez contacter directement le praticien depuis son portail.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('therapist.show', $event->user->slug) }}"
                       class="inline-flex items-center rounded-full bg-[#647a0b] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#50620c] transition">
                        <i class="fas fa-arrow-left mr-2"></i>{{ __('Retour au portail du praticien') }}
                    </a>

                    @if($event->booking_required)
                        <a href="{{ route('events.reserve.create', $event->id) }}"
                           class="inline-flex items-center rounded-full border border-[#854f38]/40 bg-white px-5 py-2.5 text-sm font-semibold text-[#854f38] hover:bg-[#854f38] hover:text-white transition">
                            <i class="fas fa-ticket-alt mr-2"></i>{{ __('Réserver') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

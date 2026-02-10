@php
    $therapistName = $therapist->company_name ?? $therapist->business_name ?? $therapist->name ?? 'Thérapeute';
@endphp

{{-- IMPORTANT: use x-app-layout (NOT x-guest-layout) to avoid narrow max-w-md wrappers --}}
<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        .btn-soft { border: 1px solid #edf1df; background: rgba(255,255,255,0.88); color: #647a0b; }
        .btn-soft:hover { background: #f7f9ef; }
        .btn-primary { background:#8ea633; color:white; }
        .btn-primary:hover { filter: brightness(0.98); }
        .chip { border: 1px solid #edf1df; background: #f7f9ef; color: #647a0b; }
        .dot { width: 10px; height: 10px; border-radius: 999px; display: inline-block; }

        .card-hover:hover { transform: translateY(-2px); }
        .card-hover { transition: all 220ms ease; }

        .field {
            border: 1px solid #edf1df;
            border-radius: 14px;
            background: white;
        }
        .field:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(142, 166, 51, 0.18);
        }
    </style>

    {{-- HERO --}}
    <section class="relative overflow-hidden border-b border-[#edf1df]">
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-[#8ea633]/20 via-white to-[#f7f9ef]"></div>

        <div class="max-w-7xl mx-auto px-6 py-10 md:py-12">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('therapist.show', ['slug' => $therapist->slug]) }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold btn-soft shadow-sm">
                    ← Retour au profil
                </a>

                <a href="{{ route('therapist.show', ['slug' => $therapist->slug]) }}"
                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold btn-primary shadow hover:shadow-lg transition">
                    Prendre RDV
                </a>
            </div>

            <div class="mt-10">
                <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-extrabold chip">
                    <span class="dot bg-[#8ea633]"></span>
                    Blog
                </div>

                <h1 class="mt-4 text-4xl sm:text-5xl font-black tracking-tight text-gray-900">
                    Articles
                </h1>

                <p class="mt-3 text-gray-600 text-base sm:text-lg">
                    {{ $therapistName }}
                </p>
            </div>
        </div>
    </section>

    {{-- BODY --}}
    <section class="bg-[#f7f9ef]">
        <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

            {{-- Feature: search + sort --}}
            <div class="bg-white rounded-2xl border border-[#edf1df] shadow-sm p-5">
                <form method="GET" class="flex flex-col md:flex-row gap-3 md:items-end">
                    <div class="w-full md:w-96">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Recherche</label>
                        <input type="text"
                               name="q"
                               value="{{ request('q') }}"
                               class="w-full px-4 py-2.5 field"
                               placeholder="Rechercher un article…">
                    </div>

                    <div class="w-full md:w-64">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tri</label>
                        <select name="sort" class="w-full px-4 py-2.5 field">
                            <option value="new" @selected(request('sort', 'new') === 'new')>Plus récents</option>
                            <option value="old" @selected(request('sort') === 'old')>Plus anciens</option>
                            <option value="readtime" @selected(request('sort') === 'readtime')>Temps de lecture</option>
                        </select>
                    </div>

                    <button class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold btn-soft transition">
                        Filtrer
                    </button>

                    @if(request()->has('q') || request()->has('sort'))
                        <a href="{{ route('pro.articles.index', ['therapist' => $therapist->slug]) }}"
                           class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold btn-soft transition">
                            Réinitialiser
                        </a>
                    @endif
                </form>

                {{-- tiny meta --}}
                <div class="mt-3 flex flex-wrap items-center gap-2 text-sm text-gray-600">
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 bg-[#f7f9ef] border border-[#edf1df]">
                        <span class="dot bg-[#8ea633]"></span>
                        {{ $articles->total() }} article{{ $articles->total() > 1 ? 's' : '' }}
                    </span>

                    @if(trim((string)request('q')) !== '')
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 bg-[#f7f9ef] border border-[#edf1df]">
                            Recherche: <span class="font-semibold">“{{ request('q') }}”</span>
                        </span>
                    @endif
                </div>
            </div>

            @if($articles->count() === 0)
                <div class="p-8 bg-white rounded-3xl shadow-sm border border-[#edf1df]">
                    <div class="text-lg font-bold text-gray-900">Aucun article publié pour le moment.</div>
                    <div class="text-gray-600 mt-2">
                        Revenez bientôt, de nouveaux contenus arrivent.
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('therapist.show', ['slug' => $therapist->slug]) }}"
                           class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold btn-primary shadow transition">
                            Voir le profil
                        </a>
                    </div>
                </div>
            @else
                {{-- Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($articles as $a)
                        @php
                            $cover = $a->cover_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($a->cover_path) : null;
                            $desc = $a->excerpt ?: ($a->meta_description ?: '');
                        @endphp

                        <a href="{{ route('pro.articles.show', ['therapist' => $therapist->slug, 'articleSlug' => $a->slug]) }}"
                           class="group block bg-white rounded-3xl shadow-sm border border-[#edf1df] overflow-hidden card-hover hover:shadow-md">

                            {{-- cover / placeholder --}}
                            <div class="relative">
                                @if($cover)
                                    <img src="{{ $cover }}" alt="" class="w-full h-48 object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/0 to-black/0 opacity-70"></div>
                                @else
                                    <div class="w-full h-48 bg-gradient-to-br from-[#8ea633]/20 via-white to-[#f7f9ef]"></div>
                                @endif

                                <div class="absolute bottom-3 left-3 flex items-center gap-2">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-extrabold bg-white/90 border border-[#edf1df] text-[#647a0b] backdrop-blur">
                                        <span class="dot bg-[#8ea633]"></span>
                                        {{ optional($a->published_at)->format('d/m/Y') }}
                                    </span>

                                    @if($a->reading_time)
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-extrabold bg-white/90 border border-[#edf1df] text-[#647a0b] backdrop-blur">
                                            {{ $a->reading_time }} min
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-6">
                                <div class="text-xs text-gray-500">
                                    /pro/{{ $therapist->slug }}/article/{{ $a->slug }}
                                </div>

                                <div class="mt-2 text-xl font-black tracking-tight text-gray-900 group-hover:text-[#647a0b] transition">
                                    {{ $a->title }}
                                </div>

                                @if($desc)
                                    <p class="mt-3 text-gray-700 line-clamp-3">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($desc), 160) }}
                                    </p>
                                @endif

                                <div class="mt-5 flex items-center justify-between">
                                    <div class="text-sm font-semibold text-[#647a0b]">
                                        Lire l’article →
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        Par {{ $therapistName }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $articles->links() }}
                </div>
            @endif
        </div>
    </section>
</x-app-layout>

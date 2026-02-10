@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $therapistName = $therapist->company_name ?? $therapist->business_name ?? $therapist->name ?? 'Thérapeute';
    $coverUrl = $article->cover_path ? Storage::disk('public')->url($article->cover_path) : null;
    $metaDesc = $article->meta_description ?: ($article->excerpt ?: '');

    // Profile info
    $profilePicUrl = null;
    if (!empty($therapist->profile_picture)) {
        $profilePicUrl = Str::startsWith($therapist->profile_picture, ['http://','https://'])
            ? $therapist->profile_picture
            : Storage::disk('public')->url($therapist->profile_picture);
    }

    $profileDesc = $therapist->profile_description
        ?: (isset($therapist->about) ? strip_tags((string)$therapist->about) : '');

    $profileDesc = trim((string)$profileDesc);

    // 5 random other published articles by same therapist (exclude current)
    // NOTE: if you already pass $otherArticles from controller, this will not override it.
    if (!isset($otherArticles)) {
        $otherArticles = \App\Models\TherapistArticle::query()
            ->where('user_id', $therapist->id)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('id', '!=', $article->id)
            ->inRandomOrder()
            ->limit(5)
            ->get();
    }
@endphp

<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        .article-prose { font-size: 18px; line-height: 1.9; color: #0f172a; }
        .article-prose h1 { font-size: 40px; line-height: 1.12; margin: 24px 0 10px; font-weight: 900; letter-spacing: -0.02em; }
        .article-prose h2 { font-size: 30px; line-height: 1.2;  margin: 26px 0 10px; font-weight: 800; letter-spacing: -0.01em; }
        .article-prose h3 { font-size: 22px; line-height: 1.3;  margin: 22px 0 8px;  font-weight: 800; }
        .article-prose p  { margin: 14px 0; }
        .article-prose ul, .article-prose ol { margin: 14px 0 14px 22px; }
        .article-prose li { margin: 8px 0; }
        .article-prose blockquote {
            border-left: 4px solid #8ea633;
            padding-left: 14px;
            margin: 20px 0;
            color: #334155;
        }
        .article-prose pre {
            background: #0b1020;
            color: #e5e7eb;
            border-radius: 16px;
            padding: 16px;
            overflow-x: auto;
            margin: 20px 0;
        }
        .article-prose code {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 2px 7px;
            font-size: 0.95em;
        }
        .article-prose pre code { background: transparent; padding: 0; }
        .article-prose a { color: #647a0b; text-decoration: underline; font-weight: 800; }
        .article-prose img { border-radius: 18px; border: 1px solid #edf1df; margin: 18px 0; }

        .btn-soft { border: 1px solid #edf1df; background: rgba(255,255,255,0.88); color: #647a0b; }
        .btn-soft:hover { background: #f7f9ef; }
        .btn-primary { background:#8ea633; color:white; }
        .btn-primary:hover { filter: brightness(0.98); }
        .chip { border: 1px solid #edf1df; background: #f7f9ef; color: #647a0b; }
        .dot { width: 10px; height: 10px; border-radius: 999px; display: inline-block; }

        .glass {
            background: rgba(255,255,255,0.80);
            border: 1px solid rgba(237,241,223,0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .soft-ring:focus { outline: none; box-shadow: 0 0 0 4px rgba(142, 166, 51, 0.18); }
    </style>

    {{-- FULL WIDTH HERO --}}
    <section class="relative overflow-hidden isolate">
        @if($coverUrl)
            <div class="absolute inset-0 -z-10">
                <img src="{{ $coverUrl }}" class="w-full h-full object-cover" alt="">
                <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/40 to-[#f7f9ef]"></div>
            </div>
        @else
            <div class="absolute inset-0 -z-10 bg-gradient-to-b from-[#8ea633]/25 via-white to-[#f7f9ef]"></div>
        @endif

        <div class="max-w-7xl mx-auto px-6 py-10 md:py-14">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('pro.articles.index', ['therapist' => $therapist->slug]) }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold btn-soft glass shadow-sm">
                    ← Retour aux articles
                </a>

                <div class="flex items-center gap-2">
                    <a href="{{ route('therapist.show', ['slug' => $therapist->slug]) }}"
                       class="hidden sm:inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold btn-soft glass shadow-sm">
                        Voir le profil
                    </a>

                    <a href="{{ route('therapist.show', ['slug' => $therapist->slug]) }}"
                       class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold btn-primary shadow hover:shadow-lg transition">
                        Prendre RDV
                    </a>
                </div>
            </div>

            <div class="mt-10 max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-extrabold chip">
                    <span class="dot bg-[#8ea633]"></span>
                    Article
                </div>

                <h1 class="mt-4 text-4xl sm:text-5xl font-black tracking-tight text-white drop-shadow">
                    {{ $article->title }}
                </h1>

                @if($metaDesc)
                    <p class="mt-4 text-base sm:text-lg text-white/90 max-w-2xl">
                        {{ Str::limit(strip_tags($metaDesc), 220) }}
                    </p>
                @endif

                <div class="mt-5 flex flex-wrap items-center gap-3 text-sm text-white/90">
                    <span class="inline-flex items-center gap-2">
                        <span class="dot bg-white/80"></span>
                        Par <span class="font-extrabold text-white">{{ $therapistName }}</span>
                    </span>

                    <span class="hidden sm:inline text-white/70">•</span>

                    <span>
                        Publié le <span class="font-semibold text-white">{{ optional($article->published_at)->format('d/m/Y') }}</span>
                    </span>

                    @if($article->reading_time)
                        <span class="hidden sm:inline text-white/70">•</span>
                        <span><span class="font-semibold text-white">{{ $article->reading_time }}</span> min</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- PAGE BODY --}}
    <section class="bg-[#f7f9ef] border-t border-[#edf1df]">
        <div class="max-w-7xl mx-auto px-6 py-10">

            {{-- Big cover card (optional) --}}
            @if($coverUrl)
                <div class="-mt-16 mb-8">
                    <div class="bg-white rounded-[28px] border border-[#edf1df] shadow-sm overflow-hidden">
                        <img src="{{ $coverUrl }}" alt="" class="w-full object-cover" style="max-height: 520px;">
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                {{-- Main --}}
                <div class="lg:col-span-8">
                    <article class="bg-white rounded-[28px] border border-[#edf1df] shadow-sm p-6 sm:p-10">
                        @php
                            $rawHtml = (string) ($article->content_html ?? '');
                            $htmlTrim = trim(strip_tags($rawHtml));
                        @endphp

                        @if($htmlTrim === '')
                            <div class="p-4 rounded-xl border border-yellow-200 bg-yellow-50 text-yellow-900 text-sm">
                                ⚠️ Contenu indisponible.
                            </div>
                        @else
                            <div class="article-prose">
                                {!! $article->content_html !!}
                            </div>
                        @endif

                        <div class="mt-10 pt-6 border-t border-[#edf1df] text-sm text-gray-600 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <div class="text-xs text-gray-500">Publié par</div>
                                <div class="font-extrabold text-gray-900">{{ $therapistName }}</div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('pro.articles.index', ['therapist' => $therapist->slug]) }}"
                                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold btn-soft transition">
                                    Tous les articles
                                </a>
                                <a href="{{ route('therapist.show', ['slug' => $therapist->slug]) }}"
                                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold btn-primary shadow transition">
                                    Voir le profil
                                </a>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- Sidebar --}}
                <aside class="lg:col-span-4 space-y-6">

                    {{-- À propos (with pic + name + description) --}}
                    <div class="bg-white rounded-[28px] border border-[#edf1df] shadow-sm p-6">
                        <div class="flex items-start gap-4">
                            <div class="shrink-0">
                                @if($profilePicUrl)
                                    <img src="{{ $profilePicUrl }}"
                                         alt=""
                                         class="w-14 h-14 rounded-2xl object-cover border border-[#edf1df]">
                                @else
                                    <div class="w-14 h-14 rounded-2xl bg-[#f7f9ef] border border-[#edf1df] flex items-center justify-center text-[#647a0b] font-black">
                                        {{ Str::upper(Str::substr($therapistName, 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="text-sm font-extrabold text-[#647a0b]">À propos</div>
                                <div class="mt-1 font-black text-gray-900 truncate">{{ $therapistName }}</div>

                                @if($profileDesc !== '')
                                    <div class="mt-2 text-gray-700 text-sm leading-relaxed">
                                        {{ Str::limit($profileDesc, 220) }}
                                    </div>
                                @else
                                    <div class="mt-2 text-gray-600 text-sm">
                                        Découvrez le profil pour en savoir plus.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-5 flex flex-col gap-2">
                            <a href="{{ route('therapist.show', ['slug' => $therapist->slug]) }}"
                               class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold btn-primary shadow hover:shadow-lg transition">
                                Prendre rendez-vous
                            </a>

                            <a href="{{ route('therapist.show', ['slug' => $therapist->slug]) }}"
                               class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold btn-soft transition">
                                Voir le profil
                            </a>
                        </div>
                    </div>

                    {{-- Tags --}}
                    @if(is_array($article->tags) && count($article->tags))
                        <div class="bg-white rounded-[28px] border border-[#edf1df] shadow-sm p-6">
                            <div class="text-sm font-extrabold text-[#647a0b]">Tags</div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($article->tags as $t)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#f7f9ef] border border-[#edf1df] text-xs font-extrabold text-[#647a0b]">
                                        #{{ $t }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Navigation --}}
                    <div class="bg-white rounded-[28px] border border-[#edf1df] shadow-sm p-6">
                        <div class="text-sm font-extrabold text-[#647a0b]">Navigation</div>

                        <div class="mt-3 space-y-2 text-sm">
                            <a class="font-semibold text-[#647a0b] hover:underline"
                               href="{{ route('pro.articles.index', ['therapist' => $therapist->slug]) }}">
                                ← Retour aux articles
                            </a>

                            <div class="text-gray-600">
                                Profil :
                                <a class="font-semibold text-[#647a0b] hover:underline"
                                   href="{{ route('therapist.show', ['slug' => $therapist->slug]) }}">
                                    {{ $therapistName }}
                                </a>
                            </div>
                        </div>

                        {{-- Random 5 other articles --}}
                        @if($otherArticles && $otherArticles->count())
                            <div class="mt-5 pt-5 border-t border-[#edf1df]">
                                <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wide">
                                    Autres articles
                                </div>

                                <div class="mt-3 space-y-3">
                                    @foreach($otherArticles as $oa)
                                        @php
                                            $oaCover = $oa->cover_path ? Storage::disk('public')->url($oa->cover_path) : null;
                                        @endphp

                                        <a href="{{ route('pro.articles.show', ['therapist' => $therapist->slug, 'articleSlug' => $oa->slug]) }}"
                                           class="group flex items-center gap-3 rounded-2xl border border-[#edf1df] bg-white hover:bg-[#f7f9ef] transition p-3">
                                            <div class="shrink-0">
                                                @if($oaCover)
                                                    <img src="{{ $oaCover }}" alt="" class="w-12 h-12 rounded-xl object-cover border border-[#edf1df]">
                                                @else
                                                    <div class="w-12 h-12 rounded-xl bg-[#f7f9ef] border border-[#edf1df] flex items-center justify-center text-[#647a0b] font-black">
                                                        {{ Str::upper(Str::substr($oa->title, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="min-w-0">
                                                <div class="font-extrabold text-gray-900 text-sm truncate group-hover:text-[#647a0b]">
                                                    {{ $oa->title }}
                                                </div>
                                                <div class="text-xs text-gray-600">
                                                    {{ optional($oa->published_at)->format('d/m/Y') }}
                                                    @if($oa->reading_time) · {{ $oa->reading_time }} min @endif
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                </aside>
            </div>
        </div>
    </section>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            Aperçu
        </h2>
    </x-slot>

    <style>
        /* Medium-like article typography on the preview */
        .article-prose {
            font-size: 18px;
            line-height: 1.8;
            color: #111827;
        }
        .article-prose h1 { font-size: 34px; line-height: 1.2; margin: 22px 0 10px; font-weight: 800; }
        .article-prose h2 { font-size: 26px; line-height: 1.25; margin: 22px 0 10px; font-weight: 800; }
        .article-prose h3 { font-size: 20px; line-height: 1.35; margin: 18px 0 8px; font-weight: 800; }
        .article-prose p  { margin: 12px 0; }
        .article-prose ul, .article-prose ol { margin: 12px 0 12px 22px; }
        .article-prose li { margin: 6px 0; }
        .article-prose blockquote {
            border-left: 4px solid #8ea633;
            padding-left: 14px;
            margin: 16px 0;
            color: #374151;
        }
        .article-prose pre {
            background: #0b1020;
            color: #e5e7eb;
            border-radius: 14px;
            padding: 14px;
            overflow-x: auto;
            margin: 16px 0;
        }
        .article-prose code {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 2px 6px;
            font-size: 0.95em;
        }
        .article-prose pre code {
            background: transparent;
            padding: 0;
        }
        .article-prose a {
            color: #647a0b;
            text-decoration: underline;
            font-weight: 600;
        }
        .article-prose img {
            border-radius: 16px;
            border: 1px solid #edf1df;
            margin: 14px 0;
        }
        .article-prose hr {
            border: 0;
            border-top: 1px solid #edf1df;
            margin: 22px 0;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #edf1df;
            background: #f7f9ef;
            color: #647a0b;
        }
        .dot { width: 10px; height: 10px; border-radius: 999px; display: inline-block; }
    </style>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Top bar --}}
            <div class="bg-white shadow rounded-2xl p-6 border border-[#edf1df] flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-sm text-gray-500">Article</div>
                    <h1 class="text-2xl font-bold text-gray-900 mt-1 break-words">{{ $article->title }}</h1>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @if($article->status === 'published')
                            <span class="badge">
                                <span class="dot bg-[#8ea633]"></span> Publié
                            </span>
                        @else
                            <span class="badge" style="background:#f3f4f6;color:#374151;border-color:#e5e7eb;">
                                <span class="dot bg-gray-400"></span> Brouillon
                            </span>
                        @endif

                        @if($article->published_at)
                            <span class="text-sm text-gray-600">
                                · Publication: <span class="font-semibold">{{ $article->published_at->format('d/m/Y H:i') }}</span>
                            </span>
                        @endif

                        <span class="text-sm text-gray-600">
                            · <span class="font-semibold">{{ $article->reading_time ?? 1 }}</span> min
                        </span>

                        <span class="text-sm text-gray-600">
                            · Vues: <span class="font-semibold">{{ $article->views ?? 0 }}</span>
                        </span>
                    </div>

                    @if(is_array($article->tags) && count($article->tags))
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($article->tags as $t)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white border border-[#edf1df] text-xs font-semibold text-[#647a0b]">
                                    #{{ $t }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2 justify-end">
                    <a href="{{ route('dashboardpro.articles.index') }}"
                       class="inline-flex items-center justify-center rounded-lg border border-[#edf1df] bg-white px-4 py-2 text-sm font-semibold text-[#647a0b] hover:bg-[#f7f9ef] transition">
                        Retour
                    </a>

                    <a href="{{ route('dashboardpro.articles.edit', $article) }}"
                       class="inline-flex items-center justify-center rounded-lg bg-[#8ea633] px-4 py-2 text-sm font-semibold text-white shadow hover:shadow-lg transition">
                        Éditer
                    </a>

                    @if($article->status === 'published')
                        <a href="{{ route('pro.articles.show', ['therapist' => auth()->user()->slug, 'articleSlug' => $article->slug]) }}"
                           target="_blank"
                           class="inline-flex items-center justify-center rounded-lg border border-[#edf1df] bg-white px-4 py-2 text-sm font-semibold text-[#647a0b] hover:bg-[#f7f9ef] transition">
                            Voir public
                        </a>
                    @endif
                </div>
            </div>

            {{-- Cover (only if exists) --}}
            @if($article->cover_path)
                <div class="bg-white shadow rounded-2xl p-6 border border-[#edf1df]">
                    <div class="text-sm font-semibold text-[#647a0b] mb-3">Image de couverture</div>

                    <img class="w-full rounded-2xl border border-[#edf1df] object-cover"
                         style="max-height: 380px;"
                         src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($article->cover_path) }}"
                         alt="">
                </div>
            @endif

            {{-- Content --}}
            <div class="bg-white shadow rounded-2xl p-6 border border-[#edf1df]">
                <div class="text-sm font-semibold text-[#647a0b] mb-3">Lecture</div>

                @php
                    $rawHtml = (string) ($article->content_html ?? '');
                    $htmlTrim = trim(strip_tags($rawHtml));

                    // Quill sometimes stores "<p><br></p>" for empty.
                    $looksEmpty = ($htmlTrim === '');
                @endphp

                @if($looksEmpty)
                    <div class="p-4 rounded-xl border border-yellow-200 bg-yellow-50 text-yellow-900 text-sm">
                        ⚠️ Le contenu est vide.
                        <div class="mt-2">
                            <a href="{{ route('dashboardpro.articles.edit', $article) }}" class="underline font-semibold">
                                Ouvrir l’éditeur
                            </a>
                            puis enregistrer.
                        </div>
                    </div>
                @else
                    {{-- IMPORTANT: render as HTML (Quill output) --}}
                    <article class="article-prose">
                        {!! $article->content_html !!}
                    </article>
                @endif
            </div>

            {{-- Footer meta --}}
            <div class="bg-white shadow rounded-2xl p-6 border border-[#edf1df] text-sm text-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <div class="text-xs text-gray-500">Slug</div>
                        <div class="font-semibold">{{ $article->slug }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Dernière maj</div>
                        <div class="font-semibold">{{ $article->updated_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">URL publique</div>
                        <div class="font-semibold break-all">
                            /pro/{{ auth()->user()->slug }}/article/{{ $article->slug }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

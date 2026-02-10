<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            Blog · Articles
        </h2>
    </x-slot>

    <style>
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            border: 1px solid #edf1df;
            background: #f7f9ef;
            color: #647a0b;
            white-space: nowrap;
        }
        .dot { width: 10px; height: 10px; border-radius: 999px; display: inline-block; }
        .btn-soft {
            border: 1px solid #edf1df;
            background: white;
            color: #647a0b;
        }
        .btn-soft:hover { background: #f7f9ef; }
        .btn-primary {
            background: #8ea633;
            color: white;
        }
        .btn-primary:hover { filter: brightness(0.98); }
        .field {
            border: 1px solid #edf1df;
            border-radius: 12px;
        }
        .field:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(142, 166, 51, 0.18);
        }
        /* clickable row feel */
        tr.row-hover:hover td { background: #fbfcf6; }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Top bar --}}
            <div class="bg-white shadow rounded-2xl p-6 border border-[#edf1df]">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-sm text-gray-500">Portail Pro</div>
                        <div class="text-lg font-semibold text-gray-900">
                            Gérez vos articles
                        </div>

                        {{-- Tiny stats --}}
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="badge">
                                <span class="dot bg-gray-400"></span>
                                Total: {{ $articles->total() }}
                            </span>

                            @php
                                $status = request('status');
                                $q = trim((string) request('q'));
                            @endphp

                            @if($status)
                                <span class="badge">
                                    <span class="dot bg-[#8ea633]"></span>
                                    Filtre: {{ $status === 'published' ? 'Publiés' : 'Brouillons' }}
                                </span>
                            @endif

                            @if($q !== '')
                                <span class="badge">
                                    <span class="dot bg-[#8ea633]"></span>
                                    Recherche: “{{ $q }}”
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 justify-end">
                        {{-- Reset filters --}}
                        @if(request()->has('q') || request()->has('status'))
                            <a href="{{ route('dashboardpro.articles.index') }}"
                               class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold btn-soft transition">
                                Réinitialiser
                            </a>
                        @endif

                        <a href="{{ route('dashboardpro.articles.create') }}"
                           class="inline-flex items-center justify-center rounded-lg btn-primary px-4 py-2 text-sm font-semibold shadow hover:shadow-lg transition">
                            + Nouvel article
                        </a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-white shadow rounded-2xl p-4 border border-[#edf1df]">
                    <div class="text-[#647a0b] font-semibold">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            {{-- Filters --}}
            <div class="bg-white shadow rounded-2xl p-6 border border-[#edf1df]">
                <form method="GET" class="flex flex-col md:flex-row gap-3 md:items-end">
                    <div class="w-full md:w-96">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Recherche</label>
                        <input type="text" name="q" value="{{ request('q') }}"
                               class="w-full px-3 py-2 field"
                               placeholder="Rechercher un titre, un extrait…">
                    </div>

                    <div class="w-full md:w-56">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Statut</label>
                        <select name="status" class="w-full px-3 py-2 field bg-white">
                            <option value="">Tous</option>
                            <option value="draft" @selected(request('status')==='draft')>Brouillons</option>
                            <option value="published" @selected(request('status')==='published')>Publiés</option>
                        </select>
                    </div>

                    <button class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold btn-soft transition">
                        Filtrer
                    </button>

                    {{-- Quick link to create when searching --}}
                    <a href="{{ route('dashboardpro.articles.create') }}"
                       class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold btn-primary shadow transition">
                        Créer
                    </a>
                </form>
            </div>

            {{-- List (table on desktop, cards on mobile) --}}
            <div class="bg-white shadow rounded-2xl border border-[#edf1df] overflow-hidden">

                {{-- Desktop table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#f7f9ef] border-b border-[#edf1df]">
                            <tr>
                                <th class="text-left p-4 text-[#647a0b] font-semibold">Titre</th>
                                <th class="text-left p-4 text-[#647a0b] font-semibold">Statut</th>
                                <th class="text-left p-4 text-[#647a0b] font-semibold">Publication</th>
                                <th class="text-left p-4 text-[#647a0b] font-semibold">Dernière maj</th>
                                <th class="text-right p-4 text-[#647a0b] font-semibold">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($articles as $a)
                                <tr class="border-b border-[#edf1df] row-hover">
                                    <td class="p-4">
                                        <div class="font-semibold text-gray-900">{{ $a->title }}</div>
                                        <div class="text-xs text-gray-500">/{{ $a->slug }}</div>
                                        @if(!empty($a->excerpt))
                                            <div class="text-xs text-gray-600 mt-2 line-clamp-2">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($a->excerpt), 110) }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="p-4">
                                        @if($a->status === 'published')
                                            <span class="badge">
                                                <span class="dot bg-[#8ea633]"></span> Publié
                                            </span>
                                        @else
                                            <span class="badge" style="background:#f3f4f6;color:#374151;border-color:#e5e7eb;">
                                                <span class="dot bg-gray-400"></span> Brouillon
                                            </span>
                                        @endif
                                    </td>

                                    <td class="p-4 text-gray-700">
                                        {{ $a->published_at ? $a->published_at->format('d/m/Y H:i') : '—' }}
                                    </td>

                                    <td class="p-4 text-gray-700">
                                        {{ $a->updated_at ? $a->updated_at->format('d/m/Y H:i') : '—' }}
                                    </td>

                                    <td class="p-4 text-right whitespace-nowrap">
                                        <a href="{{ route('dashboardpro.articles.edit', $a) }}"
                                           class="text-sm font-semibold text-[#647a0b] hover:underline mr-4">
                                            Éditer
                                        </a>

                                        <a href="{{ route('dashboardpro.articles.show', $a) }}"
                                           class="text-sm font-semibold text-[#647a0b] hover:underline mr-4">
                                            Aperçu
                                        </a>

                                        @if($a->status === 'published')
                                            <a href="{{ route('pro.articles.show', ['therapist' => auth()->user()->slug, 'articleSlug' => $a->slug]) }}"
                                               target="_blank"
                                               class="text-sm font-semibold text-[#647a0b] hover:underline mr-4">
                                                Public
                                            </a>
                                        @endif

                                        <form action="{{ route('dashboardpro.articles.destroy', $a) }}"
                                              method="POST" class="inline"
                                              onsubmit="return confirm('Supprimer cet article ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm font-semibold text-[#a96b56] hover:underline">
                                                Supprimer
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-6 text-gray-600" colspan="5">
                                        Aucun article.
                                        <a class="text-[#647a0b] font-semibold hover:underline"
                                           href="{{ route('dashboardpro.articles.create') }}">Créer le premier</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="md:hidden divide-y divide-[#edf1df]">
                    @forelse($articles as $a)
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-900 break-words">{{ $a->title }}</div>
                                    <div class="text-xs text-gray-500 break-all mt-1">/{{ $a->slug }}</div>

                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        @if($a->status === 'published')
                                            <span class="badge">
                                                <span class="dot bg-[#8ea633]"></span> Publié
                                            </span>
                                        @else
                                            <span class="badge" style="background:#f3f4f6;color:#374151;border-color:#e5e7eb;">
                                                <span class="dot bg-gray-400"></span> Brouillon
                                            </span>
                                        @endif

                                        <span class="text-xs text-gray-600">
                                            Maj: {{ $a->updated_at?->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </div>

                                <a href="{{ route('dashboardpro.articles.edit', $a) }}"
                                   class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold btn-soft transition">
                                    Éditer
                                </a>
                            </div>

                            @if(!empty($a->excerpt))
                                <div class="text-sm text-gray-700 mt-3">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($a->excerpt), 140) }}
                                </div>
                            @endif

                            <div class="mt-3 flex flex-wrap gap-3 text-sm">
                                <a href="{{ route('dashboardpro.articles.show', $a) }}"
                                   class="font-semibold text-[#647a0b] hover:underline">
                                    Aperçu
                                </a>

                                @if($a->status === 'published')
                                    <a href="{{ route('pro.articles.show', ['therapist' => auth()->user()->slug, 'articleSlug' => $a->slug]) }}"
                                       target="_blank"
                                       class="font-semibold text-[#647a0b] hover:underline">
                                        Public
                                    </a>
                                @endif

                                <form action="{{ route('dashboardpro.articles.destroy', $a) }}"
                                      method="POST"
                                      onsubmit="return confirm('Supprimer cet article ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="font-semibold text-[#a96b56] hover:underline">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-gray-600">
                            Aucun article.
                            <a class="text-[#647a0b] font-semibold hover:underline"
                               href="{{ route('dashboardpro.articles.create') }}">Créer le premier</a>.
                        </div>
                    @endforelse
                </div>
            </div>

            <div>
                {{ $articles->links() }}
            </div>

        </div>
    </div>
</x-app-layout>

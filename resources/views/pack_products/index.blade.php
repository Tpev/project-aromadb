<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl" style="color:#647a0b;">
                    Packs (Forfaits)
                </h2>
                <p class="mt-1 text-xs text-slate-600">
                    Créez des forfaits (ex: 5 massages) et suivez les crédits consommés.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('products.create') }}"
                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold ring-1 ring-[#6b4f2a]/25 bg-white hover:bg-[#f7f2ea] transition"
                   style="color:#6b4f2a;">
                    + Nouvelle prestation
                </a>

                <a href="{{ route('pack-products.create') }}"
                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-95 transition"
                   style="background:#647a0b;">
                    + Nouveau pack
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        :root{
            --brand: #647a0b;
            --brown: #6b4f2a;
            --cream: #f7f2ea;
        }
        .am-shell{
            background:
                radial-gradient(circle at 0% 0%, rgba(100,122,11,0.10), transparent 45%),
                radial-gradient(circle at 100% 10%, rgba(107,79,42,0.10), transparent 45%),
                radial-gradient(circle at 20% 100%, rgba(100,122,11,0.08), transparent 55%),
                linear-gradient(180deg, #fbfaf7 0%, #f3f4f6 100%);
            border-radius: 1.25rem;
        }
        .am-card{
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(15,23,42,0.10);
            box-shadow: 0 10px 30px rgba(15,23,42,0.05);
            border-radius: 1.25rem;
            overflow: hidden;
        }
        .am-badge{
            border-radius: 999px;
            padding: .25rem .6rem;
            font-size: .75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border: 1px solid rgba(15,23,42,0.10);
            background: white;
        }
        .am-dot{
            width: .45rem;
            height: .45rem;
            border-radius: 999px;
            display: inline-block;
        }
        .am-table th{
            font-size: .75rem;
            letter-spacing: .02em;
            text-transform: uppercase;
        }
        .am-row:hover{
            background: rgba(100,122,11,0.06);
        }
    </style>

    <div class="container mt-6">
        <div class="am-shell p-4 md:p-6">
            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-bold">Succès</div>
                            <div class="text-sm mt-0.5">{{ session('success') }}</div>
                        </div>
                        <div class="text-xs font-semibold opacity-70">AromaMade</div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                <div class="am-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold text-slate-500">Nombre de packs</div>
                            <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ $packs->count() }}</div>
                        </div>
                        <div class="h-10 w-10 rounded-2xl flex items-center justify-center"
                             style="background: rgba(100,122,11,0.10); color: var(--brand);">
                            ✦
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-slate-600">
                        Forfaits visibles dans votre espace thérapeute.
                    </div>
                </div>

                <div class="am-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold text-slate-500">Attributions (total)</div>
                            <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ $packs->sum('purchases_count') }}</div>
                        </div>
                        <div class="h-10 w-10 rounded-2xl flex items-center justify-center"
                             style="background: rgba(107,79,42,0.10); color: var(--brown);">
                            ☰
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-slate-600">
                        Total des packs attribués à vos clients.
                    </div>
                </div>

                <div class="am-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold text-slate-500">Action rapide</div>
                            <div class="mt-1 text-sm font-bold text-slate-900">Créer un nouveau pack</div>
                        </div>
                        <a href="{{ route('pack-products.create') }}"
                           class="rounded-xl px-3 py-2 text-xs font-bold text-white shadow-sm hover:opacity-95 transition"
                           style="background: var(--brand);">
                            + Pack
                        </a>
                    </div>
                    <div class="mt-2 text-xs text-slate-600">
                        Exemple : “Forfait 5 massages”.
                    </div>
                </div>
            </div>

            <div class="am-card">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-slate-200/70">
                    <div>
                        <div class="text-sm font-extrabold text-slate-900">Liste des packs</div>
                        <div class="text-xs text-slate-600 mt-0.5">Gérez vos forfaits et accédez aux détails.</div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('products.index') }}"
                           class="rounded-xl px-3 py-2 text-xs font-bold ring-1 ring-slate-200 bg-white hover:bg-slate-50 transition"
                           style="color: var(--brown);">
                            Voir prestations
                        </a>
                        <a href="{{ route('pack-products.create') }}"
                           class="rounded-xl px-3 py-2 text-xs font-bold text-white shadow-sm hover:opacity-95 transition"
                           style="background: var(--brand);">
                            + Nouveau pack
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm am-table">
                        <thead style="background: linear-gradient(90deg, rgba(100,122,11,0.10), rgba(107,79,42,0.08));">
                            <tr class="text-left text-slate-700">
                                <th class="px-4 py-3 font-extrabold">Nom</th>
                                <th class="px-4 py-3 font-extrabold">Prix</th>
                                <th class="px-4 py-3 font-extrabold">TVA</th>
                                <th class="px-4 py-3 font-extrabold">Statut</th>
                                <th class="px-4 py-3 font-extrabold">Attributions</th>
                                <th class="px-4 py-3 font-extrabold text-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($packs as $pack)
                            <tr class="am-row">
                                <td class="px-4 py-3">
                                    <a class="font-extrabold text-slate-900 hover:underline"
                                       href="{{ route('pack-products.show', $pack) }}">
                                        {{ $pack->name }}
                                    </a>

                                    @if($pack->description)
                                        <div class="text-xs text-slate-600 mt-1 line-clamp-2">
                                            {{ $pack->description }}
                                        </div>
                                    @endif

                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @if($pack->visible_in_portal)
                                            <span class="am-badge" style="color: var(--brand);">
                                                <span class="am-dot" style="background: var(--brand)"></span>
                                                Portail: visible
                                            </span>
                                        @else
                                            <span class="am-badge text-slate-600">
                                                <span class="am-dot bg-slate-400"></span>
                                                Portail: masqué
                                            </span>
                                        @endif

                                        @if($pack->price_visible_in_portal)
                                            <span class="am-badge" style="color: var(--brown);">
                                                <span class="am-dot" style="background: var(--brown)"></span>
                                                Prix: visible
                                            </span>
                                        @else
                                            <span class="am-badge text-slate-600">
                                                <span class="am-dot bg-slate-400"></span>
                                                Prix: masqué
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap font-bold text-slate-900">
                                    {{ number_format($pack->price, 2, ',', ' ') }} €
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                                    {{ number_format($pack->tax_rate, 2, ',', ' ') }}%
                                </td>

                                <td class="px-4 py-3">
                                    @if($pack->is_active)
                                        <span class="am-badge" style="color: var(--brand); border-color: rgba(100,122,11,0.25); background: rgba(100,122,11,0.06);">
                                            <span class="am-dot" style="background: var(--brand)"></span>
                                            Actif
                                        </span>
                                    @else
                                        <span class="am-badge text-slate-700" style="background: rgba(15,23,42,0.04);">
                                            <span class="am-dot bg-slate-500"></span>
                                            Inactif
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-extrabold"
                                          style="background: rgba(107,79,42,0.10); color: var(--brown); border: 1px solid rgba(107,79,42,0.18);">
                                        {{ $pack->purchases_count }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <div class="inline-flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('pack-products.show', $pack) }}"
                                           class="rounded-xl px-3 py-1.5 text-xs font-extrabold ring-1 ring-slate-200 bg-white hover:bg-slate-50 transition"
                                           style="color: var(--brand);">
                                            Voir
                                        </a>

                                        <a href="{{ route('pack-products.edit', $pack) }}"
                                           class="rounded-xl px-3 py-1.5 text-xs font-extrabold ring-1 ring-[#6b4f2a]/25 bg-white hover:bg-[#f7f2ea] transition"
                                           style="color: var(--brown);">
                                            Modifier
                                        </a>

                                        <form action="{{ route('pack-products.destroy', $pack) }}" method="POST"
                                              onsubmit="return confirm('Supprimer ce pack ?');">
                                            @csrf @method('DELETE')
                                            <button class="rounded-xl px-3 py-1.5 text-xs font-extrabold ring-1 ring-red-200 text-red-700 hover:bg-red-50 transition">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <div class="mx-auto max-w-md">
                                        <div class="text-lg font-extrabold text-slate-900">Aucun pack pour l’instant</div>
                                        <div class="mt-1 text-sm text-slate-600">
                                            Crée ton premier forfait et attribue-le à tes clients pour suivre les crédits.
                                        </div>
                                        <div class="mt-4 flex flex-col sm:flex-row gap-2 justify-center">
                                            <a href="{{ route('pack-products.create') }}"
                                               class="rounded-xl px-4 py-2 text-sm font-extrabold text-white shadow-sm hover:opacity-95 transition"
                                               style="background: var(--brand);">
                                                + Nouveau pack
                                            </a>
                                            <a href="{{ route('products.create') }}"
                                               class="rounded-xl px-4 py-2 text-sm font-extrabold ring-1 ring-[#6b4f2a]/25 bg-white hover:bg-[#f7f2ea] transition"
                                               style="color: var(--brown);">
                                                + Nouvelle prestation
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-200/70 bg-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div class="text-xs text-slate-600">
                            Astuce : crée un pack “Suivi” et attribue-le au client après paiement (crédits suivis automatiquement).
                        </div>
                        <div class="text-xs font-bold" style="color: var(--brown);">
                            AromaMade PRO
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

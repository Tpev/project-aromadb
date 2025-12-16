<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl" style="color:#647a0b;">
                    {{ $pack->name }}
                </h2>
                <p class="mt-1 text-xs text-slate-600">
                    Pack / forfait de prestations
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('pack-products.edit', $pack) }}"
                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-bold text-white shadow-sm hover:opacity-95 transition"
                   style="background:#647a0b;">
                    ✎ Modifier
                </a>

                <a href="{{ route('pack-products.index') }}"
                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold ring-1 ring-slate-200 bg-white hover:bg-slate-50 transition"
                   style="color:#6b4f2a;">
                    ← Retour
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        :root{
            --brand:#647a0b;
            --brown:#6b4f2a;
            --cream:#f7f2ea;
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
        }
        .am-badge{
            border-radius: 999px;
            padding: .25rem .6rem;
            font-size: .75rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border: 1px solid rgba(15,23,42,0.10);
            background: white;
        }
        .am-dot{ width:.45rem; height:.45rem; border-radius:999px; display:inline-block; }
        .am-table th{ font-size:.75rem; letter-spacing:.02em; text-transform: uppercase; }
        .am-row:hover{ background: rgba(100,122,11,0.06); }
        .am-input{
            width:100%;
            border-radius: .9rem;
            border-color: rgb(203 213 225);
        }
        .am-input:focus{
            border-color: var(--brand);
            --tw-ring-color: var(--brand);
        }
    </style>

    <div class="container mt-6">
        <div class="am-shell p-4 md:p-6 space-y-5">

            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-extrabold">Succès</div>
                            <div class="text-sm mt-0.5">{{ session('success') }}</div>
                        </div>
                        <div class="text-xs font-bold opacity-70">AromaMade</div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- Pack details --}}
                <div class="lg:col-span-2 am-card p-5">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-slate-600">Prix du pack</div>

                            <div class="mt-1 flex flex-wrap items-baseline gap-2">
                                <div class="text-3xl font-extrabold text-slate-900">
                                    {{ number_format($pack->price, 2, ',', ' ') }} €
                                </div>
                                <div class="text-sm font-semibold text-slate-600">
                                    TVA {{ number_format($pack->tax_rate, 2, ',', ' ') }}%
                                </div>
                            </div>

                            @if($pack->description)
                                <div class="mt-4 text-sm text-slate-700 whitespace-pre-line">
                                    {{ $pack->description }}
                                </div>
                            @else
                                <div class="mt-4 text-sm text-slate-500">
                                    Aucune description.
                                </div>
                            @endif

                            <div class="mt-4 flex flex-wrap gap-2">
                                @if($pack->is_active)
                                    <span class="am-badge" style="color:var(--brand); border-color: rgba(100,122,11,0.25); background: rgba(100,122,11,0.06);">
                                        <span class="am-dot" style="background:var(--brand)"></span> Actif
                                    </span>
                                @else
                                    <span class="am-badge text-slate-700" style="background: rgba(15,23,42,0.04);">
                                        <span class="am-dot bg-slate-500"></span> Inactif
                                    </span>
                                @endif

                                @if($pack->visible_in_portal)
                                    <span class="am-badge" style="color:var(--brand);">
                                        <span class="am-dot" style="background:var(--brand)"></span> Portail visible
                                    </span>
                                @else
                                    <span class="am-badge text-slate-600">
                                        <span class="am-dot bg-slate-400"></span> Portail masqué
                                    </span>
                                @endif

                                @if($pack->price_visible_in_portal)
                                    <span class="am-badge" style="color:var(--brown);">
                                        <span class="am-dot" style="background:var(--brown)"></span> Prix visible
                                    </span>
                                @else
                                    <span class="am-badge text-slate-600">
                                        <span class="am-dot bg-slate-400"></span> Prix masqué
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="shrink-0 w-full md:w-auto">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-600">Actions</div>
                                <div class="mt-3 grid grid-cols-1 gap-2">
                                    <a href="{{ route('pack-products.edit', $pack) }}"
                                       class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-bold text-white shadow-sm hover:opacity-95 transition"
                                       style="background:var(--brand);">
                                        ✎ Modifier le pack
                                    </a>
                                    <a href="{{ route('pack-products.index') }}"
                                       class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold ring-1 ring-slate-200 bg-white hover:bg-slate-50 transition"
                                       style="color:var(--brown);">
                                        ← Retour à la liste
                                    </a>
                                </div>
                                <div class="mt-3 text-xs text-slate-500">
                                    Conseil : utilisez “Modifier” pour ajuster le contenu (crédits) et le prix.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-base font-extrabold text-slate-900">Contenu du pack</h3>
                            <a href="{{ route('pack-products.edit', $pack) }}"
                               class="rounded-xl px-3 py-2 text-xs font-bold ring-1 ring-slate-200 bg-white hover:bg-slate-50 transition"
                               style="color:var(--brown);">
                                Modifier le contenu
                            </a>
                        </div>

                        <div class="mt-3 overflow-x-auto rounded-xl ring-1 ring-slate-200 bg-white">
                            <table class="min-w-full text-sm am-table">
                                <thead style="background: linear-gradient(90deg, rgba(100,122,11,0.10), rgba(107,79,42,0.08));" class="text-slate-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-extrabold">Prestation</th>
                                        <th class="px-3 py-2 text-left font-extrabold">Crédits</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($pack->items as $it)
                                    <tr class="am-row">
                                        <td class="px-3 py-2 font-extrabold text-slate-900">
                                            {{ $it->product?->name ?? 'Prestation supprimée' }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-extrabold"
                                                  style="background: rgba(107,79,42,0.10); color: var(--brown); border: 1px solid rgba(107,79,42,0.18);">
                                                {{ $it->quantity }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-2 text-xs text-slate-600">
                            Les crédits sont consommés lorsque vous utilisez ce pack lors d’un rendez-vous / facture.
                        </div>
                    </div>
                </div>

                {{-- Assign to client --}}
                <div class="am-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Attribuer à un client</h3>
                            <p class="mt-1 text-xs text-slate-600">Crée un achat de pack avec crédits initiaux.</p>
                        </div>
                        <div class="h-10 w-10 rounded-2xl flex items-center justify-center"
                             style="background: rgba(100,122,11,0.10); color: var(--brand);">
                            ✦
                        </div>
                    </div>

                    <form action="{{ route('pack-products.assign', $pack) }}" method="POST" class="mt-4 space-y-3">
                        @csrf

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Client</label>
                            <select name="client_profile_id" required class="mt-1 am-input">
                                <option value="">— Choisir un client —</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}">
                                        {{ trim(($c->last_name ?? '').' '.($c->first_name ?? '')) ?: ('Client #'.$c->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Date d’achat</label>
                                <input type="date" name="purchased_at" class="mt-1 am-input" />
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Expiration</label>
                                <input type="date" name="expires_at" class="mt-1 am-input" />
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Note (optionnel)</label>
                            <textarea name="notes" rows="2" class="mt-1 am-input"></textarea>
                        </div>

                        <button class="w-full rounded-xl px-4 py-2 text-sm font-extrabold text-white shadow-sm hover:opacity-95 transition"
                                style="background:var(--brand);">
                            Attribuer le pack
                        </button>

                        <div class="text-xs text-slate-600">
                            Conseil : si le pack a une durée (ex: 3 mois), utilisez le champ “Expiration”.
                        </div>
                    </form>
                </div>
            </div>

            {{-- Recent purchases --}}
            <div class="am-card p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Dernières attributions</h3>
                        <p class="mt-1 text-xs text-slate-600">Les 10 plus récentes.</p>
                    </div>
                    <a href="{{ route('pack-products.edit', $pack) }}"
                       class="rounded-xl px-3 py-2 text-xs font-bold ring-1 ring-slate-200 bg-white hover:bg-slate-50 transition"
                       style="color:var(--brown);">
                        ✎ Modifier
                    </a>
                </div>

                <div class="mt-3 overflow-x-auto rounded-xl ring-1 ring-slate-200 bg-white">
                    <table class="min-w-full text-sm am-table">
                        <thead style="background: linear-gradient(90deg, rgba(100,122,11,0.10), rgba(107,79,42,0.08));" class="text-slate-700">
                            <tr>
                                <th class="px-3 py-2 text-left font-extrabold">Client</th>
                                <th class="px-3 py-2 text-left font-extrabold">Statut</th>
                                <th class="px-3 py-2 text-left font-extrabold">Crédits</th>
                                <th class="px-3 py-2 text-left font-extrabold">Acheté le</th>
                                <th class="px-3 py-2 text-left font-extrabold">Expire le</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($recentPurchases as $p)
                            @php
                                $rem = (int) $p->items->sum('quantity_remaining');
                                $tot = (int) $p->items->sum('quantity_total');
                            @endphp
                            <tr class="am-row">
                                <td class="px-3 py-2 font-extrabold text-slate-900">
                                    {{ trim(($p->clientProfile->last_name ?? '').' '.($p->clientProfile->first_name ?? '')) ?: ('Client #'.$p->client_profile_id) }}
                                </td>
                                <td class="px-3 py-2">
                                    @if($p->status === 'active')
                                        <span class="am-badge" style="color:var(--brand); border-color: rgba(100,122,11,0.25); background: rgba(100,122,11,0.06);">
                                            <span class="am-dot" style="background:var(--brand)"></span> Actif
                                        </span>
                                    @elseif($p->status === 'exhausted')
                                        <span class="am-badge" style="color:var(--brown); border-color: rgba(107,79,42,0.25); background: rgba(107,79,42,0.06);">
                                            <span class="am-dot" style="background:var(--brown)"></span> Terminé
                                        </span>
                                    @elseif($p->status === 'expired')
                                        <span class="am-badge text-slate-700" style="background: rgba(15,23,42,0.04);">
                                            <span class="am-dot bg-slate-500"></span> Expiré
                                        </span>
                                    @else
                                        <span class="am-badge text-slate-700" style="background: rgba(15,23,42,0.04);">
                                            <span class="am-dot bg-slate-500"></span> {{ $p->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-extrabold"
                                          style="background: rgba(107,79,42,0.10); color: var(--brown); border: 1px solid rgba(107,79,42,0.18);">
                                        {{ $rem }} / {{ $tot }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">{{ optional($p->purchased_at)->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">{{ optional($p->expires_at)->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-10 text-center text-slate-600">
                                    Aucune attribution pour l’instant.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-xs text-slate-600">
                    Astuce : vous pourrez bientôt relier une attribution à un rendez-vous pour consommer automatiquement les crédits.
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

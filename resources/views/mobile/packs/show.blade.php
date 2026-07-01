@php
    $formatMoney = fn ($value) => number_format((float) $value, 2, ',', ' ') . ' EUR';
    $statusClasses = [
        'active' => 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]',
        'exhausted' => 'border-amber-200 bg-amber-50 text-amber-700',
        'expired' => 'border-gray-200 bg-gray-50 text-gray-600',
        'cancelled' => 'border-red-200 bg-red-50 text-red-700',
    ];
@endphp

<x-mobile-layout :title="$pack->name">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.packs.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Packs
            </a>

            <div class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                        <i class="fas fa-layer-group text-sm"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl font-semibold leading-tight text-gray-900">
                            {{ $pack->name }}
                        </h1>
                        <p class="mt-1 line-clamp-2 text-sm leading-snug text-gray-600">
                            {{ $pack->description ?: 'Forfait de prestations' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Prix TTC</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $formatMoney($pack->price_incl_tax) }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Lignes</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $pack->items_count }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Attribues</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $pack->purchases_count }}</div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span class="rounded-full border px-2 py-1 text-[11px] font-medium {{ $pack->is_active ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' : 'border-gray-200 bg-gray-50 text-gray-600' }}">
                        {{ $pack->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                        {{ $pack->visible_in_portal ? 'Portail visible' : 'Portail masque' }}
                    </span>
                    <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                        TVA {{ number_format((float) $pack->tax_rate, 2, ',', ' ') }}%
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('mobile.packs.edit', $pack) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-edit mr-1.5 text-[11px]"></i>
                        Modifier
                    </a>
                    <a href="{{ route('pack-products.show', $pack) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-external-link-alt mr-1.5 text-[10px]"></i>
                        Vue web
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <div class="font-semibold">A corriger</div>
                <ul class="mt-1 list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Contenu du pack</h2>

                @if($pack->items->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">Aucune prestation incluse.</p>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach($pack->items as $item)
                            <div class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-gray-900">
                                            {{ $item->product?->name ?: 'Prestation supprimee' }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-600">
                                            {{ $item->product?->duration ? $item->product->duration . ' min' : 'Duree non renseignee' }}
                                        </div>
                                    </div>
                                    <span class="shrink-0 rounded-full border border-[#647a0b]/20 bg-[#647a0b]/10 px-2 py-0.5 text-[10px] font-medium text-[#647a0b]">
                                        {{ $item->quantity }} credit{{ $item->quantity > 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Attribuer a un client</h2>

                @if($clients->isEmpty())
                    <p class="mt-2 text-sm text-gray-500">
                        Creez d abord une fiche client pour attribuer ce pack.
                    </p>
                    <a href="{{ route('mobile.clients.create') }}"
                       class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg bg-[#647a0b] text-xs font-semibold text-white">
                        Creer un client
                    </a>
                @else
                    <form method="POST" action="{{ route('mobile.packs.assign', $pack) }}" class="mt-3 space-y-3">
                        @csrf

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Client</span>
                            <select name="client_profile_id"
                                    required
                                    class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                                <option value="">Choisir</option>
                                @foreach($clients as $client)
                                    @php
                                        $clientName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
                                    @endphp
                                    <option value="{{ $client->id }}">
                                        {{ $clientName ?: 'Client #' . $client->id }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <div class="grid grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Achat</span>
                                <input type="date"
                                       name="purchased_at"
                                       value="{{ now()->format('Y-m-d') }}"
                                       class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Expiration</span>
                                <input type="date"
                                       name="expires_at"
                                       class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            </label>
                        </div>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Note</span>
                            <textarea name="notes"
                                      rows="2"
                                      class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]"></textarea>
                        </label>

                        <button type="submit"
                                class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-[#647a0b] text-sm font-semibold text-white">
                            Attribuer le pack
                        </button>
                    </form>
                @endif
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Dernieres attributions</h2>

                @if($recentPurchases->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">Aucune attribution pour ce pack.</p>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach($recentPurchases as $purchase)
                            @php
                                $remaining = (int) $purchase->items->sum('quantity_remaining');
                                $totalCredits = (int) $purchase->items->sum('quantity_total');
                                $clientName = trim(($purchase->clientProfile->first_name ?? '') . ' ' . ($purchase->clientProfile->last_name ?? ''));
                                $statusClass = $statusClasses[$purchase->status] ?? 'border-gray-200 bg-gray-50 text-gray-600';
                            @endphp

                            <article class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-gray-900">
                                            {{ $clientName ?: 'Client #' . $purchase->client_profile_id }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-600">
                                            {{ optional($purchase->purchased_at)->format('d/m/Y') ?: 'Date inconnue' }}
                                            @if($purchase->expires_at)
                                                <span class="text-gray-300">/</span>
                                                expire le {{ $purchase->expires_at->format('d/m/Y') }}
                                            @endif
                                        </div>
                                    </div>
                                    <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $statusClass }}">
                                        {{ $purchase->status ?: 'statut' }}
                                    </span>
                                </div>

                                <div class="mt-3 grid grid-cols-2 gap-2">
                                    <div class="rounded-lg bg-white p-2">
                                        <div class="text-[11px] font-medium text-gray-500">Credits</div>
                                        <div class="mt-0.5 text-sm font-semibold text-gray-900">
                                            {{ $remaining }} / {{ $totalCredits }}
                                        </div>
                                    </div>
                                    <div class="rounded-lg bg-white p-2">
                                        <div class="text-[11px] font-medium text-gray-500">Paiement</div>
                                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                            {{ ($purchase->payment_mode ?? 'one_time') === 'installments' ? 'Echeances' : '1 fois' }}
                                        </div>
                                    </div>
                                </div>

                                @if($purchase->notes)
                                    <p class="mt-2 line-clamp-2 text-xs text-gray-600">{{ $purchase->notes }}</p>
                                @endif

                                @if($purchase->status === 'active')
                                    <form method="POST"
                                          action="{{ route('mobile.packs.purchases.revoke', $purchase) }}"
                                          class="mt-3"
                                          onsubmit="return confirm('Revoquer ce pack client ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-xs font-semibold text-red-600">
                                            <i class="fas fa-ban mr-1.5 text-[11px]"></i>
                                            Revoquer
                                        </button>
                                    </form>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <form method="POST"
                  action="{{ route('mobile.packs.destroy', $pack) }}"
                  onsubmit="return confirm('Supprimer ce pack ? Les attributions liees seront aussi supprimees.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-sm font-semibold text-red-600">
                    <i class="fas fa-trash-alt mr-1.5 text-[11px]"></i>
                    Supprimer le pack
                </button>
            </form>
        </div>
    </div>
</x-mobile-layout>

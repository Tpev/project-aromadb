@php
    $formatMoney = fn ($value) => number_format((float) $value, 2, ',', ' ') . ' EUR';
    $total = $packs->count();
    $active = $packs->where('is_active', true)->count();
    $purchases = $packs->sum('purchases_count');
@endphp

<x-mobile-layout title="Packs">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-layer-group text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Packs</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Forfaits, credits clients et ventes groupees.
                </p>
            </div>

            <a href="{{ route('mobile.menu') }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500 shadow-sm"
               aria-label="Retour au menu">
                <i class="fas fa-bars text-xs"></i>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Packs</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Actifs</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $active }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Attribues</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $purchases }}</div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            <a href="{{ route('mobile.packs.create') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                Ajouter
            </a>
            <a href="{{ route('pack-products.index') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Vue web
            </a>
        </div>

        @if($packs->isNotEmpty())
            <div class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <label class="flex h-10 items-center gap-2 rounded-lg bg-[#f7f8f1] px-3">
                    <i class="fas fa-search text-[11px] text-gray-400"></i>
                    <input type="search"
                           id="mobilePackSearch"
                           placeholder="Rechercher un pack"
                           class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:ring-0"
                           oninput="filterMobilePacks()">
                </label>
            </div>
        @endif

        @if($packs->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-layer-group text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucun pack</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Creez un forfait pour vendre plusieurs prestations et suivre les credits.
                </p>
            </div>
        @else
            <div id="mobilePackList" class="space-y-2">
                @foreach($packs as $pack)
                    @php
                        $badgeClass = $pack->is_active
                            ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]'
                            : 'border-gray-200 bg-gray-50 text-gray-600';
                        $portalText = $pack->visible_in_portal ? 'Portail visible' : 'Portail masque';
                        $searchText = trim($pack->name . ' ' . ($pack->description ?? '') . ' ' . $portalText);
                    @endphp

                    <a href="{{ route('mobile.packs.show', $pack) }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]"
                       data-pack="{{ Str::lower($searchText) }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">
                                    {{ $pack->name }}
                                </h2>
                                <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                    {{ $pack->description ?: 'Pack sans description' }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $badgeClass }}">
                                {{ $pack->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Prix TTC</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $formatMoney($pack->price_incl_tax) }}</div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Attribues</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $pack->purchases_count }}</div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $pack->items_count }} prestation{{ $pack->items_count > 1 ? 's' : '' }}
                            </span>
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $portalText }}
                            </span>
                            @if($pack->installments_enabled)
                                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                    Paiement {{ implode('/', $pack->allowed_installments ?? []) }}x
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function filterMobilePacks() {
            const input = document.getElementById('mobilePackSearch');
            const filter = input ? input.value.toLowerCase() : '';
            const items = document.querySelectorAll('#mobilePackList > a');

            items.forEach((item) => {
                const text = item.getAttribute('data-pack') || '';
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>

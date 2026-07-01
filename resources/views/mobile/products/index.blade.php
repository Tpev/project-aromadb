@php
    $formatMoney = fn ($value) => number_format((float) $value, 2, ',', ' ') . ' EUR';
    $total = $products->count();
    $bookable = $products->where('can_be_booked_online', true)->count();
    $portalVisible = $products->where('visible_in_portal', true)->count();
@endphp

<x-mobile-layout title="Prestations">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-spa text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Prestations</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Prix, durees, reservation en ligne et visibilite portail.
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
                <div class="text-[11px] font-medium leading-tight text-gray-500">Total</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Reservables</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $bookable }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Portail</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $portalVisible }}</div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            <a href="{{ route('mobile.products.create') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                Ajouter
            </a>
            <a href="{{ route('products.index') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Vue web
            </a>
        </div>

        @if($products->isNotEmpty())
            <div class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <label class="flex h-10 items-center gap-2 rounded-lg bg-[#f7f8f1] px-3">
                    <i class="fas fa-search text-[11px] text-gray-400"></i>
                    <input type="search"
                           id="mobileProductSearch"
                           placeholder="Rechercher une prestation"
                           class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:ring-0"
                           oninput="filterMobileProducts()">
                </label>
            </div>
        @endif

        @if($products->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-spa text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucune prestation</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Ajoutez vos premieres prestations pour alimenter votre agenda et votre portail.
                </p>
            </div>
        @else
            <div id="mobileProductList" class="space-y-2">
                @foreach($products as $product)
                    @php
                        $price = $formatMoney($product->price_incl_tax);
                        $badgeClass = $product->can_be_booked_online
                            ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]'
                            : 'border-gray-200 bg-gray-50 text-gray-600';
                        $searchText = trim($product->name . ' ' . ($product->description ?? '') . ' ' . $product->getConsultationModes() . ' ' . $price);
                    @endphp

                    <a href="{{ route('mobile.products.show', $product) }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]"
                       data-product="{{ Str::lower($searchText) }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">
                                    {{ $product->name }}
                                </h2>
                                <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                    {{ $product->description ?: 'Prestation sans description' }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $badgeClass }}">
                                {{ $product->can_be_booked_online ? 'Reservable' : 'Interne' }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-3 gap-2">
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Prix TTC</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $price }}</div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Duree</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $product->duration ?: '-' }} min</div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Factures</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $product->invoice_items_count }}</div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $product->getConsultationModes() }}
                            </span>
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $product->visible_in_portal ? 'Portail visible' : 'Portail masque' }}
                            </span>
                            @if($product->collect_payment)
                                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                    Paiement en ligne
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function filterMobileProducts() {
            const input = document.getElementById('mobileProductSearch');
            const filter = input ? input.value.toLowerCase() : '';
            const items = document.querySelectorAll('#mobileProductList > a');

            items.forEach((item) => {
                const text = item.getAttribute('data-product') || '';
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>

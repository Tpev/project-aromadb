@php
    $formatMoney = fn ($value) => number_format((float) $value, 2, ',', ' ') . ' EUR';
    $formatQty = function ($value) {
        $formatted = number_format((float) $value, 2, ',', ' ');

        return rtrim(rtrim($formatted, '0'), ',');
    };

    $total = $inventoryItems->count();
    $lowStockCount = $inventoryItems->filter(function ($item) {
        if ($item->unit_type === 'unit') {
            return (float) ($item->quantity_in_stock ?? 0) <= 2;
        }

        $capacity = (float) ($item->quantity_per_unit ?? 0);
        $remaining = (float) ($item->quantity_remaining ?? 0);
        $threshold = $capacity > 0 ? $capacity * 0.2 : 0;

        return $remaining <= $threshold;
    })->count();
    $saleValue = $inventoryItems->sum(function ($item) {
        if ($item->unit_type === 'unit') {
            return (float) ($item->quantity_in_stock ?? 0) * (float) ($item->selling_price ?? 0);
        }

        $remaining = (float) ($item->quantity_remaining ?? 0);
        $unitSalePrice = (float) ($item->selling_price_per_ml ?? 0);

        if ($unitSalePrice <= 0 && (float) ($item->quantity_per_unit ?? 0) > 0) {
            $unitSalePrice = (float) ($item->selling_price ?? 0) / (float) $item->quantity_per_unit;
        }

        return $remaining * $unitSalePrice;
    });
@endphp

<x-mobile-layout title="Stock">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-boxes text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Stock</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Articles, contenants et consommations rapides.
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

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-700">
                {{ session('error') }}
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

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Articles</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">A surveiller</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $lowStockCount }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Valeur vente</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $formatMoney($saleValue) }}</div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            <a href="{{ route('mobile.inventory.create') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                Ajouter
            </a>
            <a href="{{ route('inventory_items.index') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Vue web
            </a>
        </div>

        @if($inventoryItems->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-box-open text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucun article</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Ajoutez vos huiles, produits ou consommables pour suivre les sorties.
                </p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($inventoryItems as $item)
                    @php
                        $isUnit = $item->unit_type === 'unit';
                        $unitLabel = match ($item->unit_type) {
                            'ml', 'drop' => 'ml',
                            'gramme' => 'g',
                            default => 'unite',
                        };
                        $typeLabel = match ($item->unit_type) {
                            'unit' => 'Unite',
                            'ml' => 'Millilitres',
                            'drop' => 'Gouttes',
                            'gramme' => 'Grammes',
                            default => 'Stock',
                        };
                        $remaining = $isUnit ? (float) ($item->quantity_in_stock ?? 0) : (float) ($item->quantity_remaining ?? 0);
                        $capacity = (float) ($item->quantity_per_unit ?? 0);
                        $lowThreshold = $isUnit ? 2 : ($capacity > 0 ? $capacity * 0.2 : 0);
                        $isLow = $remaining <= $lowThreshold;
                        $badgeClass = $isLow
                            ? 'border-amber-200 bg-amber-50 text-amber-700'
                            : 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]';
                        $badgeLabel = $isLow ? 'Bas' : 'OK';
                    @endphp

                    <article class="rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">
                                    {{ $item->name }}
                                </h2>
                                <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                    {{ $item->brand ?: 'Sans marque' }}
                                    @if($item->reference)
                                        <span class="text-gray-300">/</span> {{ $item->reference }}
                                    @endif
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $badgeClass }}">
                                {{ $badgeLabel }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Disponible</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">
                                    {{ $formatQty($remaining) }} {{ $unitLabel }}
                                </div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Prix vente</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">
                                    {{ $formatMoney($item->selling_price ?? 0) }}
                                </div>
                            </div>
                        </div>

                        @if($item->description)
                            <p class="mt-3 line-clamp-2 text-xs leading-snug text-gray-600">
                                {{ $item->description }}
                            </p>
                        @endif

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $typeLabel }}
                            </span>
                            @if(!$isUnit && $capacity > 0)
                                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                    Contenant {{ $formatQty($capacity) }} {{ $unitLabel }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('mobile.inventory.edit', $item) }}"
                               class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                                <i class="fas fa-edit mr-1.5 text-[11px]"></i>
                                Modifier
                            </a>
                            <form method="POST"
                                  action="{{ route('mobile.inventory.destroy', $item->id) }}"
                                  onsubmit="return confirm('Supprimer cet article ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-xs font-semibold text-red-600">
                                    <i class="fas fa-trash-alt mr-1.5 text-[11px]"></i>
                                    Supprimer
                                </button>
                            </form>
                        </div>

                        @if($isUnit)
                            <form method="POST" action="{{ route('mobile.inventory.consume.unit', $item) }}" class="mt-2">
                                @csrf
                                <button type="submit"
                                        class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-[#647a0b] text-xs font-semibold text-white disabled:opacity-50"
                                        @disabled($remaining < 1)>
                                    <i class="fas fa-minus-circle mr-1.5 text-[11px]"></i>
                                    Consommer 1 unite
                                </button>
                            </form>
                        @elseif($item->unit_type === 'gramme')
                            <form method="POST" action="{{ route('mobile.inventory.consume', $item) }}" class="mt-2 flex gap-2">
                                @csrf
                                <input type="number"
                                       name="amount_gramme"
                                       step="0.01"
                                       min="0.01"
                                       max="{{ $remaining }}"
                                       inputmode="decimal"
                                       placeholder="g"
                                       class="h-10 min-w-0 flex-1 rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                                <button type="submit"
                                        class="inline-flex h-10 shrink-0 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white">
                                    Consommer
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('mobile.inventory.consume', $item) }}" class="mt-2 grid grid-cols-[1fr_auto] gap-2">
                                @csrf
                                <div class="grid min-w-0 gap-2 {{ $item->unit_type === 'drop' ? 'grid-cols-2' : 'grid-cols-1' }}">
                                    <input type="number"
                                           name="amount_ml"
                                           step="0.01"
                                           min="0.01"
                                           max="{{ $remaining }}"
                                           inputmode="decimal"
                                           placeholder="ml"
                                           class="h-10 min-w-0 rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                                    @if($item->unit_type === 'drop')
                                        <input type="number"
                                               name="amount_drops"
                                               step="1"
                                               min="0"
                                               inputmode="numeric"
                                               placeholder="gouttes"
                                               class="h-10 min-w-0 rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                                    @endif
                                </div>
                                <button type="submit"
                                        class="inline-flex h-10 shrink-0 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white">
                                    Consommer
                                </button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-mobile-layout>

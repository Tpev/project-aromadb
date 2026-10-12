@php
    $packDesc = (string)($pack->description ?? '');
    $truncatedPackDescription = \Illuminate\Support\Str::limit($packDesc, 200);

    // Total si acheté à l'unité (TTC)
    $unitTotalTtc = (float) ($pack->items ?? collect())->sum(function($it) {
        $p = $it->product;
        if (!$p) return 0;

        $tax = (float) ($p->tax_rate ?? 0);
        $ttc = (float) ($p->price_incl_tax ?? ($p->price + ($p->price * $tax / 100)));

        return $ttc * (int) ($it->quantity ?? 1);
    });

    $packTax = (float) ($pack->tax_rate ?? 0);
    $packPriceTtc = (float) ($pack->price_incl_tax ?? ($pack->price + ($pack->price * $packTax / 100)));

    $saving    = max(0, $unitTotalTtc - $packPriceTtc);
    $savingPct = $unitTotalTtc > 0 ? (int) round(($saving / $unitTotalTtc) * 100) : null;

    $showPrice = (bool)($pack->price_visible_in_portal ?? false) && (float)($pack->price ?? 0) > 0;

    $items = $pack->items ?? collect();
    $itemsToShow = $items->take(4);
    $remainingCount = max(0, $items->count() - $itemsToShow->count());
@endphp

<div class="border border-[#e8e8e8] rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 prestation-item bg-[#f9fafb]">
    <div class="p-6">
        <div class="flex items-start justify-between gap-3">
            <h4 class="text-2xl font-semibold text-[#647a0b] leading-tight">
                {{ $pack->name }}
            </h4>

            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#e8f0d8] text-[#647a0b] border border-[#dfe8c7]">
                🎁 {{ __('Pack') }}
            </span>
        </div>

        {{-- Contenu du pack --}}
        <div class="mt-4">
            <p class="text-sm font-semibold text-gray-700 mb-2">{{ __('Contenu :') }}</p>

            <ul class="text-sm text-gray-700 space-y-1">
                @foreach($itemsToShow as $it)
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5">•</span>
                        <span>
                            <span class="font-semibold">{{ (int)($it->quantity ?? 1) }}×</span>
                            {{ $it->product?->name ?? __('Prestation supprimée') }}
                        </span>
                    </li>
                @endforeach

                @if($remainingCount > 0)
                    <li class="text-xs text-gray-500 italic">
                        + {{ $remainingCount }} {{ __('autre(s) prestation(s)…') }}
                    </li>
                @endif
            </ul>
        </div>

        {{-- Prix + réduction --}}
        @if($showPrice)
            <div class="mt-4">
                <p class="text-gray-600 font-semibold">
                    {{ __('Prix du pack :') }}
                    <span class="text-[#854f38] font-extrabold">
                        {{ number_format($packPriceTtc, 2, ',', ' ') }} €
                    </span>
                </p>

                @if($unitTotalTtc > 0 && $saving > 0.01)
                    <p class="mt-1 text-sm text-gray-600">
                        <span class="line-through opacity-80">
                            {{ __('À l’unité :') }} {{ number_format($unitTotalTtc, 2, ',', ' ') }} €
                        </span>

                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-white border border-[#e4e8d5] text-[#647a0b] font-bold text-xs">
                            {{ __('Économie :') }} {{ number_format($saving, 2, ',', ' ') }} €
                            @if(!is_null($savingPct))
                                ({{ $savingPct }}%)
                            @endif
                        </span>
                    </p>
                @endif
            </div>
        @endif
<a href="{{ route('packs.checkout.show', ['slug' => $therapist->slug, 'pack' => $pack->id]) }}"
   class="mt-5 inline-flex items-center justify-center w-full bg-[#647a0b] text-white font-semibold py-2.5 rounded-full hover:bg-[#8ea633] transition">
    <i class="fas fa-shopping-cart mr-2"></i> {{ __('Acheter le pack') }}
</a>

        {{-- Description --}}
        @if(trim($packDesc) !== '')
            <p class="mt-4 text-gray-700 prestation-description"
               data-full-text="{{ e($packDesc) }}"
               data-truncated-text="{{ e($truncatedPackDescription) }}">
                {!! nl2br(e($truncatedPackDescription)) !!}
                @if(\Illuminate\Support\Str::length($packDesc) > 200)
                    <span class="text-[#854f38] cursor-pointer voir-plus">{{ __('Voir plus') }}</span>
                @endif
            </p>
        @endif
    </div>
</div>

@props(['product', 'limit' => null])
@php
    $isLong = $limit && \Illuminate\Support\Str::length($product->description ?? '') > $limit;
@endphp
<div {{ $attributes->class(['product-description-content']) }} style="overflow-wrap:anywhere" @if($isLong) x-data="{ expanded: false }" @endif>
    @if($isLong && !$product->description_html)
        <div x-show="!expanded">{!! nl2br(e(\Illuminate\Support\Str::limit($product->description, $limit))) !!}</div>
    @endif
    <div class="product-description-full"
         @if($isLong && $product->description_html) :style="expanded ? '' : 'max-height: 6em; overflow: hidden'" style="max-height:6em;overflow:hidden"
         @elseif($isLong) x-show="expanded" x-cloak @endif>{!! \App\Support\ProductDescription::html($product) !!}</div>
    @if($isLong)
        <button type="button" class="py-2 text-sm font-semibold text-[#854f38] hover:underline" @click="expanded = !expanded" :aria-expanded="expanded.toString()">
            <span x-show="!expanded">{{ __('Voir plus') }}</span>
            <span x-show="expanded" x-cloak>{{ __('Voir moins') }}</span>
        </button>
    @endif
</div>
@once
    <style>.product-description-content p { margin: 0; min-height: 1em; } .product-description-content strong, .product-description-content b { font-weight: 700; } .product-description-content em, .product-description-content i { font-style: italic; } .product-description-content u { text-decoration: underline; }</style>
@endonce

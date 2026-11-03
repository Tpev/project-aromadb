@props(['pack', 'compact' => false, 'sizes' => '(max-width: 640px) 100vw, 640px'])
@if($pack->photo_url)
    <img data-pack-photo src="{{ $compact ? $pack->photo_thumbnail_url : $pack->photo_url }}"
         @unless($compact) srcset="{{ $pack->photo_thumbnail_url }} 640w, {{ $pack->photo_url }} 1200w" sizes="{{ $sizes }}" @endunless
         alt="Photo du pack {{ $pack->name }}" width="1200" height="675" loading="lazy" decoding="async"
         {{ $attributes->merge(['style' => 'display:block;width:'.($compact ? '80px' : '100%').';max-width:100%;height:auto;aspect-ratio:16/9;object-fit:cover;border-radius:8px;margin-bottom:12px;']) }}>
@endif

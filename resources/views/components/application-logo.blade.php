@php
    $logo1x = asset('images/brand/olithea-logo-horizontal-green-cropped.png');
@endphp

<img
    src="{{ $logo1x }}"
    width="941"
    height="294"
    class="block h-12 w-auto sm:h-14 {{ $attributes->get('class') }}"
    alt="{{ config('app.name') }}"
    fetchpriority="high"
    loading="eager"
    decoding="async">

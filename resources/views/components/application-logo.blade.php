@php
    // 320 × 80 → intrinsic box matches nav design
    $logo1x = asset('images/png-01.webp');
    $logo2x = asset('images/png-01@2x.png');   // leave out if you don’t ship it
@endphp

<img
    src="{{ $logo1x }}"
    srcset="{{ $logo1x }} 1x, {{ $logo2x }} 2x"
    width="320" height="80"                         {{-- 80 px high, CLS-safe --}}
    class="block h-20 w-auto {{ $attributes->get('class') }}" {{-- h-20 = 5 rem = 80 px --}}
    alt="{{ config('app.name') }}"
    fetchpriority="high"
    loading="eager" decoding="async">

{{-- resources/views/components/application-logo.blade.php --}}
@php
    /**
     * Single-resolution logo
     *  – file must be ~256 × 64 px  (ratio 4 : 1)
     *  – matches a 64 px-high nav bar on mobile (h-16)
     */
    $logo1x = asset('images/png-01.webp');
@endphp

<img
    src="{{ $logo1x }}"
    width="256" height="64"                     {{-- intrinsic box → zero CLS --}}
    class="block h-16 w-auto sm:h-20            {{-- 64 px on mobile, 80 px ≥ 640 px --}}
           {{ $attributes->get('class') }}"
    alt="{{ config('app.name') }}"
    fetchpriority="high" loading="eager" decoding="async">

{{-- resources/views/components/application-logo.blade.php --}}

@php
    /* -----------------------------------------------------------
     | CONFIG
     | -----------------------------------------------------------
     | 1. logo.png must be square (or you can set different height/width)
     | 2. if you have a @2x / Retina asset, add it to /images/logo@2x.png
     | -----------------------------------------------------------*/
    $logo1x = asset('images/png-01.webp');          // 128 × 128 px
    $logo2x = asset('images/png-01@2x.png');       // 256 × 256 px (optional)
@endphp

<img
    src="{{ $logo1x }}"
    srcset="{{ $logo1x }} 1x, {{ $logo2x }} 2x"      {{-- crisp on Retina, harmless if file missing --}}
    width="128" height="128"                         {{-- intrinsic box ➜ zero CLS --}}
    class="block h-32 w-32 {{ $attributes->get('class') }}" {{-- h-32 = 8 rem (128 px) --}}
    alt="{{ config('app.name', 'AromaMade') }}"
    loading="eager"  decoding="async"                {{-- best-practice for above-the-fold icons --}}
>

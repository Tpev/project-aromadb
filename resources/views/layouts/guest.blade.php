<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Olithea') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/brand/olithea-mark-cropped.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-brand-text antialiased" style="margin: 0; background: #F6F2EB;">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-6 sm:py-10" style="min-height: 100vh; min-height: 100dvh; background: radial-gradient(circle at top left, rgba(167, 184, 138, 0.24), transparent 28rem), radial-gradient(circle at bottom right, rgba(233, 176, 122, 0.18), transparent 28rem), #F6F2EB;">
            <div>
 <a href="{{ url('/') }}">
     <x-application-logo class="mx-auto" />   {{-- centred, no extra sizing --}}
 </a>

            </div>

            <div class="w-full max-w-md mt-6 px-5 py-5 sm:px-7 sm:py-6 bg-white shadow-md overflow-hidden rounded-3xl border border-brand-border/70">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

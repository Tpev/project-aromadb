<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AromaMade') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased" style="margin: 0; background: #f4f1e8;">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-6 sm:py-10" style="min-height: 100vh; min-height: 100dvh; background: radial-gradient(circle at top left, rgba(100, 122, 11, 0.13), transparent 28rem), radial-gradient(circle at bottom right, rgba(133, 79, 56, 0.12), transparent 28rem), #f4f1e8;">
            <div>
 <a href="{{ url('/') }}">
     <x-application-logo class="mx-auto" />   {{-- centred, no extra sizing --}}
 </a>

            </div>

            <div class="w-full max-w-md mt-6 px-5 py-5 sm:px-7 sm:py-6 bg-white shadow-md overflow-hidden rounded-3xl border border-white/80">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

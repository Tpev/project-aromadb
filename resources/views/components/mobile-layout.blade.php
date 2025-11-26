@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        {{ $title ? $title . ' | AromaMade PRO' : 'AromaMade PRO' }}
    </title>

    {{-- Tailwind / app assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome for icons used in mobile views --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
          integrity="sha512-dyZtMZ1Yjv1hkP9mzi1TL0KWs/oUNsGrD/qjUKPTN3lNMndvrYVF/1DdbC+aD7agEXkN6mAZQ5x1M3lmbXKf0A=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-[#fff9f6] font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        {{-- Header mobile minimal --}}
        <header class="px-4 pt-4 pb-3 flex items-center justify-between bg-white/90 border-b border-[#e4e8d5]">
            <div class="flex items-center gap-2">
                {{-- You can swap this for your real logo --}}
                <div class="w-8 h-8 rounded-full bg-[#647a0b] flex items-center justify-center text-white text-sm font-bold">
                    A
                </div>
                <div class="flex flex-col leading-tight">
                    <span class="text-sm font-semibold text-gray-900">AromaMade PRO</span>
                    <span class="text-[11px] text-gray-500">Espace mobile</span>
                </div>
            </div>
        </header>

        <main class="flex-1 flex flex-col">
            {{ $slot }}
        </main>
    </div>
</body>
</html>

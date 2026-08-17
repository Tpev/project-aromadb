{{-- resources/views/mobile/appointments/index.blade.php --}}
@php
    use Carbon\Carbon;

    $now        = Carbon::now();
    $todayStart = $now->copy()->startOfDay();
    $todayEnd   = $now->copy()->endOfDay();

    $today = $appointments->filter(function ($a) use ($todayStart, $todayEnd) {
        return $a->appointment_date->between($todayStart, $todayEnd);
    });

    $upcoming = $appointments->filter(function ($a) use ($todayEnd) {
        return $a->appointment_date->greaterThan($todayEnd);
    });

    $past = $appointments->filter(function ($a) use ($todayStart) {
        return $a->appointment_date->lessThan($todayStart);
    });

    $requestedTab = request('filter');
    $defaultTab = in_array($requestedTab, ['today', 'upcoming', 'past'], true)
        ? $requestedTab
        : ($today->isNotEmpty()
            ? 'today'
            : ($upcoming->isNotEmpty()
                ? 'upcoming'
                : ($past->isNotEmpty() ? 'past' : 'today')));
@endphp

<x-mobile-layout title="Mes rendez-vous">
    <div class="px-4 pt-4 pb-24 space-y-4">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-900">Mes rendez-vous</h1>
                <p class="text-xs text-gray-500">
                    {{ $appointments->count() }} rendez-vous {{ $appointments->count() > 1 ? 'affichés' : 'affiché' }}
                </p>
            </div>

            <a href="{{ route('mobile.appointments.create') }}"
               class="inline-flex items-center px-3 py-2 rounded-full text-xs font-medium bg-[#647a0b] text-white shadow-sm">
                <i class="fas fa-plus mr-1.5 text-[11px]"></i>
                Nouveau
            </a>
        </div>

        <form action="{{ route('mobile.appointments.index') }}" method="GET">
            @if(request()->filled('filter'))
                <input type="hidden" name="filter" value="{{ request('filter') }}">
            @endif
            <input type="hidden" name="appointment_status" value="{{ $appointmentStatusFilter }}">
            <input type="hidden" name="calendar_source" id="mobile-calendar-source" value="{{ $showGoogleEvents ? 'all' : 'olithea' }}">
            <label for="mobile-google-events-toggle" class="flex cursor-pointer items-center justify-between gap-3 rounded-lg border border-[#e4e8d5] bg-white px-3 py-2.5">
                <span class="min-w-0">
                    <span class="block text-xs font-semibold text-gray-800">Événements Google</span>
                    <span class="block text-[10px] text-gray-500">{{ $showGoogleEvents ? 'Affichés dans le calendrier' : 'Masqués du calendrier' }}</span>
                </span>
                <span class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition {{ $showGoogleEvents ? 'bg-[#647a0b]' : 'bg-gray-300' }}">
                    <input type="checkbox"
                           id="mobile-google-events-toggle"
                           class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                           @checked($showGoogleEvents)
                           aria-label="Afficher les événements Google dans le calendrier"
                           onchange="document.getElementById('mobile-calendar-source').value = this.checked ? 'all' : 'olithea'; this.form.submit();">
                    <span class="absolute top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition {{ $showGoogleEvents ? 'translate-x-5' : 'translate-x-0.5' }}"></span>
                </span>
            </label>
        </form>

        @php
            $appointmentStatusOptions = [
                'active' => 'Actifs',
                'cancelled' => 'Annulés',
                'all' => 'Tous',
            ];
        @endphp
        <nav class="flex items-center gap-1 rounded-lg border border-[#e4e8d5] bg-white p-1" aria-label="Filtrer les rendez-vous par statut">
            @foreach($appointmentStatusOptions as $statusValue => $statusLabel)
                <a href="{{ request()->fullUrlWithQuery(['appointment_status' => $statusValue]) }}"
                   class="flex min-h-9 flex-1 items-center justify-center gap-1 rounded-md px-2 text-[11px] font-semibold {{ $appointmentStatusFilter === $statusValue ? 'bg-[#647a0b] text-white' : 'text-gray-600' }}"
                   @if($appointmentStatusFilter === $statusValue) aria-current="page" @endif>
                    {{ $statusLabel }}
                    <span class="rounded-full px-1.5 py-0.5 text-[9px] {{ $appointmentStatusFilter === $statusValue ? 'bg-white/20' : 'bg-[#f0f4df] text-[#526509]' }}">
                        {{ $appointmentStatusCounts[$statusValue] }}
                    </span>
                </a>
            @endforeach
        </nav>

        {{-- Quick stats --}}
        <div class="grid grid-cols-3 gap-2 text-center text-xs">
            <div class="rounded-xl bg-white border border-[#e4e8d5] py-2">
                <p class="text-[10px] uppercase tracking-wide text-gray-400">Aujourd’hui</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">{{ $today->count() }}</p>
            </div>
            <div class="rounded-xl bg-white border border-[#e4e8d5] py-2">
                <p class="text-[10px] uppercase tracking-wide text-gray-400">À venir</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">{{ $upcoming->count() }}</p>
            </div>
            <div class="rounded-xl bg-white border border-[#e4e8d5] py-2">
                <p class="text-[10px] uppercase tracking-wide text-gray-400">Passés</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">{{ $past->count() }}</p>
            </div>
        </div>

        {{-- Filters + list --}}
        <div x-data="{ tab: @js($defaultTab) }" class="space-y-4">
            {{-- Tabs --}}
            <div class="flex items-center gap-2 text-xs bg-white rounded-full p-1 border border-[#e4e8d5]">
                <button
                    type="button"
                    @click="tab = 'today'"
                    :class="tab === 'today'
                        ? 'bg-[#647a0b] text-white shadow-sm'
                        : 'text-gray-600'"
                    class="flex-1 px-3 py-1.5 rounded-full font-medium transition">
                    Aujourd’hui
                </button>

                <button
                    type="button"
                    @click="tab = 'upcoming'"
                    :class="tab === 'upcoming'
                        ? 'bg-[#647a0b] text-white shadow-sm'
                        : 'text-gray-600'"
                    class="flex-1 px-3 py-1.5 rounded-full font-medium transition">
                    À venir
                </button>

                <button
                    type="button"
                    @click="tab = 'past'"
                    :class="tab === 'past'
                        ? 'bg-[#647a0b] text-white shadow-sm'
                        : 'text-gray-600'"
                    class="flex-1 px-3 py-1.5 rounded-full font-medium transition">
                    Passés
                </button>
            </div>

            {{-- Today --}}
            <div x-show="tab === 'today'" x-cloak class="space-y-3">
                @if($today->isEmpty())
                    <p class="text-xs text-gray-500 text-center mt-4">
                        Aucun rendez-vous pour aujourd’hui.
                    </p>
                @else
                    @foreach($today->sortBy('appointment_date') as $appointment)
                        @include('mobile.appointments.partials.card', ['appointment' => $appointment])
                    @endforeach
                @endif
            </div>

            {{-- Upcoming --}}
            <div x-show="tab === 'upcoming'" x-cloak class="space-y-3">
                @if($upcoming->isEmpty())
                    <p class="text-xs text-gray-500 text-center mt-4">
                        Aucun rendez-vous à venir.
                    </p>
                @else
                    @foreach($upcoming->sortBy('appointment_date') as $appointment)
                        @include('mobile.appointments.partials.card', ['appointment' => $appointment])
                    @endforeach
                @endif
            </div>

            {{-- Past --}}
            <div x-show="tab === 'past'" x-cloak class="space-y-3">
                @if($past->isEmpty())
                    <p class="text-xs text-gray-500 text-center mt-4">
                        Aucun rendez-vous passé à afficher.
                    </p>
                @else
                    @foreach($past->sortByDesc('appointment_date') as $appointment)
                        @include('mobile.appointments.partials.card', ['appointment' => $appointment])
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</x-mobile-layout>

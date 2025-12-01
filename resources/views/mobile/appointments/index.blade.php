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
@endphp

<x-mobile-layout title="Mes rendez-vous">
    <div class="px-4 pt-4 pb-24 space-y-4">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-900">Mes rendez-vous</h1>
                <p class="text-xs text-gray-500">
                    {{ $appointments->count() }} rendez-vous au total
                </p>
            </div>

            <a href="{{ route('appointments.create') }}"
               class="inline-flex items-center px-3 py-2 rounded-full text-xs font-medium bg-[#647a0b] text-white shadow-sm">
                <i class="fas fa-plus mr-1.5 text-[11px]"></i>
                Nouveau
            </a>
        </div>

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
        <div x-data="{ tab: 'today' }" class="space-y-4">
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

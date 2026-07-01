@php
    $total = $events->count();
    $upcomingCount = $upcomingEvents->count();
    $reservationCount = $events->sum('reservations_count');
@endphp

<x-mobile-layout title="Evenements">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-calendar-plus text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Evenements</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Ateliers, visios et reservations groupees.
                </p>
            </div>

            <a href="{{ route('mobile.menu') }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500 shadow-sm"
               aria-label="Retour au menu">
                <i class="fas fa-bars text-xs"></i>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Evenements</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">A venir</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $upcomingCount }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Reservations</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $reservationCount }}</div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            @if($canCreateEvent)
                <a href="{{ route('mobile.events.create') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                    Ajouter
                </a>
            @else
                <a href="{{ route('license-tiers.pricing') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-amber-200 bg-amber-50 px-3 text-sm font-semibold text-amber-700 shadow-sm active:scale-[0.99]">
                    Offre PRO
                </a>
            @endif
            <a href="{{ route('events.index') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Vue web
            </a>
        </div>

        @unless($canCreateEvent)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm leading-snug text-amber-800">
                Le module evenements est disponible a partir de l offre PRO.
            </div>
        @endunless

        @if($events->isNotEmpty())
            <div class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <label class="flex h-10 items-center gap-2 rounded-lg bg-[#f7f8f1] px-3">
                    <i class="fas fa-search text-[11px] text-gray-400"></i>
                    <input type="search"
                           id="mobileEventSearch"
                           placeholder="Rechercher un evenement"
                           class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:ring-0"
                           oninput="filterMobileEvents()">
                </label>
            </div>
        @endif

        @if($events->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-calendar-plus text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucun evenement</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Creez un atelier ou une visio pour ouvrir des reservations.
                </p>
            </div>
        @else
            <div id="mobileEventList" class="space-y-3">
                @foreach($events as $event)
                    @php
                        $start = \Carbon\Carbon::parse($event->start_date_time);
                        $isPast = $start->isPast();
                        $isVisio = ($event->event_type ?? 'in_person') === 'visio';
                        $capacity = $event->limited_spot && $event->number_of_spot ? $event->number_of_spot : 'Illimite';
                        $searchText = strtolower(trim($event->name . ' ' . ($event->location ?? '') . ' ' . ($event->description ?? '')));
                    @endphp

                    <a href="{{ route('mobile.events.show', $event) }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]"
                       data-event="{{ $searchText }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">
                                    {{ $event->name }}
                                </h2>
                                <p class="mt-1 text-xs leading-snug text-gray-600">
                                    {{ $start->format('d/m/Y H:i') }} - {{ $isVisio ? 'Visio' : ($event->location ?: 'Lieu non renseigne') }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $isPast ? 'border-gray-200 bg-gray-50 text-gray-600' : 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' }}">
                                {{ $isPast ? 'Passe' : 'A venir' }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-3 gap-2">
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Duree</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $event->duration }} min</div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Places</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $event->reservations_count }} / {{ $capacity }}</div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Portail</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $event->showOnPortail ? 'Oui' : 'Non' }}</div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function filterMobileEvents() {
            const input = document.getElementById('mobileEventSearch');
            const filter = input ? input.value.toLowerCase() : '';
            const items = document.querySelectorAll('#mobileEventList > a');

            items.forEach((item) => {
                const text = item.getAttribute('data-event') || '';
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>

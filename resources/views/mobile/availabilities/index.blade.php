@php
    $dayLabels = [
        0 => 'Lundi',
        1 => 'Mardi',
        2 => 'Mercredi',
        3 => 'Jeudi',
        4 => 'Vendredi',
        5 => 'Samedi',
        6 => 'Dimanche',
    ];

    $total = $availabilities->count();
    $globalCount = $availabilities->where('applies_to_all', true)->count();
    $locationCount = $availabilities->whereNotNull('practice_location_id')->count();
@endphp

<x-mobile-layout title="Disponibilites">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-clock text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Disponibilites</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Creneaux recurrents utilises par la reservation en ligne.
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
                <div class="text-[11px] font-medium leading-tight text-gray-500">Creneaux</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Tous services</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $globalCount }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Avec lieu</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $locationCount }}</div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            <a href="{{ route('mobile.availabilities.create') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                Ajouter
            </a>
            <a href="{{ route('mobile.practice-locations.index') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Lieux
            </a>
        </div>

        @if($availabilities->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-calendar-plus text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucune disponibilite</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Ajoutez vos premiers creneaux pour ouvrir la reservation.
                </p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($availabilities as $availability)
                    @php
                        $productNames = $availability->applies_to_all
                            ? collect(['Toutes les prestations'])
                            : $availability->products->pluck('name')->filter()->take(3);
                        $location = $availability->practiceLocation;
                    @endphp

                    <article class="rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="text-sm font-semibold text-gray-900">
                                    {{ $dayLabels[(int) $availability->day_of_week] ?? 'Jour' }}
                                </h2>
                                <p class="mt-1 text-base font-semibold text-[#647a0b]">
                                    {{ substr((string) $availability->start_time, 0, 5) }}
                                    <span class="text-gray-300">-</span>
                                    {{ substr((string) $availability->end_time, 0, 5) }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $availability->applies_to_all ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                                {{ $availability->applies_to_all ? 'Global' : 'Cible' }}
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $location ? ($location->label ?: 'Cabinet') : 'Sans lieu specifique' }}
                            </span>
                            @foreach($productNames as $productName)
                                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                    {{ $productName }}
                                </span>
                            @endforeach
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('mobile.availabilities.edit', $availability) }}"
                               class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                                <i class="fas fa-edit mr-1.5 text-[11px]"></i>
                                Modifier
                            </a>
                            <form method="POST"
                                  action="{{ route('mobile.availabilities.destroy', $availability) }}"
                                  onsubmit="return confirm('Supprimer cette disponibilite ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-xs font-semibold text-red-600">
                                    <i class="fas fa-trash-alt mr-1.5 text-[11px]"></i>
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-mobile-layout>

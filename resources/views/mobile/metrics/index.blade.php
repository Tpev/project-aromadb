@php
    $fullName = trim(($clientProfile->first_name ?? '') . ' ' . ($clientProfile->last_name ?? '')) ?: 'Client sans nom';
    $metricCount = $metrics->count();
    $entryCount = $metrics->sum(fn ($metric) => $metric->entries->count());
    $latestEntry = $metrics->flatMap(fn ($metric) => $metric->entries)->sortByDesc('entry_date')->first();
    $formatValue = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, ',', ' '), '0'), ',');
@endphp

<x-mobile-layout :title="'Suivi - ' . $fullName">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <a href="{{ route('mobile.clients.show', $clientProfile) }}"
                   class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                    <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                    Fiche client
                </a>
                <h1 class="break-words text-xl font-semibold leading-tight text-gray-900">Suivi des mesures</h1>
                <p class="mt-1 break-words text-sm leading-snug text-gray-600">{{ $fullName }}</p>
            </div>

            <a href="{{ route('mobile.metrics.create', $clientProfile) }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#647a0b] text-white shadow-sm"
               aria-label="Creer une mesure">
                <i class="fas fa-plus text-xs"></i>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Mesures</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $metricCount }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Valeurs</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $entryCount }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Derniere</div>
                <div class="mt-1 truncate text-sm font-semibold text-gray-900">
                    {{ $latestEntry?->entry_date ? \Illuminate\Support\Carbon::parse($latestEntry->entry_date)->format('d/m') : '-' }}
                </div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            <a href="{{ route('mobile.metrics.create', $clientProfile) }}"
               class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                Ajouter
            </a>
            <a href="{{ route('client_profiles.metrics.index', $clientProfile) }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Vue web
            </a>
        </div>

        @if($metrics->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-chart-line text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucune mesure</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Ajoutez une mesure pour suivre une valeur dans le temps.
                </p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($metrics as $metric)
                    @php
                        $last = $metric->entries->first();
                    @endphp

                    <a href="{{ route('mobile.metrics.show', [$clientProfile, $metric]) }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">{{ $metric->name }}</h2>
                                <p class="mt-1 text-xs leading-snug text-gray-600">
                                    Objectif : {{ $metric->goal !== null ? $formatValue($metric->goal) : 'non defini' }}
                                </p>
                            </div>
                            <i class="fas fa-chevron-right shrink-0 pt-1 text-[10px] text-gray-300"></i>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Derniere valeur</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                    {{ $last ? $formatValue($last->value) : '-' }}
                                </div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Historique</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                    {{ $metric->entries->count() }} valeur{{ $metric->entries->count() > 1 ? 's' : '' }}
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-mobile-layout>

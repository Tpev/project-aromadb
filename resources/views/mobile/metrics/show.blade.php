@php
    $fullName = trim(($clientProfile->first_name ?? '') . ' ' . ($clientProfile->last_name ?? '')) ?: 'Client sans nom';
    $formatValue = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, ',', ' '), '0'), ',');
    $latest = $entries->first();
@endphp

<x-mobile-layout :title="$metric->name">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.metrics.index', $clientProfile) }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Suivi des mesures
            </a>
            <h1 class="break-words text-xl font-semibold leading-tight text-gray-900">{{ $metric->name }}</h1>
            <p class="mt-1 break-words text-sm leading-snug text-gray-600">{{ $fullName }}</p>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        <section class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="grid grid-cols-3 gap-2">
                <div class="rounded-lg bg-[#f7f8f1] p-2">
                    <div class="text-[11px] font-medium text-gray-500">Objectif</div>
                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                        {{ $metric->goal !== null ? $formatValue($metric->goal) : '-' }}
                    </div>
                </div>
                <div class="rounded-lg bg-[#f7f8f1] p-2">
                    <div class="text-[11px] font-medium text-gray-500">Derniere</div>
                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                        {{ $latest ? $formatValue($latest->value) : '-' }}
                    </div>
                </div>
                <div class="rounded-lg bg-[#f7f8f1] p-2">
                    <div class="text-[11px] font-medium text-gray-500">Valeurs</div>
                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $entries->count() }}</div>
                </div>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2">
                <a href="{{ route('mobile.metrics.entries.create', [$clientProfile, $metric]) }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                    Ajouter une valeur
                </a>
                <a href="{{ route('mobile.metrics.edit', [$clientProfile, $metric]) }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                    Modifier
                </a>
            </div>
        </section>

        <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-gray-900">Historique</h2>
                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                    {{ $entries->count() }}
                </span>
            </div>

            @if($entries->isEmpty())
                <p class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-4 text-center text-sm text-gray-600">
                    Aucune valeur pour le moment.
                </p>
            @else
                <div class="space-y-2">
                    @foreach($entries as $entry)
                        <article class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $formatValue($entry->value) }}</h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ \Illuminate\Support\Carbon::parse($entry->entry_date)->format('d/m/Y') }}
                                    </p>
                                </div>

                                <a href="{{ route('mobile.metrics.entries.edit', [$clientProfile, $metric, $entry]) }}"
                                   class="inline-flex h-8 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700">
                                    Modifier
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <form method="POST"
              action="{{ route('mobile.metrics.destroy', [$clientProfile, $metric]) }}"
              class="mt-4"
              onsubmit="return confirm('Supprimer cette mesure et ses valeurs ?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 text-sm font-semibold text-red-700 shadow-sm active:scale-[0.99]">
                Supprimer la mesure
            </button>
        </form>
    </div>
</x-mobile-layout>

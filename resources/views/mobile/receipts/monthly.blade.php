@php
    $money = fn ($value) => number_format((float) $value, 2, ',', ' ') . ' EUR';
    $monthLabels = [
        1 => 'Janvier',
        2 => 'Fevrier',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juillet',
        8 => 'Aout',
        9 => 'Septembre',
        10 => 'Octobre',
        11 => 'Novembre',
        12 => 'Decembre',
    ];

    $annualTotal = 0.0;
    $annualService = 0.0;
    $annualGoods = 0.0;
    $annualOther = 0.0;

    foreach ($data as $row) {
        $annualTotal += (float) ($row['total'] ?? 0);
        $annualService += (float) ($row['service'] ?? 0);
        $annualGoods += (float) ($row['goods'] ?? 0);
        $annualOther += (float) ($row['other'] ?? 0);
    }
@endphp

<x-mobile-layout title="CA mensuel">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-chart-line text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">CA mensuel</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Chiffre d affaires encaisse par nature pour {{ $year }}.
                </p>
            </div>

            <a href="{{ route('mobile.receipts.index') }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500 shadow-sm"
               aria-label="Retour au livre">
                <i class="fas fa-arrow-left text-xs"></i>
            </a>
        </div>

        <form method="GET" class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
            <label class="block">
                <span class="text-xs font-semibold text-gray-600">Annee</span>
                <input type="number"
                       name="year"
                       value="{{ $year }}"
                       min="2000"
                       max="2100"
                       inputmode="numeric"
                       class="mt-1 h-10 w-full rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
            </label>
            <button type="submit"
                    class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white">
                Afficher
            </button>
        </form>

        <div class="mb-4 grid grid-cols-2 gap-2">
            <div class="rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <div class="text-[11px] font-medium text-gray-500">Annuel TTC</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">{{ $money($annualTotal) }}</div>
            </div>
            <div class="rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <div class="text-[11px] font-medium text-gray-500">Prestations</div>
                <div class="mt-1 text-sm font-semibold text-[#647a0b]">{{ $money($annualService) }}</div>
            </div>
            <div class="rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <div class="text-[11px] font-medium text-gray-500">Biens</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">{{ $money($annualGoods) }}</div>
            </div>
            <div class="rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <div class="text-[11px] font-medium text-gray-500">Autres</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">{{ $money($annualOther) }}</div>
            </div>
        </div>

        <div class="space-y-2">
            @foreach($monthLabels as $month => $label)
                @php
                    $row = $data[$month] ?? ['total' => 0, 'service' => 0, 'goods' => 0, 'other' => 0];
                    $monthTotal = (float) ($row['total'] ?? 0);
                    $monthService = (float) ($row['service'] ?? 0);
                    $monthGoods = (float) ($row['goods'] ?? 0);
                    $monthOther = (float) ($row['other'] ?? 0);
                @endphp

                <article class="rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="text-sm font-semibold text-gray-900">{{ $label }}</h2>
                            <p class="mt-1 text-xs text-gray-500">{{ $year }}</p>
                        </div>
                        <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $monthTotal == 0.0 ? 'border-gray-200 bg-gray-50 text-gray-600' : 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' }}">
                            {{ $money($monthTotal) }}
                        </span>
                    </div>

                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <div class="rounded-lg bg-[#f7f8f1] p-2">
                            <div class="text-[11px] font-medium text-gray-500">Services</div>
                            <div class="mt-0.5 truncate text-xs font-semibold text-gray-900">{{ $money($monthService) }}</div>
                        </div>
                        <div class="rounded-lg bg-[#f7f8f1] p-2">
                            <div class="text-[11px] font-medium text-gray-500">Biens</div>
                            <div class="mt-0.5 truncate text-xs font-semibold text-gray-900">{{ $money($monthGoods) }}</div>
                        </div>
                        <div class="rounded-lg bg-[#f7f8f1] p-2">
                            <div class="text-[11px] font-medium text-gray-500">Autres</div>
                            <div class="mt-0.5 truncate text-xs font-semibold text-gray-900">{{ $money($monthOther) }}</div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</x-mobile-layout>

@php
    $money = fn ($value) => number_format((float) $value, 2, ',', ' ') . ' EUR';
    $dateValue = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '-';

    $paymentLabels = [
        'transfer' => 'Virement',
        'card' => 'Carte',
        'check' => 'Cheque',
        'cash' => 'Especes',
        'other' => 'Autre',
    ];

    $natureLabels = [
        'service' => 'Service',
        'goods' => 'Biens',
        'other' => 'Autre',
    ];

    $sourceLabels = [
        'payment' => 'Paiement',
        'manual' => 'Manuel',
        'correction' => 'Correction',
        'refund' => 'Remboursement',
    ];
@endphp

<x-mobile-layout title="Livre de recettes">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-receipt text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Livre de recettes</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Encaissements scelles, corrections et total net.
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

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <div class="font-semibold">A corriger</div>
                <ul class="mt-1 list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @unless($canUseReceipts)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-amber-700">
                        <i class="fas fa-lock text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-semibold">Module reserve a l offre PRO</h2>
                        <p class="mt-1 leading-snug">
                            Les lignes du livre de recettes et le CA mensuel sont disponibles avec une formule incluant ce module.
                        </p>
                    </div>
                </div>

                <a href="{{ route('license-tiers.pricing') }}"
                   class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg bg-white px-3 text-xs font-semibold text-amber-900 shadow-sm">
                    Voir les formules
                </a>
            </div>
        @endunless

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Lignes</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $lineCount }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Net TTC</div>
                <div class="mt-1 truncate text-sm font-semibold text-gray-900">{{ $money($total) }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Corrections</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $correctionCount }}</div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            @if($canUseReceipts)
                <a href="{{ route('mobile.receipts.create') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                    Ajouter
                </a>
                <a href="{{ route('mobile.receipts.monthly') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                    CA mensuel
                </a>
            @else
                <a href="{{ route('license-tiers.pricing') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-amber-200 bg-amber-50 px-3 text-sm font-semibold text-amber-700 shadow-sm active:scale-[0.99]">
                    Offre PRO
                </a>
                <span class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm font-semibold text-gray-400">
                    CA mensuel
                </span>
            @endif
        </div>

        <form method="GET" class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
            <div class="grid grid-cols-2 gap-3">
                <label class="block min-w-0">
                    <span class="text-xs font-semibold text-gray-600">Du</span>
                    <input type="date"
                           name="from"
                           value="{{ request('from') }}"
                           class="mt-1 h-10 w-full rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                </label>
                <label class="block min-w-0">
                    <span class="text-xs font-semibold text-gray-600">Au</span>
                    <input type="date"
                           name="to"
                           value="{{ request('to') }}"
                           class="mt-1 h-10 w-full rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                </label>
            </div>

            <div class="mt-3 grid grid-cols-3 gap-2">
                <button type="submit"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-[#647a0b] px-2 text-xs font-semibold text-white">
                    Filtrer
                </button>
                <a href="{{ route('mobile.receipts.index') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-2 text-xs font-semibold text-gray-700">
                    Reset
                </a>
                @if($canUseReceipts)
                    <a href="{{ route('receipts.export', request()->only(['from', 'to'])) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-2 text-xs font-semibold text-gray-700">
                        CSV
                    </a>
                @else
                    <span class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-2 text-xs font-semibold text-gray-400">
                        CSV
                    </span>
                @endif
            </div>
        </form>

        @if($canUseReceipts && $receipts->isNotEmpty())
            <div class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <label class="flex h-10 items-center gap-2 rounded-lg bg-[#f7f8f1] px-3">
                    <i class="fas fa-search text-[11px] text-gray-400"></i>
                    <input type="search"
                           id="mobileReceiptSearch"
                           placeholder="Client, facture, note"
                           class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:ring-0"
                           oninput="filterMobileReceipts()">
                </label>
            </div>
        @endif

        @if($canUseReceipts && $receipts->isNotEmpty())
            <div class="mb-4 grid grid-cols-2 gap-2">
                <div class="rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                    <div class="text-[11px] font-medium text-gray-500">Credits</div>
                    <div class="mt-1 text-sm font-semibold text-[#647a0b]">{{ $money($creditTotal) }}</div>
                </div>
                <div class="rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                    <div class="text-[11px] font-medium text-gray-500">Debits</div>
                    <div class="mt-1 text-sm font-semibold text-red-600">{{ $money($debitTotal) }}</div>
                </div>
            </div>
        @endif

        @if($canUseReceipts && $receipts->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-receipt text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucune ecriture</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Les encaissements et corrections apparaitront ici apres saisie.
                </p>
            </div>
        @elseif($canUseReceipts)
            <div id="mobileReceiptList" class="space-y-3">
                @foreach($receipts as $receipt)
                    @php
                        $signedAmount = $receipt->direction === 'debit' ? -1 * (float) $receipt->amount_ttc : (float) $receipt->amount_ttc;
                        $isDebit = $receipt->direction === 'debit';
                        $canReverse = ! ($receipt->is_reversal ?? false)
                            && empty($receipt->reversal_of_id)
                            && (int) ($receipt->reversals_count ?? 0) === 0;
                        $searchText = \Illuminate\Support\Str::lower(trim(
                            ($receipt->record_number ?? '') . ' ' .
                            ($receipt->invoice_number ?? '') . ' ' .
                            ($receipt->client_name ?? '') . ' ' .
                            ($receipt->nature ?? '') . ' ' .
                            ($receipt->note ?? '')
                        ));
                    @endphp

                    <article class="rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm"
                             data-receipt="{{ $searchText }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">
                                    #{{ $receipt->record_number }} - {{ $receipt->client_name ?: 'Sans client' }}
                                </h2>
                                <p class="mt-1 text-xs leading-snug text-gray-600">
                                    {{ $dateValue($receipt->encaissement_date) }}
                                    @if($receipt->invoice_number)
                                        <span class="text-gray-300">/</span> {{ $receipt->invoice_number }}
                                    @endif
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $isDebit ? 'border-red-200 bg-red-50 text-red-600' : 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' }}">
                                {{ $isDebit ? 'Debit' : 'Credit' }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">TTC signe</div>
                                <div class="mt-0.5 truncate text-sm font-semibold {{ $signedAmount < 0 ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $money($signedAmount) }}
                                </div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">HT</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                    {{ $money($receipt->amount_ht) }}
                                </div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Mode</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                    {{ $paymentLabels[$receipt->payment_method] ?? ucfirst((string) $receipt->payment_method) }}
                                </div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Nature</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                    {{ $natureLabels[$receipt->nature] ?? ucfirst((string) $receipt->nature) }}
                                </div>
                            </div>
                        </div>

                        @if($receipt->note)
                            <p class="mt-3 line-clamp-2 text-xs leading-snug text-gray-600">
                                {{ $receipt->note }}
                            </p>
                        @endif

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $sourceLabels[$receipt->source] ?? ucfirst((string) $receipt->source) }}
                            </span>
                            @if($receipt->is_reversal || $receipt->reversal_of_id)
                                <span class="rounded-full bg-red-50 px-2 py-1 text-[11px] text-red-600">
                                    Contre-passation
                                </span>
                            @elseif(!$canReverse)
                                <span class="rounded-full bg-amber-50 px-2 py-1 text-[11px] text-amber-700">
                                    Deja corrigee
                                </span>
                            @else
                                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                    Scellee
                                </span>
                            @endif
                        </div>

                        @if($canReverse)
                            <details class="mt-3 rounded-lg border border-[#e4e8d5] bg-[#fbfcf7]">
                                <summary class="cursor-pointer px-3 py-2 text-xs font-semibold text-[#647a0b]">
                                    Contre-passer cette ligne
                                </summary>

                                <form method="POST"
                                      action="{{ route('mobile.receipts.reverse', $receipt) }}"
                                      class="space-y-3 border-t border-[#e4e8d5] p-3"
                                      onsubmit="this.querySelector('button[type=submit]').disabled=true;">
                                    @csrf

                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="block min-w-0">
                                            <span class="text-xs font-semibold text-gray-600">Date</span>
                                            <input type="date"
                                                   name="encaissement_date"
                                                   value="{{ now()->format('Y-m-d') }}"
                                                   required
                                                   class="mt-1 h-10 w-full rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                                        </label>
                                        <label class="block min-w-0">
                                            <span class="text-xs font-semibold text-gray-600">TTC optionnel</span>
                                            <input type="number"
                                                   name="amount_ttc"
                                                   min="0.01"
                                                   max="{{ $receipt->amount_ttc }}"
                                                   step="0.01"
                                                   inputmode="decimal"
                                                   placeholder="{{ number_format((float) $receipt->amount_ttc, 2, '.', '') }}"
                                                   class="mt-1 h-10 w-full rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                                        </label>
                                    </div>

                                    <label class="block">
                                        <span class="text-xs font-semibold text-gray-600">Note</span>
                                        <input type="text"
                                               name="note"
                                               maxlength="255"
                                               placeholder="Motif de correction"
                                               class="mt-1 h-10 w-full rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                                    </label>

                                    <button type="submit"
                                            class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-red-50 text-xs font-semibold text-red-600">
                                        Enregistrer la contre-passation
                                    </button>
                                </form>
                            </details>
                        @endif
                    </article>
                @endforeach
            </div>

            @if($receipts->hasPages())
                <div class="mt-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                    {{ $receipts->links() }}
                </div>
            @endif
        @endif
    </div>

    <script>
        function filterMobileReceipts() {
            const input = document.getElementById('mobileReceiptSearch');
            const filter = input ? input.value.toLowerCase() : '';
            const items = document.querySelectorAll('#mobileReceiptList > article');

            items.forEach((item) => {
                const text = item.getAttribute('data-receipt') || '';
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>

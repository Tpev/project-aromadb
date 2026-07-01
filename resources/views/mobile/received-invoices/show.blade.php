@php
    $money = fn ($value, ?string $currency = 'EUR') => $value === null
        ? '-'
        : number_format((float) $value, 2, ',', ' ') . ' ' . ($currency ?: 'EUR');
    $displayNumber = $invoice->invoice_number ?: '#' . $invoice->super_pdp_invoice_id;
@endphp

<x-mobile-layout :title="$displayNumber">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.received-invoices.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Factures recues
            </a>

            <div class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                        <i class="fas fa-file-import text-sm"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="break-words text-xl font-semibold leading-tight text-gray-900">
                            {{ $displayNumber }}
                        </h1>
                        <p class="mt-1 line-clamp-2 text-sm leading-snug text-gray-600">
                            {{ $invoice->seller_name ?: 'Fournisseur inconnu' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Montant TTC</div>
                        <div class="mt-0.5 truncate text-base font-semibold text-gray-900">
                            {{ $money($invoice->total_with_vat, $invoice->currency_code) }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Date</div>
                        <div class="mt-0.5 truncate text-base font-semibold text-gray-900">
                            {{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : 'Inconnue' }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span class="rounded-full border border-[#647a0b]/20 bg-[#647a0b]/10 px-2 py-1 text-[11px] font-medium text-[#647a0b]">
                        {{ $invoice->latest_event_text ?: ($invoice->latest_event_code ?: 'Synchronisee') }}
                    </span>
                    <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                        {{ $invoice->currency_code ?: 'EUR' }}
                    </span>
                    <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                        ID {{ $invoice->super_pdp_invoice_id }}
                    </span>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Telechargements</h2>
                <p class="mt-1 text-xs leading-snug text-gray-500">
                    Les fichiers sont recuperes depuis SUPER PDP au moment du telechargement.
                </p>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="{{ route('mobile.received-invoices.download', $invoice) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white shadow-sm">
                        Factur-X PDF
                    </a>
                    <a href="{{ route('mobile.received-invoices.download', [$invoice, 'format' => 'original']) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700 shadow-sm">
                        Original
                    </a>
                    <a href="{{ route('mobile.received-invoices.download', [$invoice, 'format' => 'cii']) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700 shadow-sm">
                        CII XML
                    </a>
                    <a href="{{ route('mobile.received-invoices.download', [$invoice, 'format' => 'ubl']) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700 shadow-sm">
                        UBL XML
                    </a>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Parties</h2>

                <div class="mt-3 space-y-2">
                    <div class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Fournisseur</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900">{{ $invoice->seller_name ?: 'Non renseigne' }}</div>
                    </div>

                    <div class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Acheteur</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900">{{ $invoice->buyer_name ?: 'Non renseigne' }}</div>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Statut SUPER PDP</h2>

                <div class="mt-3 grid grid-cols-1 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-3">
                        <div class="text-[11px] font-medium text-gray-500">Evenement</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">
                            {{ $invoice->latest_event_text ?: ($invoice->latest_event_code ?: 'Synchronisee') }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-3">
                        <div class="text-[11px] font-medium text-gray-500">Date evenement</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">
                            {{ $invoice->latest_event_at ? $invoice->latest_event_at->timezone('Europe/Paris')->format('d/m/Y H:i') : 'Non renseignee' }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-3">
                        <div class="text-[11px] font-medium text-gray-500">Derniere synchro</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">
                            {{ $invoice->last_synced_at ? $invoice->last_synced_at->timezone('Europe/Paris')->format('d/m/Y H:i') : 'Jamais' }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Identifiants</h2>

                <div class="mt-3 space-y-2 text-sm">
                    <div class="flex items-start justify-between gap-3 rounded-lg bg-[#f7f8f1] p-3">
                        <span class="text-gray-500">SUPER PDP</span>
                        <span class="break-all text-right font-semibold text-gray-900">{{ $invoice->super_pdp_invoice_id }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3 rounded-lg bg-[#f7f8f1] p-3">
                        <span class="text-gray-500">Externe</span>
                        <span class="break-all text-right font-semibold text-gray-900">{{ $invoice->external_id ?: '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3 rounded-lg bg-[#f7f8f1] p-3">
                        <span class="text-gray-500">Entreprise PDP</span>
                        <span class="break-all text-right font-semibold text-gray-900">{{ $invoice->super_pdp_company_id ?: '-' }}</span>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-mobile-layout>

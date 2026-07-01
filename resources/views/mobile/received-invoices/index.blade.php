@php
    $money = fn ($value, ?string $currency = 'EUR') => $value === null
        ? '-'
        : number_format((float) $value, 2, ',', ' ') . ' ' . ($currency ?: 'EUR');
    $total = $receivedInvoices->count();
    $totalAmount = $receivedInvoices->sum(fn ($invoice) => (float) ($invoice->total_with_vat ?? 0));
    $synced = $receivedInvoices->whereNotNull('last_synced_at')->count();
    $isConnected = $connection?->isConnected() ?? false;
@endphp

<x-mobile-layout title="Factures recues">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-file-import text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Factures recues</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Inbox SUPER PDP des factures fournisseurs synchronisees.
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

        @unless($featureEnabled)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-amber-700">
                        <i class="fas fa-lock text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-semibold">SUPER PDP sandbox non active</h2>
                        <p class="mt-1 leading-snug">
                            Cette inbox est reservee aux comptes de test autorises pour la facturation electronique.
                        </p>
                    </div>
                </div>
                <a href="{{ route('profile.editCompanyInfo') }}"
                   class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg bg-white px-3 text-xs font-semibold text-amber-900 shadow-sm">
                    Ouvrir les connexions web
                </a>
            </div>
        @endunless

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Factures</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Total TTC</div>
                <div class="mt-1 truncate text-sm font-semibold text-gray-900">{{ $money($totalAmount) }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Synchro</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $synced }}</div>
            </div>
        </div>

        <section class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-gray-900">Connexion</h2>
                    <p class="mt-1 text-xs leading-snug text-gray-500">
                        {{ $connection?->super_pdp_company_name ?: 'Entreprise non renseignee' }}
                    </p>
                </div>
                <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $isConnected ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' : 'border-gray-200 bg-gray-50 text-gray-600' }}">
                    {{ $isConnected ? 'Connectee' : 'Non connectee' }}
                </span>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2">
                <div class="rounded-lg bg-[#f7f8f1] p-2">
                    <div class="text-[11px] font-medium text-gray-500">Reception</div>
                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                        {{ ($connection?->receiving_invoices_enabled ?? false) ? 'Active' : 'Inactive' }}
                    </div>
                </div>
                <div class="rounded-lg bg-[#f7f8f1] p-2">
                    <div class="text-[11px] font-medium text-gray-500">Derniere synchro</div>
                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                        {{ $connection?->last_synced_at ? $connection->last_synced_at->format('d/m H:i') : 'Jamais' }}
                    </div>
                </div>
            </div>

            @if($connection?->last_error)
                <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-xs leading-snug text-red-700">
                    {{ $connection->last_error }}
                </div>
            @endif

            <div class="mt-3 grid grid-cols-2 gap-2">
                @if($featureEnabled)
                    <a href="{{ route('mobile.received-invoices.index', ['sync' => 1]) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white shadow-sm active:scale-[0.99]">
                        Synchroniser
                    </a>
                @else
                    <span class="inline-flex h-10 items-center justify-center rounded-lg bg-gray-100 px-3 text-xs font-semibold text-gray-400">
                        Synchroniser
                    </span>
                @endif
                <a href="{{ route('super-pdp.received-invoices.index') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                    Vue web
                </a>
            </div>
        </section>

        @if($receivedInvoices->isNotEmpty())
            <div class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <label class="flex h-10 items-center gap-2 rounded-lg bg-[#f7f8f1] px-3">
                    <i class="fas fa-search text-[11px] text-gray-400"></i>
                    <input type="search"
                           id="mobileReceivedInvoiceSearch"
                           placeholder="Fournisseur, numero, statut"
                           class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:ring-0"
                           oninput="filterMobileReceivedInvoices()">
                </label>
            </div>
        @endif

        @if($receivedInvoices->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-file-import text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucune facture recue</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Les factures synchronisees depuis SUPER PDP apparaitront ici.
                </p>
            </div>
        @else
            <div id="mobileReceivedInvoiceList" class="space-y-2">
                @foreach($receivedInvoices as $invoice)
                    @php
                        $searchText = Str::lower(trim(
                            ($invoice->invoice_number ?? '') . ' ' .
                            ($invoice->seller_name ?? '') . ' ' .
                            ($invoice->buyer_name ?? '') . ' ' .
                            ($invoice->latest_event_text ?? '') . ' ' .
                            ($invoice->latest_event_code ?? '')
                        ));
                    @endphp

                    <a href="{{ route('mobile.received-invoices.show', $invoice) }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]"
                       data-received-invoice="{{ $searchText }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">
                                    {{ $invoice->invoice_number ?: 'Facture sans numero' }}
                                </h2>
                                <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                    {{ $invoice->seller_name ?: 'Fournisseur inconnu' }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-[10px] font-medium text-gray-600">
                                {{ $invoice->currency_code ?: 'EUR' }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Date</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                    {{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : 'Date inconnue' }}
                                </div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">TTC</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                    {{ $money($invoice->total_with_vat, $invoice->currency_code) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $invoice->latest_event_text ?: ($invoice->latest_event_code ?: 'Synchronisee') }}
                            </span>
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $invoice->last_synced_at ? 'Sync ' . $invoice->last_synced_at->format('d/m') : 'Non synchronisee' }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function filterMobileReceivedInvoices() {
            const input = document.getElementById('mobileReceivedInvoiceSearch');
            const filter = input ? input.value.toLowerCase() : '';
            const items = document.querySelectorAll('#mobileReceivedInvoiceList > a');

            items.forEach((item) => {
                const text = item.getAttribute('data-received-invoice') || '';
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>

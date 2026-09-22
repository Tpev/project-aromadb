@php
    $number = $isQuote
        ? ($document->quote_number ?: 'Devis #' . $document->id)
        : ($document->invoice_number ?: $document->document_label . ' #' . $document->id);

    $client = $document->clientProfile;
    $company = $document->corporateClient;
    $recipient = $document->recipient_data;
    $clientName = $recipient['client_name'] ?: $recipient['company_name'] ?: 'Client non renseigné';
    $recipientEmail = $recipient['email'] ?? '';
    $recipientPhone = $recipient['phone'] ?? '';

    $formatEuro = fn ($value) => number_format((float) ($value ?? 0), 2, ',', ' ') . ' EUR';
    $formatDate = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '-';

    $status = $document->status ?: ($isQuote ? 'Devis' : 'En attente');
    $statusTone = match ($status) {
        'Payee', 'Payée', 'Devis Accepte', 'Devis Accepté' => 'bg-green-50 text-green-700 border-green-100',
        'Partiellement payee', 'Partiellement payée' => 'bg-amber-50 text-amber-700 border-amber-100',
        'Annulee', 'Annulée', 'Devis Refuse', 'Devis Refusé' => 'bg-red-50 text-red-700 border-red-100',
        default => 'bg-slate-50 text-slate-700 border-slate-100',
    };

    $webShowRoute = $isQuote
        ? route('invoices.showQuote', $document)
        : route('invoices.show', $document);

    $webEditRoute = $isQuote
        ? route('invoices.editQuote', $document)
        : route('invoices.edit', $document);

    $pdfRoute = $isQuote
        ? route('invoices.quotePdf', $document)
        : route('invoices.pdf', $document);
@endphp

<x-mobile-layout :title="$number">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.invoices.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Factures & devis
            </a>
            <div class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            {{ $document->document_label }}
                        </p>
                        <h1 class="mt-1 truncate text-xl font-semibold text-gray-900">{{ $number }}</h1>
                        <p class="mt-1 text-sm text-gray-600">{{ $clientName ?: 'Client non renseigné' }}</p>
                    </div>
                    <span class="inline-flex shrink-0 items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $statusTone }}">
                        {{ $status }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2 text-center text-[11px]">
                    <div class="rounded-lg bg-[#f7f8f1] px-2 py-2">
                        <p class="text-gray-500">Date</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ $formatDate($document->invoice_date) }}</p>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] px-2 py-2">
                        <p class="text-gray-500">Échéance</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ $formatDate($document->due_date) }}</p>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] px-2 py-2">
                        <p class="text-gray-500">Total TTC</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ $formatEuro($document->total_amount_with_tax) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Client</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700">
                    @if($client)
                        <a href="{{ route('mobile.clients.show', $client) }}" class="flex items-center justify-between gap-3 rounded-lg bg-[#f7f8f1] px-3 py-2">
                            <span class="min-w-0 truncate">{{ $clientName ?: 'Client sans nom' }}</span>
                            <i class="fas fa-chevron-right shrink-0 text-[10px] text-gray-400"></i>
                        </a>
                        @if($recipientEmail)
                            <a href="mailto:{{ $recipientEmail }}" class="flex items-center gap-2">
                                <i class="fas fa-envelope w-4 text-[11px] text-gray-400"></i>
                                <span class="break-all">{{ $recipientEmail }}</span>
                            </a>
                        @endif
                        @if($recipientPhone)
                            <a href="tel:{{ $recipientPhone }}" class="flex items-center gap-2">
                                <i class="fas fa-phone w-4 text-[11px] text-gray-400"></i>
                                <span>{{ $recipientPhone }}</span>
                            </a>
                        @endif
                    @elseif($company)
                        <div class="rounded-lg bg-[#f7f8f1] px-3 py-2 font-medium">{{ $company->name }}</div>
                        @if($company->billing_email)
                            <a href="mailto:{{ $company->billing_email }}" class="flex items-center gap-2">
                                <i class="fas fa-envelope w-4 text-[11px] text-gray-400"></i>
                                <span class="break-all">{{ $company->billing_email }}</span>
                            </a>
                        @endif
                    @else
                        <p class="text-gray-500">Aucun client rattaché.</p>
                    @endif
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Lignes</h2>
                    <span class="rounded-full bg-[#f7f8f1] px-2 py-0.5 text-[11px] text-gray-500">
                        {{ $document->items->count() }}
                    </span>
                </div>

                <div class="mt-3 divide-y divide-gray-100">
                    @forelse($document->items as $item)
                        <div class="py-3 first:pt-0 last:pb-0">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">{{ $item->name }}</p>
                                    @if($item->billing_description && $item->billing_description !== $item->name)
                                        <p class="mt-0.5 text-xs text-gray-500">{{ $item->billing_description }}</p>
                                    @endif
                                    @if($item->service_date_label)
                                        <p class="mt-1 text-[11px] font-medium text-[#647a0b]">Prestation : {{ $item->service_date_label }}</p>
                                    @endif
                                    <p class="mt-1 text-[11px] text-gray-500">
                                        {{ (float) $item->quantity }} x {{ $formatEuro($item->unit_price) }}
                                        @if(!is_null($item->tax_rate))
                                            · TVA {{ (float) $item->tax_rate }}%
                                        @endif
                                    </p>
                                </div>
                                <p class="shrink-0 text-sm font-semibold text-gray-900">
                                    {{ $formatEuro($item->total_price_with_tax ?? $item->total_price) }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Aucune ligne sur ce document.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Totaux</h2>
                <div class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <span class="text-gray-500">Total HT</span>
                        <span class="font-semibold text-gray-900">{{ $formatEuro($document->total_amount) }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-gray-500">TVA</span>
                        <span class="font-semibold text-gray-900">{{ $formatEuro($document->total_tax_amount) }}</span>
                    </div>
                    <div class="flex justify-between gap-3 border-t border-gray-100 pt-2 text-base">
                        <span class="font-semibold text-gray-900">Total TTC</span>
                        <span class="font-bold text-[#647a0b]">{{ $formatEuro($document->total_amount_with_tax) }}</span>
                    </div>

                    @if(!$isQuote && !$document->isCreditNote())
                        <div class="mt-3 rounded-lg bg-[#f7f8f1] px-3 py-2">
                            <div class="flex justify-between gap-3">
                                <span class="text-gray-600">Encaissé</span>
                                <span class="font-semibold text-gray-900">{{ $formatEuro($document->total_encaisse) }}</span>
                            </div>
                            <div class="mt-1 flex justify-between gap-3">
                                <span class="text-gray-600">Solde restant</span>
                                <span class="font-semibold text-[#854f38]">{{ $formatEuro($document->solde_restant) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            @if(!$isQuote && !$document->isCreditNote())
                <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold text-gray-900">Encaissements</h2>
                        <span class="rounded-full bg-[#f7f8f1] px-2 py-0.5 text-[11px] text-gray-500">
                            {{ $document->receipts->count() }}
                        </span>
                    </div>

                    <div class="mt-3 divide-y divide-gray-100">
                        @forelse($document->receipts as $receipt)
                            <div class="flex items-center justify-between gap-3 py-2 first:pt-0 last:pb-0 text-sm">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $formatDate($receipt->encaissement_date) }}</p>
                                    <p class="text-xs text-gray-500">{{ $receipt->payment_method_label ?? $receipt->payment_method ?? 'Paiement' }}</p>
                                </div>
                                <span class="font-semibold {{ $receipt->direction === 'debit' ? 'text-red-600' : 'text-green-700' }}">
                                    {{ $receipt->direction === 'debit' ? '-' : '+' }}{{ $formatEuro($receipt->amount_ttc) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Aucun encaissement enregistré.</p>
                        @endforelse
                    </div>
                </section>
            @endif

            @if(!$isQuote && ($document->originalInvoice || $document->corrections->isNotEmpty()))
                <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-900">Documents liés</h2>
                    <div class="mt-3 space-y-2 text-sm">
                        @if($document->originalInvoice)
                            <a href="{{ route('mobile.invoices.show', $document->originalInvoice) }}"
                               class="flex items-center justify-between gap-3 rounded-lg bg-[#f7f8f1] px-3 py-2 text-gray-700">
                                <span>{{ $document->originalInvoice->document_label }} #{{ $document->originalInvoice->invoice_number }}</span>
                                <i class="fas fa-chevron-right text-[10px] text-gray-400"></i>
                            </a>
                        @endif
                        @foreach($document->corrections as $correction)
                            <a href="{{ route('mobile.invoices.show', $correction) }}"
                               class="flex items-center justify-between gap-3 rounded-lg bg-[#f7f8f1] px-3 py-2 text-gray-700">
                                <span>{{ $correction->document_label }} #{{ $correction->invoice_number }}</span>
                                <i class="fas fa-chevron-right text-[10px] text-gray-400"></i>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($document->notes)
                <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-900">Notes</h2>
                    <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $document->notes }}</p>
                </section>
            @endif

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Actions</h2>
                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                    <a href="{{ $pdfRoute }}"
                       class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 font-semibold text-white">
                        <i class="fas fa-download mr-2 text-xs"></i>
                        PDF
                    </a>

                    @if($isQuote || $document->isEditable())
                        <a href="{{ $webEditRoute }}"
                           class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 font-semibold text-gray-700">
                            <i class="fas fa-edit mr-2 text-xs"></i>
                            Modifier
                        </a>
                    @else
                        <div class="inline-flex min-h-11 items-center justify-center rounded-lg bg-[#f7f8f1] px-3 text-center text-xs font-semibold text-[#4b5722]">
                            Document finalisé
                        </div>
                    @endif

                    @if(!$isQuote && $document->payment_link)
                        <a href="{{ $document->payment_link }}"
                           class="col-span-2 inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 font-semibold text-gray-700">
                            <i class="fas fa-link mr-2 text-xs"></i>
                            Lien de paiement
                        </a>
                    @endif

                    <a href="{{ $webShowRoute }}"
                       class="col-span-2 inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 font-semibold text-gray-700">
                        <i class="fas fa-desktop mr-2 text-xs"></i>
                        Ouvrir la vue web complète
                    </a>
                </div>
            </section>
        </div>
    </div>
</x-mobile-layout>

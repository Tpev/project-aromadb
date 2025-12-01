{{-- resources/views/mobile/invoices/index.blade.php --}}
@php
    $title = __('Factures & devis');

    $formatEuro = fn($v) => number_format($v ?? 0, 2, ',', ' ') . ' €';
@endphp

<x-mobile-layout :title="$title">
    <div class="px-4 pt-4 pb-24 space-y-5 bg-[#f6f7f2]">

        {{-- Search + quick actions --}}
        <div class="space-y-3">
            <div class="relative">
                <input id="invoiceSearch"
                       type="text"
                       class="w-full rounded-2xl border border-[#d5dcc0] bg-white/90 px-3 py-2.5 text-[13px] shadow-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40"
                       placeholder="{{ __('Rechercher par client, numéro ou statut...') }}">
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">
                    <i class="fas fa-search"></i>
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-[12px]">
                <a href="{{ route('invoices.create') }}"
                   class="inline-flex items-center justify-center px-3 py-2.5 rounded-2xl bg-[#647a0b] text-white font-medium shadow-sm active:scale-[0.99]">
                    <i class="fas fa-file-invoice-dollar text-[11px] mr-1.5"></i>
                    {{ __('Créer une facture') }}
                </a>
                <a href="{{ route('invoices.createQuote') }}"
                   class="inline-flex items-center justify-center px-3 py-2.5 rounded-2xl bg-white text-gray-800 font-medium border border-[#e4e8d5] shadow-sm active:scale-[0.99]">
                    <i class="fas fa-file-signature text-[11px] mr-1.5"></i>
                    {{ __('Créer un devis') }}
                </a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="rounded-3xl bg-gradient-to-br from-[#f5f7eb] via-white to-[#f1f3e4] p-3.5 border border-[#e2e8d0] shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-2xl bg-[#647a0b]/10 text-[#647a0b] text-xs">
                        <i class="fas fa-chart-line"></i>
                    </span>
                    <div class="flex flex-col">
                        <span class="text-[12px] font-semibold text-[#4b5722]">
                            {{ __('Vue d’ensemble') }}
                        </span>
                        <span class="text-[11px] text-gray-500">
                            {{ __('Suivi rapide de votre activité') }}
                        </span>
                    </div>
                </div>

                <span class="inline-flex items-center rounded-full bg-white/80 px-2.5 py-1 text-[10px] text-gray-600 border border-[#e4e8d5]">
                    <i class="far fa-clock mr-1"></i>
                    {{ __('Mis à jour en temps réel') }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3 text-[11px]">
                {{-- Invoices --}}
                <div class="rounded-2xl bg-white/90 p-3 border border-[#e4e8d5]">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-semibold text-gray-600 uppercase tracking-wide flex items-center gap-1.5">
                            <i class="fas fa-file-invoice-dollar text-[#647a0b] text-[10px]"></i>
                            {{ __('Factures') }}
                        </span>
                        <span class="text-[11px] text-gray-400">
                            {{ $invoiceStats['count'] }} {{ __('doc.') }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[11px] text-gray-600 flex justify-between">
                            <span>{{ __('Total TTC émis') }}</span>
                            <span class="font-semibold text-gray-900">
                                {{ $formatEuro($invoiceStats['total_ttc']) }}
                            </span>
                        </p>
                        <p class="text-[11px] text-gray-600 flex justify-between">
                            <span>{{ __('En attente') }}</span>
                            <span class="font-semibold text-[#854f38]">
                                {{ $invoiceStats['outstanding_count'] }} {{ __('factures') }}
                            </span>
                        </p>
                        <p class="text-[11px] text-gray-600 flex justify-between">
                            <span>{{ __('Solde restant') }}</span>
                            <span class="font-semibold text-[#854f38]">
                                {{ $formatEuro($invoiceStats['outstanding_total']) }}
                            </span>
                        </p>
                    </div>

                    <a href="{{ route('invoices.index') }}"
                       class="mt-2 inline-flex items-center text-[11px] text-[#647a0b]">
                        {{ __('Ouvrir la vue web') }}
                        <i class="fas fa-external-link-alt text-[9px] ml-1"></i>
                    </a>
                </div>

                {{-- Quotes --}}
                <div class="rounded-2xl bg-white/90 p-3 border border-[#e4e8d5]">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-semibold text-gray-600 uppercase tracking-wide flex items-center gap-1.5">
                            <i class="fas fa-file-signature text-[#647a0b] text-[10px]"></i>
                            {{ __('Devis') }}
                        </span>
                        <span class="text-[11px] text-gray-400">
                            {{ $quoteStats['count'] }} {{ __('doc.') }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[11px] text-gray-600 flex justify-between">
                            <span>{{ __('Total TTC devis') }}</span>
                            <span class="font-semibold text-gray-900">
                                {{ $formatEuro($quoteStats['total_ttc']) }}
                            </span>
                        </p>
                        <p class="text-[11px] text-gray-600 flex justify-between">
                            <span>{{ __('Acceptés / en attente') }}</span>
                            <span>
                                <span class="font-semibold text-green-700">
                                    {{ $quoteStats['accepted'] }}
                                </span>
                                <span class="text-gray-400">
                                    / {{ $quoteStats['pending'] }}
                                </span>
                            </span>
                        </p>
                        <p class="text-[11px] text-gray-600 flex justify-between">
                            <span>{{ __('Refusés') }}</span>
                            <span class="font-semibold text-red-600">
                                {{ $quoteStats['rejected'] }}
                            </span>
                        </p>
                    </div>

                    <a href="{{ route('invoices.index') }}#devis"
                       class="mt-2 inline-flex items-center text-[11px] text-[#647a0b]">
                        {{ __('Gérer les devis (web)') }}
                        <i class="fas fa-external-link-alt text-[9px] ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Factures list --}}
        <div class="rounded-3xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#647a0b]/10 text-[#647a0b] text-[10px]">
                        <i class="fas fa-file-invoice"></i>
                    </span>
                    <h2 class="text-xs font-semibold text-gray-700 uppercase tracking-wide">
                        {{ __('Factures') }}
                    </h2>
                </div>
                <span class="inline-flex items-center rounded-full bg-[#f5f7eb] px-2 py-0.5 text-[10px] text-gray-500">
                    {{ $invoiceStats['count'] }} {{ __('au total') }}
                </span>
            </div>

            @if($invoices->isEmpty())
                <p class="text-xs text-gray-500">
                    {{ __('Vous n’avez pas encore créé de facture.') }}
                </p>
            @else
                <div class="space-y-2" id="invoiceList">
                    @foreach($invoices as $invoice)
                        @php
                            $status = $invoice->status ?? 'En attente';

                            $statusClasses = match ($status) {
                                'Payée' => 'bg-green-50 text-green-700 border-green-100',
                                'Partiellement payée' => 'bg-amber-50 text-amber-700 border-amber-100',
                                'Annulée' => 'bg-red-50 text-red-700 border-red-100',
                                default => 'bg-slate-50 text-slate-700 border-slate-100',
                            };

                            $sent = !is_null($invoice->sent_at);
                        @endphp

                        <a href="{{ route('invoices.show', $invoice->id) }}"
                           class="block rounded-2xl border border-[#e4e8d5] bg-white/95 px-3.5 py-3 text-[11px] shadow-sm active:scale-[0.99] transition-transform"
                           data-type="invoice"
                           data-client="{{ strtolower($invoice->clientProfile->first_name . ' ' . $invoice->clientProfile->last_name) }}"
                           data-status="{{ strtolower($status) }}"
                           data-number="{{ strtolower($invoice->invoice_number) }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold text-gray-900 truncate">
                                        #{{ $invoice->invoice_number }}
                                        <span class="text-gray-400 ml-1.5">
                                            • {{ $invoice->clientProfile->first_name }} {{ $invoice->clientProfile->last_name }}
                                        </span>
                                    </p>

                                    <p class="mt-0.5 text-[10px] text-gray-500 flex items-center gap-1.5">
                                        <i class="fas fa-calendar-alt text-[9px]"></i>
                                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                                        <span class="mx-1 text-gray-300">•</span>
                                        <span class="text-gray-800 font-semibold">
                                            {{ $formatEuro($invoice->total_amount_with_tax) }}
                                        </span>
                                    </p>

                                    <p class="mt-0.5 text-[10px] text-gray-500 flex items-center gap-1.5">
                                        @if($sent)
                                            <i class="fas fa-paper-plane text-[9px] text-green-600"></i>
                                            {{ __('Envoyée le') }}
                                            {{ \Carbon\Carbon::parse($invoice->sent_at)->format('d/m/Y H:i') }}
                                        @else
                                            <i class="fas fa-paper-plane text-[9px] text-gray-400"></i>
                                            <span class="text-gray-400">
                                                {{ __('Non envoyée') }}
                                            </span>
                                        @endif
                                    </p>
                                </div>

                                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full border {{ $statusClasses }} text-[10px] font-semibold whitespace-nowrap">
                                        {{ $status }}
                                    </span>

                                    @if($invoice->payment_link)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#f5f7eb] text-[9px] text-[#4b5722]">
                                            <i class="fas fa-link text-[8px] mr-1"></i>
                                            {{ __('Lien de paiement') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Devis list --}}
        <div class="rounded-3xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#647a0b]/10 text-[#647a0b] text-[10px]">
                        <i class="fas fa-file-contract"></i>
                    </span>
                    <h2 class="text-xs font-semibold text-gray-700 uppercase tracking-wide">
                        {{ __('Devis') }}
                    </h2>
                </div>
                <span class="inline-flex items-center rounded-full bg-[#f5f7eb] px-2 py-0.5 text-[10px] text-gray-500">
                    {{ $quoteStats['count'] }} {{ __('au total') }}
                </span>
            </div>

            @if($quotes->isEmpty())
                <p class="text-xs text-gray-500">
                    {{ __('Aucun devis créé pour le moment.') }}
                </p>
            @else
                <div class="space-y-2" id="quoteList">
                    @foreach($quotes as $quote)
                        @php
                            $status = $quote->status ?? 'Devis';
                            $statusClasses = match ($status) {
                                'Devis Accepté' => 'bg-green-50 text-green-700 border-green-100',
                                'Devis Refusé'  => 'bg-red-50 text-red-700 border-red-100',
                                default         => 'bg-slate-50 text-slate-700 border-slate-100',
                            };

                            $sent = !is_null($quote->sent_at);
                        @endphp

                        <a href="{{ route('invoices.showQuote', $quote->id) }}"
                           class="block rounded-2xl border border-[#e4e8d5] bg-white/95 px-3.5 py-3 text-[11px] shadow-sm active:scale-[0.99] transition-transform"
                           data-type="quote"
                           data-client="{{ strtolower($quote->clientProfile->first_name . ' ' . $quote->clientProfile->last_name) }}"
                           data-status="{{ strtolower($status) }}"
                           data-number="{{ strtolower($quote->quote_number ?? '') }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold text-gray-900 truncate">
                                        {{ $quote->quote_number ?? 'Devis' }}
                                        <span class="text-gray-400 ml-1.5">
                                            • {{ $quote->clientProfile->first_name }} {{ $quote->clientProfile->last_name }}
                                        </span>
                                    </p>
                                    <p class="mt-0.5 text-[10px] text-gray-500 flex items-center gap-1.5">
                                        <i class="fas fa-calendar-alt text-[9px]"></i>
                                        {{ \Carbon\Carbon::parse($quote->invoice_date)->format('d/m/Y') }}
                                        <span class="mx-1 text-gray-300">•</span>
                                        <span class="text-gray-800 font-semibold">
                                            {{ $formatEuro($quote->total_amount_with_tax) }}
                                        </span>
                                    </p>

                                    <p class="mt-0.5 text-[10px] text-gray-500 flex items-center gap-1.5">
                                        @if($sent)
                                            <i class="fas fa-paper-plane text-[9px] text-green-600"></i>
                                            {{ __('Envoyé le') }}
                                            {{ \Carbon\Carbon::parse($quote->sent_at)->format('d/m/Y H:i') }}
                                        @else
                                            <i class="fas fa-paper-plane text-[9px] text-gray-400"></i>
                                            <span class="text-gray-400">
                                                {{ __('Non envoyé') }}
                                            </span>
                                        @endif
                                    </p>
                                </div>

                                <span class="inline-flex items-center px-2 py-0.5 rounded-full border {{ $statusClasses }} text-[10px] font-semibold whitespace-nowrap flex-shrink-0">
                                    {{ $status }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Back to dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center justify-center w-full px-3 py-2.5 rounded-2xl bg-white border border-[#e4e8d5] text-[12px] font-medium text-gray-800 shadow-sm active:scale-[0.99]">
            <i class="fas fa-arrow-left text-[11px] mr-1.5"></i>
            {{ __('Retour au tableau de bord') }}
        </a>
    </div>

    {{-- Simple search filter --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input     = document.getElementById('invoiceSearch');
            const invList   = document.getElementById('invoiceList');
            const quoteList = document.getElementById('quoteList');

            if (!input) return;

            const filterCards = () => {
                const q = input.value.toLowerCase();

                const applyFilter = (container) => {
                    if (!container) return;
                    [...container.querySelectorAll('a[data-type]')].forEach(card => {
                        const client = card.dataset.client || '';
                        const status = card.dataset.status || '';
                        const number = card.dataset.number || '';

                        if (
                            client.includes(q)
                            || status.includes(q)
                            || number.includes(q)
                        ) {
                            card.classList.remove('hidden');
                        } else {
                            card.classList.add('hidden');
                        }
                    });
                };

                applyFilter(invList);
                applyFilter(quoteList);
            };

            input.addEventListener('input', filterCards);
        });
    </script>
</x-mobile-layout>

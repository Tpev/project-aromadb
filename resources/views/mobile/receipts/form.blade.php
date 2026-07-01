@php
    $paymentLabels = [
        'transfer' => ['label' => 'Virement', 'icon' => 'fa-exchange-alt'],
        'card' => ['label' => 'Carte', 'icon' => 'fa-credit-card'],
        'check' => ['label' => 'Cheque', 'icon' => 'fa-money-check-alt'],
        'cash' => ['label' => 'Especes', 'icon' => 'fa-coins'],
        'other' => ['label' => 'Autre', 'icon' => 'fa-ellipsis-h'],
    ];

    $natureLabels = [
        'service' => ['label' => 'Service', 'hint' => 'Prestation ou seance'],
        'goods' => ['label' => 'Biens', 'hint' => 'Produit ou marchandise'],
        'other' => ['label' => 'Autre', 'hint' => 'Cas particulier'],
    ];
@endphp

<x-mobile-layout title="Nouvelle ecriture" :hide-nav="true">
    <form method="POST" action="{{ route('mobile.receipts.store') }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf

        <div class="mb-4">
            <a href="{{ route('mobile.receipts.index') }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Livre de recettes
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">Nouvelle ecriture</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Saisie manuelle d un encaissement ou d une correction.
            </p>
        </div>

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

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Montants</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Date d encaissement</span>
                        <input type="date"
                               name="encaissement_date"
                               value="{{ old('encaissement_date', now()->format('Y-m-d')) }}"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <div>
                        <span class="text-sm font-medium text-gray-700">Direction</span>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            @foreach([
                                'credit' => ['label' => 'Credit', 'icon' => 'fa-arrow-down'],
                                'debit' => ['label' => 'Debit', 'icon' => 'fa-arrow-up'],
                            ] as $value => $option)
                                <label class="flex h-12 items-center gap-2 rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 has-[:checked]:border-[#647a0b] has-[:checked]:bg-[#647a0b]/10 has-[:checked]:text-[#647a0b]">
                                    <input type="radio"
                                           name="direction"
                                           value="{{ $value }}"
                                           required
                                           class="h-4 w-4 shrink-0 border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                           {{ old('direction', 'credit') === $value ? 'checked' : '' }}>
                                    <span class="flex min-w-0 items-center gap-2">
                                        <i class="fas {{ $option['icon'] }} shrink-0 text-xs"></i>
                                        {{ $option['label'] }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block min-w-0">
                            <span class="text-sm font-medium text-gray-700">TTC EUR</span>
                            <input type="number"
                                   name="amount_ttc"
                                   value="{{ old('amount_ttc') }}"
                                   min="0.01"
                                   step="0.01"
                                   required
                                   inputmode="decimal"
                                   placeholder="50.00"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block min-w-0">
                            <span class="text-sm font-medium text-gray-700">HT EUR</span>
                            <input type="number"
                                   name="amount_ht"
                                   value="{{ old('amount_ht') }}"
                                   min="0"
                                   step="0.01"
                                   inputmode="decimal"
                                   placeholder="= TTC"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>

                    <p class="rounded-lg bg-[#f7f8f1] p-3 text-xs leading-snug text-gray-600">
                        Si le HT est vide, il sera egal au TTC. Pratique pour les cas sans TVA.
                    </p>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Qualification</h2>

                <label class="mt-3 block">
                    <span class="text-sm font-medium text-gray-700">Mode de reglement</span>
                    <select name="payment_method"
                            required
                            class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        @foreach($paymentLabels as $value => $option)
                            <option value="{{ $value }}" {{ old('payment_method', 'card') === $value ? 'selected' : '' }}>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <div class="mt-3">
                    <span class="text-sm font-medium text-gray-700">Nature</span>
                    <div class="mt-2 space-y-2">
                        @foreach($natureLabels as $value => $option)
                            <label class="flex min-h-12 items-center gap-3 rounded-lg border border-[#e4e8d5] bg-white px-3 py-2 text-sm font-semibold text-gray-700 has-[:checked]:border-[#647a0b] has-[:checked]:bg-[#647a0b]/10 has-[:checked]:text-[#647a0b]">
                                <input type="radio"
                                       name="nature"
                                       value="{{ $value }}"
                                       required
                                       class="h-4 w-4 shrink-0 border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                       {{ old('nature', 'service') === $value ? 'checked' : '' }}>
                                <span class="min-w-0">
                                    <span class="block">{{ $option['label'] }}</span>
                                    <span class="block text-[11px] font-medium text-gray-500">{{ $option['hint'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Reference</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Client</span>
                        <input type="text"
                               name="client_name"
                               value="{{ old('client_name') }}"
                               maxlength="255"
                               placeholder="Nom du client"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Numero de facture</span>
                        <input type="text"
                               name="invoice_number"
                               value="{{ old('invoice_number') }}"
                               maxlength="255"
                               placeholder="Facture externe ou reference"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Note</span>
                        <input type="text"
                               name="note"
                               value="{{ old('note') }}"
                               maxlength="255"
                               placeholder="Acompte, salon, regularisation..."
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-amber-700">
                        <i class="fas fa-lock text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-semibold">Registre immuable</h2>
                        <p class="mt-1 leading-snug">
                            Une ecriture enregistree ne peut pas etre modifiee ni supprimee. En cas d erreur, utilisez une contre-passation depuis la liste.
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ route('mobile.receipts.index') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-sm font-semibold text-gray-700">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] text-sm font-semibold text-white">
                    Enregistrer
                </button>
            </div>
        </div>
    </form>
</x-mobile-layout>

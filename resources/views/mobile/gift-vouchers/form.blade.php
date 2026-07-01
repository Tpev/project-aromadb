@php
    $fieldValue = fn (string $field, mixed $default = null) => old($field, $default);
    $paymentMethod = old('payment_method', 'other');
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf

        <div class="mb-4">
            <a href="{{ route('mobile.gift-vouchers.index') }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Bons cadeaux
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Creez un code cadeau, envoyez le PDF et suivez son solde.
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
                <h2 class="text-sm font-semibold text-gray-900">Montant</h2>

                <div class="mt-3 grid grid-cols-2 gap-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Montant EUR</span>
                        <input type="number"
                               name="amount_eur"
                               value="{{ $fieldValue('amount_eur') }}"
                               step="0.01"
                               min="5"
                               required
                               inputmode="decimal"
                               placeholder="50"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Expiration</span>
                        <input type="date"
                               name="expires_at"
                               value="{{ $fieldValue('expires_at') }}"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                        <i class="fas fa-user text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold text-gray-900">Acheteur</h2>
                        <p class="mt-0.5 text-xs leading-snug text-gray-500">L acheteur recoit toujours le PDF par email.</p>
                    </div>
                </div>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Nom</span>
                        <input type="text"
                               name="buyer_name"
                               value="{{ $fieldValue('buyer_name') }}"
                               placeholder="Marie Dupont"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Email</span>
                        <input type="email"
                               name="buyer_email"
                               value="{{ $fieldValue('buyer_email') }}"
                               required
                               placeholder="marie@example.com"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Telephone</span>
                        <input type="text"
                               name="buyer_phone"
                               value="{{ $fieldValue('buyer_phone') }}"
                               inputmode="tel"
                               placeholder="0601020304"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#f5f7eb] text-[#647a0b]">
                        <i class="fas fa-heart text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold text-gray-900">Beneficiaire</h2>
                        <p class="mt-0.5 text-xs leading-snug text-gray-500">Optionnel. S il y a un email, il recoit aussi le PDF.</p>
                    </div>
                </div>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Nom</span>
                        <input type="text"
                               name="recipient_name"
                               value="{{ $fieldValue('recipient_name') }}"
                               placeholder="Camille"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Email</span>
                        <input type="email"
                               name="recipient_email"
                               value="{{ $fieldValue('recipient_email') }}"
                               placeholder="camille@example.com"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Message</h2>
                <label class="mt-3 block">
                    <span class="text-sm font-medium text-gray-700">Message personnalise</span>
                    <textarea name="message"
                              rows="4"
                              placeholder="Joyeux anniversaire"
                              class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ $fieldValue('message') }}</textarea>
                </label>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <label class="flex items-start justify-between gap-4 rounded-lg bg-[#f7f8f1] px-3 py-3">
                    <span>
                        <span class="block text-sm font-semibold text-gray-900">Facture de vente</span>
                        <span class="mt-0.5 block text-xs leading-snug text-gray-500">Creer une facture payee et un encaissement.</span>
                    </span>
                    <span>
                        <input type="hidden" name="create_sale_invoice" value="0">
                        <input type="checkbox"
                               name="create_sale_invoice"
                               value="1"
                               class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                               {{ old('create_sale_invoice') ? 'checked' : '' }}>
                    </span>
                </label>

                <label class="mt-3 block">
                    <span class="text-sm font-medium text-gray-700">Paiement</span>
                    <select name="payment_method"
                            class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        @foreach([
                            'other' => 'Autre',
                            'card' => 'Carte',
                            'transfer' => 'Virement',
                            'check' => 'Cheque',
                            'cash' => 'Especes',
                        ] as $value => $label)
                            <option value="{{ $value }}" {{ $paymentMethod === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ route('mobile.gift-vouchers.index') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-sm font-semibold text-gray-700">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] text-sm font-semibold text-white">
                    {{ $submitLabel }}
                </button>
            </div>
        </div>
    </form>
</x-mobile-layout>

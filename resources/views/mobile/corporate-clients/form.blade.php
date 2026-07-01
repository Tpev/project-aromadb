@php
    $fieldValue = fn (string $field, mixed $default = null) => old($field, $company->{$field} ?? $default);
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <a href="{{ $company->exists ? route('mobile.corporate-clients.show', $company) : route('mobile.corporate-clients.index') }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Entreprises
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Informations utiles pour facturer, appeler ou rattacher des clients.
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
                <h2 class="text-sm font-semibold text-gray-900">Identite entreprise</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Raison sociale</span>
                        <input type="text"
                               name="name"
                               value="{{ $fieldValue('name') }}"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Nom commercial</span>
                        <input type="text"
                               name="trade_name"
                               value="{{ $fieldValue('trade_name') }}"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">SIRET</span>
                            <input type="text"
                                   name="siret"
                                   value="{{ $fieldValue('siret') }}"
                                   inputmode="numeric"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">TVA intra.</span>
                            <input type="text"
                                   name="vat_number"
                                   value="{{ $fieldValue('vat_number') }}"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base uppercase focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Adresse de facturation</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Adresse</span>
                        <input type="text"
                               name="billing_address"
                               value="{{ $fieldValue('billing_address') }}"
                               autocomplete="street-address"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Code postal</span>
                            <input type="text"
                                   name="billing_zip"
                                   value="{{ $fieldValue('billing_zip') }}"
                                   inputmode="numeric"
                                   autocomplete="postal-code"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Ville</span>
                            <input type="text"
                                   name="billing_city"
                                   value="{{ $fieldValue('billing_city') }}"
                                   autocomplete="address-level2"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Pays</span>
                        <input type="text"
                               name="billing_country"
                               value="{{ $fieldValue('billing_country', 'France') }}"
                               autocomplete="country-name"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Contact facturation</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Email facturation</span>
                        <input type="email"
                               name="billing_email"
                               value="{{ $fieldValue('billing_email') }}"
                               autocomplete="email"
                               inputmode="email"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Telephone facturation</span>
                        <input type="tel"
                               name="billing_phone"
                               value="{{ $fieldValue('billing_phone') }}"
                               autocomplete="tel"
                               inputmode="tel"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Contact principal</h2>

                <div class="mt-3 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Prenom</span>
                            <input type="text"
                                   name="main_contact_first_name"
                                   value="{{ $fieldValue('main_contact_first_name') }}"
                                   autocomplete="given-name"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Nom</span>
                            <input type="text"
                                   name="main_contact_last_name"
                                   value="{{ $fieldValue('main_contact_last_name') }}"
                                   autocomplete="family-name"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Email</span>
                        <input type="email"
                               name="main_contact_email"
                               value="{{ $fieldValue('main_contact_email') }}"
                               autocomplete="email"
                               inputmode="email"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Telephone</span>
                        <input type="tel"
                               name="main_contact_phone"
                               value="{{ $fieldValue('main_contact_phone') }}"
                               autocomplete="tel"
                               inputmode="tel"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <label class="block">
                    <span class="text-sm font-semibold text-gray-900">Notes internes</span>
                    <textarea name="notes"
                              rows="4"
                              class="mt-2 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ $fieldValue('notes') }}</textarea>
                </label>
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ $company->exists ? route('mobile.corporate-clients.show', $company) : route('mobile.corporate-clients.index') }}"
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

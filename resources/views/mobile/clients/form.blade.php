@php
    $fieldValue = fn (string $field, mixed $default = null) => old($field, $clientProfile->{$field} ?? $default);
    $selectedCompany = old('company_id', $selectedCompanyId ?? $clientProfile->company_id);
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <a href="{{ route('mobile.clients.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Clients
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Les informations utiles pendant un appel, un message ou un rendez-vous.
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
                <h2 class="text-sm font-semibold text-gray-900">Identite</h2>

                <div class="mt-3 grid grid-cols-2 gap-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Prenom</span>
                        <input type="text"
                               name="first_name"
                               value="{{ $fieldValue('first_name') }}"
                               required
                               autocomplete="given-name"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Nom</span>
                        <input type="text"
                               name="last_name"
                               value="{{ $fieldValue('last_name') }}"
                               required
                               autocomplete="family-name"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>

                <label class="mt-3 block">
                    <span class="text-sm font-medium text-gray-700">Date de naissance</span>
                    <input type="date"
                           name="birthdate"
                           value="{{ $fieldValue('birthdate') }}"
                           class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                </label>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Contact</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Email</span>
                        <input type="email"
                               name="email"
                               value="{{ $fieldValue('email') }}"
                               autocomplete="email"
                               inputmode="email"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Telephone</span>
                        <input type="tel"
                               name="phone"
                               value="{{ $fieldValue('phone') }}"
                               autocomplete="tel"
                               inputmode="tel"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Adresse</span>
                        <textarea name="address"
                                  rows="2"
                                  autocomplete="street-address"
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ $fieldValue('address') }}</textarea>
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Entreprise et facturation</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Entreprise rattachee</span>
                        <select name="company_id"
                                class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            <option value="">Client particulier</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ (string) $selectedCompany === (string) $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Prenom facture</span>
                            <input type="text"
                                   name="first_name_billing"
                                   value="{{ $fieldValue('first_name_billing') }}"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Nom facture</span>
                            <input type="text"
                                   name="last_name_billing"
                                   value="{{ $fieldValue('last_name_billing') }}"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>
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
                <a href="{{ $clientProfile->exists ? route('mobile.clients.show', $clientProfile) : route('mobile.clients.index') }}"
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

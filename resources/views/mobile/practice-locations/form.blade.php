@php
    $fieldValue = fn (string $field, mixed $default = null) => old($field, $location->{$field} ?? $default);
    $isPrimary = (bool) old('is_primary', (bool) ($location->is_primary ?? false));
    $isShared = (bool) old('is_shared', (bool) ($location->is_shared ?? false));
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <a href="{{ route('mobile.practice-locations.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Lieux
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Adresse utilisee pour les rendez-vous au cabinet.
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

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Nom du lieu</span>
                        <input type="text"
                               name="label"
                               value="{{ $fieldValue('label') }}"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="flex items-center justify-between gap-4 rounded-lg bg-[#f7f8f1] px-3 py-3">
                        <span class="text-sm font-medium text-gray-700">Cabinet principal</span>
                        <span>
                            <input type="hidden" name="is_primary" value="0">
                            <input type="checkbox"
                                   name="is_primary"
                                   value="1"
                                   class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ $isPrimary ? 'checked' : '' }}>
                        </span>
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Adresse</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Adresse</span>
                        <input type="text"
                               name="address_line1"
                               value="{{ $fieldValue('address_line1') }}"
                               required
                               autocomplete="street-address"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Complement</span>
                        <input type="text"
                               name="address_line2"
                               value="{{ $fieldValue('address_line2') }}"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Code postal</span>
                            <input type="text"
                                   name="postal_code"
                                   value="{{ $fieldValue('postal_code') }}"
                                   inputmode="numeric"
                                   autocomplete="postal-code"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Ville</span>
                            <input type="text"
                                   name="city"
                                   value="{{ $fieldValue('city') }}"
                                   autocomplete="address-level2"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Pays</span>
                        <input type="text"
                               name="country"
                               value="{{ $fieldValue('country', 'FR') }}"
                               maxlength="2"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base uppercase focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>
            </section>

            @if($sharedCabinetsEnabled)
                <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-900">Partage</h2>
                    <label class="mt-3 flex items-start justify-between gap-4 rounded-lg bg-[#f7f8f1] px-3 py-3">
                        <span>
                            <span class="block text-sm font-medium text-gray-700">Cabinet partage</span>
                            <span class="mt-0.5 block text-xs leading-snug text-gray-500">
                                Les creneaux reserves au cabinet bloquent aussi les autres membres.
                            </span>
                        </span>
                        <span>
                            <input type="hidden" name="is_shared" value="0">
                            <input type="checkbox"
                                   name="is_shared"
                                   value="1"
                                   class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ $isShared ? 'checked' : '' }}>
                        </span>
                    </label>
                </section>
            @else
                <input type="hidden" name="is_shared" value="0">
            @endif
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ route('mobile.practice-locations.index') }}"
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

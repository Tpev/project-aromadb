@php
    $appointmentDate = $appointment->appointment_date
        ? \Carbon\Carbon::parse($appointment->appointment_date)
        : now()->addHour();

    $selectedClient = old('client_profile_id', $selectedClientId ?? $appointment->client_profile_id);
    $selectedProduct = old('product_id', $appointment->product_id);
    $selectedMode = old('type', $appointment->type ?: ($appointment->exists ? $appointment->getResolvedMode() : 'visio'));
    $selectedLocation = old('practice_location_id', $appointment->practice_location_id);
    $selectedStatus = old('status', $appointment->status ?: 'Programme');

    $modeOptions = [
        'cabinet' => ['label' => 'Cabinet', 'icon' => 'fa-map-marker-alt'],
        'visio' => ['label' => 'Visio', 'icon' => 'fa-video'],
        'domicile' => ['label' => 'Domicile', 'icon' => 'fa-home'],
        'entreprise' => ['label' => 'Entreprise', 'icon' => 'fa-building'],
    ];

    $statusOptions = [
        'Programme' => 'Programme',
        'Confirme' => 'Confirme',
        'Complete' => 'Complete',
        'Annulee' => 'Annule',
    ];
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <input type="hidden" name="force_availability_override" value="1">

        <div class="mb-4">
            <a href="{{ route('mobile.appointments.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Rendez-vous
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Creation rapide depuis le mobile. Les disponibilites web restent disponibles pour une planification fine.
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

        @if($clientProfiles->isEmpty() || $products->isEmpty())
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                Ajoutez au moins un client et une prestation avant de creer un rendez-vous.
            </div>
        @endif

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Client et prestation</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Client</span>
                        <select name="client_profile_id"
                                required
                                class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            <option value="">Selectionner un client</option>
                            @foreach($clientProfiles as $client)
                                <option value="{{ $client->id }}" {{ (string) $selectedClient === (string) $client->id ? 'selected' : '' }}>
                                    {{ trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')) ?: 'Client sans nom' }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Prestation</span>
                        <select name="product_id"
                                id="mobileAppointmentProduct"
                                required
                                class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            <option value="">Selectionner une prestation</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                        data-duration="{{ (int) ($product->duration ?: 60) }}"
                                        data-cabinet="{{ $product->dans_le_cabinet ? '1' : '0' }}"
                                        data-visio="{{ $product->visio ? '1' : '0' }}"
                                        data-domicile="{{ $product->adomicile ? '1' : '0' }}"
                                        data-entreprise="{{ $product->en_entreprise ? '1' : '0' }}"
                                        {{ (string) $selectedProduct === (string) $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} - {{ (int) ($product->duration ?: 60) }} min
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Date et horaire</h2>

                <div class="mt-3 grid grid-cols-2 gap-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Date</span>
                        <input type="date"
                               name="appointment_date"
                               value="{{ old('appointment_date', $appointmentDate->format('Y-m-d')) }}"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Heure</span>
                        <input type="time"
                               name="appointment_time"
                               value="{{ old('appointment_time', $appointmentDate->format('H:i')) }}"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Mode</h2>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    @foreach($modeOptions as $value => $option)
                        <label class="flex h-12 items-center gap-2 rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 has-[:checked]:border-[#647a0b] has-[:checked]:bg-[#647a0b]/10 has-[:checked]:text-[#647a0b]">
                            <input type="radio"
                                   name="type"
                                   value="{{ $value }}"
                                   class="h-4 w-4 shrink-0 border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ $selectedMode === $value ? 'checked' : '' }}>
                            <span class="flex min-w-0 items-center gap-2">
                                <i class="fas {{ $option['icon'] }} shrink-0 text-xs"></i>
                                {{ $option['label'] }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div id="mobileAppointmentCabinet" class="mt-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Cabinet</span>
                        <select name="practice_location_id"
                                class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            <option value="">Selectionner un lieu</option>
                            @foreach($practiceLocations as $location)
                                <option value="{{ $location->id }}" {{ (string) $selectedLocation === (string) $location->id ? 'selected' : '' }}>
                                    {{ $location->label ?: $location->full_address ?: 'Cabinet' }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div id="mobileAppointmentAddress" class="mt-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Adresse</span>
                        <textarea name="address"
                                  rows="2"
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('address', $appointment->address) }}</textarea>
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Suivi</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Statut</span>
                        <select name="status"
                                required
                                class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Notes</span>
                        <textarea name="notes"
                                  rows="4"
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('notes', $appointment->notes) }}</textarea>
                    </label>
                </div>
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ $appointment->exists ? route('mobile.appointments.show', $appointment) : route('mobile.appointments.index') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-sm font-semibold text-gray-700">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] text-sm font-semibold text-white disabled:bg-gray-300"
                        @disabled($clientProfiles->isEmpty() || $products->isEmpty())>
                    {{ $submitLabel }}
                </button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const product = document.getElementById('mobileAppointmentProduct');
            const modeInputs = [...document.querySelectorAll('input[name="type"]')];
            const cabinet = document.getElementById('mobileAppointmentCabinet');
            const address = document.getElementById('mobileAppointmentAddress');

            const syncModeSections = () => {
                const selectedMode = document.querySelector('input[name="type"]:checked')?.value;
                cabinet.classList.toggle('hidden', selectedMode !== 'cabinet');
                address.classList.toggle('hidden', !['domicile', 'entreprise'].includes(selectedMode));
            };

            const syncProductModes = () => {
                const option = product?.selectedOptions?.[0];
                if (!option) return;

                const capabilities = {
                    cabinet: option.dataset.cabinet === '1',
                    visio: option.dataset.visio === '1',
                    domicile: option.dataset.domicile === '1',
                    entreprise: option.dataset.entreprise === '1',
                };

                modeInputs.forEach((input) => {
                    const enabled = capabilities[input.value] || !Object.values(capabilities).some(Boolean);
                    input.disabled = !enabled;
                    input.closest('label')?.classList.toggle('opacity-40', !enabled);
                });

                const checked = modeInputs.find((input) => input.checked && !input.disabled);
                if (!checked) {
                    const firstEnabled = modeInputs.find((input) => !input.disabled);
                    if (firstEnabled) firstEnabled.checked = true;
                }

                syncModeSections();
            };

            product?.addEventListener('change', syncProductModes);
            modeInputs.forEach((input) => input.addEventListener('change', syncModeSections));
            syncProductModes();
        });
    </script>
</x-mobile-layout>

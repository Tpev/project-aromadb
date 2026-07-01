@php
    $fieldValue = fn (string $field, mixed $default = null) => old($field, $event->{$field} ?? $default);
    $boolValue = fn (string $field, bool $default = false) => (bool) old($field, (bool) ($event->{$field} ?? $default));
    $selectedType = old('event_type', $event->event_type ?? 'in_person');
    $selectedProvider = old('visio_provider', $event->visio_provider ?? 'external');
    $startValue = old('start_date_time');

    if ($startValue === null && $event->start_date_time) {
        $startValue = \Carbon\Carbon::parse($event->start_date_time)->format('Y-m-d\TH:i');
    }

    $stripeConnected = ! empty(auth()->user()->stripe_account_id);
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <a href="{{ $event->exists ? route('mobile.events.show', $event) : route('mobile.events.index') }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Evenements
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Les reglages essentiels pour publier un atelier ou une visio.
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
                <h2 class="text-sm font-semibold text-gray-900">Informations</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Nom</span>
                        <input type="text"
                               name="name"
                               value="{{ $fieldValue('name') }}"
                               required
                               placeholder="Atelier decouverte"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Description</span>
                        <textarea name="description"
                                  rows="4"
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ $fieldValue('description') }}</textarea>
                    </label>

                    <div class="grid grid-cols-[1fr_100px] gap-3">
                        <label class="block min-w-0">
                            <span class="text-sm font-medium text-gray-700">Debut</span>
                            <input type="datetime-local"
                                   name="start_date_time"
                                   value="{{ $startValue }}"
                                   required
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Duree</span>
                            <input type="number"
                                   name="duration"
                                   value="{{ $fieldValue('duration', 60) }}"
                                   min="1"
                                   required
                                   inputmode="numeric"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Format</h2>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    @foreach([
                        'in_person' => ['label' => 'Lieu', 'icon' => 'fa-map-marker-alt'],
                        'visio' => ['label' => 'Visio', 'icon' => 'fa-video'],
                    ] as $value => $option)
                        <label class="flex h-12 items-center gap-2 rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 has-[:checked]:border-[#647a0b] has-[:checked]:bg-[#647a0b]/10 has-[:checked]:text-[#647a0b]">
                            <input type="radio"
                                   name="event_type"
                                   value="{{ $value }}"
                                   data-event-type
                                   class="h-4 w-4 shrink-0 border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ $selectedType === $value ? 'checked' : '' }}>
                            <span class="flex min-w-0 items-center gap-2">
                                <i class="fas {{ $option['icon'] }} shrink-0 text-xs"></i>
                                {{ $option['label'] }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Lieu</span>
                        <input type="text"
                               name="location"
                               value="{{ $fieldValue('location') }}"
                               placeholder="Cabinet, salle ou ville"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <div id="mobileEventVisioOptions" class="space-y-3 rounded-lg bg-[#f7f8f1] p-3">
                        <div class="grid grid-cols-2 gap-2">
                            @foreach([
                                'external' => 'Lien externe',
                                'aromamade' => 'AromaMade',
                            ] as $value => $label)
                                <label class="flex min-h-11 items-center gap-2 rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700 has-[:checked]:border-[#647a0b] has-[:checked]:text-[#647a0b]">
                                    <input type="radio"
                                           name="visio_provider"
                                           value="{{ $value }}"
                                           data-visio-provider
                                           class="h-4 w-4 border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                           {{ $selectedProvider === $value ? 'checked' : '' }}>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>

                        <label id="mobileEventVisioUrlWrap" class="block">
                            <span class="text-sm font-medium text-gray-700">Lien visio</span>
                            <input type="url"
                                   name="visio_url"
                                   value="{{ $fieldValue('visio_url') }}"
                                   inputmode="url"
                                   placeholder="https://..."
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Reservations</h2>

                <div class="mt-3 divide-y divide-gray-100">
                    <label class="flex items-center justify-between gap-4 py-3">
                        <span class="text-sm font-medium text-gray-700">Reservation requise</span>
                        <span class="flex gap-2">
                            @foreach(['1' => 'Oui', '0' => 'Non'] as $value => $label)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-700">
                                    <input type="radio"
                                           name="booking_required"
                                           value="{{ $value }}"
                                           class="h-4 w-4 border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                           {{ (string) old('booking_required', (int) ($event->booking_required ?? true)) === (string) $value ? 'checked' : '' }}>
                                    {{ $label }}
                                </span>
                            @endforeach
                        </span>
                    </label>

                    <label class="flex items-center justify-between gap-4 py-3">
                        <span class="text-sm font-medium text-gray-700">Places limitees</span>
                        <span class="flex gap-2">
                            @foreach(['1' => 'Oui', '0' => 'Non'] as $value => $label)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-700">
                                    <input type="radio"
                                           name="limited_spot"
                                           value="{{ $value }}"
                                           data-limited-spot
                                           class="h-4 w-4 border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                           {{ (string) old('limited_spot', (int) ($event->limited_spot ?? false)) === (string) $value ? 'checked' : '' }}>
                                    {{ $label }}
                                </span>
                            @endforeach
                        </span>
                    </label>
                </div>

                <label id="mobileEventSpotWrap" class="mt-3 block">
                    <span class="text-sm font-medium text-gray-700">Nombre de places</span>
                    <input type="number"
                           name="number_of_spot"
                           value="{{ $fieldValue('number_of_spot') }}"
                           min="1"
                           inputmode="numeric"
                           class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                </label>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Publication</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Prestation associee</span>
                        <select name="associated_product"
                                class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            <option value="">Aucune</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ (string) old('associated_product', $event->associated_product) === (string) $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="flex items-center justify-between gap-4 rounded-lg bg-[#f7f8f1] px-3 py-3">
                        <span class="text-sm font-medium text-gray-700">Afficher sur le portail</span>
                        <span>
                            <input type="hidden" name="showOnPortail" value="0">
                            <input type="checkbox"
                                   name="showOnPortail"
                                   value="1"
                                   class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ $boolValue('showOnPortail', true) ? 'checked' : '' }}>
                        </span>
                    </label>

                    @if($method === 'POST')
                        <label class="flex items-center justify-between gap-4 rounded-lg bg-[#f7f8f1] px-3 py-3">
                            <span class="text-sm font-medium text-gray-700">Bloquer mon agenda</span>
                            <span>
                                <input type="hidden" name="block_calendar" value="0">
                                <input type="checkbox"
                                       name="block_calendar"
                                       value="1"
                                       class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                       {{ old('block_calendar') ? 'checked' : '' }}>
                            </span>
                        </label>
                    @endif
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Paiement</h2>

                @unless($stripeConnected)
                    <p class="mt-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs leading-snug text-amber-800">
                        Connectez Stripe depuis la vue web pour activer le paiement des reservations.
                    </p>
                @endunless

                <label class="mt-3 flex items-center justify-between gap-4 rounded-lg bg-[#f7f8f1] px-3 py-3">
                    <span class="text-sm font-medium text-gray-700">Paiement obligatoire</span>
                    <span>
                        <input type="hidden" name="collect_payment" value="0">
                        <input type="checkbox"
                               name="collect_payment"
                               value="1"
                               data-collect-payment
                               class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                               {{ $boolValue('collect_payment') ? 'checked' : '' }}
                               {{ $stripeConnected ? '' : 'disabled' }}>
                    </span>
                </label>

                <div id="mobileEventPaymentFields" class="mt-3 grid grid-cols-2 gap-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Prix TTC</span>
                        <input type="number"
                               name="price"
                               value="{{ $fieldValue('price') }}"
                               min="0"
                               step="0.01"
                               inputmode="decimal"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">TVA %</span>
                        <input type="number"
                               name="tax_rate"
                               value="{{ $fieldValue('tax_rate', 0) }}"
                               min="0"
                               max="100"
                               step="0.01"
                               inputmode="decimal"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ $event->exists ? route('mobile.events.show', $event) : route('mobile.events.index') }}"
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

    <script>
        function checkedValue(selector) {
            const checked = document.querySelector(`${selector}:checked`);
            return checked ? checked.value : null;
        }

        function syncMobileEventForm() {
            const eventType = checkedValue('[data-event-type]');
            const provider = checkedValue('[data-visio-provider]');
            const limitedSpot = checkedValue('[data-limited-spot]') === '1';
            const collectPayment = document.querySelector('[data-collect-payment]')?.checked;

            document.getElementById('mobileEventVisioOptions')?.classList.toggle('hidden', eventType !== 'visio');
            document.getElementById('mobileEventVisioUrlWrap')?.classList.toggle('hidden', eventType !== 'visio' || provider !== 'external');
            document.getElementById('mobileEventSpotWrap')?.classList.toggle('hidden', !limitedSpot);
            document.getElementById('mobileEventPaymentFields')?.classList.toggle('hidden', !collectPayment);
        }

        document.querySelectorAll('[data-event-type], [data-visio-provider], [data-limited-spot], [data-collect-payment]').forEach((input) => {
            input.addEventListener('change', syncMobileEventForm);
        });

        syncMobileEventForm();
    </script>
</x-mobile-layout>

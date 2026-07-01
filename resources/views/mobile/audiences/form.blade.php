@php
    $fieldValue = fn (string $field, mixed $default = null) => old($field, $audience->{$field} ?? $default);
    $selectedIds = collect(old('client_ids', $selectedClientIds ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <a href="{{ $audience->exists ? route('mobile.audiences.show', $audience) : route('mobile.audiences.index') }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Audiences
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Selectionnez les clients qui recevront les campagnes de cette liste.
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
                        <span class="text-sm font-medium text-gray-700">Nom de l audience</span>
                        <input type="text"
                               name="name"
                               value="{{ $fieldValue('name') }}"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Description</span>
                        <textarea name="description"
                                  rows="3"
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ $fieldValue('description') }}</textarea>
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold text-gray-900">Contacts</h2>
                        <p class="mt-1 text-xs leading-snug text-gray-500">
                            <span id="selectedAudienceClientsCount">{{ count($selectedIds) }}</span> selectionne(s).
                        </p>
                    </div>

                    @if($clients->isNotEmpty())
                        <button type="button"
                                onclick="toggleAudienceClients(true)"
                                class="inline-flex h-9 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700">
                            Tout
                        </button>
                    @endif
                </div>

                @if($clients->isEmpty())
                    <div class="mt-3 rounded-lg border border-dashed border-[#d7ddc6] bg-[#fbfcf7] p-4 text-center">
                        <h3 class="text-sm font-semibold text-gray-900">Aucun client</h3>
                        <p class="mt-1 text-sm leading-snug text-gray-600">
                            Creez une fiche client avant de remplir cette audience.
                        </p>
                        <a href="{{ route('mobile.clients.create') }}"
                           class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg bg-[#647a0b] text-xs font-semibold text-white">
                            Creer un client
                        </a>
                    </div>
                @else
                    <label class="mt-3 flex h-10 items-center gap-2 rounded-lg bg-[#f7f8f1] px-3">
                        <i class="fas fa-search text-[11px] text-gray-400"></i>
                        <input type="search"
                               id="mobileAudienceClientSearch"
                               placeholder="Rechercher un client"
                               class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:ring-0"
                               oninput="filterAudienceClients()">
                    </label>

                    <div id="mobileAudienceClientList" class="mt-3 max-h-[440px] space-y-2 overflow-y-auto pr-1">
                        @foreach($clients as $client)
                            @php
                                $clientName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
                                $searchText = trim($clientName . ' ' . ($client->email ?? '') . ' ' . ($client->phone ?? ''));
                                $checked = in_array((int) $client->id, $selectedIds, true);
                            @endphp

                            <label class="flex items-center gap-3 rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3 active:scale-[0.99]"
                                   data-audience-client="{{ Str::lower($searchText) }}">
                                <input type="checkbox"
                                       name="client_ids[]"
                                       value="{{ $client->id }}"
                                       class="h-5 w-5 shrink-0 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                       onchange="updateAudienceClientCount()"
                                       {{ $checked ? 'checked' : '' }}>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-gray-900">
                                        {{ $clientName ?: 'Client #' . $client->id }}
                                    </span>
                                    <span class="mt-0.5 block truncate text-xs text-gray-600">
                                        {{ $client->email ?: ($client->phone ?: 'Coordonnees manquantes') }}
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ $audience->exists ? route('mobile.audiences.show', $audience) : route('mobile.audiences.index') }}"
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
        function audienceClientCheckboxes() {
            return document.querySelectorAll('#mobileAudienceClientList input[type="checkbox"]');
        }

        function updateAudienceClientCount() {
            const selected = Array.from(audienceClientCheckboxes()).filter((input) => input.checked).length;
            const count = document.getElementById('selectedAudienceClientsCount');

            if (count) {
                count.textContent = selected;
            }
        }

        function toggleAudienceClients(checked) {
            audienceClientCheckboxes().forEach((input) => {
                input.checked = checked;
            });

            updateAudienceClientCount();
        }

        function filterAudienceClients() {
            const input = document.getElementById('mobileAudienceClientSearch');
            const filter = input ? input.value.toLowerCase() : '';
            const items = document.querySelectorAll('#mobileAudienceClientList > label');

            items.forEach((item) => {
                const text = item.getAttribute('data-audience-client') || '';
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>

{{-- resources/views/mobile/clients/index.blade.php --}}
<x-mobile-layout :title="__('Mes clients')">
    <div class="px-4 pt-4 pb-20 space-y-4">

        {{-- Header + search --}}
        <div class="flex items-center justify-between gap-2 mb-1">
            <div>
                <h1 class="text-lg font-semibold text-[#647a0b]">
                    {{ __('Mes clients') }}
                </h1>
                <p class="text-[11px] text-gray-500 mt-0.5">
                    {{ __('Gérez vos fiches clients depuis votre mobile.') }}
                </p>
            </div>

            <a href="{{ route('client_profiles.create') }}"
               class="inline-flex items-center justify-center rounded-full bg-[#647a0b] text-white text-xs px-3 py-1.5 active:scale-[0.97]">
                <i class="fas fa-user-plus text-[11px] mr-1.5"></i>
                {{ __('Nouveau') }}
            </a>
        </div>

        <div class="rounded-2xl border border-[#e4e8d5] bg-white p-3 shadow-sm">
            <div class="flex items-center gap-2 px-2 py-1.5 rounded-xl bg-[#f5f7eb]">
                <i class="fas fa-search text-[11px] text-gray-400"></i>
                <input
                    type="text"
                    id="mobileClientSearch"
                    placeholder="{{ __('Rechercher par nom…') }}"
                    class="w-full bg-transparent text-[13px] focus:outline-none text-gray-800"
                    oninput="filterMobileClients()"
                />
            </div>
        </div>

        {{-- List --}}
        @if($clientProfiles->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#e4e8d5] bg-white p-6 text-center text-sm text-gray-500">
                {{ __('Vous n’avez pas encore de clients enregistrés.') }}<br>
                <span class="text-xs text-gray-400">
                    {{ __('Créez votre première fiche client pour commencer.') }}
                </span>
            </div>
        @else
            <div id="mobileClientList" class="space-y-3">
                @foreach($clientProfiles as $client)
                    @php
                        $fullName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
                        $initials = strtoupper(mb_substr($client->first_name ?? '', 0, 1) . mb_substr($client->last_name ?? '', 0, 1));
                        $companyTag = (!empty($client->company_id) && $client->company) ? $client->company->name : null;
                    @endphp

                    <a href="{{ route('mobile.clients.show', $client->id) }}"
                       class="block rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm active:scale-[0.99] transition transform"
                       data-name="{{ Str::lower($fullName) }}">
                        <div class="flex items-start gap-3">
                            {{-- Avatar --}}
                            <div class="w-10 h-10 rounded-full bg-[#647a0b]/10 flex items-center justify-center text-[13px] font-semibold text-[#647a0b]">
                                {{ $initials ?: 'C' }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900 truncate">
                                        {{ $fullName ?: __('Client sans nom') }}
                                    </p>

                                    @if($companyTag)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#647a0b]/10 text-[#647a0b] whitespace-nowrap">
                                            👔 {{ __('Entreprise') }}
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-1 text-[11px] text-gray-500 flex items-center gap-1.5">
                                    <i class="fas fa-envelope text-[10px]"></i>
                                    <span class="truncate">
                                        {{ $client->email ?: __('Email non renseigné') }}
                                    </span>
                                </p>

                                <p class="mt-0.5 text-[11px] text-gray-500 flex items-center gap-1.5">
                                    <i class="fas fa-phone text-[10px]"></i>
                                    <span>
                                        {{ $client->phone ?: __('Téléphone non renseigné') }}
                                    </span>
                                </p>
                            </div>

                            <i class="fas fa-chevron-right text-[10px] text-gray-300 mt-1"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function filterMobileClients() {
            const input = document.getElementById('mobileClientSearch');
            const filter = input.value.toLowerCase();
            const items  = document.querySelectorAll('#mobileClientList > a');

            items.forEach(el => {
                const name = el.getAttribute('data-name') || '';
                el.style.display = name.indexOf(filter) > -1 ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>

{{-- resources/views/client_profiles/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Liste des Profils Clients') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Titre de la Page -->
            <h1 class="text-3xl font-bold text-[#647a0b] text-center">
                {{ __('Liste des Profils Clients') }}
            </h1>

@php
    $user = auth()->user();
    $canCreateClient = $user->canUseFeature('client_profiles');

    // Determine the minimum license family that includes this feature
    $plansConfig = config('license_features.plans', []);

    // We IGNORE 'trial' here on purpose (trial is just a temporary access)
    $familyOrder = ['free', 'starter', 'pro', 'premium'];

    $requiredFamily = null;

    foreach ($familyOrder as $family) {
        if (in_array('client_profiles', $plansConfig[$family] ?? [], true)) {
            $requiredFamily = $family;
            break;
        }
    }

    // Human-readable labels
    $familyLabels = [
        'free'    => __('l’offre Gratuite'),
        'starter' => __('Starter'),
        'pro'     => __('PRO'),
        'premium' => __('Premium'),
    ];

    $requiredLabel = $requiredFamily
        ? ($familyLabels[$requiredFamily] ?? $requiredFamily)
        : __('une formule supérieure');

    $clientSortUrl = function (string $column) use ($clientSort, $clientSortDirection): string {
        $nextDirection = $clientSort === $column && $clientSortDirection === 'asc' ? 'desc' : 'asc';

        return request()->fullUrlWithQuery([
            'sort' => $column,
            'direction' => $nextDirection,
        ]);
    };

    $clientSortIcon = function (string $column) use ($clientSort, $clientSortDirection): string {
        if ($clientSort !== $column) {
            return 'fa-sort';
        }

        return $clientSortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
    };
@endphp

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">

    {{-- Search Bar --}}
    <div class="w-full md:max-w-sm relative">
        <input
            id="search"
            type="text"
            placeholder="{{ __('Rechercher un client...') }}"
            onkeyup="filterTable()"
            class="w-full rounded-xl border border-[#854f38]/40 bg-white px-4 py-2.5 text-sm shadow-sm 
                   focus:outline-none focus:ring-2 focus:ring-[#854f38] transition" />

        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-5 w-5 text-[#854f38]/70 absolute right-3 top-1/2 -translate-y-1/2"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1110.5 3a7.5 7.5 0 016.15 12.65z" />
        </svg>
    </div>

    {{-- Right side: button + small tag-like pill when locked --}}
    <div class="w-full md:w-auto flex justify-start md:justify-end">
        <div class="relative inline-flex w-full md:w-auto">

            @if($canCreateClient)
                {{-- ✅ Normal green action button --}}
                <a href="{{ route('client_profiles.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#647a0b] px-5 py-2.5 text-white text-sm font-semibold
                          shadow-sm hover:bg-[#586f09] transition w-full md:w-auto">

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor"
                         viewBox="0 0 20 20">
                        <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" />
                    </svg>

                    {{ __('Créer un profil client') }}
                </a>
            @else
                {{-- ❌ Locked: greyed-out button that sends to pricing --}}
                <a href="/license-tiers/pricing"
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-gray-200 text-gray-500 px-5 py-2.5 text-sm font-semibold
                          border border-gray-300 shadow-sm transition w-full md:w-auto hover:bg-gray-300">

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="currentColor"
                         viewBox="0 0 20 20">
                        <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" />
                    </svg>

                    {{ __('Créer un profil client') }}
                </a>

                {{-- 🔖 Small pill slightly above the top-right corner (minimal overlap) --}}
                <div class="absolute -top-3 right-0 translate-x-2 inline-flex items-center gap-1 rounded-full 
                            bg-[#fff1d6] border border-[#facc15]/40 px-2.5 py-0.5 
                            text-[10px] font-semibold text-[#854f38] shadow-sm">

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="currentColor"
                         viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 2a4 4 0 00-4 4v2H5a2 2 0 
                                 00-2 2v6a2 2 0 002 2h10a2 2 0 
                                 002-2v-6a2 2 0 00-2-2h-1V6a4 4 
                                 0 00-4-4zm0 6a2 2 0 00-2 2v2a2 2 0 104 0v-2a2 2 0 00-2-2z"
                              clip-rule="evenodd" />
                    </svg>

                    <span>
                        {{ __('Disponible à partir de :') }}
                        <span class="font-bold">{{ $requiredLabel }}</span>
                    </span>
                </div>
            @endif

        </div>
    </div>
</div>

            @if($clientTagsEnabled)
                <form method="POST" action="{{ route('offer-journeys.client-tags.bulk') }}" class="space-y-3">
                    @csrf
                    <div class="flex flex-col gap-3 rounded-lg border border-[#dfe6c7] bg-[#f7f9ec] p-4 md:flex-row md:items-end">
                        <div class="flex-1">
                            <label for="bulk-tag" class="block text-sm font-semibold text-gray-900">Étiquette interne</label>
                            <select id="bulk-tag" name="tag_id" required class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                                <option value="">Choisir une étiquette</option>
                                @foreach($marketingTags as $tag)<option value="{{ $tag->id }}">{{ $tag->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label for="bulk-tag-action" class="block text-sm font-semibold text-gray-900">Action</label>
                            <select id="bulk-tag-action" name="action" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                <option value="attach">Ajouter</option>
                                <option value="detach">Retirer</option>
                            </select>
                        </div>
                        <button class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#526509]" @disabled($marketingTags->isEmpty())>Appliquer aux fiches cochées</button>
                        <a href="{{ route('offer-journeys.contacts.segments') }}" class="text-sm font-semibold text-[#647a0b]">Gérer les étiquettes</a>
                    </div>
                    <p class="text-xs text-gray-500">Repères internes uniquement. N’utilisez pas d’informations médicales ou sensibles. Une étiquette ne constitue jamais un consentement marketing.</p>
            @endif

            <!-- Tableau -->
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="clientTable">
                        <thead class="bg-[#647a0b] text-white">
                            <tr>
                                @if($clientTagsEnabled)<th class="w-10 px-3 py-3"><span class="sr-only">Sélectionner</span></th>@endif
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    aria-sort="{{ $clientSort === 'name' ? ($clientSortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                    <a href="{{ $clientSortUrl('name') }}" class="inline-flex items-center gap-2 text-white hover:text-white focus:outline-none focus:ring-2 focus:ring-white/70">
                                        {{ __('Nom du Client') }}
                                        <i class="fas {{ $clientSortIcon('name') }}" aria-hidden="true"></i>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    aria-sort="{{ $clientSort === 'email' ? ($clientSortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                    <a href="{{ $clientSortUrl('email') }}" class="inline-flex items-center gap-2 text-white hover:text-white focus:outline-none focus:ring-2 focus:ring-white/70">
                                        {{ __('Email') }}
                                        <i class="fas {{ $clientSortIcon('email') }}" aria-hidden="true"></i>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    aria-sort="{{ $clientSort === 'phone' ? ($clientSortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                    <a href="{{ $clientSortUrl('phone') }}" class="inline-flex items-center gap-2 text-white hover:text-white focus:outline-none focus:ring-2 focus:ring-white/70">
                                        {{ __('Téléphone') }}
                                        <i class="fas {{ $clientSortIcon('phone') }}" aria-hidden="true"></i>
                                    </a>
                                </th>
                                @if($clientTagsEnabled)<th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Étiquettes</th>@endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($clientProfiles as $clientProfile)
                                <tr class="hover:bg-gray-100 cursor-pointer" onclick="window.location='{{ route('client_profiles.show', $clientProfile->id) }}'">
                                    @if($clientTagsEnabled)<td class="px-3 py-4" onclick="event.stopPropagation()"><input type="checkbox" name="client_ids[]" value="{{ $clientProfile->id }}" class="rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"></td>@endif
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span>
                                                {{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
                                            </span>

                                            @if(!empty($clientProfile->company_id) && $clientProfile->company)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#647a0b]/10 text-[#647a0b]">
                                                    👔 {{ __('Entreprise') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $clientProfile->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $clientProfile->phone }}
                                    </td>
                                    @if($clientTagsEnabled)
                                        <td class="px-6 py-4"><div class="flex max-w-xs flex-wrap gap-1">@forelse($clientProfile->marketingTags as $tag)<span class="rounded-full bg-[#f0f4df] px-2 py-0.5 text-xs font-medium text-[#526509]">{{ $tag->name }}</span>@empty<span class="text-xs text-gray-400">Aucune</span>@endforelse</div></td>
                                    @endif
                                </tr>
                            @endforeach
                            @if($clientProfiles->isEmpty())
                                <tr>
                                    <td colspan="{{ $clientTagsEnabled ? 5 : 3 }}" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ __('Aucun profil client trouvé.') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            @if($clientTagsEnabled)</form>@endif
        </div>
    </div>

    <!-- JavaScript pour le filtrage -->
    <script>
        function filterTable() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('clientTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td')[{{ $clientTagsEnabled ? 1 : 0 }}];
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }

    </script>

    <!-- Styles Personnalisés -->
    <style>
        /* Ajustements pour les petits écrans */
        @media (max-width: 640px) {
            .md\:flex-row {
                flex-direction: column;
            }
            .md\:justify-between {
                justify-content: flex-start;
            }
            .md\:space-y-0 > :not([hidden]) ~ :not([hidden]) {
                --tw-space-y-reverse: 0;
                margin-top: 1rem;
            }
            .md\:w-auto {
                width: 100%;
            }
        }
    </style>
</x-app-layout>

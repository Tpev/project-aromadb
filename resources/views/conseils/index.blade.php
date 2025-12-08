{{-- resources/views/conseils/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Liste des Conseils') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Titre de la Page -->
            <h1 class="text-3xl font-bold text-[#647a0b] text-center">
                {{ __('Liste des Conseils') }}
            </h1>

@php
    $user = auth()->user();
    $canCreateConseil = $user->canUseFeature('conseil');

    // Determine the minimum license needed
    $plansConfig = config('license_features.plans', []);
    $familyOrder = ['free', 'starter', 'pro', 'premium']; // ignore trial tier

    $requiredFamily = null;
    foreach ($familyOrder as $family) {
        if (in_array('conseil', $plansConfig[$family] ?? [], true)) {
            $requiredFamily = $family;
            break;
        }
    }

    $familyLabels = [
        'free'    => __('Gratuit'),
        'starter' => __('Starter'),
        'pro'     => __('PRO'),
        'premium' => __('Premium'),
    ];

    $requiredLabel = $requiredFamily
        ? ($familyLabels[$requiredFamily] ?? ucfirst($requiredFamily))
        : __('une formule supérieure');
@endphp


<!-- Barre de Recherche et Bouton de Création -->
<div class="flex flex-col md:flex-row md:justify-between items-center mb-4 space-y-4 md:space-y-0">

    <!-- Barre de Recherche -->
    <div class="w-full md:w-auto">
        <input
            type="text"
            id="search"
            class="border border-[#854f38] rounded-md py-2 px-4 w-full md:w-80 
                   focus:outline-none focus:ring-2 focus:ring-[#854f38]"
            placeholder="{{ __('Recherche par nom de conseil...') }}"
            onkeyup="filterTable()"
        >
    </div>

    <!-- Bouton Créer un Conseil -->
    <div class="relative inline-flex w-full md:w-auto">

        @if($canCreateConseil)
            {{-- Normal working button --}}
            <a href="{{ route('conseils.create') }}"
               class="bg-[#647a0b] text-white px-4 py-2 rounded-md 
                      hover:bg-[#854f38] transition duration-200 
                      flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-5 w-5 mr-2" fill="currentColor"
                     viewBox="0 0 20 20">
                    <path d="M10 5a1 1 0 011 1v3h3a1 1 0 
                             110 2h-3v3a1 1 0 
                             11-2 0v-3H6a1 1 0 
                             110-2h3V6a1 1 0 
                             011-1z" />
                </svg>
                {{ __('Créer un conseil') }}
            </a>

        @else
            {{-- Greyed-out button with redirect --}}
            <a href="/license-tiers/pricing"
               class="px-4 py-2 rounded-md flex items-center justify-center
                      bg-gray-200 text-gray-600 border border-gray-300
                      cursor-pointer transition duration-200
                      hover:bg-gray-300"
               style="white-space: nowrap;">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-5 w-5 mr-2" fill="currentColor"
                     viewBox="0 0 20 20">
                    <path d="M10 5a1 1 0 011 1v3h3a1 1 0 
                             110 2h-3v3a1 1 0 
                             11-2 0v-3H6a1 1 0 
                             110-2h3V6a1 1 0 
                             011-1z" />
                </svg>
                {{ __('Créer un conseil') }}
            </a>

            {{-- Floating pill --}}
            <div class="absolute -top-3 -right-2 
                        bg-[#fff1d6] border border-[#facc15]/40
                        rounded-full px-2.5 py-0.5 text-[10px] 
                        font-semibold text-[#854f38] shadow-sm 
                        flex items-center gap-1">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-3 w-3" fill="currentColor"
                     viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M10 2a4 4 0 
                             00-4 4v2H5a2 2 0 
                             00-2 2v6a2 2 0 
                             002 2h10a2 2 0 
                             002-2v-6a2 2 0 
                             00-2-2h-1V6a4 4 
                             0 00-4-4zm0 6a2 2 
                             0 00-2 2v2a2 2 
                             0 104 0v-2a2 2 
                             0 00-2-2z"
                          clip-rule="evenodd" />
                </svg>

                <span>{{ __('À partir de :') }} <strong>{{ $requiredLabel }}</strong></span>
            </div>
        @endif

    </div>
</div>


            <!-- Tableau -->
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="conseilTable">
                        <thead class="bg-[#647a0b] text-white">
                            <tr>
                                <th onclick="sortTable(0)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Nom du Conseil') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                                <th onclick="sortTable(1)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Tag') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                                <th onclick="sortTable(2)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Date de Création') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($conseils as $conseil)
                                <tr class="hover:bg-gray-100 cursor-pointer" onclick="window.location='{{ route('conseils.show', $conseil->id) }}'">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $conseil->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $conseil->tag ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $conseil->created_at->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @endforeach
                            @if($conseils->isEmpty())
                                <tr>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ __('Aucun conseil trouvé.') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript pour le tri et le filtrage -->
    <script>
        function filterTable() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('conseilTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td')[0]; // Recherche dans la colonne Nom du Conseil
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }

        function sortTable(n) {
            let table = document.getElementById('conseilTable');
            let rows = table.rows;
            let switching = true;
            let shouldSwitch;
            let dir = "asc";
            let switchcount = 0;

            while (switching) {
                switching = false;
                for (let i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    let x = rows[i].getElementsByTagName('td')[n];
                    let y = rows[i + 1].getElementsByTagName('td')[n];

                    let xContent = x.textContent || x.innerText;
                    let yContent = y.textContent || y.innerText;

                    if (dir === "asc") {
                        if (xContent.toLowerCase() > yContent.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir === "desc") {
                        if (xContent.toLowerCase() < yContent.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount === 0 && dir === "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }

            // Mettre à jour les icônes de tri
            let ths = table.getElementsByTagName('th');
            for (let i = 0; i < ths.length; i++) {
                let icon = ths[i].getElementsByTagName('svg')[0];
                if (i === n) {
                    icon.classList.toggle('rotate-180', dir === 'desc');
                } else {
                    icon.classList.remove('rotate-180');
                }
            }
        }
    </script>

    <!-- Styles Personnalisés -->
    <style>
        /* Styles pour les icônes de tri */
        .rotate-180 {
            transform: rotate(180deg);
        }

        /* Ajustements pour les petits écrans */
        @media (max-width: 640px) {
            .md\:flex-row {
                flex-direction: column;
            }
            .md\:justify-between {
                justify-content: flex-start;
            }
            .md\:space-y-0 > :not([hidden]) ~ :not([hidden]) {
                margin-top: 1rem;
            }
            .md\:w-auto {
                width: 100%;
            }
        }
    </style>
</x-app-layout>

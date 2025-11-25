{{-- resources/views/corporate_clients/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Liste des Entreprises Clientes') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Titre de la Page -->
            <h1 class="text-3xl font-bold text-[#647a0b] text-center">
                {{ __('Liste des Entreprises Clientes') }}
            </h1>

            <!-- Barre de Recherche et Bouton de Création -->
            <div class="flex flex-col md:flex-row md:justify-between items-center mb-4 space-y-4 md:space-y-0">
                <!-- Barre de Recherche -->
                <div class="w-full md:w-auto">
                    <input
                        type="text"
                        id="search"
                        class="border border-[#854f38] rounded-md py-2 px-4 w-full md:w-80 focus:outline-none focus:ring-2 focus:ring-[#854f38]"
                        placeholder="{{ __('Recherche par nom d\'entreprise...') }}"
                        onkeyup="filterTable()"
                    >
                </div>

                <!-- Bouton Créer une Entreprise Cliente -->
                <a href="{{ route('corporate-clients.create') }}"
                   class="bg-[#647a0b] text-white px-4 py-2 rounded-md hover:bg-[#854f38] transition duration-200 flex items-center justify-center">
                    <!-- Icône Plus -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" />
                    </svg>
                    {{ __('Créer une entreprise cliente') }}
                </a>
            </div>

            <!-- Tableau -->
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="corporateClientTable">
                        <thead class="bg-[#647a0b] text-white">
                            <tr>
                                <th onclick="sortTable(0)"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Nom de l\'entreprise') }}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-4 w-4 inline-block ml-1 transform"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                                <th onclick="sortTable(1)"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('SIRET') }}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-4 w-4 inline-block ml-1 transform"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                                <th onclick="sortTable(2)"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Ville') }}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-4 w-4 inline-block ml-1 transform"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                                <th onclick="sortTable(3)"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Contact principal') }}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-4 w-4 inline-block ml-1 transform"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($companies as $company)
                                <tr class="hover:bg-gray-100 cursor-pointer"
                                    onclick="window.location='{{ route('corporate-clients.show', $company) }}'">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $company->name }}
                                        @if($company->trade_name)
                                            <span class="text-gray-500 text-xs block">
                                                ({{ $company->trade_name }})
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $company->siret ?: '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $company->billing_city ?: '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($company->main_contact_first_name || $company->main_contact_last_name)
                                            {{ $company->main_contact_first_name }} {{ $company->main_contact_last_name }}
                                        @else
                                            <span class="text-gray-500 text-xs">
                                                {{ __('Non défini') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            @if($companies->isEmpty())
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ __('Aucune entreprise cliente trouvée.') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(method_exists($companies, 'links'))
                    <div class="px-4 py-3 bg-white border-t border-gray-200">
                        {{ $companies->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- JavaScript pour le tri et le filtrage -->
    <script>
        function filterTable() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('corporateClientTable');
            let tr = table.getElementsByTagName('tr');

            // i = 1 pour ignorer l'en-tête
            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td')[0]; // Nom de l'entreprise
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }

        function sortTable(n) {
            let table = document.getElementById('corporateClientTable');
            let rows = table.rows;
            let switching = true;
            let dir = "asc";
            let switchcount = 0;

            while (switching) {
                switching = false;
                for (let i = 1; i < (rows.length - 1); i++) {
                    let shouldSwitch = false;
                    let x = rows[i].getElementsByTagName('td')[n];
                    let y = rows[i + 1].getElementsByTagName('td')[n];

                    if (!x || !y) continue;

                    let xContent = (x.textContent || x.innerText).toLowerCase();
                    let yContent = (y.textContent || y.innerText).toLowerCase();

                    if (dir === "asc") {
                        if (xContent > yContent) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir === "desc") {
                        if (xContent < yContent) {
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
                if (!icon) continue;

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
        .rotate-180 {
            transform: rotate(180deg);
        }

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

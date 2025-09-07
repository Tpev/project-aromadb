<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Vos Disponibilités') }}
        </h2>
    </x-slot>

    <!-- Font Awesome (optionnel) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <h1 class="text-3xl font-bold text-[#647a0b] text-center">
                {{ __('Liste des Disponibilités') }}
            </h1>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Recherche + Boutons -->
            <div class="flex flex-col md:flex-row md:justify-between items-center mb-4 space-y-4 md:space-y-0">
                <div class="w-full md:w-auto">
                    <input type="text" id="search" class="border border-[#854f38] rounded-md py-2 px-4 w-full md:w-96 focus:outline-none focus:ring-2 focus:ring-[#854f38]" placeholder="{{ __('Recherche par jour, prestation ou lieu...') }}" onkeyup="filterTable()">
                </div>

                <div class="flex flex-col sm:flex-row sm:space-x-4 w-full md:w-auto space-y-4 sm:space-y-0">
                    <a href="{{ route('availabilities.create') }}" class="bg-[#647a0b] text-white px-4 py-2 rounded-md hover:bg-[#854f38] transition duration-200 flex items-center justify-center">
                        <i class="fas fa-plus mr-2"></i> {{ __('Ajouter une Disponibilité') }}
                    </a>
                    <a href="{{ route('unavailabilities.create') }}" class="bg-[#854f38] text-white px-4 py-2 rounded-md hover:bg-[#6a3f2c] transition duration-200 flex items-center justify-center">
                        <i class="fas fa-plus mr-2"></i> {{ __('Ajouter une Indisponibilité temporaire') }}
                    </a>
                </div>
            </div>

            <!-- Tableau -->
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="availabilityTable">
                        <thead class="bg-[#647a0b] text-white">
                            <tr>
                                <th onclick="sortTable(0)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer whitespace-nowrap">
                                    {{ __('Jour') }} <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th onclick="sortTable(1)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer whitespace-nowrap">
                                    {{ __('Début') }} <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th onclick="sortTable(2)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer whitespace-nowrap">
                                    {{ __('Fin') }} <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th onclick="sortTable(3)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer whitespace-nowrap">
                                    {{ __('Lieu') }} <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th onclick="sortTable(4)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer whitespace-nowrap">
                                    {{ __('Toutes les Prestations') }} <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    {{ __('Prestations Associées') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($availabilities as $availability)
                                <tr class="hover:bg-gray-100">
                                    <!-- Jour -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $daysOfWeek = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                                            echo $daysOfWeek[$availability->day_of_week];
                                        @endphp
                                    </td>

                                    <!-- Début -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $availability->start_time)->format('H:i') }}
                                    </td>

                                    <!-- Fin -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $availability->end_time)->format('H:i') }}
                                    </td>

                                    <!-- Lieu -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($availability->practiceLocation)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-[#f5f5f5] text-[#647a0b] border border-[#647a0b]">
                                                {{ $availability->practiceLocation->label }}
                                                @if($availability->practiceLocation->is_primary)
                                                    &nbsp;· {{ __('Principal') }}
                                                @endif
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $availability->practiceLocation->city }}
                                                @if($availability->practiceLocation->postal_code)
                                                    ({{ $availability->practiceLocation->postal_code }})
                                                @endif
                                            </div>
                                        @else
                                            <span class="bg-[#854f38] text-white px-2 py-1 rounded-full text-xs font-semibold">
                                                {{ __('Sans lieu') }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Applique à toutes -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($availability->applies_to_all)
                                            <span class="bg-[#647a0b] text-white px-2 py-1 rounded-full text-xs font-semibold">
                                                {{ __('Oui') }}
                                            </span>
                                        @else
                                            <span class="bg-[#854f38] text-white px-2 py-1 rounded-full text-xs font-semibold">
                                                {{ __('Non') }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Produits -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($availability->applies_to_all)
                                            <span class="bg-[#647a0b] text-white px-2 py-1 rounded-full text-xs font-semibold">
                                                {{ __('Toutes les Prestations') }}
                                            </span>
                                        @elseif($availability->products->isEmpty())
                                            <span class="bg-[#854f38] text-white px-2 py-1 rounded-full text-xs font-semibold">
                                                {{ __('Aucune Prestation associée') }}
                                            </span>
                                        @else
                                            <div class="flex flex-wrap">
                                                @foreach($availability->products as $product)
                                                    <span class="bg-[#647a0b] text-white px-2 py-1 rounded-full text-xs font-semibold inline-block mb-1 mr-1">
                                                        {{ $product->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('availabilities.edit', $availability->id) }}" class="text-white bg-blue-500 hover:bg-blue-600 px-3 py-2 rounded-md" title="{{ __('Éditer') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('availabilities.destroy', $availability->id) }}" method="POST" onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette disponibilité ?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-white bg-red-500 hover:bg-red-600 px-3 py-2 rounded-md" title="{{ __('Supprimer') }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            @if($availabilities->isEmpty())
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ __('Aucune disponibilité trouvée.') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <!-- Tri + Filtre -->
    <script>
        function filterTable() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('availabilityTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let tdDay = tr[i].getElementsByTagName('td')[0];
                let tdStart = tr[i].getElementsByTagName('td')[1];
                let tdEnd = tr[i].getElementsByTagName('td')[2];
                let tdLoc = tr[i].getElementsByTagName('td')[3];
                let tdProducts = tr[i].getElementsByTagName('td')[5];

                if (tdDay && tdProducts && tdLoc) {
                    let txtDay = (tdDay.textContent || tdDay.innerText).toLowerCase();
                    let txtLoc = (tdLoc.textContent || tdLoc.innerText).toLowerCase();
                    let txtProducts = (tdProducts.textContent || tdProducts.innerText).toLowerCase();

                    if (txtDay.indexOf(filter) > -1 || txtProducts.indexOf(filter) > -1 || txtLoc.indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function sortTable(n) {
            let table = document.getElementById('availabilityTable');
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

                    let xContent = x.textContent || x.innerText;
                    let yContent = y.textContent || y.innerText;

                    // Colonnes 1 et 2 = heures -> trier numériquement HHmm
                    if (n === 1 || n === 2) {
                        xContent = parseInt(xContent.replace(':','').trim() || '0', 10);
                        yContent = parseInt(yContent.replace(':','').trim() || '0', 10);
                    } else {
                        xContent = xContent.toLowerCase();
                        yContent = yContent.toLowerCase();
                    }

                    if (dir === "asc" && xContent > yContent) { shouldSwitch = true; }
                    if (dir === "desc" && xContent < yContent) { shouldSwitch = true; }

                    if (shouldSwitch) {
                        rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                        switching = true;
                        switchcount++;
                        break;
                    }
                }
                if (!switching && switchcount === 0) {
                    dir = (dir === "asc") ? "desc" : "asc";
                    switching = true;
                }
            }

            // Mettre à jour icônes
            let ths = table.getElementsByTagName('th');
            for (let i = 0; i < ths.length; i++) {
                let icon = ths[i].getElementsByTagName('i')[0];
                if (!icon) continue;
                icon.className = "fas fa-sort";
                if (i === n) {
                    icon.className = (dir === "asc") ? "fas fa-sort-up" : "fas fa-sort-down";
                }
            }
        }
    </script>

    <!-- Font Awesome JS (optionnel) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" defer></script>
</x-app-layout>

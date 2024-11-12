<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Vos Disponibilités') }}
        </h2>
    </x-slot>

    <!-- Inclure Font Awesome pour les icônes (optionnel) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Titre de la Page -->
            <h1 class="text-3xl font-bold text-[#647a0b] text-center">
                {{ __('Liste des Disponibilités') }}
            </h1>

            <!-- Afficher les Messages de Succès -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Barre de Recherche et Boutons -->
            <div class="flex flex-col md:flex-row md:justify-between items-center mb-4 space-y-4 md:space-y-0">
                <!-- Barre de Recherche -->
                <div class="w-full md:w-auto">
                    <input type="text" id="search" class="border border-[#854f38] rounded-md py-2 px-4 w-full md:w-80 focus:outline-none focus:ring-2 focus:ring-[#854f38]" placeholder="{{ __('Recherche par jour ou prestation...') }}" onkeyup="filterTable()">
                </div>

                <!-- Boutons -->
                <div class="flex flex-col sm:flex-row sm:space-x-4 w-full md:w-auto space-y-4 sm:space-y-0">
                    <!-- Bouton Ajouter une Disponibilité -->
                    <a href="{{ route('availabilities.create') }}" class="bg-[#647a0b] text-white px-4 py-2 rounded-md hover:bg-[#854f38] transition duration-200 flex items-center justify-center">
                        <i class="fas fa-plus mr-2"></i> {{ __('Ajouter une Disponibilité') }}
                    </a>
                    <!-- Bouton Ajouter une Indisponibilité -->
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
                                    {{ __('Jour de la Semaine') }}
                                    <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th onclick="sortTable(1)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer whitespace-nowrap">
                                    {{ __('Heure de Début') }}
                                    <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th onclick="sortTable(2)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer whitespace-nowrap">
                                    {{ __('Heure de Fin') }}
                                    <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th onclick="sortTable(3)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer whitespace-nowrap">
                                    {{ __('Toutes les Prestations') }}
                                    <i class="fas fa-sort ml-1"></i>
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
                                    <!-- Jour de la Semaine -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $daysOfWeek = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                                            echo $daysOfWeek[$availability->day_of_week];
                                        @endphp
                                    </td>
                                    <!-- Heure de Début -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $availability->start_time)->format('H:i') }}
                                    </td>
                                    <!-- Heure de Fin -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $availability->end_time)->format('H:i') }}
                                    </td>
                                    <!-- Applique à Toutes les Prestations -->
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
                                    <!-- Prestations Associées -->
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
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ __('Aucune disponibilité trouvée.') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <!-- JavaScript pour le tri et le filtrage -->
    <script>
        function filterTable() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('availabilityTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let tdDay = tr[i].getElementsByTagName('td')[0];
                let tdProducts = tr[i].getElementsByTagName('td')[4];
                if (tdDay && tdProducts) {
                    let txtValueDay = tdDay.textContent || tdDay.innerText;
                    let txtValueProducts = tdProducts.textContent || tdProducts.innerText;
                    if (txtValueDay.toLowerCase().indexOf(filter) > -1 || txtValueProducts.toLowerCase().indexOf(filter) > -1) {
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
            let shouldSwitch;
            let i, x, y;
            let dir = "asc";
            let switchcount = 0;

            while (switching) {
                switching = false;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName('td')[n];
                    y = rows[i + 1].getElementsByTagName('td')[n];
                    let xContent = x.textContent || x.innerText;
                    let yContent = y.textContent || y.innerText;

                    // Déterminer si la colonne est numérique (comme le temps) ou non
                    if (n === 1 || n === 2) { // Pour 'Heure de Début' et 'Heure de Fin'
                        xContent = xContent.replace(':', '');
                        yContent = yContent.replace(':', '');
                        xContent = parseInt(xContent);
                        yContent = parseInt(yContent);
                    } else { // Pour 'Jour de la Semaine' et 'Applique à Toutes les Prestations'
                        xContent = xContent.toLowerCase();
                        yContent = yContent.toLowerCase();
                    }

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
                let icon = ths[i].getElementsByTagName('i')[0];
                if (i === n) {
                    icon.className = dir === "asc" ? "fas fa-sort-up" : "fas fa-sort-down";
                } else {
                    icon.className = "fas fa-sort";
                }
            }
        }
    </script>

    <!-- Inclure Font Awesome JS pour les icônes (optionnel) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" defer></script>
</x-app-layout>

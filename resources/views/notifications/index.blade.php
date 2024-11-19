{{-- resources/views/notifications/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Liste des Notifications') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Titre de la Page -->
            <h1 class="text-3xl font-bold text-[#647a0b] text-center">
                {{ __('Liste des Notifications') }}
            </h1>

            <!-- Barre de Recherche et Bouton Marquer Tout comme Lu -->
            <div class="flex flex-col md:flex-row md:justify-between items-center mb-4 space-y-4 md:space-y-0">
                <!-- Barre de Recherche -->
                <div class="w-full md:w-auto">
                    <input type="text" id="search" class="border border-[#854f38] rounded-md py-2 px-4 w-full md:w-80 focus:outline-none focus:ring-2 focus:ring-[#854f38]" placeholder="{{ __('Recherche par message...') }}" onkeyup="filterTable()">
                </div>

                <!-- Bouton Marquer Tout comme Lu -->
                <form id="markAllAsReadForm" method="POST" action="{{ route('notifications.markAllAsRead') }}">
                    @csrf
                    <button type="submit" class="bg-[#647a0b] text-white px-4 py-2 rounded-md hover:bg-[#854f38] transition duration-200 flex items-center justify-center">
                        <!-- Icône Check -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('Marquer Tout comme Lu') }}
                    </button>
                </form>
            </div>

            <!-- Tableau des Notifications -->
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="notificationTable">
                        <thead class="bg-[#647a0b] text-white">
                            <tr>
                                <th onclick="sortTable(0)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Message') }}
                                    <!-- Icône de Tri -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                                <th onclick="sortTable(1)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Date') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($notifications as $notification)
                                <tr class="hover:bg-gray-100 cursor-pointer">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ $notification->data['url'] }}" class="text-[#647a0b] hover:text-[#854f38]">
                                            {{ $notification->data['message'] }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $notification->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(is_null($notification->read_at))
                                            <form method="POST" action="{{ route('notifications.markAsRead', $notification->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-sm text-blue-600 hover:underline">
                                                    {{ __('Marquer comme Lu') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-sm text-gray-500">{{ __('Lu') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @if($notifications->isEmpty())
                                <tr>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ __('Aucune notification trouvée.') }}
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
            let table = document.getElementById('notificationTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
                let td = tr[i].getElementsByTagName('td')[0]; // Message column
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }

        function sortTable(n) {
            let table = document.getElementById('notificationTable');
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
                --tw-space-y-reverse: 0;
                margin-top: 1rem;
            }
            .md\:w-auto {
                width: 100%;
            }
        }
    </style>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Vos Disponibilités Ponctuelles') }}
        </h2>
    </x-slot>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <h1 class="text-3xl font-bold text-[#647a0b] text-center">
                {{ __('Liste des Disponibilités Ponctuelles') }}
            </h1>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col md:flex-row md:justify-between items-center mb-4 space-y-4 md:space-y-0">
                <div class="w-full md:w-auto">
                    <input type="text" id="search" class="border border-[#854f38] rounded-md py-2 px-4 w-full md:w-96 focus:outline-none focus:ring-2 focus:ring-[#854f38]" placeholder="{{ __('Recherche par date, prestation ou lieu...') }}" onkeyup="filterTable()">
                </div>

                <div class="flex flex-col sm:flex-row sm:space-x-4 w-full md:w-auto space-y-4 sm:space-y-0">
                    <a href="{{ route('special-availabilities.create') }}" class="bg-[#647a0b] text-white px-4 py-2 rounded-md hover:bg-[#854f38] transition duration-200 flex items-center justify-center">
                        <i class="fas fa-plus mr-2"></i> {{ __('Ajouter des Disponibilités Ponctuelles') }}
                    </a>
                </div>
            </div>

            <div class="bg-white shadow overflow-hidden rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="specialAvailabilityTable">
                        <thead class="bg-[#647a0b] text-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">
                                    {{ __('Date') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">
                                    {{ __('Début') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">
                                    {{ __('Fin') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">
                                    {{ __('Lieu') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">
                                    {{ __('Toutes les Prestations') }}
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
                            @forelse($specialAvailabilities as $sa)
                                <tr class="hover:bg-gray-100">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ \Carbon\Carbon::parse($sa->date)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $sa->start_time)->format('H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $sa->end_time)->format('H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($sa->practiceLocation)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-[#f5f5f5] text-[#647a0b] border border-[#647a0b]">
                                                {{ $sa->practiceLocation->label }}
                                                @if($sa->practiceLocation->is_primary)
                                                    &nbsp;· {{ __('Principal') }}
                                                @endif
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $sa->practiceLocation->city }}
                                                @if($sa->practiceLocation->postal_code)
                                                    ({{ $sa->practiceLocation->postal_code }})
                                                @endif
                                            </div>
                                        @else
                                            <span class="bg-[#854f38] text-white px-2 py-1 rounded-full text-xs font-semibold">
                                                {{ __('Sans lieu') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($sa->applies_to_all)
                                            <span class="bg-[#647a0b] text-white px-2 py-1 rounded-full text-xs font-semibold">
                                                {{ __('Oui') }}
                                            </span>
                                        @else
                                            <span class="bg-[#854f38] text-white px-2 py-1 rounded-full text-xs font-semibold">
                                                {{ __('Non') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($sa->applies_to_all)
                                            <span class="bg-[#647a0b] text-white px-2 py-1 rounded-full text-xs font-semibold">
                                                {{ __('Toutes les Prestations') }}
                                            </span>
                                        @elseif($sa->products->isEmpty())
                                            <span class="bg-[#854f38] text-white px-2 py-1 rounded-full text-xs font-semibold">
                                                {{ __('Aucune Prestation associée') }}
                                            </span>
                                        @else
                                            <div class="flex flex-wrap">
                                                @foreach($sa->products as $product)
                                                    <span class="bg-[#647a0b] text-white px-2 py-1 rounded-full text-xs font-semibold inline-block mb-1 mr-1">
                                                        {{ $product->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('special-availabilities.edit', $sa->id) }}" class="text-white bg-blue-500 hover:bg-blue-600 px-3 py-2 rounded-md" title="{{ __('Éditer') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('special-availabilities.destroy', $sa->id) }}" method="POST" onsubmit="return confirm('{{ __('Supprimer cette disponibilité ponctuelle ?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-white bg-red-500 hover:bg-red-600 px-3 py-2 rounded-md" title="{{ __('Supprimer') }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ __('Aucune disponibilité ponctuelle trouvée.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterTable() {
            let input  = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let table  = document.getElementById('specialAvailabilityTable');
            let tr     = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let tdDate     = tr[i].getElementsByTagName('td')[0];
                let tdLoc      = tr[i].getElementsByTagName('td')[3];
                let tdProducts = tr[i].getElementsByTagName('td')[5];

                if (tdDate && tdLoc && tdProducts) {
                    let txtDate     = (tdDate.textContent || tdDate.innerText).toLowerCase();
                    let txtLoc      = (tdLoc.textContent || tdLoc.innerText).toLowerCase();
                    let txtProducts = (tdProducts.textContent || tdProducts.innerText).toLowerCase();

                    if (txtDate.indexOf(filter) > -1 || txtLoc.indexOf(filter) > -1 || txtProducts.indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</x-app-layout>

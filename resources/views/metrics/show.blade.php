<!-- resources/views/metrics/show.blade.php -->
<x-app-layout>
    <x-slot name="header">

    </x-slot>

    <div class="py-8 px-4 max-w-3xl mx-auto">
	        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            Mesure : {{ $metric->name }} &mdash; Client : {{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
        </h2>
        <p class="mb-4 text-gray-700">
            Objectif : 
            <strong>{{ $metric->goal ?? 'N/A' }}</strong>
        </p>

        <!-- Button to create a new Metric Entry -->
        <div class="mb-4">
            <a href="{{ route('client_profiles.metrics.entries.create', [$clientProfile, $metric]) }}"
               class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                Ajouter une nouvelle entrée
            </a>
        </div>

        <h3 class="text-lg font-semibold mb-2" style="color: #647a0b;">
            Liste des Entrées
        </h3>

        @if ($entries->isEmpty())
            <p class="text-gray-600">Aucune entrée pour le moment.</p>
        @else
            <!-- Responsive Table -->
            <div class="overflow-x-auto bg-white p-4 rounded shadow">
                <table class="table-auto w-full border-collapse">
                    <thead>
                        <tr class="bg-green-600 text-white">
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Valeur</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $entry)
                            <tr class="border-b hover:bg-green-50">
                                <td class="px-4 py-2">
                                    {{ $entry->entry_date }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $entry->value }}
                                </td>
                                <td class="px-4 py-2">
                                    <!-- Link to edit this specific MetricEntry -->
                                    <a href="{{ route('client_profiles.metrics.entries.edit', [$clientProfile, $metric, $entry]) }}"
                                       class="text-blue-500 underline">
                                        Modifier
                                    </a>
                                    <!-- You can add a 'Delete' button here if needed -->
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</x-app-layout>

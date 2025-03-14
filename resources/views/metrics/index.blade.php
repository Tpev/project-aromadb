<!-- resources/views/metrics/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            Toutes les Mesures pour le Client : {{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
        </h2>
    </x-slot>

    <div class="py-8 px-4 max-w-3xl mx-auto">
        @if (session('success'))
            <div class="mb-4 text-green-600 font-bold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Button to create a new Metric -->
        <div class="mb-4">
            <a href="{{ route('client_profiles.metrics.create', $clientProfile) }}"
               class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                Créer une Nouvelle Mesure
            </a>
        </div>

        @if($metrics->isEmpty())
            <p class="text-gray-600">
                Aucune mesure n’a encore été créée pour ce client.
            </p>
        @else
            <!-- Responsive table container -->
            <div class="overflow-x-auto bg-white p-4 rounded shadow">
                <table class="table-auto w-full border-collapse">
                    <thead>
                        <tr class="bg-green-600 text-white">
                            <th class="px-4 py-2 text-left">Nom de la Mesure</th>
                            <th class="px-4 py-2 text-left">Objectif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($metrics as $metric)
                            <tr class="border-b hover:bg-green-50">
                                <td class="px-4 py-2">
                                    <a href="{{ route('client_profiles.metrics.show', [$clientProfile, $metric]) }}"
                                       class="text-blue-600 underline">
                                        {{ $metric->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-2">
                                    {{ $metric->goal ?? 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>

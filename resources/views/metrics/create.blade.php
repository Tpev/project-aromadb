<!-- resources/views/metrics/create.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            Créer une Nouvelle Mesure pour {{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
        </h2>
    </x-slot>

    <div class="py-8 px-4">
        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('client_profiles.metrics.store', $clientProfile) }}" method="POST" class="space-y-4 max-w-xl mx-auto bg-white p-6 rounded shadow">
            @csrf

            <div>
                <label for="name" class="block font-medium mb-1 text-gray-800">
                    Nom de la Mesure
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name"
                    class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:border-green-600"
                    required
                >
            </div>

            <div>
                <label for="goal" class="block font-medium mb-1 text-gray-800">
                    Objectif (optionnel)
                </label>
                <input 
                    type="number" 
                    step="0.01" 
                    name="goal" 
                    id="goal"
                    class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:border-green-600"
                >
            </div>

            <button 
                type="submit" 
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition"
            >
                Enregistrer la Mesure
            </button>
        </form>
    </div>
</x-app-layout>

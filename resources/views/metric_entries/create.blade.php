<!-- resources/views/metric_entries/create.blade.php -->
<x-app-layout>
    <x-slot name="header">

    </x-slot>

    <div class="py-8 px-4 max-w-2xl mx-auto">
        <!-- Error Handling (if needed) -->
        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            Ajouter une Entrée pour : {{ $metric->name }}
        </h2>
        <!-- Form Container -->
        <div class="bg-white p-6 rounded shadow">
            <form 
                action="{{ route('client_profiles.metrics.entries.store', [$clientProfile, $metric]) }}" 
                method="POST" 
                class="space-y-4"
            >
                @csrf

                <div>
                    <label for="entry_date" class="block font-medium mb-1 text-gray-800">
                        Date
                    </label>
                    <input 
                        type="date" 
                        name="entry_date" 
                        id="entry_date"
                        required
                        class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:border-green-600"
                    >
                </div>

                <div>
                    <label for="value" class="block font-medium mb-1 text-gray-800">
                        Valeur
                    </label>
                    <input 
                        type="number" 
                        step="0.01" 
                        name="value" 
                        id="value"
                        required
                        class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:border-green-600"
                    >
                </div>

                <button 
                    type="submit" 
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition"
                >
                    Enregistrer l’Entrée
                </button>
            </form>
        </div>
    </div>
</x-app-layout>

<!-- resources/views/metric_entries/edit.blade.php -->
<x-app-layout>
    <x-slot name="header">

    </x-slot>

    <div class="py-6 px-4 max-w-2xl mx-auto">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            Modifier l’Entrée pour : {{ $metric->name }}
        </h2>

        <!-- Example success message -->
        @if(session('success'))
            <div class="mb-4 text-green-600 font-bold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Validation Errors -->
        @if($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Container -->
        <div class="bg-white p-6 rounded shadow">
            <form 
                action="{{ route('client_profiles.metrics.entries.update', [$clientProfile, $metric, $metricEntry]) }}" 
                method="POST"
                class="space-y-4"
            >
                @csrf
                @method('PATCH')

                <div>
                    <label for="entry_date" class="block font-medium mb-1 text-gray-800">
                        Date de l’Entrée
                    </label>
                    <input 
                        type="date" 
                        name="entry_date" 
                        id="entry_date" 
                        value="{{ old('entry_date', $metricEntry->entry_date) }}"
                        class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:border-green-600"
                        required
                    />
                </div>

                <div>
                    <label for="value" class="block font-medium mb-1 text-gray-800">
                        Valeur
                    </label>
                    <input 
                        type="number" 
                        name="value" 
                        id="value" 
                        step="0.01"
                        value="{{ old('value', $metricEntry->value) }}"
                        class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:border-green-600"
                        required
                    />
                </div>

                <button 
                    type="submit" 
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition"
                >
                    Enregistrer les Modifications
                </button>
            </form>
        </div>
    </div>
</x-app-layout>

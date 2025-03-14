<!-- resources/views/metrics/edit.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            Modifier la Mesure : {{ $metric->name }}
        </h2>
    </x-slot>

    <div class="py-8 px-4 max-w-2xl mx-auto">
        <!-- Success/Errors -->
        @if(session('success'))
            <div class="mb-4 text-green-600 font-bold">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Edit Metric Form -->
        <div class="bg-white p-6 rounded shadow">
            <form 
                action="{{ route('client_profiles.metrics.update', [$clientProfile, $metric]) }}" 
                method="POST" 
                class="space-y-4"
            >
                @csrf
                @method('PATCH') {{-- or @method('PUT') --}}

                <div>
                    <label for="name" class="block font-medium mb-1 text-gray-800">
                        Nom de la Mesure
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        value="{{ old('name', $metric->name) }}"
                        class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:border-green-600"
                        required
                    />
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
                        value="{{ old('goal', $metric->goal) }}"
                        class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:border-green-600"
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

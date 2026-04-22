<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#647a0b] leading-tight">Créer une communauté</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('communities.store') }}" class="space-y-6 rounded-3xl bg-white p-8 shadow-sm">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700" for="name">Nom de la communauté</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" class="mt-2 w-full rounded-2xl border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]" required>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700" for="description">Description</label>
                <textarea id="description" name="description" rows="5" class="mt-2 w-full rounded-2xl border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('description') }}</textarea>
                <p class="mt-2 text-xs text-gray-500">Cette description sera visible pour les membres invites dans leur espace client.</p>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="rounded-2xl bg-[#f7faef] p-4 text-sm text-gray-600">
                Deux salons seront crees automatiquement : <strong>General</strong> pour les echanges et <strong>Annonces</strong> pour vos publications reservees au praticien.
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center rounded-full bg-[#647a0b] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#55670a]">
                    Créer la communauté
                </button>
                <a href="{{ route('communities.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700">Annuler</a>
            </div>
        </form>
    </div>
</x-app-layout>

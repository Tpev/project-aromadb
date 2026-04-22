<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#647a0b] leading-tight">Modifier la communauté</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('communities.update', $community) }}" class="space-y-6 rounded-3xl bg-white p-8 shadow-sm">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-semibold text-gray-700" for="name">Nom de la communauté</label>
                <input id="name" name="name" type="text" value="{{ old('name', $community->name) }}" class="mt-2 w-full rounded-2xl border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]" required>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700" for="description">Description</label>
                <textarea id="description" name="description" rows="5" class="mt-2 w-full rounded-2xl border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('description', $community->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <label class="flex items-center gap-3 rounded-2xl bg-gray-50 px-4 py-3 text-sm text-gray-700">
                <input type="checkbox" name="is_archived" value="1" class="rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]" @checked(old('is_archived', $community->is_archived))>
                Archiver cette communauté en lecture seule
            </label>

            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center rounded-full bg-[#647a0b] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#55670a]">
                    Enregistrer
                </button>
                <a href="{{ route('communities.show', $community) }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700">Retour</a>
            </div>
        </form>
    </div>
</x-app-layout>

@php
    $isEdit = isset($audience) && $audience->exists;
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">
                Nom de la liste
            </label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $audience->name ?? '') }}"
                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]">
            @error('name')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">
                Description (optionnel)
            </label>
            <input type="text"
                   name="description"
                   value="{{ old('description', $audience->description ?? '') }}"
                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]">
            @error('description')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">
            Contacts dans cette liste
        </label>
        <p class="text-[11px] text-gray-400 mb-2">
            Sélectionnez les clients qui recevront les newsletters envoyées à cette liste.
        </p>
        <div class="border rounded-lg border-gray-200 max-h-72 overflow-y-auto">
            @forelse ($clients as $client)
                @php
                    $checked = in_array($client->id, old('client_ids', $selectedClientIds ?? []));
                @endphp
                <label class="flex items-center justify-between px-3 py-2 text-sm border-b border-gray-100 last:border-b-0">
                    <div class="flex flex-col">
                        <span class="text-gray-800">
                            {{ $client->first_name }} {{ $client->last_name }}
                        </span>
                        <span class="text-xs text-gray-500">
                            {{ $client->email ?: 'Aucun email' }}
                        </span>
                    </div>
                    <input type="checkbox"
                           name="client_ids[]"
                           value="{{ $client->id }}"
                           @checked($checked)
                           class="rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]">
                </label>
            @empty
                <div class="px-3 py-3 text-xs text-gray-500">
                    Vous n’avez pas encore de clients dans l’application.
                </div>
            @endforelse
        </div>
        @error('client_ids')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-4 flex justify-end">
    <button type="submit"
            class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm"
            style="background-color:#647a0b;">
        {{ $isEdit ? 'Enregistrer la liste' : 'Créer la liste' }}
    </button>
</div>

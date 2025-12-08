<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer une formation digitale') }}
        </h2>
    </x-slot>

    <div class="container mt-6">
        <div class="mx-auto max-w-3xl bg-white shadow-sm rounded-xl p-6">
            <form action="{{ route('digital-trainings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                @if($errors->any())
                    <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <ul class="list-disc pl-4">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('Titre de la formation') }} *
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('Description') }}
                    </label>
                    <textarea name="description" rows="4"
                              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('Image de couverture') }}
                        </label>
                        <input type="file" name="cover_image"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#647a0b] file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('Tags (séparés par des virgules)') }}
                        </label>
                        <input type="text" name="tags" value="{{ old('tags') }}"
                               placeholder="stress, sommeil, digestion"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_free" value="1" id="is_free"
                               @checked(old('is_free'))>
                        <label for="is_free" class="text-sm text-slate-700">
                            {{ __('Formation gratuite') }}
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('Prix (TTC) en €') }}
                        </label>
                        <input type="number" step="0.01" name="price_eur" value="{{ old('price_eur') }}"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('TVA %') }}
                        </label>
                        <input type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate', 0) }}"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('Type d’accès') }}
                        </label>
                        <select name="access_type"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                            <option value="public" {{ old('access_type') === 'public' ? 'selected' : '' }}>
                                {{ __('Public') }}
                            </option>
                            <option value="private" {{ old('access_type') === 'private' ? 'selected' : '' }}>
                                {{ __('Privé') }}
                            </option>
                            <option value="subscription" {{ old('access_type') === 'subscription' ? 'selected' : '' }}>
                                {{ __('Abonnement') }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('Statut') }}
                        </label>
                        <select name="status"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                            <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>
                                {{ __('Brouillon') }}
                            </option>
                            <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>
                                {{ __('Publié') }}
                            </option>
                            <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>
                                {{ __('Archivé') }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('Durée estimée (minutes)') }}
                        </label>
                        <input type="number" name="estimated_duration_minutes" value="{{ old('estimated_duration_minutes') }}"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('digital-trainings.index') }}"
                       class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        {{ __('Annuler') }}
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#506108]">
                        {{ __('Créer et passer à la construction du contenu') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

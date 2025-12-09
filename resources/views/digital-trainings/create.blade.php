{{-- resources/views/digital-trainings/create.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl" style="color: #647a0b;">
                    {{ __('Créer une formation digitale') }}
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('Définissez les informations générales avant de construire le contenu.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="container mt-6">
        <div class="mx-auto max-w-4xl bg-white shadow-sm rounded-2xl border border-slate-100 p-6">
            <form action="{{ route('digital-trainings.store') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-6">
                @csrf

                @if($errors->any())
                    <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Titre & description --}}
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-800 mb-1">
                            {{ __('Titre de la formation') }} <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="title"
                               value="{{ old('title') }}"
                               required
                               class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                        <p class="mt-1 text-[11px] text-slate-500">
                            {{ __('Ex : Programme complet – Gestion du stress au quotidien') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-800 mb-1">
                            {{ __('Description') }}
                        </label>
                        <textarea name="description"
                                  rows="4"
                                  class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40"
                                  placeholder="{{ __('Expliquez en quelques lignes le contenu et les bénéfices pour vos clients.') }}">{{ old('description') }}</textarea>
                    </div>
                </div>

                {{-- Cover + tags --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-800 mb-1">
                            {{ __('Image de couverture') }}
                        </label>
                        <input type="file"
                               name="cover_image"
                               class="w-full rounded-lg border border-slate-200 px-2 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#647a0b] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                        <p class="mt-1 text-[11px] text-slate-500">
                            {{ __('JPG ou PNG, idéalement au format horizontal. Visible sur la page de présentation et dans le player.') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-800 mb-1">
                            {{ __('Tags (séparés par des virgules)') }}
                        </label>
                        <input type="text"
                               name="tags"
                               value="{{ old('tags') }}"
                               placeholder="stress, sommeil, digestion"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                        <p class="mt-1 text-[11px] text-slate-500">
                            {{ __('Ces mots-clés facilitent la recherche et le filtrage (ex : stress, sommeil, aromathérapie).') }}
                        </p>
                    </div>
                </div>

                {{-- Accès, statut, durée --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-800 mb-1">
                            {{ __('Type d’accès') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="access_type"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                            <option value="public" {{ old('access_type') === 'public' ? 'selected' : '' }}>
                                {{ __('Public (accessible à tous)') }}
                            </option>
                            <option value="private" {{ old('access_type') === 'private' ? 'selected' : '' }}>
                                {{ __('Privé (uniquement sur invitation)') }}
                            </option>
                            <option value="subscription" {{ old('access_type') === 'subscription' ? 'selected' : '' }}>
                                {{ __('Réservé aux abonnements') }}
                            </option>
                        </select>
                        <p class="mt-1 text-[11px] text-slate-500">
                            {{ __('Détermine comment vos clients pourront accéder à cette formation.') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-800 mb-1">
                            {{ __('Statut') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="status"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                            <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>
                                {{ __('Brouillon (non visible par les clients)') }}
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
                        <label class="block text-sm font-medium text-slate-800 mb-1">
                            {{ __('Durée estimée (minutes)') }}
                        </label>
                        <input type="number"
                               name="estimated_duration_minutes"
                               value="{{ old('estimated_duration_minutes') }}"
                               min="1"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40"
                               placeholder="Ex : 90">
                        <p class="mt-1 text-[11px] text-slate-500">
                            {{ __('Simple indication pour vos clients (facultatif).') }}
                        </p>
                    </div>
                </div>

                {{-- Lien optionnel vers un produit --}}
                <div>
                    <label class="block text-sm font-medium text-slate-800 mb-1">
                        {{ __('Lier à une prestation (produit)') }}
                    </label>
                    <select name="product_id"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                        <option value="">
                            {{ __('Aucun (accès géré sans facturation automatique)') }}
                        </option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                                @if(!is_null($product->price))
                                    — {{ number_format($product->price, 2, ',', ' ') }} €
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-slate-500">
                        {{ __('Optionnel : permet de relier cette formation à une prestation existante pour la facturation (achat en ligne, pack séance + formation, etc.).') }}
                    </p>
                </div>

                {{-- Actions --}}
                <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                    <a href="{{ route('digital-trainings.index') }}"
                       class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        {{ __('Annuler') }}
                    </a>
                    <button type="submit"
                            class="rounded-full bg-[#647a0b] px-5 py-2 text-sm font-semibold text-white hover:bg-[#506108]">
                        {{ __('Créer la formation et passer à la construction du contenu') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

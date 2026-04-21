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

    @php
        $oldIsFree = (bool) old('is_free', false);
        $oldFreeAccessRequiresIdentity = (bool) old('free_access_requires_identity', false);
    @endphp

    <div class="container mt-6" x-data="{ isFree: {{ $oldIsFree ? 'true' : 'false' }} }">
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

                {{-- Pricing --}}
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ __('Tarification') }}</p>
                            <p class="text-[11px] text-slate-500">
                                {{ __('Définissez un prix TTC ou cochez "Formation gratuite".') }}
                            </p>
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-800 select-none">
                            <input type="checkbox"
                                   name="is_free"
                                   value="1"
                                   x-model="isFree"
                                   class="rounded border-slate-300 text-[#647a0b] focus:ring-[#647a0b]/40">
                            {{ __('Formation gratuite') }}
                        </label>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">
                                {{ __('Prix TTC (€)') }}
                            </label>
                            <input type="text"
                                   name="price_eur"
                                   value="{{ old('price_eur') }}"
                                   :disabled="isFree"
                                   :class="isFree ? 'opacity-50 cursor-not-allowed' : ''"
                                   placeholder="Ex : 29,90"
                                   class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                            @error('price_eur') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">
                                {{ __('TVA (%)') }}
                            </label>
                            <input type="number"
                                   name="tax_rate"
                                   value="{{ old('tax_rate', 0) }}"
                                   min="0" max="100" step="0.01"
                                   class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                            @error('tax_rate') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="text-[11px] text-slate-500 leading-relaxed">
                            <div class="rounded-xl bg-white border border-slate-100 p-3 h-full">
                                <p class="font-semibold text-slate-700 mb-1">{{ __('Note') }}</p>
                                <p>{{ __('Le prix est stocké en centimes (TTC). Exemple : 29,90 € → 2990.') }}</p>
                                <p class="mt-1">{{ __('La TVA est indicative pour l’affichage / logique future.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold text-slate-800">{{ __('Paiement en plusieurs fois') }}</p>
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-800 select-none">
                                <input type="hidden" name="installments_enabled" value="0">
                                <input
                                    type="checkbox"
                                    name="installments_enabled"
                                    value="1"
                                    {{ old('installments_enabled') ? 'checked' : '' }}
                                    :disabled="isFree"
                                    class="rounded border-slate-300 text-[#647a0b] focus:ring-[#647a0b]/40"
                                >
                                {{ __('Autoriser') }}
                            </label>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500">
                            {{ __('Choisissez les échéances autorisées (2 à 12).') }}
                        </p>

                        <div class="mt-3 grid grid-cols-4 sm:grid-cols-6 md:grid-cols-11 gap-2">
                            @for($i = 2; $i <= 12; $i++)
                                <label class="inline-flex items-center gap-1 rounded-md border border-slate-200 px-2 py-1 text-xs">
                                    <input
                                        type="checkbox"
                                        name="allowed_installments[]"
                                        value="{{ $i }}"
                                        {{ in_array($i, array_map('intval', old('allowed_installments', [])), true) ? 'checked' : '' }}
                                        :disabled="isFree"
                                    >
                                    {{ $i }}x
                                </label>
                            @endfor
                        </div>
                        @error('allowed_installments')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>


                <div x-show="isFree" x-cloak class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ __('Accès gratuit avec collecte de contact') }}</p>
                            <p class="text-[11px] text-slate-500">
                                {{ __('Optionnel : sur la page publique de cette formation, demander prénom, nom et email avant de laisser accéder gratuitement au contenu.') }}
                            </p>
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-800 select-none">
                            <input type="hidden" name="free_access_requires_identity" value="0">
                            <input type="checkbox"
                                   name="free_access_requires_identity"
                                   value="1"
                                   {{ $oldFreeAccessRequiresIdentity ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-[#647a0b] focus:ring-[#647a0b]/40">
                            {{ __('Activer le formulaire avant accès') }}
                        </label>
                    </div>

                    <div class="mt-3 rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-700">
                        {{ __('Les visiteurs verront un bouton "Accéder gratuitement", puis un formulaire avec prénom, nom et email. Une fois validé, un accès sera créé dans vos enrollments et le contenu s’ouvrira immédiatement.') }}
                    </div>
                </div>
                {{-- Accès, statut, durée --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="hidden" name="access_type" value="public">

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
                        {{ __('Optionnel : relier cette formation à une prestation existante (pack séance + formation, etc.).') }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ __('Droit de rétractation') }}</p>
                            <p class="text-[11px] text-slate-500">
                                {{ __('Optionnel : afficher une case à cocher avant paiement avec le document configuré dans Informations de l’entreprise.') }}
                            </p>
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-800 select-none">
                            <input type="hidden" name="use_global_retractation_notice" value="0">
                            <input type="checkbox"
                                   name="use_global_retractation_notice"
                                   value="1"
                                   {{ old('use_global_retractation_notice') ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-[#647a0b] focus:ring-[#647a0b]/40">
                            {{ __('Activer pour cette formation') }}
                        </label>
                    </div>

                    @php
                        $owner = auth()->user();
                    @endphp

                    @if($owner?->hasDigitalSalesRetractationNoticeConfigured())
                        <div class="mt-3 rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-700">
                            <p class="font-semibold text-slate-800">{{ __('Document global actuellement configuré') }}</p>
                            <p class="mt-1">{{ $owner->digitalSalesRetractationNoticeLabel() }}</p>
                            <a href="{{ $owner->digital_sales_retractation_url }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="mt-2 inline-flex text-sm font-semibold text-[#647a0b] underline underline-offset-2">
                                {{ __('Ouvrir le document') }}
                            </a>
                        </div>
                    @else
                        <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                            {{ __('Aucun document global n’est encore configuré. Rendez-vous dans Informations de l’entreprise pour ajouter le lien avant d’activer cette case au checkout.') }}
                        </div>
                    @endif
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

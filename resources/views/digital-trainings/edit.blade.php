{{-- resources/views/digital-trainings/edit.blade.php --}}

@php
    $tagsString = is_array($training->tags) ? implode(', ', $training->tags) : '';
    $isFree = (bool) ($training->is_free ?? false);
    $priceEurValue = !is_null($training->price_cents)
        ? number_format($training->price_cents / 100, 2, '.', '')
        : '';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl" style="color: #647a0b;">
                    {{ __('Paramètres de la formation') }} – {{ $training->title }}
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('Modifiez les informations générales de votre formation digitale.') }}
                </p>
            </div>

            <a href="{{ route('digital-trainings.builder', $training) }}"
               class="inline-flex items-center rounded-full border border-slate-200 px-4 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                🧱 {{ __('Accéder au builder de contenu') }}
            </a>
        </div>
    </x-slot>

    <div class="container mt-6" x-data="{ isFree: {{ old('is_free', $isFree) ? 'true' : 'false' }} }">
        <div class="mx-auto max-w-4xl bg-white shadow-sm rounded-2xl border border-slate-100 p-6">
            <form action="{{ route('digital-trainings.update', $training) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-6">
                @csrf
                @method('PUT')

                @if(session('success'))
                    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800 mb-3">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 mb-3">
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
                               value="{{ old('title', $training->title) }}"
                               required
                               class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-800 mb-1">
                            {{ __('Description') }}
                        </label>
                        <textarea name="description"
                                  rows="4"
                                  class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40"
                                  placeholder="{{ __('Expliquez en quelques lignes le contenu et les bénéfices pour vos clients.') }}">{{ old('description', $training->description) }}</textarea>
                    </div>
                </div>

                {{-- Cover + tags --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-800 mb-1">
                            {{ __('Image de couverture') }}
                        </label>

                        @if($training->cover_image_path)
                            <div class="mb-2 flex items-center gap-3">
                                <img src="{{ asset('storage/'.$training->cover_image_path) }}"
                                     class="h-14 w-14 rounded-xl object-cover shadow-sm" alt="">
                                <span class="text-[11px] text-slate-500">
                                    {{ __('Image actuelle. Vous pouvez la remplacer ci-dessous.') }}
                                </span>
                            </div>
                        @endif

                        <input type="file"
                               name="cover_image"
                               class="w-full rounded-lg border border-slate-200 px-2 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#647a0b] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                        <p class="mt-1 text-[11px] text-slate-500">
                            {{ __('JPG ou PNG. Si vous ne choisissez rien, l’image actuelle sera conservée.') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-800 mb-1">
                            {{ __('Tags (séparés par des virgules)') }}
                        </label>
                        <input type="text"
                               name="tags"
                               value="{{ old('tags', $tagsString) }}"
                               placeholder="stress, sommeil, digestion"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
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

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">
                                {{ __('Prix TTC (€)') }}
                            </label>
                            <input type="text"
                                   name="price_eur"
                                   value="{{ old('price_eur', $priceEurValue) }}"
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
                                   value="{{ old('tax_rate', (float)($training->tax_rate ?? 0)) }}"
                                   min="0" max="100" step="0.01"
                                   class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                            @error('tax_rate') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
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
                            <option value="draft" {{ old('status', $training->status) === 'draft' ? 'selected' : '' }}>
                                {{ __('Brouillon') }}
                            </option>
                            <option value="published" {{ old('status', $training->status) === 'published' ? 'selected' : '' }}>
                                {{ __('Publié') }}
                            </option>
                            <option value="archived" {{ old('status', $training->status) === 'archived' ? 'selected' : '' }}>
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
                               value="{{ old('estimated_duration_minutes', $training->estimated_duration_minutes) }}"
                               min="1"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40"
                               placeholder="Ex : 90">
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
                            {{ __('Aucun') }}
                        </option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id', $training->product_id) == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                                @if(!is_null($product->price))
                                    — {{ number_format($product->price, 2, ',', ' ') }} €
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Actions --}}
                <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                    <a href="{{ route('digital-trainings.index') }}"
                       class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        {{ __('Retour à la liste') }}
                    </a>
                    <button type="submit"
                            class="rounded-full bg-[#647a0b] px-5 py-2 text-sm font-semibold text-white hover:bg-[#506108]">
                        {{ __('Enregistrer les paramètres') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

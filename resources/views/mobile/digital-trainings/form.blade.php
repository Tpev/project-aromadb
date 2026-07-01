@php
    $fieldValue = fn (string $field, mixed $default = null) => old($field, $training->{$field} ?? $default);
    $boolValue = fn (string $field, bool $default = false) => filter_var(old($field, $training->{$field} ?? $default), FILTER_VALIDATE_BOOLEAN);
    $tagsString = old('tags', is_array($training->tags ?? null) ? implode(', ', $training->tags) : '');
    $priceValue = old('price_eur', ! is_null($training->price_cents ?? null) ? number_format($training->price_cents / 100, 2, '.', '') : '');
    $selectedInstallments = array_map(
        'intval',
        old('allowed_installments', is_array($training->allowed_installments ?? null) ? $training->allowed_installments : [])
    );
    $selectedStatus = old('status', $training->status ?? 'draft');
    $selectedAccess = old('access_type', $training->access_type ?? 'public');
    $owner = auth()->user();
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <a href="{{ $training->exists ? route('mobile.digital-trainings.show', $training) : route('mobile.digital-trainings.index') }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Formations digitales
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Reglages essentiels avant de construire le contenu.
            </p>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <div class="font-semibold">A corriger</div>
                <ul class="mt-1 list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Informations</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Titre</span>
                        <input type="text"
                               name="title"
                               value="{{ $fieldValue('title') }}"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Description</span>
                        <textarea name="description"
                                  rows="3"
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ $fieldValue('description') }}</textarea>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Tags</span>
                        <input type="text"
                               name="tags"
                               value="{{ $tagsString }}"
                               placeholder="stress, sommeil, respiration"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Image de couverture</span>
                        @if($training->cover_image_path)
                            <div class="mt-1 flex items-center gap-3 rounded-lg bg-[#f7f8f1] p-2">
                                <img src="{{ asset('storage/' . $training->cover_image_path) }}" alt="" class="h-12 w-12 rounded-lg object-cover">
                                <span class="text-xs text-gray-600">Image actuelle conservee si aucun fichier n est choisi.</span>
                            </div>
                        @endif
                        <input type="file"
                               name="cover_image"
                               accept="image/*"
                               class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#647a0b] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Tarification</h2>

                <div class="mt-3 divide-y divide-gray-100">
                    <label class="flex items-center justify-between gap-4 py-3">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">Formation gratuite</span>
                            <span class="mt-0.5 block text-xs leading-snug text-gray-500">Aucun paiement ne sera demande.</span>
                        </span>
                        <span>
                            <input type="hidden" name="is_free" value="0">
                            <input type="checkbox"
                                   name="is_free"
                                   value="1"
                                   class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ $boolValue('is_free', false) ? 'checked' : '' }}>
                        </span>
                    </label>

                    <div class="grid grid-cols-2 gap-3 py-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Prix TTC</span>
                            <input type="text"
                                   name="price_eur"
                                   value="{{ $priceValue }}"
                                   inputmode="decimal"
                                   placeholder="29,90"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">TVA %</span>
                            <input type="number"
                                   name="tax_rate"
                                   value="{{ $fieldValue('tax_rate', 0) }}"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   inputmode="decimal"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>

                    <label class="flex items-start justify-between gap-4 py-3">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">Acces libre gratuit</span>
                            <span class="mt-0.5 block text-xs leading-snug text-gray-500">Ouvre le contenu sans formulaire quand la formation est gratuite.</span>
                        </span>
                        <span>
                            <input type="hidden" name="free_access_is_open" value="0">
                            <input type="checkbox"
                                   name="free_access_is_open"
                                   value="1"
                                   class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ $boolValue('free_access_is_open', false) ? 'checked' : '' }}>
                        </span>
                    </label>

                    <label class="flex items-start justify-between gap-4 py-3">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">Collecter le contact</span>
                            <span class="mt-0.5 block text-xs leading-snug text-gray-500">Demande prenom, nom et email avant acces gratuit.</span>
                        </span>
                        <span>
                            <input type="hidden" name="free_access_requires_identity" value="0">
                            <input type="checkbox"
                                   name="free_access_requires_identity"
                                   value="1"
                                   class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ $boolValue('free_access_requires_identity', false) ? 'checked' : '' }}>
                        </span>
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Publication</h2>

                <div class="mt-3 grid grid-cols-3 gap-2">
                    @foreach([
                        'draft' => ['label' => 'Brouillon', 'icon' => 'fa-pen'],
                        'published' => ['label' => 'Publiee', 'icon' => 'fa-check'],
                        'archived' => ['label' => 'Archivee', 'icon' => 'fa-box-archive'],
                    ] as $value => $option)
                        <label class="flex min-h-[48px] items-center justify-center gap-1.5 rounded-lg border border-[#e4e8d5] bg-white px-2 text-center text-xs font-semibold text-gray-700 has-[:checked]:border-[#647a0b] has-[:checked]:bg-[#647a0b]/10 has-[:checked]:text-[#647a0b]">
                            <input type="radio"
                                   name="status"
                                   value="{{ $value }}"
                                   class="sr-only"
                                   {{ $selectedStatus === $value ? 'checked' : '' }}>
                            <i class="fas {{ $option['icon'] }} text-[10px]"></i>
                            <span>{{ $option['label'] }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-3 grid grid-cols-3 gap-2">
                    @foreach([
                        'public' => ['label' => 'Public', 'icon' => 'fa-globe'],
                        'private' => ['label' => 'Prive', 'icon' => 'fa-lock'],
                        'subscription' => ['label' => 'Abo', 'icon' => 'fa-repeat'],
                    ] as $value => $option)
                        <label class="flex min-h-[48px] items-center justify-center gap-1.5 rounded-lg border border-[#e4e8d5] bg-white px-2 text-center text-xs font-semibold text-gray-700 has-[:checked]:border-[#647a0b] has-[:checked]:bg-[#647a0b]/10 has-[:checked]:text-[#647a0b]">
                            <input type="radio"
                                   name="access_type"
                                   value="{{ $value }}"
                                   class="sr-only"
                                   {{ $selectedAccess === $value ? 'checked' : '' }}>
                            <i class="fas {{ $option['icon'] }} text-[10px]"></i>
                            <span>{{ $option['label'] }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Duree estimee en minutes</span>
                        <input type="number"
                               name="estimated_duration_minutes"
                               value="{{ $fieldValue('estimated_duration_minutes') }}"
                               min="1"
                               inputmode="numeric"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Prestation liee</span>
                        <select name="product_id"
                                class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            <option value="">Aucune</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ (string) old('product_id', $training->product_id ?? '') === (string) $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <label class="flex items-start justify-between gap-4 rounded-lg bg-[#f7f8f1] px-3 py-3">
                    <span>
                        <span class="block text-sm font-semibold text-gray-900">Paiement en plusieurs fois</span>
                        <span class="mt-0.5 block text-xs leading-snug text-gray-500">Disponible uniquement sur une formation payante.</span>
                    </span>
                    <span>
                        <input type="hidden" name="installments_enabled" value="0">
                        <input type="checkbox"
                               name="installments_enabled"
                               value="1"
                               class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                               {{ $boolValue('installments_enabled', false) ? 'checked' : '' }}>
                    </span>
                </label>

                <div class="mt-3 grid grid-cols-4 gap-2">
                    @for($i = 2; $i <= 12; $i++)
                        <label class="inline-flex h-10 items-center justify-center gap-1 rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700 has-[:checked]:border-[#647a0b] has-[:checked]:bg-[#647a0b]/10 has-[:checked]:text-[#647a0b]">
                            <input type="checkbox"
                                   name="allowed_installments[]"
                                   value="{{ $i }}"
                                   class="h-3.5 w-3.5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ in_array($i, $selectedInstallments, true) ? 'checked' : '' }}>
                            {{ $i }}x
                        </label>
                    @endfor
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <label class="flex items-start justify-between gap-4">
                    <span>
                        <span class="block text-sm font-semibold text-gray-900">Droit de retractation</span>
                        <span class="mt-0.5 block text-xs leading-snug text-gray-500">Affiche le document global au checkout.</span>
                    </span>
                    <span>
                        <input type="hidden" name="use_global_retractation_notice" value="0">
                        <input type="checkbox"
                               name="use_global_retractation_notice"
                               value="1"
                               class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                               {{ $boolValue('use_global_retractation_notice', false) ? 'checked' : '' }}>
                    </span>
                </label>

                @if(! $owner?->hasDigitalSalesRetractationNoticeConfigured())
                    <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs leading-snug text-amber-800">
                        Aucun document global configure dans les informations de l entreprise.
                    </p>
                @endif
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ $training->exists ? route('mobile.digital-trainings.show', $training) : route('mobile.digital-trainings.index') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-sm font-semibold text-gray-700">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] text-sm font-semibold text-white">
                    {{ $submitLabel }}
                </button>
            </div>
        </div>
    </form>
</x-mobile-layout>

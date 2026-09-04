@php
    $selectedCategoryId = old('product_category_id', ($product ?? null)?->product_category_id);
@endphp

<div class="details-box">
    <label class="details-label" for="product_category_id">{{ __('Catégorie') }}</label>
    <select id="product_category_id" name="product_category_id" class="form-control">
        <option value="">{{ __('Aucune catégorie') }}</option>
        @foreach(($categories ?? collect()) as $category)
            <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('product_category_id')
        <p class="text-red-500">{{ $message }}</p>
    @enderror
    <small class="text-gray-500">
        {{ __('Facultatif. Les prestations sans catégorie restent affichées normalement sur votre portail.') }}
        <a href="{{ route('product-categories.index') }}" class="font-semibold text-[#647a0b] hover:underline">
            {{ __('Gérer les catégories') }}
        </a>
    </small>
</div>

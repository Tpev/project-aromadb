@php
    $selectedCategoryId = old('product_category_id', ($pack ?? null)?->product_category_id);
    $categoryManagementRoute = ($mobile ?? false) ? 'mobile.product-categories.index' : 'product-categories.index';
@endphp
<div class="min-w-0">
    <label for="product_category_id" class="block text-sm font-semibold text-gray-700">{{ __('Catégorie') }}</label>
    <select id="product_category_id" name="product_category_id" class="mt-1 w-full rounded-lg border-gray-300 text-base" style="min-height:44px">
        <option value="">{{ __('Aucune catégorie') }}</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    @error('product_category_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
    <p class="mt-1 text-xs text-gray-500">
        {{ __('Facultatif.') }}
        <a href="{{ route($categoryManagementRoute) }}" class="font-semibold text-[#647a0b] hover:underline">{{ __('Gérer les catégories') }}</a>
    </p>
</div>

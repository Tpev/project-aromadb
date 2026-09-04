<x-mobile-layout title="Catégories" :hide-nav="true">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <a href="{{ route('mobile.products.index') }}" class="inline-flex items-center text-xs font-semibold text-[#647a0b]">
            <i class="fas fa-arrow-left mr-1 text-[10px]"></i>{{ __('Prestations') }}
        </a>
        <h1 class="mt-3 text-xl font-semibold text-gray-900">{{ __('Catégories de prestations') }}</h1>
        <p class="mt-1 text-sm leading-snug text-gray-600">{{ __('Organisez votre portail avec des accordéons simples et lisibles.') }}</p>

        @if(session('success'))
            <div class="mt-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <section class="mt-4 rounded-xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-900">{{ __('Nouvelle catégorie') }}</h2>
            <form action="{{ route('mobile.product-categories.store') }}" method="POST" class="mt-3 space-y-3">
                @csrf
                <input name="name" type="text" maxlength="120" required value="{{ old('name') }}" placeholder="{{ __('Nom de la catégorie') }}"
                       class="h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                <label class="block">
                    <span class="mb-1 flex justify-end text-[11px] text-gray-400" data-counter-for="mobile-new-category-description">0 / 500</span>
                    <textarea id="mobile-new-category-description" name="description" rows="4" maxlength="500" placeholder="{{ __('Description facultative') }}"
                              class="w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('description') }}</textarea>
                </label>
                <div class="grid grid-cols-[6rem_minmax(0,1fr)] gap-2">
                    <input name="display_order" type="number" min="0" value="{{ old('display_order', 0) }}" aria-label="{{ __('Ordre d’affichage') }}"
                           class="h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    <button type="submit" class="h-11 rounded-lg bg-[#647a0b] px-4 text-sm font-semibold text-white">{{ __('Créer') }}</button>
                </div>
            </form>
        </section>

        <div class="mt-4 space-y-3">
            @forelse($categories as $category)
                <article class="rounded-xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
                    <form id="mobile-category-form-{{ $category->id }}" action="{{ route('mobile.product-categories.update', $category) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-[minmax(0,1fr)_5rem] gap-2">
                            <input name="name" type="text" maxlength="120" required value="{{ $category->name }}" aria-label="{{ __('Nom') }}"
                                   class="h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            <input name="display_order" type="number" min="0" value="{{ $category->display_order }}" aria-label="{{ __('Ordre') }}"
                                   class="h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </div>
                        <label class="block">
                            <span class="mb-1 flex items-center justify-between text-[11px] text-gray-400">
                                <span>{{ trans_choice(':count prestation|:count prestations', $category->products_count, ['count' => $category->products_count]) }}</span>
                                <span data-counter-for="mobile-category-description-{{ $category->id }}">0 / 500</span>
                            </span>
                            <textarea id="mobile-category-description-{{ $category->id }}" name="description" rows="3" maxlength="500"
                                      class="w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ $category->description }}</textarea>
                        </label>
                    </form>
                    <div class="mt-3 flex gap-2">
                        <button type="submit" form="mobile-category-form-{{ $category->id }}" class="h-10 flex-1 rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white">{{ __('Enregistrer') }}</button>
                        <form action="{{ route('mobile.product-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('{{ __('Supprimer cette catégorie ?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="h-10 rounded-lg border border-red-200 px-3 text-red-700" aria-label="{{ __('Supprimer') }}"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-[#d7ddc6] bg-white p-6 text-center text-sm text-gray-500">{{ __('Aucune catégorie. Vos prestations restent affichées normalement.') }}</div>
            @endforelse
        </div>
    </div>

    <script>
        document.querySelectorAll('textarea[maxlength="500"]').forEach((textarea) => {
            const counter = document.querySelector(`[data-counter-for="${textarea.id}"]`);
            const updateCounter = () => { if (counter) counter.textContent = `${textarea.value.length} / 500`; };
            textarea.addEventListener('input', updateCounter);
            updateCounter();
        });
    </script>
</x-mobile-layout>

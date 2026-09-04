<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-[#647a0b]">{{ __('Catégories de prestations') }}</h2>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('products.index') }}" class="text-sm font-semibold text-[#647a0b] hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i>{{ __('Retour aux prestations') }}
                </a>
                <h1 class="mt-3 text-3xl font-semibold text-gray-900">{{ __('Organiser les prestations') }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600">
                    {{ __('Créez vos propres catégories. Sur le portail, chaque catégorie apparaît sous forme d’accordéon fermé par défaut.') }}
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-[#d7dfaa] bg-[#f5f7eb] px-4 py-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">{{ __('Vérifiez les informations saisies.') }}</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.5fr)]">
            <section class="h-fit rounded-2xl border border-[#e4e8d5] bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#647a0b]/10 text-[#647a0b]">
                        <i class="fas fa-folder-plus"></i>
                    </span>
                    <div>
                        <h2 class="font-semibold text-gray-900">{{ __('Nouvelle catégorie') }}</h2>
                        <p class="text-xs text-gray-500">{{ __('Visible dès qu’elle contient une prestation publiée.') }}</p>
                    </div>
                </div>

                <form action="{{ route('product-categories.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">{{ __('Nom') }}</span>
                        <input name="name" type="text" maxlength="120" value="{{ old('name') }}" required
                               class="mt-1 block w-full rounded-xl border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"
                               placeholder="{{ __('Ex. Accompagnements individuels') }}">
                    </label>

                    <label class="block">
                        <span class="flex items-center justify-between gap-3 text-sm font-medium text-gray-700">
                            <span>{{ __('Description') }}</span>
                            <span class="text-xs font-normal text-gray-400" data-counter-for="new-category-description">0 / 500</span>
                        </span>
                        <textarea id="new-category-description" name="description" rows="5" maxlength="500"
                                  class="mt-1 block w-full rounded-xl border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"
                                  placeholder="{{ __('Présentez brièvement cette famille de prestations.') }}">{{ old('description') }}</textarea>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">{{ __('Ordre d’affichage') }}</span>
                        <input name="display_order" type="number" min="0" value="{{ old('display_order', 0) }}"
                               class="mt-1 block w-full rounded-xl border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[#647a0b] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#536508]">
                        <i class="fas fa-plus mr-2"></i>{{ __('Créer la catégorie') }}
                    </button>
                </form>
            </section>

            <section>
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Vos catégories') }}</h2>
                        <p class="text-sm text-gray-500">{{ __('Les nombres les plus petits apparaissent en premier.') }}</p>
                    </div>
                    <span class="rounded-full bg-[#f5f7eb] px-3 py-1 text-xs font-semibold text-[#647a0b]">
                        {{ trans_choice(':count catégorie|:count catégories', $categories->count(), ['count' => $categories->count()]) }}
                    </span>
                </div>

                @forelse($categories as $category)
                    <article class="mb-4 rounded-2xl border border-[#e4e8d5] bg-white p-5 shadow-sm">
                        <form id="category-form-{{ $category->id }}" action="{{ route('product-categories.update', $category) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_8rem]">
                                <label class="block">
                                    <span class="text-sm font-medium text-gray-700">{{ __('Nom') }}</span>
                                    <input name="name" type="text" maxlength="120" value="{{ $category->name }}" required
                                           class="mt-1 block w-full rounded-xl border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">
                                </label>
                                <label class="block">
                                    <span class="text-sm font-medium text-gray-700">{{ __('Ordre') }}</span>
                                    <input name="display_order" type="number" min="0" value="{{ $category->display_order }}"
                                           class="mt-1 block w-full rounded-xl border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">
                                </label>
                            </div>

                            <label class="block">
                                <span class="flex items-center justify-between gap-3 text-sm font-medium text-gray-700">
                                    <span>{{ __('Description') }}</span>
                                    <span class="text-xs font-normal text-gray-400" data-counter-for="category-description-{{ $category->id }}">0 / 500</span>
                                </span>
                                <textarea id="category-description-{{ $category->id }}" name="description" rows="3" maxlength="500"
                                          class="mt-1 block w-full rounded-xl border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">{{ $category->description }}</textarea>
                            </label>
                        </form>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
                            <span class="text-xs font-medium text-gray-500">
                                <i class="fas fa-spa mr-1 text-[#647a0b]"></i>
                                {{ trans_choice(':count prestation|:count prestations', $category->products_count, ['count' => $category->products_count]) }}
                            </span>
                            <div class="flex items-center gap-2">
                                <button type="submit" form="category-form-{{ $category->id }}" class="rounded-lg bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#536508]">
                                    {{ __('Enregistrer') }}
                                </button>
                                <form action="{{ route('product-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('{{ __('Supprimer cette catégorie ? Les prestations resteront disponibles sans catégorie.') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50" aria-label="{{ __('Supprimer :category', ['category' => $category->name]) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-[#d7ddc6] bg-white px-6 py-12 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#647a0b]/10 text-[#647a0b]">
                            <i class="fas fa-folder-open"></i>
                        </span>
                        <h3 class="mt-4 font-semibold text-gray-900">{{ __('Aucune catégorie pour le moment') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Vos prestations continuent d’apparaître normalement sur le portail.') }}</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>

    <script>
        document.querySelectorAll('textarea[maxlength="500"]').forEach((textarea) => {
            const counter = document.querySelector(`[data-counter-for="${textarea.id}"]`);
            const updateCounter = () => {
                if (counter) counter.textContent = `${textarea.value.length} / 500`;
            };
            textarea.addEventListener('input', updateCounter);
            updateCounter();
        });
    </script>
</x-app-layout>

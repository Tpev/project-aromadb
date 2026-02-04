<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xl font-semibold">Templates</div>
                <div class="text-sm text-slate-500">Gérez les templates Konva (DB)</div>
            </div>
            <a href="{{ route('admin.design-templates.create') }}"
               class="rounded-xl bg-emerald-600 px-4 py-2 text-white text-sm font-semibold hover:bg-emerald-700">
                + Créer un template
            </a>
        </div>
    </x-slot>

    @php
        abort_unless(auth()->check() && auth()->user()->is_admin, 403);
    @endphp

    <div class="max-w-6xl mx-auto py-8 px-4 space-y-4">
        <div class="rounded-2xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            <div class="grid grid-cols-12 px-4 py-3 bg-slate-50 text-xs font-semibold text-slate-600">
                <div class="col-span-4">Template</div>
                <div class="col-span-2">Catégorie</div>
                <div class="col-span-2">Format</div>
                <div class="col-span-1 text-center">Actif</div>
                <div class="col-span-1 text-center">Ordre</div>
                <div class="col-span-2 text-right">Actions</div>
            </div>

            @foreach($templates as $t)
                <div class="grid grid-cols-12 px-4 py-3 border-t border-slate-100 items-center">
                    <div class="col-span-4 flex items-center gap-3">
                        <div class="h-12 w-12 rounded-xl bg-slate-100 overflow-hidden border border-slate-200">
                            @if($t->previewUrl())
                                <img src="{{ $t->previewUrl() }}" class="h-full w-full object-cover" alt="">
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-slate-900 truncate">{{ $t->name }}</div>
                            <div class="text-xs text-slate-500 truncate">#{{ $t->id }}</div>
                        </div>
                    </div>

                    <div class="col-span-2 text-sm text-slate-700">{{ $t->category }}</div>
                    <div class="col-span-2 text-sm text-slate-700">{{ $t->format_id }}</div>

                    <div class="col-span-1 text-center">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $t->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $t->is_active ? 'Oui' : 'Non' }}
                        </span>
                    </div>

                    <div class="col-span-1 text-center text-sm text-slate-700">{{ $t->sort_order }}</div>

                    <div class="col-span-2 text-right flex items-center justify-end gap-2">
                        <a href="{{ route('admin.design-templates.edit', $t) }}"
                           class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50">
                            Éditer
                        </a>

                        <form method="POST" action="{{ route('admin.design-templates.toggle', $t) }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50">
                                {{ $t->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.design-templates.destroy', $t) }}"
                              onsubmit="return confirm('Supprimer ce template ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach

            @if($templates->count() === 0)
                <div class="px-4 py-10 text-center text-slate-500">
                    Aucun template pour l’instant.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

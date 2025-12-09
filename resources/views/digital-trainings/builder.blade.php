{{-- resources/views/digital-trainings/builder.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl" style="color:#647a0b;">
                    {{ __('Construction du contenu') }}
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $training->title }}
                </p>
            </div>

            {{-- Step progress indicator --}}
            <div class="flex items-center gap-3 text-[11px] text-slate-500">
                <div class="flex items-center gap-2">
                    <div class="h-6 w-6 rounded-full bg-[#647a0b] text-white flex items-center justify-center text-[11px] font-semibold">1</div>
                    <span class="font-semibold text-slate-800">{{ __('Configurer') }}</span>
                </div>
                <div class="h-px w-6 bg-slate-300"></div>
                <div class="flex items-center gap-2">
                    <div class="h-6 w-6 rounded-full bg-[#647a0b] text-white flex items-center justify-center text-[11px] font-semibold">2</div>
                    <span class="font-semibold text-slate-800">{{ __('Construire le contenu') }}</span>
                </div>
                <div class="h-px w-6 bg-slate-300"></div>
                <div class="flex items-center gap-2">
                    <div class="h-6 w-6 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-[11px] font-semibold">3</div>
                    <span>{{ __('Prévisualiser & publier') }}</span>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Quill CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">

    <div class="container mt-6">
        <div class="mx-auto max-w-6xl space-y-4">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/90 px-4 py-2.5 text-sm text-emerald-800 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                    <a href="{{ route('digital-trainings.preview', $training) }}"
                       class="inline-flex items-center rounded-full bg-emerald-600 px-3 py-1 text-[11px] font-semibold text-white hover:bg-emerald-700">
                        👀 {{ __('Voir l’aperçu') }}
                    </a>
                </div>
            @endif

            <div class="flex flex-col gap-4 md:flex-row"
                 x-data="{
                    activeAction: null,      // 'edit' | 'create'
                    activeModuleId: null,
                    activeBlockId: null,
                    activeType: null,        // 'text' | 'video_url' | 'pdf'
                    showEditorModal: false
                 }">

                {{-- LEFT COLUMN: Training info + add module --}}
                <div class="w-full md:w-1/3 space-y-4">
                    {{-- Training overview --}}
                    <div class="bg-white shadow-sm rounded-2xl p-4 border border-slate-100">
                        <div class="flex items-start gap-3">
                            @if($training->cover_image_path)
                                <img src="{{ asset('storage/'.$training->cover_image_path) }}"
                                     class="h-14 w-14 rounded-xl object-cover shadow-sm" alt="">
                            @else
                                <div class="h-14 w-14 rounded-xl bg-[#647a0b]/10 flex items-center justify-center text-xs font-semibold text-[#647a0b]">
                                    {{ __('AM') }}
                                </div>
                            @endif

                            <div class="flex-1 space-y-1">
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ $training->title }}
                                </div>
                                @if($training->estimated_duration_minutes)
                                    <div class="text-[11px] text-slate-500">
                                        ⏱ {{ __('Durée estimée :') }}
                                        <span class="font-medium text-slate-800">
                                            {{ $training->estimated_duration_minutes }} min
                                        </span>
                                    </div>
                                @endif
                                <div class="text-[11px] text-slate-500">
                                    {{ __('Statut :') }}
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold
                                        @if($training->status === 'draft') bg-slate-100 text-slate-700
                                        @elseif($training->status === 'published') bg-emerald-100 text-emerald-800
                                        @else bg-rose-100 text-rose-700 @endif">
                                        @if($training->status === 'draft')
                                            {{ __('Brouillon') }}
                                        @elseif($training->status === 'published')
                                            {{ __('Publié') }}
                                        @else
                                            {{ __('Archivé') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($training->tags)
                            <div class="mt-3 flex flex-wrap gap-1">
                                @foreach($training->tags as $tag)
                                    <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5 text-[10px] text-slate-700 border border-slate-100">
                                        #{{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-4 space-y-2 text-[11px] text-slate-600 border-t border-slate-100 pt-3">
                            <div class="flex justify-between">
                                <span>{{ __('Type d’accès :') }}</span>
                                <strong>
                                    @if($training->access_type === 'public')
                                        {{ __('Public') }}
                                    @elseif($training->access_type === 'private')
                                        {{ __('Privé') }}
                                    @else
                                        {{ __('Abonnement') }}
                                    @endif
                                </strong>
                            </div>
                            <div class="flex justify-between">
                                <span>{{ __('Tarif :') }}</span>
                                @if($training->is_free)
                                    <strong class="text-emerald-700">{{ __('Gratuit') }}</strong>
                                @elseif($training->formatted_price)
                                    <strong>{{ $training->formatted_price }}</strong>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </div>
                            <div class="flex justify-between">
                                <span>{{ __('Modules :') }}</span>
                                <strong>{{ $training->modules->count() }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>{{ __('Blocs de contenu :') }}</span>
                                <strong>{{ $training->modules->sum(fn($m) => $m->blocks->count()) }}</strong>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('digital-trainings.edit', $training) }}"
                               class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">
                                ⚙️ {{ __('Modifier les paramètres') }}
                            </a>
                            <a href="{{ route('digital-trainings.preview', $training) }}"
                               class="inline-flex items-center rounded-full bg-[#647a0b] px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-[#506108]">
                                👀 {{ __('Prévisualiser la formation') }}
                            </a>
                        </div>
                    </div>

                    {{-- Add module --}}
                    <div class="bg-white shadow-sm rounded-2xl p-4 border border-slate-100">
                        <h3 class="text-sm font-semibold text-slate-900 mb-1">
                            {{ __('Étape 1 – Ajouter un module') }}
                        </h3>
                        <p class="text-[11px] text-slate-500 mb-3">
                            {{ __('Créez vos grands chapitres : introduction, modules thématiques, conclusion…') }}
                        </p>

                        <form action="{{ route('digital-trainings.modules.store', $training) }}"
                              method="POST"
                              class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-[11px] text-slate-600 mb-1">
                                    {{ __('Titre du module') }}
                                </label>
                                <input type="text" name="title"
                                       placeholder="Ex : Module 1 – Introduction"
                                       class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-600 mb-1">
                                    {{ __('Description (facultatif)') }}
                                </label>
                                <textarea name="description" rows="2"
                                          placeholder="Ex : Présentation générale de la formation et objectifs."
                                          class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40"></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                        class="rounded-full bg-[#647a0b] px-4 py-1.5 text-xs font-semibold text-white hover:bg-[#506108]">
                                    + {{ __('Ajouter le module') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="text-[11px] text-slate-500 bg-white/60 border border-dashed border-slate-200 rounded-xl p-3">
                        <p class="font-semibold mb-1 text-slate-700">{{ __('Comment ça marche ?') }}</p>
                        <ol class="list-decimal list-inside space-y-0.5">
                            <li>{{ __('Créez un module (colonne gauche).') }}</li>
                            <li>{{ __('Dans le module, cliquez sur “Texte”, “Vidéo” ou “PDF”.') }}</li>
                            <li>{{ __('Un grand éditeur plein écran s’ouvre pour rédiger votre contenu.') }}</li>
                        </ol>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Étape 2 – Plan & contenus (full width of column 2 & 3) --}}
                <div class="w-full md:w-2/3 space-y-4">
                    <div class="w-full bg-white shadow-sm rounded-2xl p-4 border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">
                                    {{ __('Étape 2 – Plan & contenus') }}
                                </h3>
                                <p class="text-[11px] text-slate-500">
                                    {{ __('Ajoutez les blocs de contenu à chaque module, puis cliquez sur “Éditer” pour ouvrir le grand éditeur en plein écran.') }}
                                </p>
                            </div>
                        </div>

                        @if($training->modules->isEmpty())
                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-6 text-center">
                                <p class="text-sm text-slate-600">
                                    {{ __('Aucun module pour le moment.') }}
                                </p>
                                <p class="mt-1 text-[11px] text-slate-500">
                                    {{ __('Commencez par créer un module dans la colonne de gauche.') }}
                                </p>
                            </div>
                        @else
                            <div class="space-y-3 max-h-[680px] overflow-y-auto pr-1">
                                @foreach($training->modules as $module)
                                    <div x-data="{ open: true }"
                                         class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                                        {{-- MODULE HEADER --}}
                                        <div class="flex items-start justify-between gap-3">
                                            <button type="button"
                                                    @click="open = !open"
                                                    class="flex-1 text-left flex items-start gap-3">
                                                <div class="mt-1">
                                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white text-[11px] font-semibold text-slate-600 border border-slate-200">
                                                        {{ $loop->iteration }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-semibold text-slate-900">
                                                            {{ $module->title ?: __('Module sans titre') }}
                                                        </span>
                                                    </div>
                                                    @if($module->description)
                                                        <p class="mt-0.5 text-[11px] text-slate-600">
                                                            {{ \Illuminate\Support\Str::limit($module->description, 120) }}
                                                        </p>
                                                    @endif
                                                    <div class="mt-1 flex flex-wrap gap-2 text-[11px] text-slate-500">
                                                        <span class="inline-flex items-center rounded-full bg-white px-2 py-0.5 border border-slate-100">
                                                            📚 {{ $module->blocks->count() }} {{ __('contenu(s)') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </button>

                                            <div class="flex flex-col items-end gap-1">
                                                {{-- Edit module inline --}}
                                                <details class="w-full">
                                                    <summary class="list-none text-[11px] text-slate-600 hover:text-slate-800 cursor-pointer">
                                                        ✏️ {{ __('Module') }}
                                                    </summary>
                                                    <div class="mt-2">
                                                        <form action="{{ route('digital-trainings.modules.update', [$training, $module]) }}"
                                                              method="POST"
                                                              class="space-y-2">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="text" name="title" value="{{ $module->title }}"
                                                                   class="w-full rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#647a0b]/40"
                                                                   placeholder="{{ __('Titre du module') }}">
                                                            <textarea name="description" rows="2"
                                                                      class="w-full rounded-md border border-slate-200 px-2 py-1 text-[11px] text-slate-700 focus:outline-none focus:ring-1 focus:ring-[#647a0b]/40"
                                                                      placeholder="{{ __('Description (facultatif)') }}">{{ $module->description }}</textarea>
                                                            <div class="flex justify-end gap-2">
                                                                <button type="submit"
                                                                        class="rounded-md border border-slate-200 px-3 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-100">
                                                                    {{ __('Enregistrer') }}
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </details>

                                                {{-- Delete module --}}
                                                <form action="{{ route('digital-trainings.modules.destroy', [$training, $module]) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Supprimer ce module et tout son contenu ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="rounded-md border border-rose-200 px-2 py-0.5 text-[11px] font-semibold text-rose-700 hover:bg-rose-50">
                                                        🗑
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        {{-- MODULE BODY --}}
                                        <div x-show="open" x-cloak class="mt-3 space-y-2">
                                            {{-- BLOCKS --}}
                                            @forelse($module->blocks as $block)
                                                <div class="rounded-xl bg-white border border-slate-200 px-3 py-2 text-xs flex items-start justify-between gap-3">
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            {{-- Type badge --}}
                                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] uppercase tracking-wide text-slate-600">
                                                                @if($block->type === 'text')
                                                                    📝 {{ __('Texte riche') }}
                                                                @elseif($block->type === 'video_url')
                                                                    🎥 {{ __('Vidéo (URL)') }}
                                                                @else
                                                                    📄 {{ __('PDF') }}
                                                                @endif
                                                            </span>
                                                            {{-- Title --}}
                                                            @if($block->title)
                                                                <span class="text-[12px] font-semibold text-slate-800">
                                                                    {{ $block->title }}
                                                                </span>
                                                            @else
                                                                <span class="text-[11px] text-slate-500 italic">
                                                                    {{ __('Sans titre') }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        {{-- mini preview --}}
                                                        @if($block->type === 'text' && $block->content)
                                                            <div class="prose prose-xs max-w-none text-slate-700 line-clamp-3">
                                                                {{ \Illuminate\Support\Str::limit(strip_tags($block->content), 120) }}
                                                            </div>
                                                        @elseif($block->type === 'video_url' && $block->content)
                                                            <div class="text-[11px] text-slate-600">
                                                                {{ __('URL vidéo :') }}
                                                                <span class="underline">
                                                                    {{ \Illuminate\Support\Str::limit($block->content, 50) }}
                                                                </span>
                                                            </div>
                                                        @elseif($block->type === 'pdf' && $block->file_path)
                                                            <div class="text-[11px] text-slate-600">
                                                                {{ __('Fichier PDF :') }}
                                                                <span class="underline">
                                                                    {{ __('Document associé') }}
                                                                </span>
                                                            </div>
                                                        @else
                                                            <div class="text-[11px] text-slate-400">
                                                                {{ __('Aucun contenu saisi pour le moment.') }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="flex flex-col items-end gap-1">
                                                        <button type="button"
                                                                @click="
                                                                    activeAction='edit';
                                                                    activeModuleId={{ $module->id }};
                                                                    activeBlockId={{ $block->id }};
                                                                    activeType='{{ $block->type }}';
                                                                    showEditorModal = true;
                                                                "
                                                                class="rounded-md border border-slate-200 px-2 py-0.5 text-[11px] text-slate-600 hover:bg-slate-50">
                                                            ✏️ {{ __('Éditer') }}
                                                        </button>
                                                        <form action="{{ route('digital-trainings.blocks.destroy', [$training, $module, $block]) }}"
                                                              method="POST"
                                                              onsubmit="return confirm('Supprimer ce contenu ?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="rounded-md border border-rose-200 px-2 py-0.5 text-[11px] font-semibold text-rose-700 hover:bg-rose-50">
                                                                🗑
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-[11px] text-slate-500">
                                                    {{ __('Aucun contenu dans ce module pour le moment.') }}
                                                </p>
                                            @endforelse

                                            {{-- ADD CONTENT BUTTONS --}}
                                            <div class="mt-1 flex flex-wrap gap-2 pt-2 border-t border-dashed border-slate-200">
                                                <span class="text-[11px] text-slate-500 mr-1">
                                                    {{ __('Ajouter :') }}
                                                </span>
                                                <button type="button"
                                                        @click="
                                                            activeAction='create';
                                                            activeModuleId={{ $module->id }};
                                                            activeType='text';
                                                            activeBlockId=null;
                                                            showEditorModal = true;
                                                        "
                                                        class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 border border-slate-200 hover:bg-slate-50">
                                                    📝 {{ __('Texte') }}
                                                </button>
                                                <button type="button"
                                                        @click="
                                                            activeAction='create';
                                                            activeModuleId={{ $module->id }};
                                                            activeType='video_url';
                                                            activeBlockId=null;
                                                            showEditorModal = true;
                                                        "
                                                        class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 border border-slate-200 hover:bg-slate-50">
                                                    🎥 {{ __('Vidéo') }}
                                                </button>
                                                <button type="button"
                                                        @click="
                                                            activeAction='create';
                                                            activeModuleId={{ $module->id }};
                                                            activeType='pdf';
                                                            activeBlockId=null;
                                                            showEditorModal = true;
                                                        "
                                                        class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 border border-slate-200 hover:bg-slate-50">
                                                    📄 {{ __('PDF') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Bottom footer --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-[11px] text-slate-500 pt-1">
                        <a href="{{ route('digital-trainings.index') }}" class="underline">
                            {{ __('← Retour à la liste des formations') }}
                        </a>
                        <div class="flex flex-wrap items-center gap-2 justify-end">
                            <span>{{ __('Les modifications sont enregistrées à chaque clic sur “Enregistrer” ou “Créer ce contenu”.') }}</span>
                            <a href="{{ route('digital-trainings.preview', $training) }}"
                               class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">
                                👀 {{ __('Ouvrir l’aperçu côté client') }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- FULLSCREEN MODAL EDITOR --}}
                <div x-show="showEditorModal"
                     x-cloak
                     class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 p-4">
                    <div class="bg-white shadow-2xl rounded-2xl border border-slate-200 w-full max-w-5xl h-full lg:h-[90vh] flex flex-col p-4">
                        {{-- Modal header --}}
                        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">
                                    {{ __('Grand éditeur de contenu') }}
                                </h3>
                                <p class="text-[11px] text-slate-500" x-show="!activeAction">
                                    {{ __('Sélectionnez un type de contenu dans le plan pour commencer à écrire.') }}
                                </p>
                                <p class="text-[11px] text-slate-500" x-show="activeAction">
                                    {{ __('Rédigez ou modifiez votre contenu ci-dessous, puis cliquez sur “Enregistrer” ou “Créer ce contenu”.') }}
                                </p>
                            </div>
                            <button type="button"
                                    @click="showEditorModal = false; activeAction=null; activeBlockId=null; activeModuleId=null; activeType=null;"
                                    class="rounded-full border border-slate-200 px-2 py-1 text-[11px] text-slate-600 hover:bg-slate-50">
                                ✕ {{ __('Fermer') }}
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto px-1 pb-2">
                            {{-- DEFAULT STATE --}}
                            <div x-show="!activeAction" class="h-full flex items-center justify-center text-center text-[12px] text-slate-400">
                                <div class="max-w-sm space-y-2">
                                    <p>{{ __('Choisissez un module à gauche, puis cliquez sur “Texte”, “Vidéo” ou “PDF”, ou sur “Éditer” pour modifier un contenu existant.') }}</p>
                                    <p>{{ __('Ce grand éditeur vous permet de travailler à l’aise, comme dans un traitement de texte.') }}</p>
                                </div>
                            </div>

                            {{-- EDIT FORMS FOR EXISTING BLOCKS --}}
                            @foreach($training->modules as $module)
                                @foreach($module->blocks as $block)
                                    <div x-show="activeAction === 'edit' && activeModuleId === {{ $module->id }} && activeBlockId === {{ $block->id }} && activeType === '{{ $block->type }}'"
                                         x-cloak
                                         class="space-y-3">
                                        <div class="mb-2 text-[11px] text-slate-500 flex flex-wrap gap-2 items-center">
                                            <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5 border border-slate-100">
                                                {{ $module->title ?: __('Module') }}
                                            </span>
                                            @if($block->type === 'text')
                                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 border border-emerald-100 text-emerald-700">
                                                    📝 {{ __('Texte riche') }}
                                                </span>
                                            @elseif($block->type === 'video_url')
                                                <span class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 border border-sky-100 text-sky-700">
                                                    🎥 {{ __('Vidéo (URL)') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 border border-amber-100 text-amber-700">
                                                    📄 {{ __('PDF') }}
                                                </span>
                                            @endif
                                            <span class="text-[10px] text-slate-400">
                                                {{ __('ID bloc :') }} {{ $block->id }}
                                            </span>
                                        </div>

                                        <form action="{{ route('digital-trainings.blocks.update', [$training, $module, $block]) }}"
                                              method="POST"
                                              enctype="multipart/form-data"
                                              class="space-y-3">
                                            @csrf
                                            @method('PUT')

                                            <div>
                                                <label class="block text-[11px] text-slate-600 mb-1">
                                                    {{ __('Titre du contenu') }}
                                                </label>
                                                <input type="text" name="title" value="{{ $block->title }}"
                                                       class="w-full rounded-md border border-slate-200 px-3 py-2 text-[13px] focus:outline-none focus:ring-1 focus:ring-[#647a0b]/40"
                                                       placeholder="{{ __('Ex : Vidéo d’introduction') }}">
                                            </div>

                                            {{-- TEXT BLOCK: RICH EDITOR --}}
                                            @if($block->type === 'text')
                                                <div class="flex flex-col gap-1">
                                                    <label class="block text-[11px] text-slate-600">
                                                        {{ __('Contenu texte riche') }}
                                                    </label>
                                                    <input type="hidden"
                                                           name="content"
                                                           id="block-content-{{ $block->id }}"
                                                           value="{{ $block->content }}">
                                                    <div data-quill-editor
                                                         data-target-input="block-content-{{ $block->id }}"
                                                         class="quill-editor border border-slate-200 rounded-md bg-white min-h-[320px]">
                                                    </div>
                                                    <p class="mt-1 text-[10px] text-slate-400">
                                                        {{ __('Ajoutez du texte mis en forme, des listes, des titres, des liens et des images (via URL).') }}
                                                    </p>
                                                </div>
                                            @elseif($block->type === 'video_url')
                                                <div>
                                                    <label class="block text-[11px] text-slate-600 mb-1">
                                                        {{ __('URL de la vidéo') }}
                                                    </label>
                                                    <textarea name="content" rows="3"
                                                              class="w-full rounded-md border border-slate-200 px-3 py-2 text-[13px] focus:outline-none focus:ring-1 focus:ring-[#647a0b]/40"
                                                              placeholder="{{ __('Coller l’URL de votre vidéo (YouTube, Vimeo, etc.)') }}">{{ $block->content }}</textarea>
                                                    <p class="mt-1 text-[10px] text-slate-400">
                                                        {{ __('Collez une URL publique (YouTube, Vimeo…). Elle sera intégrée automatiquement dans le lecteur.') }}
                                                    </p>
                                                </div>
                                            @elseif($block->type === 'pdf')
                                                <div class="space-y-2">
                                                    @if($block->file_path)
                                                        <div class="text-[11px] text-slate-600">
                                                            {{ __('PDF actuel :') }}
                                                            <a href="{{ asset('storage/'.$block->file_path) }}" target="_blank" class="underline">
                                                                {{ __('Ouvrir le document') }}
                                                            </a>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <label class="block text-[11px] text-slate-600 mb-1">
                                                            {{ __('Remplacer le PDF (optionnel)') }}
                                                        </label>
                                                        <input type="file" name="file"
                                                               accept="application/pdf,.pdf"
                                                               class="w-full rounded-md border border-slate-200 px-2 py-1 text-[11px] file:mr-2 file:rounded-md file:border-0 file:bg-[#647a0b] file:px-3 file:py-1 file:text-[11px] file:font-semibold file:text-white">
                                                        <p class="mt-1 text-[10px] text-slate-400">
                                                            {{ __('Le PDF sera affiché en grand dans un lecteur intégré côté client.') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                                                <button type="button"
                                                        @click="showEditorModal = false; activeAction=null; activeBlockId=null; activeModuleId=null; activeType=null;"
                                                        class="rounded-md border border-slate-200 px-3 py-1.5 text-[11px] text-slate-600 hover:bg-slate-50">
                                                    {{ __('Fermer sans enregistrer') }}
                                                </button>
                                                <div class="flex gap-2">
                                                    <button type="submit"
                                                            class="rounded-md bg-[#647a0b] px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-[#506108]">
                                                        {{ __('Enregistrer les modifications') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endforeach
                            @endforeach

                            {{-- CREATE FORMS (NEW CONTENT) --}}
                            @foreach($training->modules as $module)
                                {{-- Create TEXT --}}
                                <div x-show="activeAction === 'create' && activeModuleId === {{ $module->id }} && activeType === 'text'"
                                     x-cloak
                                     class="space-y-3">
                                    <div class="mb-2 text-[11px] text-slate-500 flex flex-wrap gap-2 items-center">
                                        <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5 border border-slate-100">
                                            {{ $module->title ?: __('Module') }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 border border-emerald-100 text-emerald-700">
                                            📝 {{ __('Nouveau texte riche') }}
                                        </span>
                                    </div>

                                    @php
                                        $newId = 'block-new-text-'.$module->id;
                                    @endphp

                                    <form action="{{ route('digital-trainings.blocks.store', [$training, $module]) }}"
                                          method="POST"
                                          enctype="multipart/form-data"
                                          class="space-y-3">
                                        @csrf
                                        <input type="hidden" name="type" value="text">

                                        <div>
                                            <label class="block text-[11px] text-slate-600 mb-1">
                                                {{ __('Titre du contenu') }}
                                            </label>
                                            <input type="text" name="title"
                                                   class="w-full rounded-md border border-slate-200 px-3 py-2 text-[13px] focus:outline-none focus:ring-1 focus:ring-[#647a0b]/40"
                                                   placeholder="{{ __('Ex : Introduction au stress') }}">
                                        </div>

                                        <div class="flex flex-col gap-1">
                                            <label class="block text-[11px] text-slate-600">
                                                {{ __('Contenu texte riche') }}
                                            </label>
                                            <input type="hidden"
                                                   name="content"
                                                   id="content-{{ $newId }}">
                                            <div data-quill-editor
                                                 data-target-input="content-{{ $newId }}"
                                                 class="quill-editor border border-slate-200 rounded-md bg-white min-h-[320px]">
                                            </div>
                                            <p class="mt-1 text-[10px] text-slate-400">
                                                {{ __('Ajoutez du texte mis en forme, des listes, des titres, des liens et des images (via URL).') }}
                                            </p>
                                        </div>

                                        <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                                            <button type="button"
                                                    @click="showEditorModal = false; activeAction=null; activeModuleId=null; activeType=null;"
                                                    class="rounded-md border border-slate-200 px-3 py-1.5 text-[11px] text-slate-600 hover:bg-slate-50">
                                                {{ __('Fermer sans créer') }}
                                            </button>
                                            <div class="flex gap-2">
                                                <button type="submit"
                                                        class="rounded-md bg-[#647a0b] px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-[#506108]">
                                                    {{ __('Créer ce contenu') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                {{-- Create VIDEO --}}
                                <div x-show="activeAction === 'create' && activeModuleId === {{ $module->id }} && activeType === 'video_url'"
                                     x-cloak
                                     class="space-y-3">
                                    <div class="mb-2 text-[11px] text-slate-500 flex flex-wrap gap-2 items-center">
                                        <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5 border border-slate-100">
                                            {{ $module->title ?: __('Module') }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 border border-sky-100 text-sky-700">
                                            🎥 {{ __('Nouvelle vidéo') }}
                                        </span>
                                    </div>

                                    <form action="{{ route('digital-trainings.blocks.store', [$training, $module]) }}"
                                          method="POST"
                                          enctype="multipart/form-data"
                                          class="space-y-3">
                                        @csrf
                                        <input type="hidden" name="type" value="video_url">

                                        <div>
                                            <label class="block text-[11px] text-slate-600 mb-1">
                                                {{ __('Titre du contenu') }}
                                            </label>
                                            <input type="text" name="title"
                                                   class="w-full rounded-md border border-slate-200 px-3 py-2 text-[13px] focus:outline-none focus:ring-1 focus:ring-[#647a0b]/40"
                                                   placeholder="{{ __('Ex : Vidéo d’introduction') }}">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] text-slate-600 mb-1">
                                                {{ __('URL de la vidéo') }}
                                            </label>
                                            <textarea name="content" rows="3"
                                                      class="w-full rounded-md border border-slate-200 px-3 py-2 text-[13px] focus:outline-none focus:ring-1 focus:ring-[#647a0b]/40"
                                                      placeholder="{{ __('Coller l’URL de votre vidéo (YouTube, Vimeo, etc.)') }}"></textarea>
                                            <p class="mt-1 text-[10px] text-slate-400">
                                                {{ __('Votre client verra la vidéo intégrée dans un lecteur plein écran adapté.') }}
                                            </p>
                                        </div>

                                        <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                                            <button type="button"
                                                    @click="showEditorModal = false; activeAction=null; activeModuleId=null; activeType=null;"
                                                    class="rounded-md border border-slate-200 px-3 py-1.5 text-[11px] text-slate-600 hover:bg-slate-50">
                                                {{ __('Fermer sans créer') }}
                                            </button>
                                            <div class="flex gap-2">
                                                <button type="submit"
                                                        class="rounded-md bg-[#647a0b] px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-[#506108]">
                                                    {{ __('Créer ce contenu') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                {{-- Create PDF --}}
                                <div x-show="activeAction === 'create' && activeModuleId === {{ $module->id }} && activeType === 'pdf'"
                                     x-cloak
                                     class="space-y-3">
                                    <div class="mb-2 text-[11px] text-slate-500 flex flex-wrap gap-2 items-center">
                                        <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5 border border-slate-100">
                                            {{ $module->title ?: __('Module') }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 border border-amber-100 text-amber-700">
                                            📄 {{ __('Nouveau PDF') }}
                                        </span>
                                    </div>

                                    <form action="{{ route('digital-trainings.blocks.store', [$training, $module]) }}"
                                          method="POST"
                                          enctype="multipart/form-data"
                                          class="space-y-3">
                                        @csrf
                                        <input type="hidden" name="type" value="pdf">

                                        <div>
                                            <label class="block text-[11px] text-slate-600 mb-1">
                                                {{ __('Titre du contenu') }}
                                            </label>
                                            <input type="text" name="title"
                                                   class="w-full rounded-md border border-slate-200 px-3 py-2 text-[13px] focus:outline-none focus:ring-1 focus:ring-[#647a0b]/40"
                                                   placeholder="{{ __('Ex : Guide PDF à télécharger') }}">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] text-slate-600 mb-1">
                                                {{ __('Fichier PDF') }}
                                            </label>
                                            <input type="file" name="file"
                                                   accept="application/pdf,.pdf"
                                                   class="w-full rounded-md border border-slate-200 px-2 py-1 text-[11px] file:mr-2 file:rounded-md file:border-0 file:bg-[#647a0b] file:px-3 file:py-1 file:text-[11px] file:font-semibold file:text-white">
                                            <p class="mt-1 text-[10px] text-slate-400">
                                                {{ __('Le document sera consultable directement dans un lecteur PDF intégré côté client.') }}
                                            </p>
                                        </div>

                                        <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                                            <button type="button"
                                                    @click="showEditorModal = false; activeAction=null; activeModuleId=null; activeType=null;"
                                                    class="rounded-md border border-slate-200 px-3 py-1.5 text-[11px] text-slate-600 hover:bg-slate-50">
                                                {{ __('Fermer sans créer') }}
                                            </button>
                                            <div class="flex gap-2">
                                                <button type="submit"
                                                        class="rounded-md bg-[#647a0b] px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-[#506108]">
                                                    {{ __('Créer ce contenu') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                {{-- END FULLSCREEN MODAL --}}
            </div>
        </div>
    </div>

    {{-- Quill JS + init --}}
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toolbarOptions = [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ];

            document.querySelectorAll('[data-quill-editor]').forEach((el) => {
                const targetId = el.getAttribute('data-target-input');
                const hiddenInput = document.getElementById(targetId);
                if (!hiddenInput) return;

                const quill = new Quill(el, {
                    theme: 'snow',
                    modules: {
                        toolbar: toolbarOptions
                    }
                });

                // Initial value (for edit)
                if (hiddenInput.value) {
                    quill.root.innerHTML = hiddenInput.value;
                }

                // Sync before submit
                const form = el.closest('form');
                if (form) {
                    form.addEventListener('submit', () => {
                        hiddenInput.value = quill.root.innerHTML;
                    });
                }
            });
        });
    </script>
</x-app-layout>

{{-- resources/views/newsletters/_form.blade.php --}}
@php
    $isEdit = isset($newsletter) && $newsletter->exists;

    $oldJson = old('content_json');
    if ($oldJson) {
        $decoded = json_decode($oldJson, true);
        $initialBlocksForJs = is_array($decoded) ? $decoded : ($initialBlocks ?? []);
    } else {
        $initialBlocksForJs = $initialBlocks ?? [];
    }

    $initialFromName   = old('from_name', $newsletter->from_name ?? '');
    $initialSubject    = old('subject', $newsletter->subject ?? '');
    $initialPreheader  = old('preheader', $newsletter->preheader ?? '');
    $initialBgColor    = old('background_color', $newsletter->background_color ?? '#ffffff');
@endphp

<form action="{{ $route }}"
      method="POST"
      x-data="newsletterEditor()"
      data-initial-blocks='@json($initialBlocksForJs)'
      data-initial-from-name="{{ e($initialFromName) }}"
      data-initial-subject="{{ e($initialSubject) }}"
      data-initial-preheader="{{ e($initialPreheader) }}"
      data-initial-bg="{{ e($initialBgColor) }}">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    {{-- Hidden JSON field updated par Alpine --}}
    <input type="hidden" name="content_json" x-model="contentJson">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-6">

        {{-- Infos générales --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Titre interne
                </label>
                <input type="text" name="title"
                       value="{{ old('title', $newsletter->title ?? '') }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]">
                @error('title')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    L’objet (titre visible par le client)
                </label>
                <input type="text" name="subject"
                       x-model="subjectLine"
                       value="{{ $initialSubject }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]">
                @error('subject')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Pré-Header (petit texte d’aperçu dans la boîte mail)
                </label>
                <input type="text" name="preheader"
                       x-model="preheaderText"
                       value="{{ $initialPreheader }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]">
                @error('preheader')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Nom de l’expéditeur
                    </label>
                    <input type="text" name="from_name"
                           x-model="senderName"
                           value="{{ $initialFromName }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]">
                    @error('from_name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Email d’envoi caché, forcé à contact@aromamade.com --}}
            <input type="hidden"
                   name="from_email"
                   value="contact@aromamade.com">
        </div>

        {{-- Couleur de fond --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Couleur de fond de l’email
                </label>
                <div class="flex items-center gap-3">
                    <input type="color"
                           name="background_color"
                           x-model="backgroundColor"
                           value="{{ $initialBgColor }}"
                           class="w-10 h-10 rounded border border-gray-300 p-0 cursor-pointer">
                    <input type="text"
                           x-model="backgroundColor"
                           class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]"
                           placeholder="#ffffff">
                </div>
                @error('background_color')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-[11px] text-gray-400">
                    Cette couleur sera utilisée comme fond général de l’email.
                </p>
            </div>
        </div>

        {{-- Éditeur + Preview --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- ÉDITEUR --}}
            <div class="bg-gray-50 rounded-xl border border-dashed border-gray-200 p-4 md:p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800">Contenu de l’email</h2>
                        <p class="text-xs text-gray-500">
                            Ajoutez des blocs de texte, images et boutons. Un email responsive en HTML sera généré automatiquement.
                        </p>
                        @error('blocks')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="addBlock('heading_text')"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-100">
                            + Titre & texte
                        </button>
                        <button type="button" @click="addBlock('text')"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-100">
                            + Texte
                        </button>
                        <button type="button" @click="addBlock('image')"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-100">
                            + Image
                        </button>
                        <button type="button" @click="addBlock('button')"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-100">
                            + Bouton
                        </button>
                        <button type="button" @click="addBlock('divider')"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-100">
                            + Séparateur
                        </button>
                    </div>
                </div>

                <template x-if="blocks.length === 0">
                    <div class="text-center text-xs text-gray-500 border border-dashed border-gray-200 rounded-lg py-8">
                        Aucun bloc pour l’instant. Ajoutez un bloc avec les boutons ci-dessus.
                    </div>
                </template>

                <div class="space-y-3">
                    <template x-for="(block, index) in blocks" :key="index">
                        <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                        <span x-text="index + 1"></span>
                                    </span>
                                    <span class="text-xs font-semibold text-gray-700" x-text="labelFor(block.type)"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="moveUp(index)"
                                            class="text-xs text-gray-400 hover:text-gray-700"
                                            :disabled="index === 0">
                                        ↑
                                    </button>
                                    <button type="button" @click="moveDown(index)"
                                            class="text-xs text-gray-400 hover:text-gray-700"
                                            :disabled="index === blocks.length - 1">
                                        ↓
                                    </button>
                                    <button type="button" @click="removeBlock(index)"
                                            class="text-xs text-red-500 hover:text-red-600">
                                        Supprimer
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-3">
                                {{-- heading_text --}}
                                <template x-if="block.type === 'heading_text'">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                                Titre
                                            </label>
                                            <input type="text"
                                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]"
                                                   x-model="block.heading">
                                        </div>

                                        {{-- Zone texte + Toolbar (WYSIWYG) --}}
                                        <div data-textblock="1" :data-index="index">
                                            <div class="flex items-center flex-wrap gap-1 mb-1 text-[11px] text-gray-500">
                                                <span class="mr-1">Mise en forme :</span>
                                                <button type="button"
                                                        class="px-1.5 py-0.5 rounded border border-gray-300 bg-white text-[11px] font-semibold hover:bg-gray-100"
                                                        @click.prevent="applyFormatting(index, 'bold', $event)">
                                                    <span class="font-bold">B</span>
                                                </button>
                                                <button type="button"
                                                        class="px-1.5 py-0.5 rounded border border-gray-300 bg-white text-[11px] italic hover:bg-gray-100"
                                                        @click.prevent="applyFormatting(index, 'italic', $event)">
                                                    I
                                                </button>
                                                <button type="button"
                                                        class="px-1.5 py-0.5 rounded border border-gray-300 bg-white text-[11px] hover:bg-gray-100"
                                                        @click.prevent="applyFormatting(index, 'underline', $event)">
                                                    <span class="underline">U</span>
                                                </button>
                                                <span class="mx-1 text-gray-300">|</span>
                                                <button type="button"
                                                        class="px-1.5 py-0.5 rounded border border-gray-200 bg-white text-[11px] hover:bg-gray-100"
                                                        @click.prevent="applyFormatting(index, 'left', $event)">
                                                    ⬅︎
                                                </button>
                                                <button type="button"
                                                        class="px-1.5 py-0.5 rounded border border-gray-200 bg-white text-[11px] hover:bg-gray-100"
                                                        @click.prevent="applyFormatting(index, 'center', $event)">
                                                    ⬌
                                                </button>
                                                <button type="button"
                                                        class="px-1.5 py-0.5 rounded border border-gray-200 bg-white text-[11px] hover:bg-gray-100"
                                                        @click.prevent="applyFormatting(index, 'right', $event)">
                                                    ➝
                                                </button>
                                            </div>

                                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                                Texte
                                            </label>
                                            <div contenteditable="true"
                                                 class="w-full min-h-[90px] rounded-lg border border-gray-300 bg-white text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#647a0b] focus:border-[#647a0b] prose-sm"
                                                 :style="`text-align:${block.text_align || 'left'};`"
                                                 x-init="$el.innerHTML = block.html || ''"
                                                 @input="onEditorInput(index, $event)">
                                            </div>
                                            <input type="hidden" :value="block.html || ''">
                                            <p class="mt-1 text-[11px] text-gray-400">
                                                Vous pouvez utiliser <code>{{ '{' }}{{ ' client.first_name ' }}{{ '}' }}</code> pour personnaliser.
                                            </p>
                                        </div>

                                        {{-- Style heading_text --}}
                                        <div class="grid grid-cols-2 gap-3 mt-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                                    Taille du titre
                                                </label>
                                                <input type="text"
                                                       x-model="block.heading_size"
                                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]"
                                                       placeholder="22px">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                                    Couleur du titre
                                                </label>
                                                <input type="color"
                                                       x-model="block.heading_color"
                                                       class="w-full h-9 rounded border border-gray-300">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3 mt-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                                    Taille du texte
                                                </label>
                                                <input type="text"
                                                       x-model="block.text_size"
                                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]"
                                                       placeholder="14px">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                                    Couleur du texte
                                                </label>
                                                <input type="color"
                                                       x-model="block.text_color"
                                                       class="w-full h-9 rounded border border-gray-300">
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                                Police
                                            </label>
                                            <select x-model="block.font_family"
                                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]">
                                                <option value="Montserrat">Montserrat</option>
                                                <option value="Arial">Arial</option>
                                                <option value="Georgia">Georgia</option>
                                                <option value="Times New Roman">Times New Roman</option>
                                                <option value="Verdana">Verdana</option>
                                            </select>
                                        </div>
                                    </div>
                                </template>

                                {{-- text --}}
                                <template x-if="block.type === 'text'">
                                    <div>
                                        <div data-textblock="1" :data-index="index">
                                            <div class="flex items-center flex-wrap gap-1 mb-1 text-[11px] text-gray-500">
                                                <span class="mr-1">Mise en forme :</span>
                                                <button type="button"
                                                        class="px-1.5 py-0.5 rounded border border-gray-300 bg-white text-[11px] font-semibold hover:bg-gray-100"
                                                        @click.prevent="applyFormatting(index, 'bold', $event)">
                                                    <span class="font-bold">B</span>
                                                </button>
                                                <button type="button"
                                                        class="px-1.5 py-0.5 rounded border border-gray-300 bg-white text-[11px] italic hover:bg-gray-100"
                                                        @click.prevent="applyFormatting(index, 'italic', $event)">
                                                    I
                                                </button>
                                                <button type="button"
                                                        class="px-1.5 py-0.5 rounded border border-gray-300 bg-white text-[11px] hover:bg-gray-100"
                                                        @click.prevent="applyFormatting(index, 'underline', $event)">
                                                    <span class="underline">U</span>
                                                </button>
                                                <span class="mx-1 text-gray-300">|</span>
                                                <button type="button"
                                                        class="px-1.5 py-0.5 rounded border border-gray-200 bg-white text-[11px] hover:bg-gray-100"
                                                        @click.prevent="applyFormatting(index, 'left', $event)">
                                                    ⬅︎
                                                </button>
                                                <button type="button"
                                                        class="px-1.5 py-0.5 rounded border border-gray-200 bg-white text-[11px] hover:bg-gray-100"
                                                        @click.prevent="applyFormatting(index, 'center', $event)">
                                                    ⬌
                                                </button>
                                                <button type="button"
                                                        class="px-1.5 py-0.5 rounded border border-gray-200 bg-white text-[11px] hover:bg-gray-100"
                                                        @click.prevent="applyFormatting(index, 'right', $event)">
                                                    ➝
                                                </button>
                                            </div>

                                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                                Texte
                                            </label>
                                            <div contenteditable="true"
                                                 class="w-full min-h-[90px] rounded-lg border border-gray-300 bg-white text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#647a0b] focus:border-[#647a0b]"
                                                 :style="`text-align:${block.text_align || 'left'};`"
                                                 x-init="$el.innerHTML = block.html || ''"
                                                 @input="onEditorInput(index, $event)">
                                            </div>
                                            <input type="hidden" :value="block.html || ''">
                                        </div>

                                        <div class="grid grid-cols-2 gap-3 mt-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                                    Taille du texte
                                                </label>
                                                <input type="text"
                                                       x-model="block.text_size"
                                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]"
                                                       placeholder="14px">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                                    Couleur du texte
                                                </label>
                                                <input type="color"
                                                       x-model="block.text_color"
                                                       class="w-full h-9 rounded border border-gray-300">
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                                Police
                                            </label>
                                            <select x-model="block.font_family"
                                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]">
                                                <option value="Montserrat">Montserrat</option>
                                                <option value="Arial">Arial</option>
                                                <option value="Georgia">Georgia</option>
                                                <option value="Times New Roman">Times New Roman</option>
                                                <option value="Verdana">Verdana</option>
                                            </select>
                                        </div>
                                    </div>
                                </template>

                                {{-- image --}}
                                <template x-if="block.type === 'image'">
                                    <div class="space-y-3">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                                    URL de l’image
                                                </label>
                                                <input type="text"
                                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]"
                                                       x-model="block.url">
                                            </div>
                                            <div class="flex flex-col items-start gap-1">
                                                <span class="block text-xs font-medium text-gray-600 mb-1">
                                                    Ou téléverser
                                                </span>
                                                <label class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-100 cursor-pointer">
                                                    <span>Choisir un fichier</span>
                                                    <input type="file"
                                                           class="hidden"
                                                           accept="image/*"
                                                           @change="uploadImage($event, index)">
                                                </label>
                                                <p class="text-[10px] text-gray-400">
                                                    JPG, PNG, max 4 Mo.
                                                </p>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                                Texte alternatif (alt)
                                            </label>
                                            <input type="text"
                                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]"
                                                   x-model="block.alt">
                                        </div>
                                    </div>
                                </template>

                                {{-- button --}}
                                <template x-if="block.type === 'button'">
                                    <div class="space-y-3">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                                    Texte du bouton
                                                </label>
                                                <input type="text"
                                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]"
                                                       x-model="block.label">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                                    Lien (URL)
                                                </label>
                                                <input type="text"
                                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]"
                                                       x-model="block.url">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3 mt-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                                    Taille du texte
                                                </label>
                                                <input type="text"
                                                       x-model="block.font_size"
                                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]"
                                                       placeholder="14px">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                                    Couleur du texte
                                                </label>
                                                <input type="color"
                                                       x-model="block.text_color"
                                                       class="w-full h-9 rounded border border-gray-300">
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                                Couleur du bouton
                                            </label>
                                            <input type="color"
                                                   x-model="block.background_color"
                                                   class="w-full h-9 rounded border border-gray-300">
                                        </div>
                                    </div>
                                </template>

                                {{-- divider --}}
                                <template x-if="block.type === 'divider'">
                                    <div class="text-center text-[11px] text-gray-400">
                                        Un séparateur sera affiché ici.
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- PREVIEW --}}
            <div class="hidden lg:block">
                <h2 class="text-sm font-semibold text-gray-800 mb-2">Aperçu en direct</h2>
                <p class="text-xs text-gray-500 mb-3">
                    Ceci est un aperçu visuel ; le rendu email final sera très proche.
                </p>
                <div class="bg-gray-100 py-4 flex justify-center">
                    <div class="w-full max-w-[420px] border border-gray-200 rounded-lg overflow-hidden shadow-sm"
                         :style="'background-color:' + (backgroundColor || '#ffffff')">
                        {{-- "Boîte mail" header look --}}
                        <div class="px-3 py-2 border-b border-gray-200 bg-gray-50">
                            <div class="text-[11px] text-gray-400 truncate" x-text="subjectLine || 'Objet de l’email'"></div>
                            <div class="text-[10px] text-gray-400 truncate"
                                 x-text="preheaderText || 'Pré-header (texte d’aperçu)'"></div>
                        </div>

                        {{-- Email header --}}
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                            <div class="text-xs text-gray-500 mb-1">
                                De&nbsp;: <span class="font-medium text-gray-800" x-text="senderName || 'Votre nom'"></span>
                            </div>
                            <div class="text-xs text-gray-500">
                                À&nbsp;: Prénom Nom
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="px-4 py-4 text-[13px] text-gray-700 leading-relaxed space-y-3">
                            <template x-for="(block, index) in blocks" :key="'preview-'+index">
                                <div>
                                    <template x-if="block.type === 'heading_text'">
                                        <div>
                                            <h1 class="mb-1 font-semibold"
                                                :style="`
                                                    font-size: ${block.heading_size || '22px'};
                                                    color: ${block.heading_color || '#111111'};
                                                    font-family: ${block.font_family || 'Montserrat'};
                                                    text-align: ${block.text_align || 'left'};
                                                `"
                                                x-text="block.heading || 'Titre'">
                                            </h1>

                                            <div class="whitespace-pre-line"
                                                 :style="`
                                                    font-size: ${block.text_size || '14px'};
                                                    color: ${block.text_color || '#333333'};
                                                    font-family: ${block.font_family || 'Montserrat'};
                                                    text-align: ${block.text_align || 'left'};
                                               `"
                                               x-html="block.html || ''">
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="block.type === 'text'">
                                        <div class="whitespace-pre-line"
                                             :style="`
                                                font-size: ${block.text_size || '14px'};
                                                color: ${block.text_color || '#333333'};
                                                font-family: ${block.font_family || 'Montserrat'};
                                                text-align: ${block.text_align || 'left'};
                                           `"
                                             x-html="block.html || ''">
                                        </div>
                                    </template>

                                    <template x-if="block.type === 'image' && block.url">
                                        <div class="mt-1 mb-1">
                                            <img :src="block.url" :alt="block.alt || ''" class="w-full rounded-md">
                                        </div>
                                    </template>

                                    <template x-if="block.type === 'button' && block.url">
                                        <div class="mt-2 mb-2 text-center">
                                            <a :href="block.url"
                                               class="inline-block px-4 py-1.5 rounded-full font-semibold"
                                               :style="`
                                                    font-size: ${block.font_size || '14px'};
                                                    color: ${block.text_color || '#ffffff'};
                                                    background-color: ${block.background_color || '#647a0b'};
                                               `"
                                               x-text="block.label || 'En savoir plus'">
                                            </a>
                                        </div>
                                    </template>

                                    <template x-if="block.type === 'divider'">
                                        <hr class="my-3 border-gray-200">
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- Footer --}}
                        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                            <div class="text-[11px] text-gray-500">
                                Vous recevez cet email car vous êtes suivi(e) par
                                <span class="font-medium" x-text="senderName || 'Votre nom'"></span>.
                            </div>
                            <div class="text-[11px] text-gray-400 mt-1">
                                Lien de désabonnement affiché ici dans l’email réel.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> {{-- fin grid editor/preview --}}
    </div>

    <div class="mt-4 flex justify-end">
        <button type="submit"
                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm"
                style="background-color:#647a0b;">
            {{ $isEdit ? 'Enregistrer les modifications' : 'Créer la newsletter' }}
        </button>
    </div>

    <script>
        function newsletterEditor() {
            return {
                blocks: [],
                contentJson: '[]',
                senderName: '',
                subjectLine: '',
                preheaderText: '',
                backgroundColor: '#ffffff',

                init() {
                    let rawBlocks = this.$el.getAttribute('data-initial-blocks') || '[]';
                    let parsedBlocks = [];

                    try {
                        parsedBlocks = JSON.parse(rawBlocks);
                    } catch (e) {
                        parsedBlocks = [];
                    }

                    if (Array.isArray(parsedBlocks) && parsedBlocks.length > 0) {
                        this.blocks = parsedBlocks.map(b => {
                            if ((b.type === 'heading_text' || b.type === 'text') && !b.html) {
                                const plain = b.text || '';
                                b.html = plain.replace(/\n/g, '<br>');
                            }
                            if (b.text_align === undefined) {
                                b.text_align = 'left';
                            }
                            return b;
                        });
                    } else {
                        this.blocks = [
                            {
                                type: 'heading_text',
                                heading: 'Titre de votre newsletter',
                                text: 'Bonjour,\n\nVoici les dernières nouvelles de votre cabinet...',
                                html: 'Bonjour,<br><br>Voici les dernières nouvelles de votre cabinet...',
                                heading_size: '22px',
                                heading_color: '#111111',
                                text_size: '14px',
                                text_color: '#333333',
                                font_family: 'Montserrat',
                                text_align: 'left',
                            }
                        ];
                    }

                    this.senderName      = this.$el.getAttribute('data-initial-from-name') || '';
                    this.subjectLine     = this.$el.getAttribute('data-initial-subject') || '';
                    this.preheaderText   = this.$el.getAttribute('data-initial-preheader') || '';
                    this.backgroundColor = this.$el.getAttribute('data-initial-bg') || '#ffffff';

                    this.syncJson();
                    this.$watch('blocks', () => this.syncJson());
                },

                syncJson() {
                    this.contentJson = JSON.stringify(this.blocks);
                },

                addBlock(type) {
                    let block = { type };

                    if (type === 'heading_text') {
                        block.heading = 'Titre';
                        block.text = '';
                        block.html = '';
                        block.heading_size = '22px';
                        block.heading_color = '#111111';
                        block.text_size = '14px';
                        block.text_color = '#333333';
                        block.font_family = 'Montserrat';
                        block.text_align = 'left';
                    } else if (type === 'text') {
                        block.text = '';
                        block.html = '';
                        block.text_size = '14px';
                        block.text_color = '#333333';
                        block.font_family = 'Montserrat';
                        block.text_align = 'left';
                    } else if (type === 'image') {
                        block.url = '';
                        block.alt = '';
                    } else if (type === 'button') {
                        block.label = 'En savoir plus';
                        block.url = '';
                        block.font_size = '14px';
                        block.text_color = '#ffffff';
                        block.background_color = '#647a0b';
                    }

                    this.blocks.push(block);
                },

                removeBlock(index) {
                    this.blocks.splice(index, 1);
                },

                moveUp(index) {
                    if (index === 0) return;
                    const tmp = this.blocks[index - 1];
                    this.blocks[index - 1] = this.blocks[index];
                    this.blocks[index] = tmp;
                },

                moveDown(index) {
                    if (index === this.blocks.length - 1) return;
                    const tmp = this.blocks[index + 1];
                    this.blocks[index + 1] = this.blocks[index];
                    this.blocks[index] = tmp;
                },

                labelFor(type) {
                    switch (type) {
                        case 'heading_text': return 'Titre & texte';
                        case 'text': return 'Texte';
                        case 'image': return 'Image';
                        case 'button': return 'Bouton';
                        case 'divider': return 'Séparateur';
                        default: return type;
                    }
                },

                onEditorInput(index, event) {
                    const html = event.target.innerHTML;
                    this.blocks[index].html = html;

                    // version texte brute si besoin ailleurs
                    this.blocks[index].text = html
                        .replace(/<br\s*\/?>/gi, '\n')
                        .replace(/<\/(p|div)>/gi, '\n')
                        .replace(/<[^>]+>/g, '');
                },

                applyFormatting(idx, format, event) {
                    const container = event.target.closest('[data-textblock]');
                    if (!container) return;
                    const editor = container.querySelector('[contenteditable]');
                    if (!editor) return;

                    editor.focus();

                    let cmd = null;

                    if (format === 'bold') cmd = 'bold';
                    else if (format === 'italic') cmd = 'italic';
                    else if (format === 'underline') cmd = 'underline';
                    else if (format === 'left') cmd = 'justifyLeft';
                    else if (format === 'center') cmd = 'justifyCenter';
                    else if (format === 'right') cmd = 'justifyRight';

                    if (cmd) {
                        try {
                            document.execCommand(cmd, false, null);
                        } catch (e) {
                            console.warn('execCommand non supporté', e);
                        }
                    }

                    this.blocks[idx].html = editor.innerHTML;

                    if (!this.blocks[idx].text_align) {
                        this.blocks[idx].text_align = 'left';
                    }
                    if (['left', 'center', 'right'].includes(format)) {
                        this.blocks[idx].text_align = format;
                    }
                },

                async uploadImage(event, index) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    formData.append('_token', token);
                    formData.append('image', file);

                    try {
                        const response = await fetch('{{ route('newsletters.upload-image') }}', {
                            method: 'POST',
                            body: formData,
                        });

                        if (!response.ok) {
                            throw new Error('Erreur lors de l’upload');
                        }

                        const data = await response.json();
                        if (data.url) {
                            this.blocks[index].url = data.url;
                        } else {
                            alert('Upload réussi, mais aucune URL retournée.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Impossible de téléverser l’image. Réessayez plus tard.');
                    } finally {
                        event.target.value = '';
                    }
                }
            }
        }
    </script>
</form>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            Éditer l’article
        </h2>
    </x-slot>

    {{-- Quill --}}
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

    <style>
        /* Medium-ish typography for editor content */
        .medium-shell { max-width: 860px; }

        .ql-toolbar.ql-snow {
            border-radius: 14px 14px 0 0;
            border-color: #edf1df;
        }
        .ql-container.ql-snow {
            border-radius: 0 0 14px 14px;
            border-color: #edf1df;
            min-height: 520px;
        }
        .ql-editor {
            min-height: 520px;
            font-size: 18px;
            line-height: 1.75;
            padding: 22px 22px;
        }
        .ql-editor h1 { font-size: 34px; line-height: 1.2; margin: 18px 0 10px; }
        .ql-editor h2 { font-size: 26px; line-height: 1.25; margin: 18px 0 10px; }
        .ql-editor p  { margin: 10px 0; }
        .ql-editor blockquote {
            border-left: 4px solid #8ea633;
            padding-left: 14px;
            color: #374151;
        }
        .ql-editor img { border-radius: 14px; border: 1px solid #edf1df; }

        /* Buttons */
        .btn-primary {
            background: #8ea633;
            color: white;
        }
        .btn-primary:hover { filter: brightness(0.98); }
        .btn-soft {
            border: 1px solid #edf1df;
            background: white;
            color: #647a0b;
        }
        .btn-soft:hover { background: #f7f9ef; }

        /* Subtle focus */
        .field {
            border: 1px solid #edf1df;
            border-radius: 12px;
        }
        .field:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(142, 166, 51, 0.18);
        }

        /* Sticky “Medium-like” top bar */
        .stickybar {
            position: sticky;
            top: 0;
            z-index: 30;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            background: rgba(255,255,255,0.86);
            border-bottom: 1px solid #edf1df;
        }

        /* Hide Quill tooltip edges on small widths */
        .ql-tooltip { border-radius: 12px; }

        /* Tiny status dot */
        .dot { width: 10px; height: 10px; border-radius: 999px; display: inline-block; }
    </style>

    <div class="py-0">
        {{-- Sticky action bar (Medium-ish) --}}
        <div class="stickybar">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex items-center justify-between py-3 px-4 sm:px-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <a href="{{ route('dashboardpro.articles.index') }}"
                           class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold btn-soft">
                            ← Retour
                        </a>

                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                @php
                                    $isPublished = ($article->status === 'published');
                                @endphp
                                <span class="dot {{ $isPublished ? 'bg-[#8ea633]' : 'bg-gray-300' }}"></span>
                                <span class="text-sm font-semibold text-gray-900 truncate">
                                    {{ $article->title ?: 'Sans titre' }}
                                </span>
                                <span class="text-xs text-gray-500 hidden sm:inline">
                                    · {{ $article->reading_time ?? 1 }} min
                                    · {{ $article->updated_at?->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 truncate">
                                /pro/{{ auth()->user()->slug }}/article/{{ $article->slug }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('dashboardpro.articles.show', $article) }}"
                           class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold btn-soft">
                            Aperçu
                        </a>

                        @if($article->status === 'published')
                            <a href="{{ route('pro.articles.show', ['therapist' => auth()->user()->slug, 'articleSlug' => $article->slug]) }}"
                               target="_blank"
                               class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold btn-soft">
                                Voir public
                            </a>
                        @endif

                        <button type="button"
                                id="saveBtn"
                                class="inline-flex items-center justify-center rounded-lg px-5 py-2 text-sm font-semibold shadow btn-primary">
                            Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                @if(session('success'))
                    <div class="mb-5 bg-white shadow rounded-2xl p-4 border border-[#edf1df]">
                        <div class="text-[#647a0b] font-semibold">{{ session('success') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 bg-white shadow rounded-2xl p-4 border border-red-200">
                        <div class="font-semibold text-red-700 mb-2">Erreur</div>
                        <ul class="list-disc ml-5 text-sm text-red-700">
                            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form id="articleForm"
                      method="POST"
                      action="{{ route('dashboardpro.articles.update', $article) }}"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                        {{-- Main writing area (Medium-like) --}}
                        <div class="lg:col-span-8">
                            <div class="bg-white shadow rounded-2xl border border-[#edf1df] overflow-hidden">
                                <div class="p-6 pb-4 border-b border-[#edf1df]">
                                    <div class="text-xs text-gray-500 mb-2">Titre</div>

                                    {{-- Big title input like Medium --}}
                                    <input id="title"
                                           name="title"
                                           value="{{ old('title', $article->title) }}"
                                           required
                                           class="w-full text-3xl font-bold tracking-tight text-gray-900 border-0 focus:ring-0 p-0"
                                           style="outline: none; box-shadow: none;"
                                           placeholder="Écrivez un titre…">

                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        {{-- Status pill --}}
                                        @if($article->status === 'published')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#8ea633]/15 text-[#647a0b] text-xs font-semibold">
                                                Publié
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">
                                                Brouillon
                                            </span>
                                        @endif

                                        <span class="text-xs text-gray-500">
                                            Temps de lecture estimé: <span class="font-semibold">{{ $article->reading_time ?? 1 }}</span> min
                                        </span>

                                        <span class="text-xs text-gray-500">
                                            Vues: <span class="font-semibold">{{ $article->views ?? 0 }}</span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Editor --}}
                                <div class="p-6 pt-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="text-sm font-semibold text-[#647a0b]">Rédaction</div>
                                        <div id="saveState" class="text-xs text-gray-500">Non enregistré</div>
                                    </div>

                                    <div id="editor"></div>

                                    {{-- Hidden fields saved to DB --}}
                                    <input type="hidden" name="content_html" id="content_html" value="{{ old('content_html', $article->content_html) }}">
                                    <input type="hidden" name="content_json" id="content_json" value="{{ old('content_json', $article->content_json) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Right sidebar (Medium settings / SEO / cover) --}}
                        <div class="lg:col-span-4 space-y-6">

                            {{-- Publish panel --}}
                            <div class="bg-white shadow rounded-2xl p-6 border border-[#edf1df]">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-semibold text-[#647a0b]">Publication</div>
                                    <a href="{{ route('dashboardpro.articles.show', $article) }}" class="text-xs font-semibold text-[#647a0b] hover:underline">
                                        Aperçu
                                    </a>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Statut</label>
                                        <select name="status"
                                                class="w-full px-3 py-2 bg-white field focus:outline-none"
                                                style="border-color:#edf1df;">
                                            <option value="draft" @selected(old('status', $article->status) === 'draft')>Brouillon</option>
                                            <option value="published" @selected(old('status', $article->status) === 'published')>Publié</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Date de publication</label>
                                        <input type="datetime-local"
                                               name="published_at"
                                               value="{{ old('published_at', $article->published_at ? $article->published_at->format('Y-m-d\TH:i') : '') }}"
                                               class="w-full px-3 py-2 field focus:outline-none"
                                               style="border-color:#edf1df;">
                                        <div class="text-xs text-gray-500 mt-1">Si publié et vide → maintenant.</div>
                                    </div>

                                    <div class="pt-2 flex gap-2">
                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold shadow btn-primary">
                                            Enregistrer
                                        </button>

                                        <button type="button"
                                                id="quickSaveBtn"
                                                class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold btn-soft">
                                            Ctrl+S
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Cover --}}
                            <div class="bg-white shadow rounded-2xl p-6 border border-[#edf1df]">
                                <div class="text-sm font-semibold text-[#647a0b]">Couverture</div>

                                <div class="mt-4">
                                    <input id="cover"
                                           type="file"
                                           name="cover"
                                           accept="image/*"
                                           class="w-full px-3 py-2 bg-white field focus:outline-none"
                                           style="border-color:#edf1df;">

                                    @if($article->cover_path)
                                        <div class="mt-4">
                                            <img class="rounded-2xl border border-[#edf1df] w-full object-cover"
                                                 style="max-height: 200px;"
                                                 src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($article->cover_path) }}"
                                                 alt="">
                                            <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-700">
                                                <input type="checkbox" name="remove_cover" value="1">
                                                Supprimer la couverture
                                            </label>
                                        </div>
                                    @endif

                                    <div id="coverPreviewWrap" class="mt-4 hidden">
                                        <div class="text-xs text-gray-500 mb-2">Nouvelle couverture</div>
                                        <img id="coverPreview" class="rounded-2xl border border-[#edf1df] w-full object-cover" style="max-height: 200px;" alt="">
                                    </div>
                                </div>
                            </div>

                            {{-- SEO --}}
                            <div class="bg-white shadow rounded-2xl p-6 border border-[#edf1df]">
                                <div class="text-sm font-semibold text-[#647a0b]">SEO</div>

                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Slug (URL)</label>
                                        <input id="slug"
                                               name="slug"
                                               value="{{ old('slug', $article->slug) }}"
                                               class="w-full px-3 py-2 field focus:outline-none"
                                               style="border-color:#edf1df;"
                                               placeholder="ex: huiles-essentielles-sommeil">
                                        <div class="text-xs text-gray-500 mt-1">Unique dans votre blog.</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Meta description</label>
                                        <textarea name="meta_description"
                                                  maxlength="180"
                                                  rows="3"
                                                  class="w-full px-3 py-2 field focus:outline-none"
                                                  style="border-color:#edf1df;"
                                                  placeholder="Max 180 caractères">{{ old('meta_description', $article->meta_description) }}</textarea>

                                        @php
                                            $md = old('meta_description', $article->meta_description);
                                            $mdCount = mb_strlen((string)$md);
                                        @endphp
                                        <div class="text-xs text-gray-500 mt-1">
                                            <span id="metaCount">{{ $mdCount }}</span>/180
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tags</label>
                                        <input name="tags"
                                               value="{{ old('tags', is_array($article->tags) ? implode(', ', $article->tags) : '') }}"
                                               class="w-full px-3 py-2 field focus:outline-none"
                                               style="border-color:#edf1df;"
                                               placeholder="ex: sommeil, stress, digestion">
                                        <div class="text-xs text-gray-500 mt-1">Sépare par des virgules.</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Excerpt --}}
                            <div class="bg-white shadow rounded-2xl p-6 border border-[#edf1df]">
                                <div class="text-sm font-semibold text-[#647a0b]">Extrait</div>
                                <div class="mt-4">
                                    <textarea name="excerpt"
                                              rows="4"
                                              class="w-full px-3 py-2 field focus:outline-none"
                                              style="border-color:#edf1df;"
                                              placeholder="Résumé court affiché dans la liste d’articles…">{{ old('excerpt', $article->excerpt) }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('articleForm');

            const titleInput = document.getElementById('title');
            const slugInput  = document.getElementById('slug');

            const coverInput = document.getElementById('cover');
            const coverWrap  = document.getElementById('coverPreviewWrap');
            const coverImg   = document.getElementById('coverPreview');

            const hiddenHtml = document.getElementById('content_html');
            const hiddenJson = document.getElementById('content_json');

            const saveBtn = document.getElementById('saveBtn');
            const quickSaveBtn = document.getElementById('quickSaveBtn');
            const saveState = document.getElementById('saveState');

            // Meta description live counter
            const metaTextarea = document.querySelector('textarea[name="meta_description"]');
            const metaCount = document.getElementById('metaCount');
            if (metaTextarea && metaCount) {
                metaTextarea.addEventListener('input', () => {
                    metaCount.textContent = (metaTextarea.value || '').length;
                });
            }

            function slugify(text) {
                return (text || '')
                    .toString()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            // Quill init
            const editor = new Quill('#editor', {
                theme: 'snow',
                modules: {
                    toolbar: {
                        container: [
                            [{ header: [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['blockquote', 'code-block'],
                            ['link', 'image'],
                            [{ align: [] }],
                            ['clean']
                        ],
                        handlers: {
                            image: () => uploadImageToServer()
                        }
                    }
                }
            });

            // Restore existing HTML
            if (hiddenHtml.value) {
                editor.clipboard.dangerouslyPasteHTML(hiddenHtml.value);
            }

            function syncContentFields() {
                hiddenHtml.value = editor.root.innerHTML;
                hiddenJson.value = JSON.stringify(editor.getContents());
            }

            // Save state UX (Medium-like)
            let isDirty = false;
            let dirtyTimer = null;

            function markDirty() {
                isDirty = true;
                saveState.textContent = 'Modifications non enregistrées';
                if (dirtyTimer) clearTimeout(dirtyTimer);
                dirtyTimer = setTimeout(() => {
                    // gentle hint after pause
                    if (isDirty) saveState.textContent = 'Pensez à enregistrer';
                }, 3500);
            }

            editor.on('text-change', () => {
                markDirty();
            });

            titleInput.addEventListener('input', () => {
                markDirty();
            });

            // Slug auto only if user didn't touch it
            let slugTouched = false;
            slugInput.addEventListener('input', () => { slugTouched = true; markDirty(); });

            titleInput.addEventListener('input', () => {
                if (slugTouched) return;
                slugInput.value = slugify(titleInput.value);
            });

            // Cover preview
            coverInput.addEventListener('change', () => {
                markDirty();
                const f = coverInput.files && coverInput.files[0];
                if (!f) {
                    coverWrap.classList.add('hidden');
                    coverImg.src = '';
                    return;
                }
                coverImg.src = URL.createObjectURL(f);
                coverWrap.classList.remove('hidden');
            });

            // Upload image
            async function uploadImageToServer() {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.click();

                input.onchange = async () => {
                    const file = input.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('image', file);

                    const res = await fetch('{{ route('dashboardpro.articles.upload_image') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    if (!res.ok) {
                        alert('Erreur upload image.');
                        return;
                    }

                    const data = await res.json();
                    const range = editor.getSelection(true);
                    editor.insertEmbed(range.index, 'image', data.url);
                    markDirty();
                };
            }

            // Submit: MUST sync hidden fields
            function doSubmit() {
                syncContentFields();
                isDirty = false;
                saveState.textContent = 'Enregistrement…';
                form.submit();
            }

            form.addEventListener('submit', (e) => {
                syncContentFields();
            });

            // Top save button triggers submit
            saveBtn.addEventListener('click', () => {
                doSubmit();
            });

            // Ctrl+S (Medium-like)
            document.addEventListener('keydown', (e) => {
                const isMac = navigator.platform.toUpperCase().includes('MAC');
                if ((isMac ? e.metaKey : e.ctrlKey) && e.key.toLowerCase() === 's') {
                    e.preventDefault();
                    doSubmit();
                }
            });

            quickSaveBtn.addEventListener('click', () => {
                doSubmit();
            });

            // beforeunload warning if dirty
            window.addEventListener('beforeunload', (e) => {
                if (!isDirty) return;
                e.preventDefault();
                e.returnValue = '';
            });
        });
    </script>
</x-app-layout>

{{-- resources/views/session_notes/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Créer une note de séance') }}
        </h2>
    </x-slot>

    {{-- Quill CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">

    <div class="max-w-6xl mx-auto py-8 px-4">
        @if($errors->any())
            <div class="mb-4 rounded-lg bg-red-100 border border-red-200 text-red-800 px-4 py-3 text-sm">
                <div class="font-bold mb-1">{{ __('Erreurs') }}</div>
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="am-card">
            <div class="am-head">
                <div>
                    <h1 class="am-title">{{ __('Nouvelle note de séance') }}</h1>
                    <p class="am-sub">
                        {{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
                    </p>
                </div>

                <div class="am-actions">
                    <a href="{{ route('session_notes.index', $clientProfile->id) }}" class="am-btn am-btn-soft">
                        {{ __('Retour') }}
                    </a>
                </div>
            </div>

            <div class="am-body">
                <form action="{{ route('session_notes.store', $clientProfile->id) }}" method="POST" id="session-note-form">
                    @csrf

                    @php
                        $templates = $templates ?? collect();
                        // Build a safe JS map: id => html
                        $templatesMap = $templates->map(function($t){
                            return [
                                'id' => $t->id,
                                'title' => $t->title,
                                'content' => $t->content ?? '',
                            ];
                        })->values();
                    @endphp

                    {{-- Templates --}}
                    <div class="am-field">
                        <label class="am-label">{{ __('Template') }}</label>

                        <div class="am-inline-row">
                            <select id="template-select" class="am-input" style="min-width:280px;">
                                <option value="">{{ __('Aucun template') }}</option>
                                @foreach($templates as $t)
                                    <option value="{{ $t->id }}">
                                        {{ $t->title }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="button" id="apply-template" class="am-btn am-btn-soft">
                                {{ __('Appliquer') }}
                            </button>

                            <a href="{{ route('session-note-templates.index') }}" class="am-btn am-btn-soft">
                                {{ __('Gérer') }}
                            </a>
                        </div>

                        <input type="hidden" name="session_note_template_id" id="template-id-input" value="{{ old('session_note_template_id') }}">

                        <p class="am-help">
                            {{ __('Astuce : “Appliquer” remplace le contenu actuel. Pratique pour démarrer vite.') }}
                        </p>
                    </div>

                    {{-- Note --}}
                    <div class="am-field">
                        <label class="am-label">{{ __('Note') }}</label>

                        <input type="hidden" name="note" id="note-input" value="{{ old('note') }}">

                        <noscript>
                            <textarea name="note" class="am-textarea" rows="10" placeholder="Rédigez votre note ici...">{{ old('note') }}</textarea>
                        </noscript>

                        <div id="quill-editor" class="am-quill"></div>

                        @error('note')
                            <p class="am-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="am-footer">
                        <button type="submit" class="am-btn am-btn-primary">
                            {{ __('Créer la note') }}
                        </button>
                        <a href="{{ route('session_notes.index', $clientProfile->id) }}" class="am-btn am-btn-soft">
                            {{ __('Annuler') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Quill JS --}}
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const templates = @json($templatesMap);

            const quill = new Quill('#quill-editor', {
                theme: 'snow',
                placeholder: 'Rédigez votre note de séance ici…',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered'}, { list: 'bullet' }],
                        [{ align: [] }],
                        ['blockquote', 'code-block'],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });

            const noteInput = document.getElementById('note-input');
            const form = document.getElementById('session-note-form');
            const templateSelect = document.getElementById('template-select');
            const applyBtn = document.getElementById('apply-template');
            const templateIdInput = document.getElementById('template-id-input');

            function syncNote() {
                noteInput.value = quill.root.innerHTML;
            }

            // Load old(note)
            const oldNote = @json(old('note', ''));
            if (oldNote) {
                // If looks like HTML, paste HTML into Quill properly
                if (oldNote.includes('<') && oldNote.includes('>')) {
                    quill.setContents([]); // clear
                    quill.clipboard.dangerouslyPasteHTML(oldNote);
                } else {
                    quill.setText(oldNote);
                }
                syncNote();
            } else {
                syncNote();
            }

            quill.on('text-change', syncNote);
            form.addEventListener('submit', syncNote);

            // Restore old selected template if any
            const oldTemplateId = templateIdInput.value;
            if (oldTemplateId) {
                for (let i = 0; i < templateSelect.options.length; i++) {
                    if (templateSelect.options[i].value === oldTemplateId) {
                        templateSelect.selectedIndex = i;
                        break;
                    }
                }
            }

            // Apply template (the correct way)
            applyBtn.addEventListener('click', function () {
                const id = templateSelect.value || '';
                templateIdInput.value = id;

                if (!id) return;

                const tpl = templates.find(t => String(t.id) === String(id));
                const html = (tpl && tpl.content) ? tpl.content : '';

                const currentText = (quill.getText() || '').trim();
                if (currentText.length > 0) {
                    const ok = confirm('Appliquer ce template va remplacer le contenu actuel. Continuer ?');
                    if (!ok) return;
                }

                quill.setContents([]); // clear
                quill.clipboard.dangerouslyPasteHTML(html);
                syncNote();
            });
        });
    </script>

    <style>
        .am-card{
            background:#fff;border-radius:14px;box-shadow:0 6px 20px rgba(15,23,42,.08);
            border:1px solid rgba(100,122,11,.15); overflow:hidden;
        }
        .am-head{
            display:flex;gap:16px;align-items:flex-end;justify-content:space-between;
            padding:18px;border-bottom:1px solid rgba(15,23,42,.08);flex-wrap:wrap;
        }
        .am-title{font-size:22px;font-weight:900;color:#0f172a;margin:0;}
        .am-sub{margin:6px 0 0;color:#6b7280;font-size:13px;}
        .am-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
        .am-body{padding:18px;}

        .am-field{background:#f8fafc;border:1px solid rgba(15,23,42,.08);border-radius:14px;padding:14px;margin-bottom:14px;}
        .am-label{display:block;font-weight:900;color:#0f172a;margin-bottom:8px;}
        .am-help{margin-top:8px;color:#6b7280;font-size:12px;}
        .am-error{margin-top:8px;color:#dc2626;font-size:12px;font-weight:700;}

        .am-inline-row{display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
        .am-input{
            padding:10px 12px;border-radius:10px;border:1px solid rgba(133,79,56,.35);
            outline:none;background:#fff;
        }
        .am-input:focus{border-color:#854f38; box-shadow:0 0 0 3px rgba(133,79,56,.12);}

        .am-textarea{
            width:100%;padding:10px 12px;border-radius:10px;border:1px solid rgba(133,79,56,.35);
            outline:none;background:#fff;
        }

        .am-btn{display:inline-flex;align-items:center;justify-content:center;padding:9px 12px;border-radius:10px;text-decoration:none;border:1px solid transparent;font-weight:800;font-size:13px;cursor:pointer;white-space:nowrap;}
        .am-btn-primary{background:#647a0b;color:#fff;}
        .am-btn-primary:hover{background:#854f38;}
        .am-btn-soft{background:#fff;color:#0f172a;border-color:rgba(15,23,42,.12);border-width:1px;border-style:solid;}
        .am-btn-soft:hover{border-color:rgba(133,79,56,.55);}

        .am-footer{display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;margin-top:14px;}

        .am-quill{background:#fff;border:1px solid rgba(15,23,42,.12);border-radius:12px;overflow:hidden;}
        #quill-editor .ql-editor{min-height:220px;font-size:16px;line-height:1.85;color:#0f172a;}
        #quill-editor .ql-toolbar{border:0;border-bottom:1px solid rgba(15,23,42,.12);}
    </style>
</x-app-layout>

{{-- resources/views/session_notes/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Modifier la note de séance') }}
        </h2>
    </x-slot>

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
                    <h1 class="am-title">{{ __('Modifier la note') }}</h1>
                    <p class="am-sub">
                        {{ __('Créée le') }} {{ optional($sessionNote->created_at)->format('d/m/Y') }}
                        @if($sessionNote->template?->title)
                            · {{ __('Template :') }} <span class="am-pill">{{ $sessionNote->template->title }}</span>
                        @endif
                    </p>
                </div>

                <div class="am-actions">
                    <a href="{{ route('session_notes.index', $sessionNote->client_profile_id) }}" class="am-btn am-btn-soft">
                        {{ __('Retour') }}
                    </a>
                </div>
            </div>

            <div class="am-body">
                <form action="{{ route('session_notes.update', $sessionNote->id) }}" method="POST" id="session-note-form">
                    @csrf
                    @method('PUT')

                    <div class="am-field">
                        <label class="am-label">{{ __('Note') }}</label>

                        <input type="hidden" name="note" id="note-input" value="{{ old('note', $sessionNote->note) }}">

                        <noscript>
                            <textarea name="note" class="am-textarea" rows="10">{{ old('note', $sessionNote->note) }}</textarea>
                        </noscript>

                        <div id="quill-editor" class="am-quill"></div>

                        @error('note')
                            <p class="am-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="am-footer">
                        <button type="submit" class="am-btn am-btn-primary">
                            {{ __('Enregistrer') }}
                        </button>
                        <a href="{{ route('session_notes.show', $sessionNote->id) }}" class="am-btn am-btn-soft">
                            {{ __('Voir') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const quill = new Quill('#quill-editor', {
                theme: 'snow',
                placeholder: 'Modifiez votre note…',
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

            function syncNote() {
                noteInput.value = quill.root.innerHTML;
            }

            const existing = @json(old('note', $sessionNote->note ?? ''));

            // If DB contains HTML => paste as HTML (prevents showing tags as text)
            if (existing) {
                if (existing.includes('<') && existing.includes('>')) {
                    quill.setContents([]);
                    quill.clipboard.dangerouslyPasteHTML(existing);
                } else {
                    quill.setText(existing);
                }
                syncNote();
            } else {
                syncNote();
            }

            quill.on('text-change', syncNote);
            form.addEventListener('submit', syncNote);
        });
    </script>

    <style>
        .am-card{background:#fff;border-radius:14px;box-shadow:0 6px 20px rgba(15,23,42,.08);border:1px solid rgba(100,122,11,.15);overflow:hidden;}
        .am-head{display:flex;gap:16px;align-items:flex-end;justify-content:space-between;padding:18px;border-bottom:1px solid rgba(15,23,42,.08);flex-wrap:wrap;}
        .am-title{font-size:22px;font-weight:900;color:#0f172a;margin:0;}
        .am-sub{margin:6px 0 0;color:#6b7280;font-size:13px;}
        .am-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
        .am-body{padding:18px;}

        .am-pill{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;background:rgba(100,122,11,.12);color:#0f172a;font-weight:900;}

        .am-field{background:#f8fafc;border:1px solid rgba(15,23,42,.08);border-radius:14px;padding:14px;}
        .am-label{display:block;font-weight:900;color:#0f172a;margin-bottom:8px;}
        .am-error{margin-top:8px;color:#dc2626;font-size:12px;font-weight:700;}

        .am-textarea{width:100%;padding:10px 12px;border-radius:10px;border:1px solid rgba(133,79,56,.35);outline:none;background:#fff;}

        .am-btn{display:inline-flex;align-items:center;justify-content:center;padding:9px 12px;border-radius:10px;text-decoration:none;border:1px solid transparent;font-weight:800;font-size:13px;cursor:pointer;white-space:nowrap;}
        .am-btn-primary{background:#647a0b;color:#fff;}
        .am-btn-primary:hover{background:#854f38;}
        .am-btn-soft{background:#fff;color:#0f172a;border-color:rgba(15,23,42,.12);border-width:1px;border-style:solid;}
        .am-btn-soft:hover{border-color:rgba(133,79,56,.55);}

        .am-footer{display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;margin-top:14px;}

        .am-quill{background:#fff;border:1px solid rgba(15,23,42,.12);border-radius:12px;overflow:hidden;}
        #quill-editor .ql-editor{min-height:260px;font-size:16px;line-height:1.85;color:#0f172a;}
        #quill-editor .ql-toolbar{border:0;border-bottom:1px solid rgba(15,23,42,.12);}
    </style>
</x-app-layout>

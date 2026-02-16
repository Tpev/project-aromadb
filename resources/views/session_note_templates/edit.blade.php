<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Modifier un template') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">Modifier : {{ $template->title }}</h1>

            <form action="{{ route('session-note-templates.update', $template->id) }}" method="POST" id="template-form">
                @csrf
                @method('PUT')

                <div class="details-box">
                    <label class="details-label" for="title">Titre</label>
                    <input class="form-control" type="text" name="title" id="title" value="{{ old('title', $template->title) }}" required>
                    @error('title') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="details-box">
                    <label class="details-label">Contenu</label>
                    <input type="hidden" name="content" id="content-input">
                    <div id="quill-editor">{!! old('content', $template->content) !!}</div>
                    @error('content') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn-primary mt-4">Mettre à jour</button>
                <a href="{{ route('session-note-templates.index') }}" class="btn-secondary mt-4">Retour</a>
            </form>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const quill = new Quill('#quill-editor', {
                theme: 'snow',
                placeholder: 'Modifiez votre template…',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered'}, { list: 'bullet' }],
                        ['blockquote', 'code-block'],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });

            const existing = `{!! old('content', addslashes($template->content ?? '')) !!}`;
            quill.root.innerHTML = existing;

            const input = document.getElementById('content-input');
            const form = document.getElementById('template-form');

            function sync() { input.value = quill.root.innerHTML; }
            quill.on('text-change', sync);
            form.addEventListener('submit', sync);
            sync();
        });
    </script>

    <style>
        .container { max-width: 900px; }
        .details-container { background:#f9f9f9; border-radius:10px; padding:30px; box-shadow:0 5px 15px rgba(0,0,0,.1); margin:0 auto; }
        .details-title { font-size:2rem; font-weight:bold; color:#647a0b; margin-bottom:20px; text-align:center; }
        .details-box { margin-bottom:15px; }
        .details-label { font-weight:bold; color:#647a0b; display:block; margin-bottom:5px; }
        #quill-editor { height: 260px; background:#fff; border:1px solid #ccc; border-radius:5px; }
        .btn-primary{ background:#647a0b; color:#fff; padding:10px 20px; border:none; border-radius:5px; text-decoration:none; display:inline-block; cursor:pointer; }
        .btn-primary:hover{ background:#854f38; }
        .btn-secondary{ background:transparent; color:#854f38; padding:10px 20px; border:1px solid #854f38; border-radius:5px; text-decoration:none; display:inline-block; }
        .btn-secondary:hover{ background:#854f38; color:#fff; }
        .text-red-500 { color:#e3342f; font-size:.875rem; }
    </style>
</x-app-layout>

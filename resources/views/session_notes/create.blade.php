<!-- resources/views/session_notes/create.blade.php -->

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer une note de session') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouvelle Note de Session pour ') }}{{ $clientProfile->first_name }} {{ $clientProfile->last_name }}</h1>

            <form action="{{ route('session_notes.store', $clientProfile->id) }}" method="POST" id="session-note-form">
                @csrf

                <!-- Note -->
                <div class="details-box">
                    <label class="details-label" for="note">{{ __('Note') }}</label>
                    <!-- Hidden input to store the HTML content -->
                    <input type="hidden" name="note" id="note-input">
                    <!-- Quill Editor Container -->
                    <div id="quill-editor">{!! old('note') !!}</div>
                    @error('note')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary mt-4">{{ __('Créer la Note') }}</button>
                <a href="{{ route('session_notes.index', $clientProfile->id) }}" class="btn-secondary mt-4">{{ __('Retour à la liste') }}</a>
            </form>
        </div>
    </div>

    <!-- Quill.js CDN -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <!-- Initialiser Quill -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var quill = new Quill('#quill-editor', {
                theme: 'snow',
                placeholder: 'Rédigez votre note de session ici...',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['blockquote', 'code-block'],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });

            // Charger le contenu précédent si disponible
            @if(old('note'))
                quill.root.innerHTML = `{!! addslashes(old('note')) !!}`;
            @endif

            // Fonction pour mettre à jour le champ caché
            function updateHiddenInput() {
                var noteInput = document.querySelector('#note-input');
                noteInput.value = quill.root.innerHTML;
                console.log('Note Input Updated:', noteInput.value); // Debugging
            }

            // Mettre à jour le champ caché avant la soumission du formulaire
            var form = document.querySelector('#session-note-form');
            form.addEventListener('submit', function() {
                updateHiddenInput();
            });

            // Optionnel : Mettre à jour le champ caché à chaque changement
            quill.on('text-change', function() {
                updateHiddenInput();
            });
        });
    </script>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 800px;
        }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .details-title {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .details-box {
            margin-bottom: 15px;
        }

        .details-label {
            font-weight: bold;
            color: #647a0b;
            display: block;
            margin-bottom: 5px;
        }

        /* Style pour le Quill Editor */
        #quill-editor {
            height: 200px;
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            padding: 10px 20px;
            border: 1px solid #854f38;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
        }
    </style>
</x-app-layout>

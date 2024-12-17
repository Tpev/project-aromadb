{{-- resources/views/conseils/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Modifier le Conseil') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Titre de la Page -->
            <h1 class="text-3xl font-bold text-[#647a0b] text-center">
                {{ __('Modifier :') }} {{ $conseil->name }}
            </h1>

            <!-- Formulaire d'édition du Conseil -->
            <div class="bg-white shadow overflow-hidden rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('conseils.update', $conseil->id) }}" method="POST" enctype="multipart/form-data" id="conseil-form">
                    @csrf
                    @method('PUT')

                    <!-- Nom -->
                    <div class="mb-4">
                        <label class="block text-[#647a0b] font-semibold mb-2" for="name">{{ __('Nom du Conseil') }}</label>
                        <input type="text" name="name" id="name" 
                               class="border border-[#854f38] rounded-md w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-[#854f38]" 
                               value="{{ old('name', $conseil->name) }}" required>
                    </div>

                    <!-- Tag -->
                    <div class="mb-4">
                        <label class="block text-[#647a0b] font-semibold mb-2" for="tag">{{ __('Tag (facultatif)') }}</label>
                        <input type="text" name="tag" id="tag" 
                               class="border border-[#854f38] rounded-md w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-[#854f38]" 
                               value="{{ old('tag', $conseil->tag) }}">
                    </div>

                    <!-- Contenu (Rich Text) -->
                    <div class="mb-4">
                        <label class="block text-[#647a0b] font-semibold mb-2" for="content">{{ __('Contenu du Conseil') }}</label>
                        <p class="text-gray-600 text-sm mb-2">
                            {{ __('Modifiez le contenu du conseil ci-dessous. Vous pouvez mettre en forme le texte, insérer des images ou des liens, et organiser votre contenu pour le rendre plus clair.') }}
                        </p>
                        <!-- Hidden input to store the HTML content -->
                        <input type="hidden" name="content" id="content-input">

                        <!-- Quill Editor Container -->
                        <div id="quill-editor" class="border border-[#854f38] rounded-md" style="min-height: 200px;"></div>
                    </div>

                    <!-- Image actuelle (si disponible) -->
                    @if($conseil->image)
                        <div class="mb-4">
                            <p class="text-[#647a0b] font-semibold mb-2">{{ __('Image Actuelle') }}:</p>
                            <img src="{{ asset('storage/' . $conseil->image) }}" alt="{{ $conseil->name }}" class="w-48 h-auto rounded-md shadow mb-2">
                            <p class="text-gray-600 text-sm mb-2">
                                {{ __('Vous pouvez téléverser une nouvelle image pour remplacer l\'image actuelle, ou laisser ce champ vide pour conserver l\'actuelle.') }}
                            </p>
                        </div>
                    @endif

                    <!-- Nouvelle Image -->
                    <div class="mb-4">
                        <label class="block text-[#647a0b] font-semibold mb-2" for="image">{{ __('Nouvelle Image (facultatif)') }}</label>
                        <input type="file" name="image" id="image" 
                               class="border border-[#854f38] rounded-md w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-[#854f38]" 
                               accept="image/*">
                    </div>

                    <!-- Pièce Jointe Actuelle (si disponible) -->
                    @if($conseil->attachment)
                        <div class="mb-4">
                            <p class="text-[#647a0b] font-semibold mb-2">{{ __('Pièce Jointe Actuelle') }}:</p>
                            <a href="{{ asset('storage/' . $conseil->attachment) }}" target="_blank" class="inline-flex items-center text-[#647a0b] font-semibold hover:underline mb-2">
                                <!-- Icône PDF -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M16.5 2H7a2 2 0 00-2 2v12a2 2 0 002 2h9.5a1.5 1.5 0 001.5-1.5V3.5A1.5 1.5 0 0016.5 2zM5 4a2 2 0 00-2 2v12a2 2 0 002 2h.5V4H5z" />
                                </svg>
                                {{ __('Télécharger la pièce jointe (PDF)') }}
                            </a>
                            <p class="text-gray-600 text-sm mb-2">
                                {{ __('Vous pouvez téléverser un nouveau PDF pour remplacer l\'actuel, ou laisser ce champ vide pour conserver l\'actuel.') }}
                            </p>
                        </div>
                    @endif

                    <!-- Nouvelle Pièce Jointe (PDF) -->
                    <div class="mb-4">
                        <label class="block text-[#647a0b] font-semibold mb-2" for="attachment">{{ __('Nouvelle Pièce Jointe (PDF, facultatif)') }}</label>
                        <input type="file" name="attachment" id="attachment" 
                               class="border border-[#854f38] rounded-md w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-[#854f38]" 
                               accept="application/pdf">
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('conseils.index') }}" class="inline-block bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 transition duration-200">
                            {{ __('Annuler') }}
                        </a>
                        <button type="submit" class="bg-[#647a0b] text-white py-2 px-4 rounded-md hover:bg-[#854f38] transition duration-200">
                            {{ __('Mettre à Jour') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quill.js CDN -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var quill = new Quill('#quill-editor', {
                theme: 'snow',
                placeholder: 'Modifiez votre conseil ici...',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        [{ 'font': [] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['blockquote', 'code-block'],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });

            // Charger le contenu existant ou l'old input s'il existe
            @if(old('content'))
                quill.root.innerHTML = `{!! addslashes(old('content')) !!}`;
            @else
                quill.root.innerHTML = `{!! addslashes($conseil->content) !!}`;
            @endif

            // Mettre à jour le champ caché
            function updateHiddenInput() {
                var contentInput = document.querySelector('#content-input');
                contentInput.value = quill.root.innerHTML;
            }

            // Mettre à jour le champ caché avant la soumission du formulaire
            var form = document.querySelector('#conseil-form');
            form.addEventListener('submit', function() {
                updateHiddenInput();
            });

            // Optionnel : Mettre à jour le champ caché à chaque changement
            quill.on('text-change', function() {
                updateHiddenInput();
            });
        });
    </script>

    <!-- Styles Responsives -->
    <style>
        @media (max-width: 640px) {
            .flex.justify-end.space-x-4 {
                flex-direction: column;
                align-items: stretch;
            }

            .flex.justify-end.space-x-4 > a,
            .flex.justify-end.space-x-4 > button {
                margin-bottom: 0.5rem;
            }
        }
    </style>
</x-app-layout>

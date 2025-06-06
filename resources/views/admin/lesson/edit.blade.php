<x-app-layout>
    <div class="container mx-auto py-8">
        @if(session('success'))
            <div class="bg-green-200 text-green-800 p-4 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.lesson.update', $lesson->id) }}" method="POST">
            @csrf
            @method('PUT')
            <!-- Lesson Content Editor using Quill -->
            <div class="details-box mb-4">
                <label for="content" class="details-label block text-gray-700 font-semibold mb-2">
                    {{ __('Contenu de la Leçon') }}
                </label>

                <!-- Hidden input to store HTML output from Quill -->
                <input type="hidden" name="content" id="content-input" value="{{ old('content', $lesson->content) }}" />

                <!-- Quill Editor Container -->
                <div id="quill-editor" style="height: 300px;"></div>

                <!-- Helper Text -->
                <small class="text-gray-500">
                    Utilisez l'éditeur pour modifier le contenu qui sera affiché sur le site.
                </small>

                @error('content')
                    <p class="text-red-500 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary">
                Sauvegarder
            </button>
        </form>
    </div>

    <!-- Quill.js Styles and Script -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Quill
            var quill = new Quill('#quill-editor', {
                theme: 'snow',
                placeholder: 'Modifiez le contenu ici...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'header': 1 }, { 'header': 2 }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['blockquote', 'code-block'],
                        ['link'],
                        ['clean']
                    ]
                }
            });

            // Load existing content into Quill
            @if(old('content', $lesson->content))
                quill.root.innerHTML = `{!! addslashes(old('content', $lesson->content)) !!}`;
            @endif

            // Function to update hidden input
            function updateHiddenInput() {
                document.getElementById('content-input').value = quill.root.innerHTML;
            }

            // Update hidden input on every text change
            quill.on('text-change', function () {
                updateHiddenInput();
            });

            // Also update hidden input just before form submission
            var form = document.querySelector('form[action="{{ route('admin.lesson.update', $lesson->id) }}"]');
            form.addEventListener('submit', function () {
                updateHiddenInput();
            });
        });
    </script>
</x-app-layout>

{{-- resources/views/conseils/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Détails du Conseil') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Section Titre -->
            <div class="bg-white shadow overflow-hidden rounded-lg p-6">
                <h1 class="text-4xl font-bold text-[#647a0b] text-center mb-4">
                    {{ $conseil->name }}
                </h1>

                <!-- Meta Informations -->
                <div class="flex flex-col md:flex-row md:justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
                    <div class="flex flex-col">
                        @if($conseil->tag)
                            <span class="inline-block bg-[#647a0b] text-white text-xs font-semibold px-2 py-1 rounded-full mb-2">
                                {{ __('Tag :') }} {{ $conseil->tag }}
                            </span>
                        @endif

                        <span class="text-gray-600 text-sm">
                            {{ __('Créé le :') }} {{ $conseil->created_at->format('d/m/Y') }}
                        </span>
                    </div>

                    <!-- Boutons d'Actions -->
                    <div class="flex space-x-2">
                        <!-- Retour à la liste -->
                        <a href="{{ route('conseils.index') }}" class="inline-block bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 transition duration-200">
                            {{ __('Retour à la liste') }}
                        </a>

                        <!-- Modifier -->
                        <a href="{{ route('conseils.edit', $conseil->id) }}" class="inline-block bg-[#647a0b] text-white py-2 px-4 rounded-md hover:bg-[#854f38] transition duration-200">
                            {{ __('Modifier') }}
                        </a>

                        <!-- Supprimer (Formulaire) -->
                        <form action="{{ route('conseils.destroy', $conseil->id) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    onclick="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer ce conseil ?') }}')"
                                    class="bg-red-500 text-white py-2 px-4 rounded-md hover:bg-red-600 transition duration-200">
                                {{ __('Supprimer') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Image (si disponible) -->
                @if($conseil->image)
                    <div class="mb-6">
                        <img src="{{ asset('storage/' . $conseil->image) }}" alt="{{ $conseil->name }}" class="w-full h-auto rounded-lg shadow-md">
                    </div>
                @endif

                <!-- Contenu (Rich Text) -->
                <div class="prose max-w-none prose-lg prose-[#647a0b]">
                    {!! $conseil->content !!}
                </div>

                <!-- Pièce Jointe (PDF) -->
                @if($conseil->attachment)
                    <div class="mt-6">
                        <a href="{{ asset('storage/' . $conseil->attachment) }}" target="_blank" class="inline-flex items-center text-[#647a0b] font-semibold hover:underline">
                            <!-- Icône PDF -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M16.5 2H7a2 2 0 00-2 2v12a2 2 0 002 2h9.5a1.5 1.5 0 001.5-1.5V3.5A1.5 1.5 0 0016.5 2zM5 4a2 2 0 00-2 2v12a2 2 0 002 2h.5V4H5z" />
                            </svg>
                            {{ __('Télécharger la pièce jointe (PDF)') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Styles personnalisés -->
    <style>
        /* Styles pour la mise en forme du contenu riche */
        .prose {
            color: #333;
        }

        .prose h1, .prose h2, .prose h3 {
            color: #647a0b;
        }

        .prose a {
            color: #854f38;
            text-decoration: underline;
        }

        .prose blockquote {
            border-left: 4px solid #647a0b;
            padding-left: 1rem;
            font-style: italic;
            color: #555;
        }

        .prose code {
            background: #f9f9f9;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-size: 90%;
        }

        /* Responsive Adjustments */
        @media (max-width: 640px) {
            .md\:flex-row {
                flex-direction: column;
            }
            .md\:justify-between {
                justify-content: flex-start;
            }
            .md\:items-center {
                align-items: flex-start;
            }
            .md\:space-y-0 > :not([hidden]) ~ :not([hidden]) {
                margin-top: 1rem;
            }

            .flex.space-x-2 {
                flex-direction: column;
                space-y: 0;
                space-x-0;
            }

            .flex.space-x-2 > a,
            .flex.space-x-2 > form {
                margin-bottom: 0.5rem;
            }
        }
    </style>
</x-app-layout>

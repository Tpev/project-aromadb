<!-- resources/views/tisane/show.blade.php -->
<x-app-layout>
    {{-- En-tête de la page --}}
    <x-slot name="header">
        @section('title', 'Tisane ' .  $tisane->NomTisane . ' (' . $tisane->NomLatin . ')')
    </x-slot>

    {{-- Contenu principal --}}
    <div class="py-12 bg-gray-50">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Nom de la Tisane --}}
            <h1 class="text-4xl font-bold text-[#647a0b] text-center">
                {{ $tisane->NomTisane }}
            </h1>

            {{-- Bouton Favoris --}}
            <div class="text-center">
                @auth
                    <form id="favorite-form" method="POST" action="{{ route('favorites.toggle', ['type' => 'tisane', 'id' => $tisane->id]) }}">
                        @csrf
                        <button type="submit" class="btn-favorite" id="favorite-btn">
                            @if(auth()->user()->favorites->contains('favoritable_id', $tisane->id) && auth()->user()->favorites->contains('favoritable_type', 'App\Models\Tisane'))
                                <i class="fas fa-heart" style="color: #854f38;"></i> <span>Retirer des Favoris</span>
                            @else
                                <i class="far fa-heart" style="color: #854f38;"></i> <span>Ajouter aux Favoris</span>
                            @endif
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-favorite" id="favorite-btn">
                        <i class="far fa-heart" style="color: #854f38;"></i> <span>Ajouter aux Favoris</span>
                    </a>
                @endauth
            </div>

            {{-- Section principale --}}
            <div class="bg-white shadow rounded-lg p-8 flex flex-col md:flex-row items-center">
                {{-- Image --}}
                <div class="md:w-1/2 text-center md:pr-8">
                    <img src="{{ asset('images/default.webp') }}" alt="{{ $tisane->NomTisane }}" class="w-full h-auto rounded-lg shadow-md">
                </div>
                {{-- Informations principales --}}
                <div class="md:w-1/2 mt-6 md:mt-0">
                    <div class="space-y-6">
                        {{-- Nom Latin --}}
                        <div>
                            <h2 class="text-2xl font-bold text-[#647a0b] flex items-center">
                                <i class="fas fa-leaf mr-2 text-[#854f38]"></i> Nom Latin
                            </h2>
                            <p class="mt-2 text-gray-700 italic">{{ $tisane->NomLatin }}</p>
                        </div>
                        {{-- Provenance --}}
                        <div>
                            <h2 class="text-2xl font-bold text-[#647a0b] flex items-center">
                                <i class="fas fa-globe mr-2 text-[#854f38]"></i> Provenance
                            </h2>
                            <p class="mt-2 text-gray-700">{{ $tisane->Provenance }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Propriétés et Indications côte à côte --}}
            <div class="bg-white shadow rounded-lg p-8 flex flex-col md:flex-row space-y-8 md:space-y-0 md:space-x-8">
                {{-- Propriétés --}}
                <div class="md:w-1/2">
                    <h3 class="text-2xl font-semibold text-[#647a0b] flex items-center">
                        <i class="fas fa-capsules mr-2 text-[#854f38]"></i> Propriétés
                    </h3>
                    @php
                        $properties = explode(';', $tisane->Properties);
                    @endphp
                    @if(!empty($properties))
                        <ul class="mt-4 list-disc list-inside text-gray-700">
                            @foreach ($properties as $property)
                                <li>{{ trim($property) }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-4 text-gray-700">Aucune propriété spécifiée.</p>
                    @endif
                </div>
                {{-- Indications --}}
                <div class="md:w-1/2">
                    <h3 class="text-2xl font-semibold text-[#647a0b] flex items-center">
                        <i class="fas fa-stethoscope mr-2 text-[#854f38]"></i> Indications
                    </h3>
                    @php
                        $indications = explode(';', $tisane->Indications);
                    @endphp
                    @if(!empty($indications))
                        <ul class="mt-4 list-disc list-inside text-gray-700">
                            @foreach ($indications as $indication)
                                <li>{{ trim($indication) }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-4 text-gray-700">Aucune indication spécifiée.</p>
                    @endif
                </div>
            </div>

            {{-- Composition et Contre-indications côte à côte --}}
            <div class="bg-white shadow rounded-lg p-8 flex flex-col md:flex-row space-y-8 md:space-y-0 md:space-x-8">
                {{-- Composition --}}
                <div class="md:w-1/2">
                    <h3 class="text-2xl font-semibold text-[#647a0b] flex items-center">
                        <i class="fas fa-flask mr-2 text-[#854f38]"></i> Composition
                    </h3>
                    @php
                        $compositions = explode(';', $tisane->Sb);
                    @endphp
                    @if(!empty($compositions))
                        <ul class="mt-4 list-disc list-inside text-gray-700">
                            @foreach ($compositions as $composition)
                                <li>{{ trim($composition) }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-4 text-gray-700">Non spécifiée.</p>
                    @endif
                </div>
                {{-- Contre-indications --}}
                <div class="md:w-1/2">
                    <h3 class="text-2xl font-semibold text-[#647a0b] flex items-center">
                        <i class="fas fa-exclamation-circle mr-2 text-[#854f38]"></i> Contre-indications
                    </h3>
                    @php
                        $contreIndications = explode(';', $tisane->ContreIndications);
                    @endphp
                    @if(!empty($contreIndications))
                        <ul class="mt-4 list-disc list-inside text-gray-700">
                            @foreach ($contreIndications as $contreIndication)
                                <li>{{ trim($contreIndication) }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-4 text-gray-700">Aucune contre-indication spécifiée.</p>
                    @endif
                </div>
            </div>

            {{-- Partie Utilisée --}}
            <div class="bg-white shadow rounded-lg p-8">
                <h3 class="text-2xl font-semibold text-[#647a0b] flex items-center">
                    <i class="fas fa-seedling mr-2 text-[#854f38]"></i> Partie Utilisée
                </h3>
                <p class="mt-4 text-gray-700">{{ $tisane->OrganeProducteur }}</p>
            </div>

            {{-- Description et Note côte à côte --}}
            <div class="bg-white shadow rounded-lg p-8 flex flex-col md:flex-row space-y-8 md:space-y-0 md:space-x-8">
                {{-- Description --}}
                <div class="md:w-1/2">
                    <h3 class="text-2xl font-semibold text-[#647a0b] flex items-center">
                        <i class="fas fa-align-left mr-2 text-[#854f38]"></i> Description
                    </h3>
                    <p class="mt-4 text-gray-700">{{ $tisane->Description ?? 'Aucune' }}</p>
                </div>
                {{-- Note --}}
                <div class="md:w-1/2">
                    <h3 class="text-2xl font-semibold text-[#647a0b] flex items-center">
                        <i class="fas fa-sticky-note mr-2 text-[#854f38]"></i> Note
                    </h3>
                    <p class="mt-4 text-gray-700">{{ $tisane->Note ?? 'Aucune note' }}</p>
                </div>
            </div>

            {{-- Liste des Recettes --}}
            <div class="bg-white shadow rounded-lg p-8">
                <h3 class="text-2xl font-semibold text-[#647a0b] flex items-center">
                    <i class="fas fa-book-open mr-2 text-[#854f38]"></i> Recettes avec {{ $tisane->NomTisane }}
                </h3>
                @if($tisane->relatedRecettes()->isNotEmpty())
                    <ul class="mt-4 list-disc list-inside text-gray-700">
                        @foreach($tisane->relatedRecettes() as $recette)
                            <li class="mb-2">
                                <a href="{{ route('recettes.show', $recette->slug) }}" class="text-[#854f38] hover:underline">
                                     {{ $recette->NomRecette }} ({{ $recette->TypeApplication }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-gray-700">Aucune recette trouvée utilisant cette tisane.</p>
                @endif
            </div>

            {{-- Bouton Retour --}}
            <div class="text-center mt-8">
                <a href="{{ route('tisanes.index') }}" class="inline-block bg-[#854f38] text-white text-lg px-8 py-3 rounded-full hover:bg-[#6a3f2c] transition-colors duration-300">
                    Retour à la liste
                </a>
            </div>

            {{-- Boîte d'avertissement --}}
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mt-8" role="alert">
                <p class="font-bold">Attention</p>
                <p>L'auto-médication avec des produits naturels comporte des risques. L'usage inapproprié peut entraîner des effets secondaires. Les informations sur ce site ne constituent pas des conseils médicaux. Consultez un professionnel de santé avant utilisation.</p>
            </div>
        </div>
    </div>

    {{-- Styles personnalisés --}}
    @push('styles')
    <style>
        .btn-favorite {
            background-color: transparent;
            border: none;
            color: #854f38;
            font-size: 1.25rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .btn-favorite:hover {
            color: #6a3f2c;
        }

        .shadow {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.05);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .md\:flex-row {
                flex-direction: column;
            }
            .md\:mt-0 {
                margin-top: 1.5rem;
            }
            .md\:pr-8 {
                padding-right: 0;
            }
            .md\:space-x-8 > :not([hidden]) ~ :not([hidden]) {
                --tw-space-x-reverse: 0;
                margin-right: calc(2rem * var(--tw-space-x-reverse));
                margin-left: calc(2rem * calc(1 - var(--tw-space-x-reverse)));
            }
        }
    </style>
    @endpush

    {{-- Font Awesome pour les icônes --}}
    @push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    @endpush
</x-app-layout>

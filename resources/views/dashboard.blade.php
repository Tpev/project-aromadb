<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Vos Favoris') }}
        </h2>
    </x-slot>

<div class="container mt-5">
    <div class="details-container mx-auto p-4">
        <h1 class="details-title">Vos Favoris</h1>

        @php
            // Grouping the favorites by type
            $huileHEFavorites = auth()->user()->favorites->where('favoritable_type', 'App\Models\HuileHE');
            $huileHVFavorites = auth()->user()->favorites->where('favoritable_type', 'App\Models\HuileHV');
            $recetteFavorites = auth()->user()->favorites->where('favoritable_type', 'App\Models\Recette');
            $tisaneFavorites = auth()->user()->favorites->where('favoritable_type', 'App\Models\Tisane');
        @endphp

        @if($huileHEFavorites->isEmpty() && $huileHVFavorites->isEmpty() && $recetteFavorites->isEmpty() && $tisaneFavorites->isEmpty())
            <p class="text-center">Vous n'avez encore aucun favori.</p>
        @else
            <!-- Favoris Huiles Essentielles -->
            @if(!$huileHEFavorites->isEmpty())
                <h2 class="favorites-section-title">Favoris Huiles Essentielles</h2>
                <ul class="favorites-list">
                    @foreach($huileHEFavorites as $favorite)
                        <li class="details-box">
                            <strong>Huile Essentielle :</strong> 
                            <a href="{{ route('huilehes.show', $favorite->favoritable->slug) }}">
                                {{ $favorite->favoritable->NomHE }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            <!-- Favoris Huiles Végétales -->
            @if(!$huileHVFavorites->isEmpty())
                <h2 class="favorites-section-title">Favoris Huiles Végétales</h2>
                <ul class="favorites-list">
                    @foreach($huileHVFavorites as $favorite)
                        <li class="details-box">
                            <strong>Huile Végétale :</strong> 
                            <a href="{{ route('huilehvs.show', $favorite->favoritable->slug) }}">
                                {{ $favorite->favoritable->NomHV }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            <!-- Favoris Recettes -->
            @if(!$recetteFavorites->isEmpty())
                <h2 class="favorites-section-title">Favoris Recettes</h2>
                <ul class="favorites-list">
                    @foreach($recetteFavorites as $favorite)
                        <li class="details-box">
                            <strong>Recette :</strong> 
                            <a href="{{ route('recettes.show', $favorite->favoritable->slug) }}">
                                {{ $favorite->favoritable->NomRecette }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            <!-- Favoris Tisanes -->
            @if(!$tisaneFavorites->isEmpty())
                <h2 class="favorites-section-title">Favoris Tisanes</h2>
                <ul class="favorites-list">
                    @foreach($tisaneFavorites as $favorite)
                        <li class="details-box">
                            <strong>Tisane :</strong> 
                            <a href="{{ route('tisanes.show', $favorite->favoritable->slug) }}">
                                {{ $favorite->favoritable->NomTisane }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endif
    </div>
</div>

<!-- Custom Styles -->
<style>
    .details-container {
        background-color: #f9f9f9;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        margin: 0 auto;
    }

    .favorites-section-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #16a34a;
        margin-bottom: 10px;
        margin-top: 20px;
    }

    .details-box {
        background-color: #ffffff;
        border-radius: 10px;
        padding: 10px 15px;
        margin-bottom: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        list-style: none;
    }

    .favorites-list {
        padding-left: 0;
    }

    a {
        color: #16a34a;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }
</style>

</x-app-layout>

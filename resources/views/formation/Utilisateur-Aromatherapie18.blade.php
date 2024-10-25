{{-- resources/views/formation/Utilisateur-Aromatherapie18.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 18 : Résumé des 4 huiles essentielles de base -->
        <div class="slide-content">
            <h1 class="slide-title">Résumé des huiles essentielles pour un usage quotidien</h1>
            <div class="details-box">
                <h2 class="slide-subtitle">Lavande : Relaxation, sommeil</h2>
                <p><strong>Utilisation :</strong> Diffusion, bain relaxant, application cutanée.</p>

                <h2 class="slide-subtitle">Menthe poivrée : Énergie, maux de tête</h2>
                <p><strong>Utilisation :</strong> Inhalation, application sur les tempes, diffusion.</p>

                <h2 class="slide-subtitle">Eucalyptus : Respiration, rhume</h2>
                <p><strong>Utilisation :</strong> Inhalation à la vapeur, application sur la poitrine, diffusion.</p>

                <h2 class="slide-subtitle">Citron : Nettoyage, humeur</h2>
                <p><strong>Utilisation :</strong> Diffusion, nettoyage naturel, inhalation.</p>
            </div>
        </div>

        <!-- Boutons de navigation -->
        <div class="navigation-buttons">
            <a href="{{ route('formation.show', ['numero' => $numero - 1]) }}" class="btn-slide">Précédent</a>

            @if($numero < $totalSlides)
                <a href="{{ route('formation.show', ['numero' => $numero + 1]) }}" class="btn-slide">Suivant</a>
            @else
                <span class="btn-slide btn-disabled">Suivant</span>
            @endif
        </div>
    </div>
</x-app-layout>

{{-- resources/views/formation/Utilisateur-Aromatherapie31.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 31 : Résumé des 3 méthodes d’application et des précautions -->
        <div class="slide-content">
            <h1 class="slide-title">Résumé des méthodes d’application et des précautions</h1>

            <h2 class="slide-subtitle">Diffusion :</h2>
            <div class="details-box">
                <ul>
                    <li>Méthode idéale pour purifier l'air et créer une ambiance relaxante.</li>
                    <li>Limitez à 30 minutes et évitez la diffusion avec des bébés, des enfants, des animaux et si vous prenez certains médicaments.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Inhalation :</h2>
            <div class="details-box">
                <ul>
                    <li>Méthode rapide pour dégager les voies respiratoires ou apaiser l’esprit.</li>
                    <li>Inhalez à partir d’un mouchoir, mais évitez une inhalation directe prolongée, surtout chez les enfants ou en cas de prise de médicaments.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Application cutanée :</h2>
            <div class="details-box">
                <ul>
                    <li>Appliquer localement pour un effet ciblé, toujours diluer dans une huile végétale.</li>
                    <li>Effectuez un test cutané et évitez certaines huiles pour les enfants, les femmes enceintes, et en cas de traitement médical.</li>
                </ul>
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

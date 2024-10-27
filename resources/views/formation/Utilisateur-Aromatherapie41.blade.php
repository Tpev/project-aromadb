{{-- resources/views/formation/Utilisateur-Aromatherapie41.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
	    <div class="slide-container">
	    <!-- Progress Bar -->
    @php
        $currentSlide = $numero; // Replace with current slide number passed to the view
        $totalSlides = 49;
        $progressPercent = ($currentSlide / $totalSlides) * 100;
    @endphp

    <div class="progress-container" style="margin-bottom: 20px;">
        <div class="progress-bar" style="width: {{ $progressPercent }}%; background-color: #647a0b; height: 20px;">
            <span style="color: white; padding-left: 10px;">{{ round($progressPercent) }}%</span>
        </div>
    </div>
<style>	
	.progress-container {
    width: 100%;
    background-color: #ddd;
    border-radius: 8px;
}

.progress-bar {
    text-align: left;
    padding-left: 5px;
    line-height: 20px;
    border-radius: 8px;
}
</style>
        <!-- Diapositive 41 : Mélange énergisant pour diffusion -->
        <div class="slide-content">
            <h1 class="slide-title">Exemple pratique : Mélange énergisant pour diffusion</h1>
            <p>
                Voici un mélange parfait pour les moments où vous avez besoin de stimuler votre énergie et votre concentration, idéal pour commencer la journée ou pendant une séance de travail.
            </p>

            <h2 class="slide-subtitle">Ingrédients pour un mélange énergisant :</h2>
            <div class="details-box">
                <ul>
                    <li><strong>4 gouttes d'huile essentielle de menthe poivrée :</strong> Apporte un coup de fouet immédiat à votre énergie et stimule la concentration.</li>
                    <li><strong>4 gouttes d'huile essentielle de citron :</strong> Aide à clarifier l’esprit tout en apportant de la fraîcheur et de la vitalité.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Instructions pour la diffusion :</h2>
            <div class="details-box">
                <ul>
                    <li>Mélangez 4 gouttes de menthe poivrée et 4 gouttes de citron dans un diffuseur rempli d’eau.</li>
                    <li>Allumez le diffuseur pendant environ 20 minutes dans votre bureau ou votre espace de travail.</li>
                    <li>Respirez profondément et profitez de l’effet stimulant du mélange.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Conseil de sécurité :</h2>
            <div class="details-box" style="background-color: #ffe4e1; border: 1px solid #e3342f;">
                <ul>
                    <li>La menthe poivrée est puissante, évitez de l’utiliser si vous avez des enfants en bas âge dans la pièce, ou diluez davantage.</li>
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

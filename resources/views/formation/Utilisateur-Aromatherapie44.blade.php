{{-- resources/views/formation/Utilisateur-Aromatherapie44.blade.php --}}
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
        <!-- Diapositive 44 : Exercice pratique -->
        <div class="slide-content">
            <h1 class="slide-title">Exercice pratique</h1>
            <p><strong>Exercice pratique :</strong> Créer votre propre mélange de diffusion ou d'inhalation</p>

            <p>
                C’est à votre tour ! Utilisez les huiles essentielles que vous avez à la maison pour créer votre propre mélange personnalisé en fonction de vos besoins.
            </p>

            <h2 class="slide-subtitle">Étapes à suivre :</h2>
            <div class="details-box">
                <ul>
                    <li><strong>Identifiez votre besoin :</strong> relaxation, énergie, ou respiration ?</li>
                    <li><strong>Sélectionnez 2 à 3 huiles essentielles :</strong> adaptées à cet objectif.</li>
                    <li><strong>Choisissez la méthode :</strong> diffusion, inhalation, ou application cutanée.</li>
                    <li><strong>Préparez votre mélange :</strong> et testez-le dans votre espace.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Exemple :</h2>
            <div class="details-box">
                <p><strong>Objectif :</strong> Améliorer la concentration pendant le travail.</p>
                <p><strong>Mélange proposé :</strong> 3 gouttes de menthe poivrée + 3 gouttes de citron dans un diffuseur.</p>
                <p>
                    Prenez quelques minutes pour créer votre mélange et notez votre ressenti après l'avoir testé.
                </p>
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

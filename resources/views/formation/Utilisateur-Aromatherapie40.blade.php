{{-- resources/views/formation/Utilisateur-Aromatherapie40.blade.php --}}
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
        <!-- Diapositive 40 : Mélange maison relaxant pour diffusion -->
        <div class="slide-content">
            <h1 class="slide-title">Exemple pratique : Mélange relaxant pour diffusion</h1>
            <p>
                Le mélange que nous allons préparer est idéal pour créer une atmosphère apaisante à la maison, que ce soit dans votre chambre avant le coucher ou dans le salon pour un moment de détente.
            </p>

            <h2 class="slide-subtitle">Ingrédients pour un mélange relaxant :</h2>
            <div class="details-box">
                <ul>
                    <li><strong>5 gouttes d'huile essentielle de lavande :</strong> Connue pour ses propriétés calmantes et relaxantes, elle aide à apaiser l'esprit et à favoriser un sommeil réparateur.</li>
                    <li><strong>3 gouttes d'huile essentielle de citron :</strong> Apporte une touche de fraîcheur, purifie l'air et élève subtilement l'humeur tout en renforçant l'effet relaxant de la lavande.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Instructions pour la diffusion :</h2>
            <div class="details-box">
                <ul>
                    <li>Ajoutez 5 gouttes de lavande et 3 gouttes de citron dans un diffuseur rempli d’eau.</li>
                    <li>Allumez le diffuseur et laissez-le fonctionner pendant 15 à 30 minutes dans une pièce ventilée.</li>
                    <li>Installez-vous confortablement et respirez profondément pour profiter des bienfaits relaxants du mélange.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Conseil de sécurité :</h2>
            <div class="details-box" style="background-color: #ffe4e1; border: 1px solid #e3342f;">
                <ul>
                    <li>Si vous avez des enfants ou des animaux dans la pièce, assurez-vous de limiter la durée de diffusion à 15 minutes et d’aérer la pièce après.</li>
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

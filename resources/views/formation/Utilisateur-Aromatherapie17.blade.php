{{-- resources/views/formation/Utilisateur-Aromatherapie17.blade.php --}}
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
        <!-- Diapositive 17 : Huile essentielle de Citron -->
        <div class="slide-content">
            <h1 class="slide-title">Citron : Pour purifier et élever l’humeur</h1>
            <p><strong>Nom scientifique :</strong> <em>Citrus limon</em></p>
            <p><strong>Propriétés principales :</strong> Le citron est connu pour ses effets tonifiants et purifiants. Il améliore l’humeur tout en étant un excellent désinfectant naturel pour la maison.</p>

            <h2 class="slide-subtitle">Applications courantes :</h2>
            <div class="details-box">
                <ul>
                    <li><strong>Diffusion :</strong> Diffusez 3 à 4 gouttes de citron pour rafraîchir et purifier l’air, tout en élevant l’humeur.</li>
                    <li><strong>Nettoyage maison :</strong> Ajoutez 10 gouttes de citron à de l’eau et du vinaigre blanc pour créer un nettoyant maison désinfectant.</li>
                    <li><strong>Inhalation :</strong> Inhalez directement le citron pour un regain d’énergie et pour favoriser une attitude positive.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Utilisations rapides :</h2>
            <div class="details-box">
                <ul>
                    <li>Utilisez le citron en diffusion pour un boost d’humeur et une atmosphère fraîche et revitalisante.</li>
                    <li>Mélangez-le avec de l’eau pour créer un nettoyant naturel pour les surfaces.</li>
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

{{-- resources/views/formation/Utilisateur-Aromatherapie15.blade.php --}}
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
        <!-- Diapositive 15 : Huile essentielle de Menthe poivrée -->
        <div class="slide-content">
            <h1 class="slide-title">Menthe poivrée : Un boost d’énergie naturel</h1>
            <p><strong>Nom scientifique :</strong> <em>Mentha piperita</em></p>
            <p><strong>Propriétés principales :</strong> La menthe poivrée est réputée pour ses effets stimulants et rafraîchissants. Elle est parfaite pour augmenter l’énergie, améliorer la concentration et soulager les maux de tête.</p>

            <h2 class="slide-subtitle">Applications courantes :</h2>
            <div class="details-box">
                <ul>
                    <li><strong>Inhalation directe :</strong> Inhalez 1 goutte de menthe poivrée déposée sur un mouchoir pour un coup de fouet immédiat ou pour dégager les voies respiratoires.</li>
                    <li><strong>Application cutanée :</strong> Diluez 1 à 2 gouttes de menthe poivrée dans une huile végétale et massez les tempes pour soulager un mal de tête.</li>
                    <li><strong>Diffusion :</strong> Diffusez 2 gouttes de menthe poivrée et 3 gouttes de citron pour améliorer la concentration et dynamiser l’atmosphère.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Utilisations rapides :</h2>
            <div class="details-box">
                <ul>
                    <li><strong>En cas de fatigue :</strong> Inhalez la menthe poivrée pour augmenter l’énergie instantanément.</li>
                    <li><strong>Pour les maux de tête :</strong> Appliquez localement sur les tempes et la nuque, en massant doucement.</li>
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

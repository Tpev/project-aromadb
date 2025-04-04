{{-- resources/views/formation/Utilisateur-Aromatherapie19.blade.php --}}
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
        <!-- Diapositive 19 : Exemples d'utilisation rapide -->
        <div class="slide-content">
            <h1 class="slide-title">Exemples d’utilisation rapide des huiles essentielles</h1>
            <div class="details-box">
                <h2 class="slide-subtitle">Inhalation :</h2>
                <ul>
                    <li><strong>Menthe poivrée</strong> pour soulager les maux de tête ou augmenter l’énergie.</li>
                </ul>

                <h2 class="slide-subtitle">Diffusion :</h2>
                <ul>
                    <li><strong>Lavande</strong> pour apaiser et favoriser le sommeil.</li>
                    <li><strong>Citron</strong> pour purifier l’air et améliorer l’humeur.</li>
                </ul>

                <h2 class="slide-subtitle">Application cutanée (diluée) :</h2>
                <ul>
                    <li><strong>Eucalyptus</strong> pour soulager la congestion nasale, dilué dans une huile végétale et appliqué sur la poitrine.</li>
                    <li><strong>Lavande</strong> pour soulager le stress, appliquée sur les tempes.</li>
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

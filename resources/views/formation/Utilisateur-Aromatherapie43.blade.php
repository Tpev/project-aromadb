{{-- resources/views/formation/Utilisateur-Aromatherapie43.blade.php --}}
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
        <!-- Diapositive 43 : Précautions générales pour la création de mélanges -->
        <div class="slide-content">
            <h1 class="slide-title">Précautions importantes pour la création de vos mélanges</h1>

            <h2 class="slide-subtitle">Toujours tester avant utilisation :</h2>
            <div class="details-box">
                <ul>
                    <li>Avant d’utiliser un nouveau mélange, appliquez une petite quantité diluée sur un mouchoir et testez la réaction olfactive pour vous assurer que l’odeur ne provoque pas d'inconfort.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Dilution correcte :</h2>
            <div class="details-box">
                <ul>
                    <li>Lorsque vous appliquez sur la peau, diluez toujours les huiles dans une huile végétale. Les huiles essentielles sont très concentrées et peuvent causer des irritations si elles sont utilisées pures.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Surveiller la durée de diffusion :</h2>
            <div class="details-box">
                <ul>
                    <li>Ne diffusez jamais les huiles essentielles pendant des heures d'affilée. Limitez les sessions à 30 minutes et aérez régulièrement la pièce.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Interagir avec vos mélanges :</h2>
            <div class="details-box">
                <ul>
                    <li>N'hésitez pas à ajuster les quantités en fonction de vos préférences personnelles, mais gardez toujours à l'esprit les précautions de sécurité.</li>
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

{{-- resources/views/formation/Utilisateur-Aromatherapie48.blade.php --}}
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
        <!-- Diapositive 48 : Conclusion -->
        <div class="slide-content">
            <h1 class="slide-title">Conclusion : Créer des mélanges maison en toute sécurité</h1>

            <p style="font-size: 1.2rem; color: #333333; margin-top: 20px;">
                Utiliser des huiles essentielles pour créer vos propres mélanges maison est une manière simple et efficace d’améliorer votre bien-être.
            </p>

            <p style="font-size: 1.2rem; color: #333333; margin-top: 20px;">
                Rappelez-vous de toujours tester vos mélanges avant de les utiliser et de respecter les consignes de sécurité (dilution, durée de diffusion, etc.).
            </p>

            <p style="font-size: 1.2rem; color: #333333; margin-top: 20px;">
                En fonction de vos besoins, vous pouvez ajuster les combinaisons pour trouver ce qui fonctionne le mieux pour vous.
            </p>
        </div>

        <!-- Boutons de navigation -->
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

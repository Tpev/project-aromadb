{{-- resources/views/formation/Utilisateur-Aromatherapie42.blade.php --}}
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
        <!-- Diapositive 42 : Mélange pour inhalation rapide – Respiration claire -->
        <div class="slide-content">
            <h1 class="slide-title">Exemple pratique : Mélange pour inhalation rapide – Respiration claire</h1>
            <p>
                Ce mélange est idéal lorsque vous avez besoin de dégager rapidement vos voies respiratoires, surtout en période de rhume ou d'allergies.
            </p>

            <h2 class="slide-subtitle">Ingrédients pour une inhalation rapide :</h2>
            <div class="details-box">
                <ul>
                    <li><strong>2 gouttes d'huile essentielle d'eucalyptus :</strong> Puissante pour dégager les voies respiratoires et purifier l'air.</li>
                    <li><strong>1 goutte d'huile essentielle de menthe poivrée :</strong> Apporte une sensation rafraîchissante et aide à ouvrir les voies nasales.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Instructions pour l’inhalation :</h2>
            <div class="details-box">
                <ul>
                    <li>Déposez 2 gouttes d'eucalyptus et 1 goutte de menthe poivrée sur un mouchoir propre.</li>
                    <li>Placez le mouchoir près de votre nez et respirez profondément pendant 1 à 2 minutes.</li>
                    <li>Recommencez selon vos besoins, mais évitez l’inhalation prolongée.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Conseil de sécurité :</h2>
            <div class="details-box" style="background-color: #ffe4e1; border: 1px solid #e3342f;">
                <ul>
                    <li>Ne laissez pas les huiles entrer en contact direct avec vos narines. Si vous ressentez une irritation, éloignez le mouchoir et respirez de l’air frais.</li>
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

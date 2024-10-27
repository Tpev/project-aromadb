{{-- resources/views/formation/Utilisateur-Aromatherapie28.blade.php --}}
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
        <!-- Diapositive 28 : L'inhalation des huiles essentielles -->
        <div class="slide-content">
            <h1 class="slide-title">Inhalation : Une méthode efficace mais à utiliser avec précautions</h1>
            <h2 class="slide-subtitle">Qu’est-ce que l'inhalation ?</h2>
            <p>
                L’inhalation permet de respirer directement les molécules d’huiles essentielles, ce qui peut avoir un effet rapide sur le système respiratoire et nerveux. Elle est utile pour les maux de tête, les congestions nasales, ou pour améliorer la concentration.
            </p>

            <h2 class="slide-subtitle">Comment l’utiliser ?</h2>
            <div class="details-box">
                <ul>
                    <li>Déposez 1 à 2 gouttes d'huile essentielle sur un mouchoir ou un coton.</li>
                    <li>Respirez doucement à travers le mouchoir pendant 1 à 2 minutes.</li>
                    <li>Vous pouvez aussi pratiquer une inhalation à la vapeur : ajoutez 2 gouttes d’huile dans un bol d’eau chaude, couvrez votre tête avec une serviette, et inhalez la vapeur.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Exemples d'huiles à inhaler :</h2>
            <div class="details-box">
                <ul>
                    <li><strong>Menthe poivrée :</strong> Pour soulager les maux de tête ou stimuler la concentration.</li>
                    <li><strong>Eucalyptus :</strong> Pour dégager les voies respiratoires pendant un rhume.</li>
                    <li><strong>Lavande :</strong> Pour calmer les nerfs et favoriser la détente.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Précautions spécifiques pour l'inhalation :</h2>
            <div class="details-box" style="background-color: #ffe4e1; border: 1px solid #e3342f;">
                <ul>
                    <li>Ne pas inhaler directement à partir du flacon, surtout si vous prenez des médicaments.</li>
                    <li>Pas d'inhalation prolongée pour les enfants. Utilisez des huiles douces comme la camomille en petite quantité.</li>
                    <li>Femmes enceintes : Utilisez des huiles douces comme la lavande et évitez les huiles stimulantes comme la menthe poivrée.</li>
                    <li>Interactions avec les médicaments : Certaines huiles essentielles peuvent interférer avec les médicaments pour le système respiratoire ou nerveux. Consultez un professionnel de la santé avant utilisation.</li>
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

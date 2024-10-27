{{-- resources/views/formation/Utilisateur-Aromatherapie2.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Styles personnalisés (vous pouvez les déplacer dans un fichier CSS séparé si vous le souhaitez) -->
    <style>
        .slide-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .slide-title {
            font-size: 2rem;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .slide-content h3 {
            color: #854f38;
            margin-top: 20px;
        }

        .slide-content p {
            font-size: 1.1rem;
            line-height: 1.6;
            text-align: justify;
            margin-bottom: 15px;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .btn-slide {
            background-color: #647a0b;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .btn-slide:hover {
            background-color: #854f38;
        }

        .btn-disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            color: #666666;
        }
    </style>

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
        <!-- Diapositive 2 : Définition de l'aromathérapie -->
        <div class="slide-content">
            <h3>L'aromathérapie : Une thérapie naturelle</h3>
            <p>
                L’aromathérapie est une pratique qui utilise les huiles essentielles extraites de diverses plantes pour améliorer la santé et le bien-être général. Elle repose sur l’idée que les extraits aromatiques des plantes contiennent des molécules actives capables d’influencer notre corps, nos émotions et notre esprit.
            </p>

            <h3>Huiles essentielles : Une puissance naturelle concentrée</h3>
            <p>
                Les huiles essentielles sont des liquides hautement concentrés obtenus par distillation à la vapeur ou par d'autres méthodes d'extraction, comme le pressage à froid pour les agrumes. Elles contiennent des composés chimiques naturels spécifiques à chaque plante, ce qui leur donne des propriétés uniques.
            </p>

            <h3>Exemple d’huile essentielle :</h3>
            <p>
                <strong>Lavande :</strong> reconnue pour ses propriétés relaxantes et apaisantes. Elle est souvent utilisée pour calmer l’anxiété et améliorer le sommeil.
            </p>
        </div>

        <!-- Boutons de navigation -->
        <div class="navigation-buttons">
            <!-- Bouton Précédent -->
            <a href="{{ route('formation.show', ['numero' => $numero - 1]) }}" class="btn-slide">Précédent</a>

            <!-- Lien vers la diapositive suivante -->
            <a href="{{ route('formation.show', ['numero' => $numero + 1]) }}" class="btn-slide">Suivant</a>
        </div>
    </div>
</x-app-layout>

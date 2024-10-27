{{-- resources/views/formation/Utilisateur-Aromatherapie1.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Styles personnalisés -->
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

        .slide-content p {
            font-size: 1.1rem;
            line-height: 1.6;
            text-align: center;
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
        <!-- Diapositive 1 : Titre -->
        <div class="slide-content">
            <h1 class="slide-title">Qu’est-ce que l’aromathérapie ?</h1>
            <p><strong>Objectif :</strong> Comprendre la définition et les bienfaits de l’aromathérapie.</p>
            <p><strong>Durée :</strong> 5 minutes</p>
        </div>

        <!-- Boutons de navigation -->
        <div class="navigation-buttons">
            <!-- Bouton Précédent désactivé sur la première diapositive -->
            <span class="btn-slide btn-disabled">Précédent</span>

            <!-- Lien vers la diapositive suivante -->
            <a href="{{ route('formation.show', ['numero' => $numero + 1]) }}" class="btn-slide">Suivant</a>
        </div>
    </div>
</x-app-layout>

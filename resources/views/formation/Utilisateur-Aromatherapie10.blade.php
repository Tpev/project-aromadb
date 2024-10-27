{{-- resources/views/formation/Utilisateur-Aromatherapie10.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

  <style>
        .slide-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
        }

        .slide-title {
            font-size: 2.5rem;
            color: #854f38;
            margin-bottom: 20px;
            text-align: center;
        }

        .slide-subtitle {
            font-size: 1.8rem;
            color: #16a34a;
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .slide-content p {
            font-size: 1.1rem;
            line-height: 1.8;
            text-align: justify;
            margin-bottom: 15px;
            color: #333333;
        }

        .slide-content ul {
            list-style-type: disc;
            margin-left: 20px;
            color: #333333;
        }

        .details-box {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .details-box strong {
            color: #854f38;
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

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .slide-title {
                font-size: 2rem;
            }

            .slide-subtitle {
                font-size: 1.5rem;
            }
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
        <div class="slide-content">
            <!-- Diapositive 10 : Conclusion et réflexion -->
            <h1 class="slide-title">Conclusion et réflexion</h1>

            <h2 class="slide-subtitle">Résumé</h2>
            <p>
                L’aromathérapie utilise les huiles essentielles pour favoriser le bien-être physique et émotionnel. Elle repose sur une longue tradition de guérison naturelle et peut être intégrée facilement dans la vie quotidienne pour soulager des maux courants comme le stress, les douleurs ou les troubles du sommeil.
            </p>

            <h2 class="slide-subtitle">Réflexion</h2>
            <p>
                Comment pouvez-vous intégrer ces pratiques dans votre routine quotidienne ? Essayez de réfléchir à une huile essentielle que vous pourriez utiliser pour répondre à un besoin spécifique dans votre vie (par exemple, soulager le stress après une journée de travail ou améliorer la qualité de votre sommeil).
            </p>

            <!-- Boutons de navigation -->
            <div class="navigation-buttons">
                <!-- Bouton Précédent -->
                @if($numero > 1)
                    <a href="{{ route('formation.show', ['numero' => $numero - 1]) }}" class="btn-slide">Précédent</a>
                @else
                    <span class="btn-slide btn-disabled">Précédent</span>
                @endif

                <!-- Lien vers la diapositive suivante (désactivé si c'est la dernière) -->
                @if(view()->exists('formation.Utilisateur-Aromatherapie' . ($numero + 1)))
                    <a href="{{ route('formation.show', ['numero' => $numero + 1]) }}" class="btn-slide">Suivant</a>
                @else
                    <span class="btn-slide btn-disabled">Suivant</span>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

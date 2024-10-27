{{-- resources/views/formation/Utilisateur-Aromatherapie3.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Styles personnalisés -->
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

        .details-box {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .timeline-item {
            margin-bottom: 20px;
        }

        .timeline-item strong {
            color: #854f38;
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
            <!-- Diapositive 3 : Origines de l’aromathérapie -->
            <h1 class="slide-title">Origines de l’aromathérapie</h1>

            <h2 class="slide-subtitle">Les racines anciennes de l’aromathérapie</h2>
            <p>
                L’utilisation des plantes aromatiques à des fins thérapeutiques remonte à plusieurs millénaires. De nombreuses civilisations anciennes comme les Égyptiens, les Chinois et les Indiens utilisaient déjà des plantes aromatiques dans leurs rituels religieux, leur médecine et leurs pratiques de beauté.
            </p>

            <h2 class="slide-subtitle">Évolution au fil du temps</h2>

            <div class="details-box">
                <div class="timeline-item">
                    <p>
                        <strong>Égypte ancienne :</strong> Les Égyptiens utilisaient des extraits de plantes pour embaumer les morts et purifier l'air.
                    </p>
                </div>
                <div class="timeline-item">
                    <p>
                        <strong>Grèce et Rome :</strong> Les médecins grecs comme Hippocrate et les Romains reconnaissaient les bienfaits des plantes pour traiter les maladies.
                    </p>
                </div>
                <div class="timeline-item">
                    <p>
                        <strong>Début du 20<sup>e</sup> siècle :</strong> Le terme "aromathérapie" a été popularisé par le chimiste français René-Maurice Gattefossé après qu'il ait découvert les propriétés curatives de la lavande pour soigner une brûlure.
                    </p>
                </div>
            </div>

            <p>
                Aujourd’hui, l’aromathérapie est à la croisée des connaissances ancestrales et des recherches modernes sur les plantes médicinales.
            </p>

            <!-- Boutons de navigation -->
            <div class="navigation-buttons">
                <!-- Bouton Précédent -->
                @if($numero > 1)
                    <a href="{{ route('formation.show', ['numero' => $numero - 1]) }}" class="btn-slide">Précédent</a>
                @else
                    <span class="btn-slide btn-disabled">Précédent</span>
                @endif

                <!-- Lien vers la diapositive suivante -->
                @if(view()->exists('formation.Utilisateur-Aromatherapie' . ($numero + 1)))
                    <a href="{{ route('formation.show', ['numero' => $numero + 1]) }}" class="btn-slide">Suivant</a>
                @else
                    <span class="btn-slide btn-disabled">Suivant</span>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

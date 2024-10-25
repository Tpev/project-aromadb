{{-- resources/views/formation/Utilisateur-Aromatherapie5.blade.php --}}
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
        <div class="slide-content">
            <!-- Diapositive 5 : Le système olfactif et son impact sur les émotions -->
            <h1 class="slide-title">Le système olfactif et son impact sur les émotions</h1>

            <h2 class="slide-subtitle">Comment l’aromathérapie influence les émotions ?</h2>
            <p>
                Lorsque nous respirons une huile essentielle, les molécules aromatiques interagissent avec notre système olfactif, qui est directement relié à notre cerveau émotionnel, ou système limbique.
            </p>

            <h2 class="slide-subtitle">Les étapes du processus olfactif :</h2>
            <div class="details-box">
                <ol>
                    <li>
                        Les molécules volatiles des huiles essentielles sont détectées par les récepteurs olfactifs dans les narines.
                    </li>
                    <li>
                        Ces récepteurs envoient des signaux nerveux au système limbique, qui inclut des régions comme l’amygdale, liée aux émotions, et l’hippocampe, lié à la mémoire.
                    </li>
                    <li>
                        Ce processus explique pourquoi certains arômes peuvent apaiser ou dynamiser en fonction des besoins.
                    </li>
                </ol>
            </div>

            <h2 class="slide-subtitle">Exemple pratique :</h2>
            <p>
                <strong>Lavande :</strong> L’inhalation de cette huile a des effets calmants qui peuvent apaiser les tensions et favoriser une sensation de tranquillité. Elle est souvent utilisée pour diminuer le stress et favoriser la relaxation avant le sommeil.
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

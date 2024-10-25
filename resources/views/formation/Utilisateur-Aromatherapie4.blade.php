{{-- resources/views/formation/Utilisateur-Aromatherapie4.blade.php --}}
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
            <!-- Diapositive 4 : Les bienfaits physiques et émotionnels de l’aromathérapie -->
            <h1 class="slide-title">Les bienfaits physiques et émotionnels de l’aromathérapie</h1>

            <h2 class="slide-subtitle">L’aromathérapie et le bien-être émotionnel</h2>
            <p>
                Les huiles essentielles peuvent avoir un impact profond sur nos émotions. En effet, lorsqu’on inhale une huile essentielle, les molécules aromatiques stimulent les récepteurs olfactifs situés dans notre nez, qui envoient des signaux au cerveau, en particulier à l’amygdale et à l’hippocampe, des zones responsables de nos émotions et de nos souvenirs.
            </p>

            <div class="details-box">
                <p><strong>Exemples d’effets émotionnels :</strong></p>
                <ul>
                    <li>
                        <strong>Réduction du stress :</strong> des huiles comme la lavande et la camomille sont réputées pour leurs propriétés calmantes. Elles sont souvent utilisées pour soulager l'anxiété et le stress.
                    </li>
                    <li>
                        <strong>Amélioration de l’humeur :</strong> des huiles telles que le citron ou la bergamote peuvent stimuler l'énergie et favoriser une humeur positive.
                    </li>
                </ul>
            </div>

            <h2 class="slide-subtitle">L’aromathérapie et le bien-être physique</h2>
            <p>
                En plus de ses effets émotionnels, l’aromathérapie peut également améliorer notre bien-être physique de plusieurs façons :
            </p>

            <div class="details-box">
                <ul>
                    <li>
                        <strong>Amélioration du sommeil :</strong> l’inhalation d’huiles comme la lavande ou l’ylang-ylang aide à favoriser un sommeil profond et réparateur.
                    </li>
                    <li>
                        <strong>Soulagement des douleurs :</strong> des huiles telles que l’eucalyptus ou la menthe poivrée sont connues pour leurs propriétés anti-inflammatoires et analgésiques. Elles sont souvent utilisées pour soulager les douleurs musculaires et les maux de tête.
                    </li>
                    <li>
                        <strong>Soutien immunitaire :</strong> des huiles comme l’arbre à thé, le thym ou l’eucalyptus possèdent des propriétés antimicrobiennes, aidant à renforcer les défenses naturelles du corps.
                    </li>
                </ul>
            </div>

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

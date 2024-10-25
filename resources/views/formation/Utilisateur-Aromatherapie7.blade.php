{{-- resources/views/formation/Utilisateur-Aromatherapie7.blade.php --}}
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
        <div class="slide-content">
            <!-- Diapositive 7 : Exemple pratique d'utilisation au quotidien -->
            <h1 class="slide-title">Exemple pratique d'utilisation au quotidien</h1>

            <h2 class="slide-subtitle">Exemple d’utilisation d’huiles essentielles dans la vie quotidienne</h2>

            <div class="details-box">
                <h3 style="color: #16a34a;">Pour améliorer le sommeil :</h3>
                <ul>
                    <li>Diffusez 4 gouttes de lavande et 2 gouttes d’ylang-ylang dans votre chambre avant de dormir.</li>
                    <li>Option alternative : Ajouter quelques gouttes de lavande dans un bain tiède pour un effet apaisant avant le coucher.</li>
                </ul>
            </div>

            <div class="details-box">
                <h3 style="color: #16a34a;">Pour soulager un mal de tête :</h3>
                <ul>
                    <li>Diluez 1 goutte de menthe poivrée dans une cuillère d'huile végétale, appliquez sur les tempes et massez doucement.</li>
                    <li>Inhalez 1 goutte de menthe poivrée déposée sur un mouchoir.</li>
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

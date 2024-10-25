{{-- resources/views/formation/Utilisateur-Aromatherapie9.blade.php --}}
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
            <!-- Diapositive 9 : Quiz rapide (Question 2) -->
            <h1 class="slide-title">Quiz rapide – Question 2</h1>

            <p style="font-size: 1.2rem; color: #333333; margin-bottom: 20px;">
                Laquelle de ces huiles essentielles est recommandée pour favoriser la détente et améliorer le sommeil ?
            </p>

            <form id="quiz-form">
                <div class="details-box">
                    <label>
                        <input type="radio" name="answer" value="a">
                        a) Lavande
                    </label>
                </div>
                <div class="details-box">
                    <label>
                        <input type="radio" name="answer" value="b">
                        b) Citron
                    </label>
                </div>
                <div class="details-box">
                    <label>
                        <input type="radio" name="answer" value="c">
                        c) Menthe poivrée
                    </label>
                </div>

                <p id="feedback" style="color: #e3342f; font-weight: bold; display: none;">Veuillez sélectionner une réponse.</p>

                <!-- Boutons de navigation -->
                <div class="navigation-buttons">
                    <!-- Bouton Précédent -->
                    <a href="{{ route('formation.show', ['numero' => $numero - 1]) }}" class="btn-slide">Précédent</a>

                    <!-- Bouton Suivant désactivé initialement -->
                    <button type="button" id="submit-answer" class="btn-slide">Valider</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script pour gérer le quiz -->
    <script>
        document.getElementById('submit-answer').addEventListener('click', function() {
            let selectedAnswer = document.querySelector('input[name="answer"]:checked');
            let feedback = document.getElementById('feedback');

            if (!selectedAnswer) {
                feedback.textContent = "Veuillez sélectionner une réponse.";
                feedback.style.display = 'block';
                return;
            }

            if (selectedAnswer.value === 'a') {
                // Réponse correcte, rediriger vers la diapositive suivante
                window.location.href = "{{ route('formation.show', ['numero' => $numero + 1]) }}";
            } else {
                // Réponse incorrecte, afficher un message
                feedback.textContent = "Réponse incorrecte. Veuillez réessayer.";
                feedback.style.display = 'block';
            }
        });
    </script>
</x-app-layout>

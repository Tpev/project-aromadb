{{-- resources/views/formation/Utilisateur-Aromatherapie21.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 21 : Quiz rapide (Question 2) -->
        <h1 class="slide-title">Quiz rapide – Question 2</h1>

        <p style="font-size: 1.2rem; color: #333333; margin-bottom: 20px;">
            Quelle huile essentielle est la plus adaptée pour soulager les maux de tête ?
        </p>

        <form id="quiz-form">
            <div class="details-box">
                <label>
                    <input type="radio" name="answer" value="a">
                    a) Citron
                </label>
            </div>
            <div class="details-box">
                <label>
                    <input type="radio" name="answer" value="b">
                    b) Lavande
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

                <!-- Bouton Valider -->
                <button type="button" id="submit-answer" class="btn-slide">Valider</button>
            </div>
        </form>
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

            if (selectedAnswer.value === 'c') {
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

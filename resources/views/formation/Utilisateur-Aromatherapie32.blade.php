{{-- resources/views/formation/Utilisateur-Aromatherapie32.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #647a0b;">
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
        <!-- Diapositive 32 : Évaluation – Quiz (Question 1) -->
        <div class="slide-content">
            <h1 class="slide-title">Évaluation – Quiz (Question 1)</h1>

            <p style="font-size: 1.2rem; color: #333333; margin-bottom: 20px;">
                Quelle méthode est la plus appropriée pour dégager les voies respiratoires chez un adulte pendant un rhume ?
            </p>

            <form id="quiz-form">
                <div class="details-box">
                    <label>
                        <input type="radio" name="answer" value="a">
                        a) Diffusion
                    </label>
                </div>
                <div class="details-box">
                    <label>
                        <input type="radio" name="answer" value="b">
                        b) Inhalation
                    </label>
                </div>
                <div class="details-box">
                    <label>
                        <input type="radio" name="answer" value="c">
                        c) Application cutanée
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

                if (selectedAnswer.value === 'b') {
                    // Réponse correcte, rediriger vers la diapositive suivante
                    window.location.href = "{{ route('formation.show', ['numero' => $numero + 1]) }}";
                } else {
                    // Réponse incorrecte, afficher un message
                    feedback.textContent = "Réponse incorrecte. Veuillez réessayer.";
                    feedback.style.display = 'block';
                }
            });
        </script>
    </div>
</x-app-layout>

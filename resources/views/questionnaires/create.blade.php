<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer un Questionnaire') }}
        </h2>
    </x-slot>

    <div class="container-fluid mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouveau Questionnaire') }}</h1>

            <form action="{{ route('questionnaires.store') }}" method="POST">
                @csrf

                <div class="input-section">

                    <!-- Titre du Questionnaire -->
                    <div class="details-box">
                        <label class="details-label" for="title">{{ __('Titre') }}</label>
                        <input type="text" id="title" name="title" class="form-control" required placeholder="{{ __('Entrez le titre du questionnaire') }}">
                        @error('title')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description du Questionnaire -->
                    <div class="details-box">
                        <label class="details-label" for="description">{{ __('Description') }}</label>
                        <textarea id="description" name="description" class="form-control" placeholder="{{ __('Entrez une description optionnelle') }}"></textarea>
                        @error('description')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Questions Section -->
                    <div class="details-box">
                        <label class="details-label">{{ __('Questions') }}</label>
                        <div id="questions-container">
                            <div class="question-item">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <input type="text" name="questions[0][text]" class="form-control" placeholder="{{ __('Entrez la question') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="questions[0][type]" class="form-control question-type" required onchange="updateQuestionType(this)">
                                            <option value="text">{{ __('Texte') }}</option>
                                            <option value="multiple_choice">{{ __('Choix multiple') }}</option>

                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-question" onclick="removeQuestion(this)">-</button>
                                    </div>
                                </div>
                                <div class="additional-fields"></div>
                            </div>
                        </div>
                        <button type="button" class="btn-primary mt-2" onclick="addQuestion()">{{ __('Ajouter une question') }}</button>
                    </div>
                </div>

                <button type="submit" class="btn-primary mt-4">{{ __('Créer le Questionnaire') }}</button>
                <a href="{{ route('questionnaires.index') }}" class="btn-secondary mt-4">{{ __('Retour à la liste') }}</a>
            </form>
        </div>
    </div>

    <script>
        let questionIndex = 1; // Index for new questions

        function addQuestion() {
            const questionContainer = document.getElementById('questions-container');
            const newQuestion = document.createElement('div');
            newQuestion.classList.add('question-item');
            newQuestion.innerHTML = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" name="questions[${questionIndex}][text]" class="form-control" placeholder="{{ __('Entrez la question') }}" required>
                    </div>
                    <div class="col-md-4">
                        <select name="questions[${questionIndex}][type]" class="form-control question-type" required onchange="updateQuestionType(this)">
                            <option value="text">{{ __('Texte') }}</option>
                            <option value="number">{{ __('Numéro') }}</option>
                            <option value="multiple_choice">{{ __('Choix multiple') }}</option>
                            <option value="date">{{ __('Date') }}</option>
                            <option value="true_false">{{ __('Vrai/Faux') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-question" onclick="removeQuestion(this)">-</button>
                    </div>
                </div>
                <div class="additional-fields"></div>
            `;
            questionContainer.appendChild(newQuestion);
            questionIndex++;
        }

        function removeQuestion(button) {
            const questionItem = button.closest('.question-item');
            questionItem.remove();
        }

        function updateQuestionType(select) {
            const questionItem = select.closest('.question-item');
            const additionalFields = questionItem.querySelector('.additional-fields');
            additionalFields.innerHTML = ''; // Clear previous additional fields

            const selectedType = select.value;

            if (selectedType === 'multiple_choice') {
                additionalFields.innerHTML = `
                    <label class="details-label">{{ __('Options (séparer par des virgules)') }}</label>
                    <input type="text" name="questions[${questionIndex - 1}][options]" class="form-control" placeholder="{{ __('Entrez les options') }}" required>
                `;
            } else if (selectedType === 'true_false') {
                additionalFields.innerHTML = `
                    <label class="details-label">{{ __('Vrai ou Faux') }}</label>
                    <select name="questions[${questionIndex - 1}][true_false]" class="form-control" required>
                        <option value="true">{{ __('Vrai') }}</option>
                        <option value="false">{{ __('Faux') }}</option>
                    </select>
                `;
            } else if (selectedType === 'date') {
                additionalFields.innerHTML = `
                    <label class="details-label">{{ __('Date') }}</label>
                    <input type="date" name="questions[${questionIndex - 1}][date]" class="form-control" required>
                `;
            } else if (selectedType === 'number') {
                additionalFields.innerHTML = `
                    <label class="details-label">{{ __('Numéro') }}</label>
                    <input type="number" name="questions[${questionIndex - 1}][number]" class="form-control" required>
                `;
            }
        }
    </script>

    <style>
        .container-fluid {
            max-width: 1200px;
        }

        .input-section {
            max-width: 600px;
            margin-bottom: 30px;
        }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .details-title {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .details-box {
            margin-bottom: 15px;
        }

        .details-label {
            font-weight: bold;
            color: #647a0b;
            display: block;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            padding: 10px 20px;
            border: 1px solid #854f38;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
        }

        .remove-question {
            margin-top: 31px; /* Adjust margin to center vertically with other inputs */
        }

        @media (max-width: 768px) {
            .details-title {
                font-size: 1.5rem;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%; /* Full width on smaller screens */
                margin-top: 10px;
            }
        }
    </style>
</x-app-layout>

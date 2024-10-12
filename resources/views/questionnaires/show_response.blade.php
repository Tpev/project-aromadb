<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails de la Réponse au Questionnaire') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Réponse au Questionnaire') }}</h1>

            <!-- Questionnaire Information -->
            <div class="details-box">
                <h2>{{ __('Titre du Questionnaire') }}: {{ $response->questionnaire->title }}</h2>
                <p><strong>{{ __('Client') }}:</strong> {{ $response->clientProfile->first_name }} {{ $response->clientProfile->last_name }}</p>
                <p><strong>{{ __('Date de soumission') }}:</strong> {{ $response->created_at->format('d/m/Y à H:i') }}</p>
            </div>

<!-- Answers Section -->
<div class="details-box">
    <h2>{{ __('Réponses') }}</h2>
    <ul>
        @foreach (json_decode($response->answers, true) as $questionId => $answer)
            @php
                // Retrieve the question using the ID
                $question = \App\Models\Question::find($questionId);
            @endphp
            <li>
                <strong>{{ __('Question') }}: {{ $question->text ?? 'Question non trouvée' }}</strong>
                <br>
                {{ __('Réponse') }}: {{ $answer }}
            </li>
        @endforeach
    </ul>
</div>


            <div class="text-center mt-4">
                <a href="{{ route('questionnaires.index') }}" class="btn btn-secondary">{{ __('Retour à la liste des questionnaires') }}</a>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 900px;
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

        .details-box h2 {
            font-size: 1.5rem;
            color: #854f38;
        }

        .details-box p, .details-box li {
            color: #333333;
            font-size: 1rem;
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
    </style>
</x-app-layout>

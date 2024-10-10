<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Envoyer un Questionnaire') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Envoyer un Questionnaire à un Client') }}</h1>

            <form action="#" method="POST" id="questionnaireForm">
                @csrf

                <!-- Client Profile Selection -->
                <div class="details-box">
                    <label for="client_profile_id" class="details-label">{{ __('Sélectionnez un Client') }}</label>
                    <select id="client_profile_id" name="client_profile_id" class="form-control" required>
                        <option value="">{{ __('Choisissez un Client') }}</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->first_name }} {{ $client->last_name }}</option>
                        @endforeach
                    </select>
                    @error('client_profile_id')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Questionnaire Selection -->
                <div class="details-box mt-3">
                    <label for="questionnaire_id" class="details-label">{{ __('Sélectionnez un Questionnaire') }}</label>
                    <select id="questionnaire_id" name="questionnaire_id" class="form-control" onchange="updateFormAction()" required>
                        <option value="">{{ __('Choisissez un Questionnaire') }}</option>
                        @foreach($questionnaires as $questionnaire)
                            <option value="{{ $questionnaire->id }}">{{ $questionnaire->title }}</option>
                        @endforeach
                    </select>
                    @error('questionnaire_id')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Selection -->
                <div class="details-box mt-3">
                    <label class="details-label">{{ __('Action') }}</label><br>
                    <div class="form-check">
                        <input type="radio" name="action" value="fill_now" class="form-check-input" id="fillNow" checked>
                        <label class="form-check-label" for="fillNow">{{ __('Remplir maintenant') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="action" value="send_email" class="form-check-input" id="sendEmail">
                        <label class="form-check-label" for="sendEmail">{{ __('Envoyer par Email') }}</label>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">{{ __('Soumettre') }}</button>
                    <a href="{{ route('client_profiles.index') }}" class="btn btn-secondary">{{ __('Retour à la liste') }}</a>
                </div>
            </form>
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
    </style>

    <!-- JavaScript for dynamic form action -->
    <script>
        function updateFormAction() {
            const questionnaireSelect = document.getElementById('questionnaire_id');
            const selectedQuestionnaireId = questionnaireSelect.value;
            const form = document.getElementById('questionnaireForm');

            if (selectedQuestionnaireId) {
                form.action = `{{ url('questionnaires') }}/${selectedQuestionnaireId}/send`;
            } else {
                form.action = '#'; // Reset if no questionnaire is selected
            }
        }
    </script>
</x-app-layout>
	
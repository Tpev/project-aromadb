<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Remplir un Questionnaire') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Remplir le Questionnaire : ') }} {{ $questionnaire->title }}</h1>
			
			            <!-- Questionnaire Description -->
            <div class="details-box mb-4">

                <p class="description-text">{{ $questionnaire->description ?? __('Aucune description fournie.') }}</p>
            </div>

            <form action="{{ route('questionnaires.storeResponses', ['token' => $token]) }}" method="POST">
                @csrf
                @if($errors->any())
                    <div role="alert" class="details-box text-red-500">
                        {{ __('Veuillez vérifier vos réponses. Les questions à corriger sont indiquées ci-dessous.') }}
                        @error('answers')<p>{{ $message }}</p>@enderror
                    </div>
                @endif

                <!-- Iterate through each question and display based on its type -->
                @foreach($questions as $index => $question)
                    @php($previousAnswer = old('answers.'.$question->id))
                    <div class="details-box">
                        <label class="details-label">{{ __('Question') }} {{ $index + 1 }}: {{ $question->text }}</label>

                        @switch($question->type)
                            @case('text')
                                <input type="text" name="answers[{{ $question->id }}]" value="{{ is_scalar($previousAnswer) ? $previousAnswer : '' }}" class="form-control" placeholder="{{ __('Répondez ici') }}" required>
                                @break

                            @case('number')
                                <input type="number" name="answers[{{ $question->id }}]" class="form-control" placeholder="{{ __('Entrez un numéro') }}" required>
                                @break

                            @case('multiple_choice')
                                @if($question->allowsMultipleAnswers())
                                    <fieldset data-multiple-answer-group>
                                        <legend class="text-sm">{{ __('Sélectionnez une ou plusieurs réponses.') }}</legend>
                                        @foreach($question->choiceOptions() as $optionIndex => $option)
                                            <label class="form-check" for="answer-{{ $question->id }}-{{ $optionIndex }}">
                                                <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option }}"
                                                       id="answer-{{ $question->id }}-{{ $optionIndex }}" class="form-check-input"
                                                       @checked(in_array($option, (array) $previousAnswer, true))>
                                                {{ $option }}
                                            </label>
                                        @endforeach
                                    </fieldset>
                                @else
                                    <select name="answers[{{ $question->id }}]" class="form-control" required>
                                        <option value="">{{ __('Choisissez une option') }}</option>
                                        @foreach($question->choiceOptions() as $option)
                                            <option value="{{ $option }}" @selected($previousAnswer === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                @break

                            @case('date')
                                <input type="date" name="answers[{{ $question->id }}]" class="form-control" required>
                                @break

                            @case('true_false')
                                <div class="form-check">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="true" class="form-check-input" id="true{{ $question->id }}" required>
                                    <label class="form-check-label" for="true{{ $question->id }}">{{ __('Vrai') }}</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="false" class="form-check-input" id="false{{ $question->id }}" required>
                                    <label class="form-check-label" for="false{{ $question->id }}">{{ __('Faux') }}</label>
                                </div>
                                @break

                            @default
                                <p>{{ __('Type de question non reconnu.') }}</p>
                        @endswitch
                        @foreach($errors->get('answers.'.$question->id) as $message)
                            <p role="alert" class="text-red-500">{{ $message }}</p>
                        @endforeach
                        @foreach($errors->get('answers.'.$question->id.'.*') as $messages)
                            @foreach($messages as $message)
                                <p role="alert" class="text-red-500">{{ $message }}</p>
                            @endforeach
                        @endforeach
                    </div>
                @endforeach

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">{{ __('Soumettre') }}</button>
                    <a href="{{ route('client_profiles.index') }}" class="btn btn-secondary">{{ __('Retour à la liste') }}</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-multiple-answer-group]').forEach((group) => {
            const choices = Array.from(group.querySelectorAll('input[type="checkbox"]'));
            const validate = () => {
                if (choices.length) {
                    choices[0].setCustomValidity(choices.some((choice) => choice.checked)
                        ? '' : 'Veuillez sélectionner au moins une réponse.');
                }
            };
            group.addEventListener('change', validate);
            validate();
        });
    </script>

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

        [data-multiple-answer-group] .form-check {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 0;
            cursor: pointer;
            overflow-wrap: anywhere;
        }

        [data-multiple-answer-group] input {
            flex-shrink: 0;
            margin-top: 4px;
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

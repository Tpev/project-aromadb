<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails du Questionnaire') }} - {{ $questionnaire->title }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Questionnaire : ') }}{{ $questionnaire->title }}</h1>

            <!-- Questionnaire Description -->
            <div class="details-box mb-4">
                <h3 class="details-subtitle">{{ __('Description') }}</h3>
                <p class="description-text">{{ $questionnaire->description ?? __('Aucune description fournie.') }}</p>
            </div>

            <!-- Questions Section -->
            <h3 class="details-subtitle">{{ __('Questions') }}</h3>
            @if($questionnaire->questions->isEmpty())
                <p class="text-muted">{{ __('Aucune question trouvée pour ce questionnaire.') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Texte de la Question') }}</th>
                                <th>{{ __('Type de Question') }}</th>
								<th>{{ __('Options') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($questionnaire->questions as $question)
                                <tr>
                                    <td>{{ $question->text }}</td>
                                    <td>{{ __(ucfirst(str_replace('_', ' ', $question->type))) }}</td>
									<td>{{ $question->options }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-4 text-center">
                <a href="{{ route('questionnaires.index') }}" class="btn btn-primary">{{ __('Retour à la liste') }}</a>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
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

        .details-subtitle {
            font-size: 1.5rem;
            font-weight: bold;
            color: #647a0b;
            margin-top: 20px;
            border-bottom: 2px solid #647a0b;
            padding-bottom: 5px;
        }

        .description-text {
            font-size: 1.1rem;
            color: #555;
            margin-top: 10px;
            line-height: 1.6;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 15px;
            text-align: left;
            vertical-align: middle;
        }

        .table thead {
            background-color: #647a0b;
            color: #ffffff;
        }

        .table tbody tr {
            transition: background-color 0.3s;
        }

        .table tbody tr:hover {
            background-color: #e8e8e8;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        @media (max-width: 768px) {
            .details-title {
                font-size: 1.5rem;
            }

            .details-subtitle {
                font-size: 1.25rem;
            }
        }
    </style>
</x-app-layout>

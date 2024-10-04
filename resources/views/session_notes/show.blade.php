<!-- resources/views/session_notes/show.blade.php -->

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails de la note de séance') }} - {{ $sessionNote->clientProfile->first_name }} {{ $sessionNote->clientProfile->last_name }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Note de séance pour ') }}{{ $sessionNote->clientProfile->first_name }} {{ $sessionNote->clientProfile->last_name }}</h1>

            <div class="row mt-4">
                <!-- Note Content -->
                <div class="col-md-12">
                    <div class="details-box">
                        <label class="details-label">{{ __('Contenu de la note') }}</label>
                        <div class="details-value">
                            {!! $sessionNote->note !!}
                        </div>
                        @error('note')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <a href="{{ route('session_notes.index', $sessionNote->client_profile_id) }}" class="btn-primary">{{ __('Retour aux notes de séance') }}</a>
                    <a href="{{ route('session_notes.edit', $sessionNote->id) }}" class="btn-secondary">{{ __('Modifier la note') }}</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 800px;
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
            margin-bottom: 20px;
        }

        .details-label {
            font-weight: bold;
            color: #647a0b;
            display: block;
            margin-bottom: 5px;
        }

        .details-value {
            color: #333333;
            font-size: 1rem;
            line-height: 1.6; /* Assure une meilleure lisibilité */
        }

        /* Réduction des marges pour éviter les espaces excessifs */
        .details-value p,
        .details-value h1,
        .details-value h2,
        .details-value h3,
        .details-value h4,
        .details-value h5,
        .details-value h6,
        .details-value ul,
        .details-value ol,
        .details-value blockquote,
        .details-value table {
            margin: 0.5rem 0; /* Ajustez selon vos besoins */
        }

        /* Styles pour les listes */
        .details-value ul,
        .details-value ol {
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }

        /* Assure que les puces et numéros sont visibles */
        .details-value ul {
            list-style-type: disc;
        }

        .details-value ol {
            list-style-type: decimal;
        }

        /* Styles pour les liens */
        .details-value a {
            color: #854f38;
            text-decoration: none;
        }

        .details-value a:hover {
            text-decoration: underline;
        }

        /* Styles pour les blockquotes */
        .details-value blockquote {
            border-left: 4px solid #647a0b;
            padding-left: 1rem;
            color: #555555;
            font-style: italic;
            margin: 0.5rem 0;
        }

        /* Styles pour les tables */
        .details-value table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .details-value table, 
        .details-value th, 
        .details-value td {
            border: 1px solid #ccc;
        }

        .details-value th, 
        .details-value td {
            padding: 0.5rem;
            text-align: left;
        }

        /* Styles pour les boutons */
        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.3s ease;
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
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
        }

        /* Styles additionnels pour mieux afficher le contenu enrichi */
        .details-value h1,
        .details-value h2,
        .details-value h3,
        .details-value h4,
        .details-value h5,
        .details-value h6 {
            color: #647a0b;
            margin-bottom: 0.5rem;
        }

        /* Responsive Styles */
        @media (max-width: 600px) {
            .details-title {
                font-size: 1.5rem;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
                margin-bottom: 10px;
            }

            .btn-primary:last-child,
            .btn-secondary:last-child {
                margin-bottom: 0;
            }
        }
    </style>
</x-app-layout>

{{-- resources/views/contact.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Support Technique') }}
        </h2>
    </x-slot>

    <!-- Inclure Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Besoin d\'aide ?') }}</h1>
            <p class="support-text">
                {{ __('Si vous rencontrez des problèmes ou avez des questions, n\'hésitez pas à contacter notre équipe de support en utilisant le formulaire ci-dessous.') }}
            </p>

            <!-- Afficher le message de succès -->
            @if(Session::has('success'))
                <p class="success">{{ Session::get('success') }}</p>
            @endif

            <!-- Afficher les erreurs de validation -->
            @if ($errors->any())
                <div class="error">
                    <ul style="list-style-type: none; padding-left: 0;">
                        @foreach ($errors->all() as $error)
                            <li>- {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('contact.send') }}" method="POST">
                @csrf

                <!-- Votre Nom -->
                <div class="details-box">
                    <label class="details-label" for="name">{{ __('Votre Nom') }}</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', Auth::user()->name ?? '') }}" required>
                    @error('name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Votre Email -->
                <div class="details-box">
                    <label class="details-label" for="email">{{ __('Votre Email') }}</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', Auth::user()->email ?? '') }}" required>
                    @error('email')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sujet -->
                <div class="details-box">
                    <label class="details-label" for="subject">{{ __('Sujet') }}</label>
                    <select id="subject" name="subject" class="form-control" required>
                        <option value="" disabled selected>{{ __('Sélectionnez un sujet') }}</option>
                        <option value="Bug" {{ old('subject') == 'Bug' ? 'selected' : '' }}>{{ __('Signaler un Bug') }}</option>
                        <option value="Question" {{ old('subject') == 'Question' ? 'selected' : '' }}>{{ __('Question') }}</option>
                        <option value="Licence" {{ old('subject') == 'Licence' ? 'selected' : '' }}>{{ __('Problème de Licence') }}</option>
                        <option value="Paiement" {{ old('subject') == 'Paiement' ? 'selected' : '' }}>{{ __('Problème de Paiement') }}</option>
                        <option value="Suggestion" {{ old('subject') == 'Suggestion' ? 'selected' : '' }}>{{ __('Suggestion d\'Amélioration') }}</option>
                        <option value="Autre" {{ old('subject') == 'Autre' ? 'selected' : '' }}>{{ __('Autre') }}</option>
                    </select>
                    @error('subject')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Votre Message -->
                <div class="details-box">
                    <label class="details-label" for="message">{{ __('Votre Message') }}</label>
                    <textarea id="message" name="message" class="form-control" required>{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-paper-plane mr-2"></i> {{ __('Envoyer le Message') }}
                    </button>
                    <a href="{{ url()->previous() }}" class="btn-secondary ml-3">
                        <i class="fas fa-arrow-left mr-2"></i> {{ __('Retour') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Styles personnalisés -->
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
            margin-bottom: 10px;
            text-align: center;
        }

        .support-text {
            font-size: 1rem;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .details-box {
            margin-bottom: 20px;
            text-align: left;
        }

        .details-label {
            font-weight: 600;
            color: #647a0b;
            display: block;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #854f38;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .btn-primary, .btn-secondary {
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s;
            margin: 5px;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #ffffff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            border: 1px solid #854f38;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #ffffff;
        }

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .d-flex {
            display: flex;
            align-items: center;
        }

        .justify-content-center {
            justify-content: center;
        }

        .ml-3 {
            margin-left: 15px;
        }

        .mr-2 {
            margin-right: 8px;
        }

        .success {
            color: #28a745;
            font-size: 1rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .error {
            color: #e3342f;
            font-size: 1rem;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Ajustements responsives */
        @media (max-width: 768px) {
            .btn-primary, .btn-secondary {
                width: 100%;
                justify-content: center;
            }

            .ml-3 {
                margin-left: 0;
                margin-top: 10px;
            }

            .d-flex {
                flex-direction: column;
            }
        }
    </style>
</x-app-layout>

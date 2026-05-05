<x-guest-layout>
    <style>
        .pro-login {
            color: #171c2b;
        }

        .pro-login-header {
            text-align: center;
        }

        .pro-login-eyebrow {
            margin: 0;
            color: #854f38;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        .pro-login-title {
            margin: 0.45rem 0 0;
            font-size: clamp(1.8rem, 8vw, 2.6rem);
            line-height: 1;
            letter-spacing: -0.055em;
        }

        .pro-login-form {
            margin-top: 1.8rem;
        }

        .pro-login-field + .pro-login-field {
            margin-top: 1rem;
        }

        .pro-login-field label,
        .pro-login-remember label {
            color: #344054;
            font-weight: 700;
        }

        .pro-login-field input {
            min-height: 50px;
            border-radius: 16px;
            border-color: #d9decf;
            background: #fffdf8;
            font-size: 1rem;
        }

        .pro-login-field input:focus {
            border-color: #647a0b;
            box-shadow: 0 0 0 4px rgba(100, 122, 11, 0.14);
        }

        .pro-login-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1rem;
        }

        .pro-login-remember input {
            width: 1rem;
            height: 1rem;
            color: #647a0b;
            border-color: #cbd6a8;
        }

        .pro-login-link {
            color: #647a0b;
            font-size: 0.9rem;
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 0.18em;
        }

        .pro-login-button {
            display: inline-flex;
            width: 100%;
            min-height: 52px;
            align-items: center;
            justify-content: center;
            margin-top: 1.35rem;
            border-radius: 999px;
            background: #647a0b;
            color: #ffffff;
            font-weight: 800;
            transition: background-color 160ms ease, transform 160ms ease;
        }

        .pro-login-button:hover {
            background: #4d6104;
            transform: translateY(-1px);
        }

        .pro-login-footer {
            margin: 1.35rem 0 0;
            display: grid;
            gap: 0.65rem;
            border-top: 1px solid #ebe2d3;
            padding-top: 1.15rem;
            color: #667085;
            font-size: 0.92rem;
            line-height: 1.5;
            text-align: center;
        }

        .pro-login-footer a {
            color: #647a0b;
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 0.18em;
        }

        @media (max-width: 420px) {
            .pro-login-row {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .pro-login-button {
                transition: none;
            }

            .pro-login-button:hover {
                transform: none;
            }
        }
    </style>

    <div class="pro-login">
        <div class="pro-login-header">
            <p class="pro-login-eyebrow">Praticien</p>
            <h1 class="pro-login-title">Connexion</h1>
        </div>

        <x-auth-session-status class="mb-4 mt-5" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="pro-login-form">
            @csrf

            <div class="pro-login-field">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input
                    id="email"
                    class="block mt-1 w-full"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    inputmode="email"
                    autocomplete="username"
                    placeholder="vous@exemple.fr"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="pro-login-field">
                <x-input-label for="password" :value="__('Mot de passe')" />
                <x-password-toggle-input
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="block mt-1 w-full rounded-md border-gray-300 shadow-sm"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="pro-login-row">
                <label for="remember_me" class="pro-login-remember inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded shadow-sm focus:ring-[#647a0b]" name="remember">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Se souvenir de moi') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="pro-login-link" href="{{ route('password.request') }}">
                        {{ __('Mot de passe oublié ?') }}
                    </a>
                @endif
            </div>

            <button type="submit" class="pro-login-button">
                {{ __('Se connecter') }}
            </button>

            <div class="pro-login-footer">
                <p>
                    Vous êtes client ?
                    <a href="{{ route('client.login') }}">Espace client</a>
                </p>

                <p>
                    Nouveau praticien ?
                    <a href="{{ route('register-pro') }}">Créer un compte</a>
                </p>

                <p>
                    <a href="{{ route('login') }}">Retour</a>
                </p>
            </div>
        </form>
    </div>
</x-guest-layout>

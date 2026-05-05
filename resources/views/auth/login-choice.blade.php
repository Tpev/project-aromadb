<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Connexion - {{ config('app.name', 'AromaMade') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --olive: #647a0b;
                --olive-dark: #485c04;
                --brown: #854f38;
                --cream: #f7f3ea;
                --paper: #fffdf8;
                --ink: #171c2b;
                --muted: #667085;
                --line: #e8dfcf;
            }

            * {
                box-sizing: border-box;
            }

            html {
                min-height: 100%;
                -webkit-text-size-adjust: 100%;
            }

            body {
                margin: 0;
                min-height: 100vh;
                min-height: 100dvh;
                color: var(--ink);
                font-family: Figtree, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                background:
                    radial-gradient(circle at 18% 16%, rgba(100, 122, 11, 0.18), transparent 21rem),
                    radial-gradient(circle at 80% 86%, rgba(133, 79, 56, 0.14), transparent 24rem),
                    linear-gradient(135deg, #f9f6ef 0%, #f1eadc 100%);
            }

            .login-choice {
                min-height: 100vh;
                min-height: 100dvh;
                display: grid;
                place-items: center;
                padding: clamp(1rem, 4vw, 3rem);
            }

            .login-card {
                width: min(100%, 720px);
                overflow: hidden;
                border: 1px solid rgba(255, 255, 255, 0.78);
                border-radius: 36px;
                background: rgba(255, 253, 248, 0.96);
                box-shadow: 0 30px 80px rgba(42, 36, 22, 0.16);
            }

            .login-card-inner {
                padding: clamp(1.6rem, 5vw, 3.4rem);
            }

            .brand {
                display: flex;
                justify-content: center;
            }

            .brand a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: inherit;
                text-decoration: none;
            }

            .brand img {
                display: block;
                width: clamp(96px, 24vw, 132px);
                height: auto;
            }

            .hero {
                margin-top: clamp(1.5rem, 4vw, 2.4rem);
                text-align: center;
            }

            .eyebrow {
                margin: 0;
                color: var(--brown);
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.24em;
                text-transform: uppercase;
            }

            .hero h1 {
                margin: 0.65rem auto 0;
                max-width: 12ch;
                font-size: clamp(2.45rem, 10vw, 4.4rem);
                line-height: 0.95;
                letter-spacing: -0.06em;
            }

            .hero p {
                margin: 1rem auto 0;
                max-width: 32rem;
                color: var(--muted);
                font-size: clamp(1rem, 2.3vw, 1.12rem);
                line-height: 1.65;
            }

            .choices {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.9rem;
                margin-top: clamp(1.8rem, 5vw, 2.6rem);
            }

            .choice {
                position: relative;
                display: grid;
                min-height: 164px;
                align-content: space-between;
                padding: 1.35rem;
                border: 1px solid var(--line);
                border-radius: 26px;
                color: inherit;
                text-decoration: none;
                background: #ffffff;
                transition: transform 170ms ease, border-color 170ms ease, box-shadow 170ms ease;
            }

            .choice:hover {
                transform: translateY(-3px);
                box-shadow: 0 18px 42px rgba(45, 52, 24, 0.12);
            }

            .choice:focus-visible {
                outline: 3px solid rgba(100, 122, 11, 0.28);
                outline-offset: 4px;
            }

            .choice.client {
                background: linear-gradient(145deg, #fbfff3 0%, #f2f7e6 100%);
            }

            .choice.pro {
                background: linear-gradient(145deg, #fffaf6 0%, #faeee7 100%);
            }

            .choice-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .choice-label {
                color: var(--muted);
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.16em;
                text-transform: uppercase;
            }

            .choice-dot {
                width: 0.7rem;
                height: 0.7rem;
                border-radius: 999px;
                background: var(--olive);
                box-shadow: 0 0 0 8px rgba(100, 122, 11, 0.12);
            }

            .choice.pro .choice-dot {
                background: var(--brown);
                box-shadow: 0 0 0 8px rgba(133, 79, 56, 0.12);
            }

            .choice h2 {
                margin: 1.6rem 0 0;
                font-size: clamp(1.45rem, 4vw, 1.85rem);
                line-height: 1.08;
                letter-spacing: -0.04em;
            }

            .choice-cta {
                margin-top: 1.2rem;
                display: inline-flex;
                min-height: 44px;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                color: #ffffff;
                background: var(--olive);
                padding: 0.75rem 1rem;
                font-size: 0.92rem;
                font-weight: 800;
            }

            .choice.pro .choice-cta {
                background: var(--brown);
            }

            .footer-line {
                margin: clamp(1.4rem, 4vw, 2rem) 0 0;
                color: var(--muted);
                font-size: 0.94rem;
                line-height: 1.55;
                text-align: center;
            }

            .footer-line a {
                color: var(--olive-dark);
                font-weight: 800;
                text-decoration: underline;
                text-underline-offset: 0.18em;
            }

            @media (max-width: 660px) {
                .login-choice {
                    align-items: stretch;
                    padding: 0;
                }

                .login-card {
                    min-height: 100vh;
                    min-height: 100dvh;
                    border: 0;
                    border-radius: 0;
                    box-shadow: none;
                }

                .login-card-inner {
                    min-height: 100vh;
                    min-height: 100dvh;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    padding: 1.35rem;
                }

                .choices {
                    grid-template-columns: 1fr;
                }

                .choice {
                    min-height: 136px;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .choice {
                    transition: none;
                }

                .choice:hover {
                    transform: none;
                }
            }
        </style>
    </head>
    <body>
        <main class="login-choice" aria-labelledby="login-choice-title">
            <section class="login-card">
                <div class="login-card-inner">
                    <div class="brand">
                        <a href="{{ url('/') }}" aria-label="Retour à l'accueil AromaMade">
                            <img src="{{ asset('images/png-01.webp') }}" width="256" height="64" alt="{{ config('app.name', 'AromaMade') }}">
                        </a>
                    </div>

                    <div class="hero">
                        <p class="eyebrow">Connexion</p>
                        <h1 id="login-choice-title">Bienvenue</h1>
                        <p>Choisissez simplement votre espace pour continuer.</p>
                    </div>

                    <div class="choices" aria-label="Choix de connexion">
                        <a href="{{ route('client.login') }}" class="choice client">
                            <span class="choice-top">
                                <span class="choice-label">Client</span>
                                <span class="choice-dot" aria-hidden="true"></span>
                            </span>

                            <span>
                                <h2>Accéder à mon espace</h2>
                                <span class="choice-cta">Espace client</span>
                            </span>
                        </a>

                        <a href="{{ route('login.practitioner') }}" class="choice pro">
                            <span class="choice-top">
                                <span class="choice-label">Praticien</span>
                                <span class="choice-dot" aria-hidden="true"></span>
                            </span>

                            <span>
                                <h2>Gérer mon activité</h2>
                                <span class="choice-cta">Espace praticien</span>
                            </span>
                        </a>
                    </div>

                    <p class="footer-line">
                        Nouveau praticien ?
                        <a href="{{ route('register-pro') }}">Créer un compte</a>
                    </p>
                </div>
            </section>
        </main>
    </body>
</html>

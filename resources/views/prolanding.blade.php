<x-app-layout>
    @section('title', 'AromaMade PRO | Le logiciel pour praticiens du bien-être')
    @section('meta_description', 'AromaMade PRO centralise rendez-vous, suivi client, facturation, paiements, visio, visibilité en ligne et communication pour les praticiens du bien-être.')

    @section('meta_og')
        <meta property="og:type" content="website">
        <meta property="og:locale" content="fr_FR">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="AromaMade PRO | Le logiciel pour praticiens du bien-être">
        <meta property="og:description" content="Un seul outil pour piloter votre activité: agenda, clients, facturation, paiements, visio et visibilité en ligne.">
        <meta property="og:image" content="{{ asset('images/hero.webp') }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="AromaMade PRO | Le logiciel pour praticiens du bien-être">
        <meta name="twitter:description" content="Pilotez votre activité avec un flux simple: réservation, suivi, facturation et croissance.">
        <meta name="twitter:image" content="{{ asset('images/hero.webp') }}">
    @endsection

    @section('structured_data')
        <script type="application/ld+json">
            {
              "@context": "https://schema.org",
              "@type": "SoftwareApplication",
              "name": "AromaMade PRO",
              "applicationCategory": "BusinessApplication",
              "operatingSystem": "Web",
              "url": "{{ url('/pro') }}",
              "description": "Logiciel de gestion pour praticiens du bien-être: agenda, suivi client, facturation, paiements, visio, portail public et communication.",
              "offers": {
                "@type": "Offer",
                "priceCurrency": "EUR",
                "price": "0",
                "description": "Plan gratuit + essai PRO de 14 jours"
              }
            }
        </script>
    @endsection

    <section class="pro-hero">
        <div class="pro-hero-overlay"></div>
        <div class="pro-wrap pro-hero-grid">
            <div class="pro-hero-copy">
                <p class="pro-kicker">Logiciel métier pour praticiens du bien-être</p>
                <h1>Structurez votre activité et gagnez du temps chaque semaine</h1>
                <p class="pro-subtitle">
                    Agenda, suivi client, paiements, facturation, visio, portail public, avis et communication:
                    AromaMade PRO vous permet de tout piloter sans empiler plusieurs outils.
                    Multiplier des logiciels séparés est vite laborieux et coûteux.
                </p>
                <div class="pro-cta-row">
                    <a href="{{ route('register-pro') }}" class="pro-btn pro-btn-primary">Démarrer mon essai PRO 14 jours</a>
                    <a href="{{ route('register-pro') }}" class="pro-btn pro-btn-ghost">Créer mon profil gratuit</a>
                </div>
                <ul class="pro-hero-points">
                    <li>Mise en place rapide</li>
                    <li>Accessible mobile + ordinateur</li>
                    <li>Évolutif selon votre étape</li>
                </ul>
            </div>

            <aside class="pro-hero-panel">
                <h2>Vue rapide de votre journée</h2>
                <ul>
                    <li><strong>09:00</strong> Rendez-vous découverte</li>
                    <li><strong>11:30</strong> Séance visio</li>
                    <li><strong>15:00</strong> Suivi client</li>
                </ul>
                <p>Le même espace vous sert à planifier, suivre, facturer et fidéliser.</p>
            </aside>
        </div>
    </section>

    <section class="pro-value-strip">
        <div class="pro-wrap pro-value-grid">
            <article>
                <h2>Moins d'administratif</h2>
                <p>Vous réduisez les tâches dispersées et gardez un flux de travail plus fluide.</p>
            </article>
            <article>
                <h2>Plus de clarté</h2>
                <p>Vous suivez votre activité avec une vision propre des rendez-vous et revenus.</p>
            </article>
            <article>
                <h2>Meilleure conversion</h2>
                <p>Votre visibilité et votre réservation en ligne travaillent ensemble.</p>
            </article>
        </div>
    </section>

    <section class="pro-section">
        <div class="pro-wrap">
            <header class="pro-head">
                <p class="pro-overline">Différence AromaMade</p>
                <h2>Pourquoi notre approche est plus simple et plus rentable</h2>
            </header>
            <div class="pro-diff-grid">
                <article class="pro-card">
                    <h3>Un seul outil connecté</h3>
                    <p>Site public, réservation, visio, suivi client, facturation et communication: tout fonctionne ensemble, sans friction.</p>
                </article>
                <article class="pro-card">
                    <h3>Moins de coûts cachés</h3>
                    <p>Assembler plusieurs logiciels coûte plus cher et fait perdre du temps. Ici, vous centralisez l'essentiel dans une seule plateforme.</p>
                </article>
                <article class="pro-card">
                    <h3>Conditions plus justes</h3>
                    <p>Pas d'engagement long imposé. Et pas de surcoût automatique si vous exercez sur plusieurs lieux.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="pro-section pro-section-soft">
        <div class="pro-wrap">
            <header class="pro-head">
                <p class="pro-overline">Accompagnement humain</p>
                <h2>Vous n'êtes pas seul au démarrage, ni après</h2>
            </header>
            <div class="pro-support-box">
                <h3>On vous accompagne pour configurer votre compte</h3>
                <ul>
                    <li>Dès votre arrivée, vous obtenez un appel avec notre équipe pour mettre en place votre espace.</li>
                    <li>Nous vous guidons sur les réglages clés et répondons à vos questions concrètes.</li>
                    <li>Quand vous avez besoin d'aide, vous échangez avec une vraie personne, pas uniquement via un ticket impersonnel.</li>
                </ul>
                <div class="pro-cta-row">
                    <a href="{{ route('register-pro') }}" class="pro-btn pro-btn-primary">Démarrer avec accompagnement</a>
                </div>
            </div>
        </div>
    </section>

    <section class="pro-section">
        <div class="pro-wrap">
            <header class="pro-head">
                <p class="pro-overline">Valeur concrète</p>
                <h2>Pourquoi des praticiens choisissent AromaMade PRO</h2>
            </header>
            <div class="pro-outcome-grid">
                <article class="pro-card">
                    <h3>Un flux quotidien simple</h3>
                    <p>Réservation, suivi client, facturation et communication dans un même back-office.</p>
                </article>
                <article class="pro-card">
                    <h3>Une expérience plus claire pour vos clients</h3>
                    <p>Prise de rendez-vous fluide, rappels, visio, paiement et informations utiles bien présentées.</p>
                </article>
                <article class="pro-card">
                    <h3>Des leviers de croissance activables</h3>
                    <p>Portail public, avis, newsletters, offres et contenus digitaux pour développer votre activité.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="pro-section pro-section-soft">
        <div class="pro-wrap">
            <header class="pro-head">
                <p class="pro-overline">Fonctionnalités</p>
                <h2>Les blocs clés du produit</h2>
            </header>

            <div class="pro-feature-grid">
                <article class="pro-card">
                    <h3>Agenda et réservation</h3>
                    <p>Disponibilités, limites, règles et synchronisation.</p>
                    <a href="{{ route('features.agenda') }}">Découvrir</a>
                </article>
                <article class="pro-card">
                    <h3>Suivi client</h3>
                    <p>Dossiers, questionnaires, documents et historique.</p>
                    <a href="{{ route('features.dossiers') }}">Découvrir</a>
                </article>
                <article class="pro-card">
                    <h3>Facturation et paiements</h3>
                    <p>Édition des factures et règlement en ligne.</p>
                    <a href="{{ route('features.facturation') }}">Découvrir</a>
                </article>
                <article class="pro-card">
                    <h3>Portail public</h3>
                    <p>Vitrine personnalisée pour visibilité + réservation.</p>
                    <a href="{{ route('features.portailpro') }}">Découvrir</a>
                </article>
                <article class="pro-card">
                    <h3>Visio</h3>
                    <p>Gestion simple des rendez-vous à distance.</p>
                    <a href="{{ route('features.index') }}">Découvrir</a>
                </article>
                <article class="pro-card">
                    <h3>Communication et fidélisation</h3>
                    <p>Avis, newsletters et suivi relationnel.</p>
                    <a href="{{ route('features.index') }}">Découvrir</a>
                </article>
            </div>
        </div>
    </section>

    <section class="pro-section pro-pricing" id="tarifs">
        <div class="pro-wrap">
            <header class="pro-head">
                <p class="pro-overline">Tarifs et inclusions</p>
                <h2>Ce que vous obtenez, plan par plan</h2>
                <p class="pro-head-text">Objectif: vous aider à choisir sans ambiguïté.</p>
            </header>

            <div class="pro-pricing-grid">
                <article class="pro-plan">
                    <h3>Gratuit</h3>
                    <p class="pro-price">0 <span>€ / mois</span></p>
                    <p class="pro-plan-target">Idéal pour: démarrer votre présence en ligne</p>
                    <ul>
                        <li>Listing de base de votre profil</li>
                        <li>Visibilité auprès de milliers de clients</li>
                        <li>Essai 14 jours de la version Premium</li>
                        <li>Découverte des fonctionnalités clés</li>
                    </ul>
                    <a href="{{ route('register-pro') }}" class="pro-btn pro-btn-primary">Commencer</a>
                </article>

                <article class="pro-plan">
                    <h3>Starter</h3>
                    <p class="pro-price">9,90 <span>€ / mois</span></p>
                    <p class="pro-plan-target">Idéal pour: structurer votre activité</p>
                    <ul>
                        <li>Jusqu'à 50 dossiers clients</li>
                        <li>Agenda + réservation en ligne</li>
                        <li>Gestion des dossiers clients</li>
                        <li>Portail Pro</li>
                        <li>Questionnaires</li>
                        <li>Facturation de base</li>
                    </ul>
                    <a href="{{ route('register-pro') }}" class="pro-btn pro-btn-primary">Essai gratuit</a>
                </article>

                <article class="pro-plan pro-plan-highlight">
                    <p class="pro-badge">Le plus choisi</p>
                    <h3>Pro</h3>
                    <p class="pro-price">29,90 <span>€ / mois</span></p>
                    <p class="pro-plan-target">Idéal pour: gagner du temps et scaler</p>
                    <ul>
                        <li>Dossiers clients illimités</li>
                        <li>Toutes les fonctionnalités du plan Starter</li>
                        <li>Suivi des objectifs</li>
                        <li>Comptabilité (livre de recettes, suivi CA)</li>
                        <li>Options de paiement en ligne</li>
                        <li>Intégration réseaux sociaux</li>
                        <li>Création d'événements</li>
                        <li>Bibliothèque de conseils</li>
                        <li>Gestion et stockage de documents</li>
                        <li>Avis clients</li>
                        <li>Synchronisation des calendriers</li>
                        <li>Visio-conférence intégrée</li>
                    </ul>
                    <a href="{{ route('register-pro') }}" class="pro-btn pro-btn-primary">Essai gratuit</a>
                </article>

                <article class="pro-plan">
                    <h3>Premium</h3>
                    <p class="pro-price">39,90 <span>€ / mois</span></p>
                    <p class="pro-plan-target">Idéal pour: monétiser plus de formats</p>
                    <ul>
                        <li>Toutes les fonctionnalités Starter + Pro</li>
                        <li>Formations et contenus digitaux</li>
                        <li>Communication client avancée</li>
                        <li>Création, hébergement et vente de formations en ligne</li>
                        <li>Hébergement et vente de contenus digitaux (ebooks, guides, ressources)</li>
                        <li>Outil de newsletters et communication clients</li>
                    </ul>
                    <a href="{{ route('register-pro') }}" class="pro-btn pro-btn-primary">Essai gratuit</a>
                </article>
            </div>

            <div class="pro-compare-wrap">
                <h3>Comparatif rapide</h3>
                <div class="pro-compare-table-wrap">
                    <table class="pro-compare-table">
                        <thead>
                            <tr>
                                <th>Fonction</th>
                                <th>Gratuit</th>
                                <th>Starter</th>
                                <th>Pro</th>
                                <th>Premium</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Portail public</td><td>Oui</td><td>Oui</td><td>Oui</td><td>Oui</td></tr>
                            <tr><td>Réservation en ligne</td><td>-</td><td>Oui</td><td>Oui</td><td>Oui</td></tr>
                            <tr><td>Dossiers clients illimités</td><td>-</td><td>-</td><td>Oui</td><td>Oui</td></tr>
                            <tr><td>Questionnaires</td><td>-</td><td>Oui</td><td>Oui</td><td>Oui</td></tr>
                            <tr><td>Facturation</td><td>-</td><td>Oui</td><td>Oui</td><td>Oui</td></tr>
                            <tr><td>Paiement en ligne</td><td>-</td><td>-</td><td>Oui</td><td>Oui</td></tr>
                            <tr><td>Avis clients</td><td>-</td><td>-</td><td>Oui</td><td>Oui</td></tr>
                            <tr><td>Visio</td><td>-</td><td>-</td><td>Oui</td><td>Oui</td></tr>
                            <tr><td>Événements</td><td>-</td><td>-</td><td>Oui</td><td>Oui</td></tr>
                            <tr><td>Réseaux sociaux</td><td>-</td><td>-</td><td>Oui</td><td>Oui</td></tr>
                            <tr><td>Contenus digitaux / formations</td><td>-</td><td>-</td><td>-</td><td>Oui</td></tr>
                            <tr><td>Newsletters</td><td>-</td><td>-</td><td>-</td><td>Oui</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="pro-note">Paiement annuel disponible avec 1 mois offert.</p>
            </div>
        </div>
    </section>

    <section class="pro-section" id="faq">
        <div class="pro-wrap">
            <header class="pro-head">
                <p class="pro-overline">FAQ</p>
                <h2>Questions fréquentes</h2>
            </header>
            <div class="pro-faq-grid">
                <details><summary>Puis-je commencer sans engagement ?</summary><p>Oui, avec un plan gratuit et un essai PRO de 14 jours.</p></details>
                <details><summary>Mes clients peuvent-ils réserver en ligne ?</summary><p>Oui, selon votre plan et vos règles de disponibilité.</p></details>
                <details><summary>Puis-je gérer présentiel et visio ?</summary><p>Oui, le même flux couvre les deux modes de rendez-vous.</p></details>
                <details><summary>Le paiement en ligne est-il inclus ?</summary><p>Il est inclus à partir du plan Pro.</p></details>
                <details><summary>Comment choisir entre Starter et Pro ?</summary><p>Starter structure les bases; Pro ajoute les leviers avancés de productivité et conversion.</p></details>
                <details><summary>Puis-je évoluer de plan facilement ?</summary><p>Oui, vous pouvez passer au plan supérieur quand vous le souhaitez.</p></details>
                <details><summary>Pourquoi éviter d'assembler plusieurs logiciels séparés ?</summary><p>Parce que cela augmente les coûts, la complexité et les risques d'erreurs. Avec AromaMade PRO, tout reste synchronisé dans un seul environnement.</p></details>
                <details><summary>Est-ce que vous m'aidez à configurer mon espace ?</summary><p>Oui. Un appel de démarrage est prévu pour vous guider étape par étape et répondre à vos questions.</p></details>
                <details><summary>En cas de besoin, ai-je un vrai contact humain ?</summary><p>Oui. Vous avez un accompagnement humain, pas seulement une file de tickets automatique.</p></details>
            </div>
        </div>
    </section>

    <section class="pro-final-cta">
        <div class="pro-wrap pro-center">
            <h2>Passez d'une gestion dispersée à une activité mieux pilotée</h2>
            <p>Essayez AromaMade PRO et voyez rapidement l'impact sur votre organisation et votre croissance.</p>
            <div class="pro-cta-row pro-center-row">
                <a href="{{ route('register-pro') }}" class="pro-btn pro-btn-primary">Lancer mon essai gratuit</a>
                <a href="{{ route('features.index') }}" class="pro-btn pro-btn-outline">Explorer les fonctionnalités</a>
            </div>
        </div>
    </section>

    @push('styles')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --pro-primary: #6f860f;
                --pro-secondary: #8b5336;
                --pro-ink: #1d2430;
                --pro-ink-soft: #4f6073;
                --pro-border: #d7e3c4;
                --pro-soft: #f4f8ed;
                --pro-bg: #f8faf6;
                --pro-radius: 16px;
                --pro-shadow: 0 16px 35px rgba(36, 56, 21, 0.09);
                --pro-shadow-soft: 0 8px 18px rgba(36, 56, 21, 0.06);
            }

            .pro-wrap { max-width: 1240px; margin: 0 auto; padding: 0 1.25rem; }
            .pro-section, .pro-value-strip, .pro-pricing, .pro-final-cta {
                animation: revealUp .75s ease both;
            }
            .pro-section:nth-of-type(2) { animation-delay: .06s; }
            .pro-section:nth-of-type(3) { animation-delay: .12s; }
            .pro-section:nth-of-type(4) { animation-delay: .18s; }
            .pro-section:nth-of-type(5) { animation-delay: .24s; }
            .pro-section:nth-of-type(6) { animation-delay: .3s; }

            .pro-hero {
                position: relative;
                min-height: 88vh;
                display: flex;
                align-items: center;
                overflow: hidden;
                background: #111827;
                isolation: isolate;
            }
            .pro-hero::before {
                content: "";
                position: absolute;
                inset: 0;
                background: url('{{ asset('images/hero.webp') }}') center/cover no-repeat;
                opacity: .95;
                transform: scale(1.03);
            }
            .pro-hero::after {
                content: "";
                position: absolute;
                inset: auto -12% -120px -12%;
                height: 260px;
                background: radial-gradient(ellipse at center, rgba(248,250,246,.95) 0%, rgba(248,250,246,.2) 65%, rgba(248,250,246,0) 100%);
                pointer-events: none;
            }
            .pro-hero-overlay {
                position: absolute;
                inset: 0;
                background:
                    radial-gradient(circle at 80% 28%, rgba(111,134,15,.25) 0%, rgba(111,134,15,0) 40%),
                    radial-gradient(circle at 20% 40%, rgba(139,83,54,.18) 0%, rgba(139,83,54,0) 40%),
                    linear-gradient(101deg, rgba(9,14,23,.9) 18%, rgba(9,14,23,.45) 63%, rgba(9,14,23,.72) 100%);
            }
            .pro-hero-grid {
                position: relative;
                z-index: 2;
                display: grid;
                grid-template-columns: 1.2fr .88fr;
                gap: 1.2rem;
                width: 100%;
                padding-top: 6.2rem;
                padding-bottom: 5.3rem;
            }
            .pro-kicker {
                display: inline-block;
                margin: 0 0 1rem;
                padding: .36rem .82rem;
                border: 1px solid rgba(255,255,255,.46);
                border-radius: 999px;
                color: #fff;
                font: 600 .78rem/1 'Space Grotesk', sans-serif;
                text-transform: uppercase;
                letter-spacing: .06em;
                background: rgba(255,255,255,.08);
                backdrop-filter: blur(4px);
            }
            .pro-hero-copy h1,
            .pro-head h2,
            .pro-center h2 {
                font-family: 'Space Grotesk', sans-serif;
                letter-spacing: -.015em;
            }
            .pro-hero-copy h1 {
                margin: 0;
                color: #fff;
                line-height: 1.04;
                text-wrap: balance;
                font-size: clamp(2.2rem, 3.9vw, 4.2rem);
                text-shadow: 0 8px 24px rgba(0,0,0,.25);
            }
            .pro-subtitle {
                margin: 1rem 0 0;
                max-width: 720px;
                color: rgba(255,255,255,.96);
                line-height: 1.63;
                font: 500 clamp(1.03rem, 1.3vw, 1.23rem)/1.63 'Source Sans 3', sans-serif;
            }
            .pro-cta-row {
                display: flex;
                flex-wrap: wrap;
                gap: .78rem;
                margin-top: 1.45rem;
            }
            .pro-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                border-radius: 12px;
                padding: .88rem 1.25rem;
                border: 1px solid transparent;
                font: 700 .95rem/1 'Space Grotesk', sans-serif;
                letter-spacing: .01em;
                transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease, background-color .22s ease;
            }
            .pro-btn:hover { transform: translateY(-2px); }
            .pro-btn-primary {
                color: #fff;
                background: linear-gradient(92deg, var(--pro-primary) 0%, var(--pro-secondary) 100%);
                box-shadow: 0 12px 24px rgba(20,28,18,.35);
            }
            .pro-btn-ghost {
                color: #fff;
                border-color: rgba(255,255,255,.66);
                background: rgba(255,255,255,.08);
            }
            .pro-btn-outline {
                color: var(--pro-ink);
                border-color: #c5d4af;
                background: rgba(255,255,255,.9);
            }
            .pro-hero-points {
                margin: 1rem 0 0;
                padding: 0;
                list-style: none;
                display: flex;
                flex-wrap: wrap;
                gap: .85rem;
                color: rgba(255,255,255,.94);
                font: 600 .91rem/1.3 'Source Sans 3', sans-serif;
            }
            .pro-hero-points li::before { content: "✓ "; font-weight: 700; }
            .pro-hero-panel {
                align-self: end;
                border: 1px solid rgba(255,255,255,.35);
                border-radius: var(--pro-radius);
                background: linear-gradient(180deg, rgba(12,18,28,.68) 0%, rgba(12,18,28,.78) 100%);
                backdrop-filter: blur(8px);
                color: #fff;
                padding: 1.15rem;
                box-shadow: 0 20px 40px rgba(8,13,22,.38);
            }
            .pro-hero-panel h2 {
                margin: 0;
                color: #fff;
                font: 700 1.08rem/1.2 'Space Grotesk', sans-serif;
            }
            .pro-hero-panel ul {
                margin: .82rem 0 .88rem;
                padding-left: 1.1rem;
                font: 500 .95rem/1.45 'Source Sans 3', sans-serif;
            }
            .pro-hero-panel li { margin: .34rem 0; }
            .pro-hero-panel p { margin: 0; color: rgba(255,255,255,.96); font: 500 .95rem/1.48 'Source Sans 3', sans-serif; }

            .pro-value-strip {
                position: relative;
                background: linear-gradient(180deg, #fbfdf8 0%, #f6f9f2 100%);
                border-top: 1px solid #e8efdc;
                border-bottom: 1px solid #e8efdc;
            }
            .pro-value-grid {
                display: grid;
                grid-template-columns: repeat(3,minmax(0,1fr));
                gap: .95rem;
                padding-top: 1.3rem;
                padding-bottom: 1.3rem;
            }
            .pro-value-grid article {
                padding: .8rem .9rem;
                border-radius: 14px;
                background: #fff;
                border: 1px solid #e3ead6;
                box-shadow: var(--pro-shadow-soft);
            }
            .pro-value-grid h2 {
                margin: 0;
                color: var(--pro-ink);
                font: 700 1.1rem/1.25 'Space Grotesk', sans-serif;
            }
            .pro-value-grid p {
                margin: .4rem 0 0;
                color: #5f7083;
                font: 500 .96rem/1.45 'Source Sans 3', sans-serif;
            }

            .pro-section {
                position: relative;
                padding: 4.4rem 0;
                background: #fff;
            }
            .pro-section-soft {
                background:
                    radial-gradient(circle at 10% 10%, rgba(111,134,15,.06) 0%, rgba(111,134,15,0) 28%),
                    radial-gradient(circle at 92% 82%, rgba(139,83,54,.06) 0%, rgba(139,83,54,0) 28%),
                    var(--pro-soft);
            }
            .pro-head {
                max-width: 900px;
                margin: 0 auto 1.9rem;
                text-align: center;
            }
            .pro-overline {
                margin: 0;
                display: inline-block;
                padding: .3rem .64rem;
                border-radius: 999px;
                font: 700 .76rem/1 'Space Grotesk', sans-serif;
                text-transform: uppercase;
                letter-spacing: .06em;
                color: #385021;
                background: #ebf3db;
                border: 1px solid #cfe0b2;
            }
            .pro-head h2 {
                margin: .78rem 0 0;
                color: var(--pro-ink);
                font-size: clamp(1.75rem,2.85vw,2.7rem);
                line-height: 1.12;
                text-wrap: balance;
            }
            .pro-head-text, .pro-head p {
                margin: .78rem 0 0;
                color: var(--pro-ink-soft);
                font: 500 1.04rem/1.62 'Source Sans 3', sans-serif;
            }

            .pro-outcome-grid,
            .pro-diff-grid,
            .pro-feature-grid {
                display: grid;
                grid-template-columns: repeat(3,minmax(0,1fr));
                gap: .95rem;
            }
            .pro-card {
                border: 1px solid var(--pro-border);
                border-radius: var(--pro-radius);
                background: #fff;
                padding: 1.12rem 1.05rem;
                box-shadow: var(--pro-shadow-soft);
                transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
            }
            .pro-card:hover {
                transform: translateY(-3px);
                box-shadow: var(--pro-shadow);
                border-color: #c9d8b1;
            }
            .pro-card h3 {
                margin: 0;
                color: var(--pro-ink);
                font: 700 1.1rem/1.3 'Space Grotesk', sans-serif;
            }
            .pro-card p {
                margin: .56rem 0 0;
                color: #506273;
                font: 500 .97rem/1.56 'Source Sans 3', sans-serif;
            }
            .pro-card a {
                display: inline-block;
                margin-top: .8rem;
                color: var(--pro-primary);
                font: 700 .95rem/1 'Space Grotesk', sans-serif;
                text-decoration: none;
            }
            .pro-card a:hover { text-decoration: underline; text-underline-offset: 2px; }

            .pro-support-box {
                border: 1px solid #d5e2c1;
                border-radius: 18px;
                background: linear-gradient(180deg, #ffffff 0%, #f9fbf5 100%);
                padding: 1.2rem;
                box-shadow: var(--pro-shadow);
                max-width: 940px;
                margin: 0 auto;
            }
            .pro-support-box h3 {
                margin: 0;
                color: var(--pro-ink);
                font: 700 1.2rem/1.3 'Space Grotesk', sans-serif;
            }
            .pro-support-box ul {
                margin: .86rem 0 0;
                padding-left: 1.15rem;
                color: #4f6173;
                font: 500 1rem/1.64 'Source Sans 3', sans-serif;
            }
            .pro-support-box li { margin: .45rem 0; }

            .pro-pricing {
                background:
                    radial-gradient(circle at 18% 18%, rgba(111,134,15,.08) 0%, rgba(111,134,15,0) 30%),
                    radial-gradient(circle at 88% 22%, rgba(139,83,54,.07) 0%, rgba(139,83,54,0) 30%),
                    linear-gradient(180deg, #f2f7e9 0%, #ebf1e0 100%);
                border-top: 1px solid #dce8c9;
                border-bottom: 1px solid #dce8c9;
            }
            .pro-pricing-grid {
                display: grid;
                grid-template-columns: repeat(4,minmax(0,1fr));
                gap: .95rem;
            }
            .pro-plan {
                border: 1px solid #d7e2c6;
                border-radius: 18px;
                background: #fff;
                padding: 1.1rem;
                box-shadow: 0 12px 22px rgba(44,66,28,.09);
                display: flex;
                flex-direction: column;
                transition: transform .2s ease, box-shadow .2s ease;
            }
            .pro-plan:hover {
                transform: translateY(-3px);
                box-shadow: 0 18px 30px rgba(44,66,28,.12);
            }
            .pro-plan-highlight {
                border: 2px solid var(--pro-primary);
                box-shadow: 0 20px 34px rgba(100,122,11,.2);
                position: relative;
            }
            .pro-badge {
                margin: 0 0 .4rem;
                width: fit-content;
                border-radius: 999px;
                padding: .24rem .6rem;
                color: #fff;
                font: 700 .76rem/1 'Space Grotesk', sans-serif;
                background: linear-gradient(90deg,#6b8510 0%,#8f562f 100%);
            }
            .pro-plan h3 {
                margin: 0;
                color: var(--pro-ink);
                font: 700 1.24rem/1.2 'Space Grotesk', sans-serif;
            }
            .pro-price {
                margin: .54rem 0 .68rem;
                color: #111827;
                font: 800 2.1rem/1 'Space Grotesk', sans-serif;
            }
            .pro-price span {
                color: #64748b;
                font: 600 .86rem/1 'Source Sans 3', sans-serif;
            }
            .pro-plan-target {
                margin: 0 0 .72rem;
                color: #566a7e;
                font: 600 .92rem/1.45 'Source Sans 3', sans-serif;
            }
            .pro-plan ul {
                margin: 0 0 .95rem;
                padding-left: 1.08rem;
                color: #425366;
                font: 500 .93rem/1.56 'Source Sans 3', sans-serif;
            }
            .pro-plan .pro-btn { margin-top: auto; }

            .pro-compare-wrap { margin-top: 1.6rem; }
            .pro-compare-wrap h3 {
                margin: 0 0 .68rem;
                color: var(--pro-ink);
                font: 700 1.2rem/1.2 'Space Grotesk', sans-serif;
            }
            .pro-compare-table-wrap {
                width: 100%;
                overflow-x: auto;
                border: 1px solid #d6e2c3;
                border-radius: 12px;
                background: #fff;
                box-shadow: var(--pro-shadow-soft);
            }
            .pro-compare-table {
                width: 100%;
                min-width: 820px;
                border-collapse: collapse;
            }
            .pro-compare-table th,
            .pro-compare-table td {
                padding: .74rem .78rem;
                border-bottom: 1px solid #e8efdd;
                text-align: left;
                font: 600 .9rem/1.35 'Source Sans 3', sans-serif;
                color: #304153;
            }
            .pro-compare-table thead th {
                background: #f2f7ea;
                color: #243244;
                font-weight: 700;
                position: sticky;
                top: 0;
                z-index: 1;
            }
            .pro-compare-table tbody tr:nth-child(even) td { background: #fcfef9; }
            .pro-compare-table tbody tr:last-child td { border-bottom: none; }
            .pro-note {
                margin: .82rem 0 0;
                color: #5f7184;
                font: 600 .9rem/1.4 'Source Sans 3', sans-serif;
            }

            .pro-faq-grid {
                display: grid;
                grid-template-columns: repeat(2,minmax(0,1fr));
                gap: .75rem;
            }
            .pro-faq-grid details {
                border: 1px solid #d5e1c3;
                border-radius: 12px;
                padding: .95rem 1.04rem;
                background: #fff;
                box-shadow: var(--pro-shadow-soft);
                transition: border-color .2s ease, box-shadow .2s ease;
            }
            .pro-faq-grid details[open] {
                border-color: #bfd1a3;
                box-shadow: var(--pro-shadow);
            }
            .pro-faq-grid summary {
                cursor: pointer;
                color: var(--pro-ink);
                font: 700 1rem/1.35 'Space Grotesk', sans-serif;
                list-style: none;
                position: relative;
                padding-right: 1.6rem;
            }
            .pro-faq-grid summary::-webkit-details-marker { display: none; }
            .pro-faq-grid summary::after {
                content: "+";
                position: absolute;
                right: 0;
                top: -.02rem;
                color: var(--pro-primary);
                font: 700 1.2rem/1 'Space Grotesk', sans-serif;
            }
            .pro-faq-grid details[open] summary::after { content: "−"; }
            .pro-faq-grid p {
                margin: .62rem 0 0;
                color: #516173;
                font: 500 .98rem/1.55 'Source Sans 3', sans-serif;
            }

            .pro-final-cta {
                padding: 3.5rem 0;
                background:
                    radial-gradient(circle at 50% -30%, rgba(111,134,15,.2) 0%, rgba(111,134,15,0) 52%),
                    linear-gradient(180deg, #e9f2d8 0%, #dfebc8 100%);
                border-top: 1px solid #d0dfb4;
            }
            .pro-center { text-align: center; }
            .pro-center h2 {
                margin: 0;
                color: var(--pro-ink);
                font-size: clamp(1.55rem,2.25vw,2.35rem);
                line-height: 1.15;
            }
            .pro-center p {
                margin: .75rem auto 0;
                max-width: 760px;
                color: #4b5d6e;
                font: 500 1.02rem/1.6 'Source Sans 3', sans-serif;
            }
            .pro-center-row { justify-content: center; }

            @keyframes revealUp {
                from { opacity: 0; transform: translateY(16px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @media (max-width: 1180px) {
                .pro-hero-copy h1 { font-size: clamp(2rem, 4.2vw, 3.5rem); }
                .pro-feature-grid,
                .pro-pricing-grid,
                .pro-outcome-grid,
                .pro-diff-grid {
                    grid-template-columns: repeat(2,minmax(0,1fr));
                }
            }
            @media (max-width: 960px) {
                .pro-hero-grid {
                    grid-template-columns: 1fr;
                    padding-top: 4.9rem;
                    padding-bottom: 3.95rem;
                }
                .pro-value-grid { grid-template-columns: 1fr; }
            }
            @media (max-width: 760px) {
                .pro-wrap { padding: 0 1rem; }
                .pro-hero { min-height: auto; }
                .pro-hero-copy h1 { font-size: clamp(2rem, 8vw, 2.75rem); }
                .pro-subtitle { font-size: 1rem; }
                .pro-section { padding-top: 3.2rem; padding-bottom: 3.2rem; }
                .pro-feature-grid,
                .pro-pricing-grid,
                .pro-faq-grid,
                .pro-outcome-grid,
                .pro-diff-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endpush
</x-app-layout>

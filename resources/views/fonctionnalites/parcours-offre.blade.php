<x-app-layout>
    @section('title', 'Tunnel de vente pour praticien et page de capture | Olithea')
    @section('meta_description', 'Créez un tunnel de vente simple avec page de capture, formulaire et prochaine étape reliée à votre agenda, vos ateliers et vos offres Olithea.')

    @section('meta_og')
        <meta property="og:type" content="website">
        <meta property="og:locale" content="fr_FR">
        <meta property="og:url" content="{{ route('features.offer-journeys') }}">
        <meta property="og:title" content="Tunnel de vente pour praticien et page de capture | Olithea">
        <meta property="og:description" content="Présentez une offre, recueillez les demandes et guidez chaque personne vers la bonne prochaine étape dans Olithea.">
        <meta property="og:image" content="{{ asset('images/features/parcours-offre-creation.webp') }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Parcours d’offre Olithea">
        <meta name="twitter:description" content="Des pages de capture et un tunnel de vente reliés à votre activité de praticien.">
        <meta name="twitter:image" content="{{ asset('images/features/parcours-offre-creation.webp') }}">
    @endsection

    @section('structured_data')
        @php
            $offerFaq = [
                ['question' => 'Qu’est-ce qu’un tunnel de vente pour un praticien ?', 'answer' => 'C’est un chemin simple qui part d’un lien partagé, présente une proposition et guide la personne vers une action utile : demander un rendez-vous, s’inscrire, recevoir une ressource ou vous contacter.'],
                ['question' => 'Faut-il savoir créer un site internet ?', 'answer' => 'Non. Vous choisissez un objectif, renseignez vos textes et votre bouton, puis prévisualisez la page avant de la publier. Aucune ligne de code n’est nécessaire.'],
                ['question' => 'À quoi puis-je relier un parcours ?', 'answer' => 'Selon les éléments déjà présents dans votre compte, un parcours peut conduire vers une réservation, un événement, une formation, un bon cadeau, une ressource gratuite ou une demande de contact.'],
                ['question' => 'Les emails automatiques sont-ils obligatoires ?', 'answer' => 'Non. Une page et son formulaire peuvent fonctionner sans campagne marketing. Les fonctions d’envoi et de relance sont ouvertes progressivement selon l’activation du compte et nécessitent un consentement adapté.'],
                ['question' => 'Cette fonctionnalité garantit-elle davantage de clients ?', 'answer' => 'Non. Olithea fournit une structure pour clarifier votre offre, recueillir les demandes et mieux suivre les contacts, sans garantir un volume de rendez-vous ou de ventes.'],
            ];
            $offerStructuredData = [
                '@context' => 'https://schema.org',
                '@graph' => [
                    ['@type' => 'BreadcrumbList', 'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Fonctionnalités', 'item' => route('features.index')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => 'Parcours d’offre', 'item' => route('features.offer-journeys')],
                    ]],
                    ['@type' => 'SoftwareApplication', 'name' => 'Parcours d’offre Olithea', 'applicationCategory' => 'BusinessApplication', 'operatingSystem' => 'Web', 'description' => 'Création sans code de tunnels de vente et de pages de capture reliés à l’activité du praticien.', 'url' => route('features.offer-journeys')],
                    ['@type' => 'FAQPage', 'mainEntity' => collect($offerFaq)->map(fn ($item) => ['@type' => 'Question', 'name' => $item['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']]])->all()],
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($offerStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endsection

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/feature-marketing.css') }}">
    @endpush

    <main class="fm-page">
        <section class="fm-hero" style="background-image:url('{{ asset('images/features/parcours-offre-creation.webp') }}')">
            <div class="fm-wrap">
                <div class="fm-hero-content">
                    <nav class="fm-breadcrumb" aria-label="Fil d’Ariane">
                        <a href="{{ url('/') }}">Accueil</a><span>›</span>
                        <a href="{{ route('features.index') }}">Fonctionnalités</a><span>›</span>
                        <span>Parcours d’offre</span>
                    </nav>
                    <p class="fm-eyebrow">Tunnels de vente pour praticiens</p>
                    <h1>Transformez un simple lien en rendez-vous, inscription ou demande qualifiée.</h1>
                    <p class="fm-hero-lead">Créez une page de capture sans code, partagez-la et guidez chaque personne vers la bonne prochaine étape. Les demandes et vos offres restent reliées à Olithea.</p>
                    <div class="fm-actions">
                        <a href="{{ route('register-pro') }}" class="fm-btn fm-btn-primary">Créer mon premier parcours <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                        <a href="#comment-ca-marche" class="fm-btn fm-btn-light">Voir comment ça marche</a>
                    </div>
                    <ul class="fm-proofline" aria-label="Points clés">
                        <li>Sans code</li>
                        <li>Relié à votre activité</li>
                        <li>Vous gardez le contrôle</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="fm-section fm-section-soft fm-simple-section" id="comment-ca-marche">
            <div class="fm-wrap">
                <header class="fm-heading fm-heading-centered">
                    <p class="fm-kicker">Le principe</p>
                    <h2>Un tunnel de vente, c’est simplement un chemin vers une action.</h2>
                    <p>Au lieu d’envoyer vers une page générale, vous donnez une suite claire à l’intérêt créé par votre site, vos réseaux sociaux ou vos échanges.</p>
                </header>
            </div>
            <div class="fm-wrap fm-flow fm-flow-four" aria-label="Fonctionnement d’un tunnel de vente Olithea">
                @foreach([
                    ['01', 'Vous partagez', 'Un lien vers une proposition précise.'],
                    ['02', 'La personne comprend', 'Une page explique l’offre et la prochaine étape.'],
                    ['03', 'Elle passe à l’action', 'Elle remplit le formulaire si nécessaire.'],
                    ['04', 'Olithea prend le relais', 'Réservation, inscription, ressource ou demande.'],
                ] as $flow)
                    <div class="fm-flow-item"><span class="fm-flow-number">{{ $flow[0] }}</span><strong>{{ $flow[1] }}</strong><span>{{ $flow[2] }}</span></div>
                @endforeach
            </div>
        </section>

        <section class="fm-section">
            <div class="fm-wrap fm-two-col">
                <div class="fm-mobile-showcase">
                    <img src="{{ asset('images/features/parcours-offre-capture-mobile.webp') }}" alt="Page de capture Olithea affichée sur téléphone" width="375" height="812" loading="lazy">
                </div>
                <div class="fm-copy">
                    <p class="fm-kicker">Ce que vous pouvez faire</p>
                    <h2>Une page, un objectif, une prochaine étape évidente.</h2>
                    <p>Chaque parcours répond à une intention concrète. Vous choisissez le résultat attendu ; Olithea prépare le chemin correspondant.</p>
                    <div class="fm-outcome-list">
                        @foreach([
                            ['fa-book-open', 'Partager une ressource gratuite', 'Présentez votre guide, recueillez les coordonnées utiles puis donnez accès au contenu.'],
                            ['fa-calendar-check', 'Obtenir une réservation ou une inscription', 'Expliquez votre séance ou votre atelier avant d’ouvrir l’agenda ou l’inscription.'],
                            ['fa-gift', 'Présenter une offre existante', 'Conduisez vers une formation, un bon cadeau ou une demande de premier échange.'],
                        ] as $outcome)
                            <div class="fm-outcome-item"><span><i class="fas {{ $outcome[0] }}" aria-hidden="true"></i></span><div><h3>{{ $outcome[1] }}</h3><p>{{ $outcome[2] }}</p></div></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="fm-section fm-section-blue">
            <div class="fm-wrap">
                <header class="fm-heading fm-heading-centered">
                    <p class="fm-kicker">Mise en ligne</p>
                    <h2>Créez votre parcours en trois étapes.</h2>
                    <p>Vous partez de votre activité, pas d’un éditeur marketing vide.</p>
                </header>
                <div class="fm-grid-3 fm-steps-grid">
                    @foreach([
                        ['01', 'Choisissez votre objectif', 'Réservation, atelier, ressource, formation, bon cadeau ou demande qualifiée.'],
                        ['02', 'Personnalisez la page', 'Ajoutez votre titre, votre explication, le bouton et, si nécessaire, un formulaire simple.'],
                        ['03', 'Prévisualisez et partagez', 'Vérifiez le parcours puis diffusez son lien là où votre audience vous suit déjà.'],
                    ] as $step)
                        <article class="fm-step-card"><span>{{ $step[0] }}</span><h3>{{ $step[1] }}</h3><p>{{ $step[2] }}</p></article>
                    @endforeach
                </div>
                <figure class="fm-product-frame fm-product-frame-wide">
                    <img src="{{ asset('images/features/parcours-offre-creation.webp') }}" alt="Choix de l’objectif lors de la création d’un parcours d’offre Olithea" width="1425" height="891" loading="lazy">
                    <figcaption class="fm-product-caption">Olithea propose directement les objectifs utiles à votre activité.</figcaption>
                </figure>
            </div>
        </section>

        <section class="fm-section fm-section-dark">
            <div class="fm-wrap fm-connected-layout">
                <div>
                    <header class="fm-heading">
                    <p class="fm-kicker">La différence Olithea</p>
                    <h2>Pas de nouvel outil à recoller au reste.</h2>
                    <p>Le parcours utilise ce que vous gérez déjà dans Olithea et vous ramène les demandes au même endroit.</p>
                    </header>
                    <div class="fm-connected-list">
                        <div><i class="fas fa-calendar-alt" aria-hidden="true"></i><p><strong>Agenda et ateliers</strong><span>Dirigez vers vos disponibilités et inscriptions existantes.</span></p></div>
                        <div><i class="fas fa-graduation-cap" aria-hidden="true"></i><p><strong>Formations et bons cadeaux</strong><span>Présentez l’offre avant d’ouvrir son accès ou son paiement.</span></p></div>
                        <div><i class="fas fa-address-book" aria-hidden="true"></i><p><strong>Personnes intéressées</strong><span>Retrouvez la demande, son origine et le consentement associé.</span></p></div>
                        <div><i class="fas fa-chart-line" aria-hidden="true"></i><p><strong>Résultats</strong><span>Lorsque le suivi est activé, visualisez visites, actions et formulaires.</span></p></div>
                    </div>
                </div>
                <figure class="fm-product-frame">
                    <img src="{{ asset('images/features/parcours-offre-resultats.webp') }}" alt="Tableau de résultats d’un parcours d’offre Olithea" width="1425" height="891" loading="lazy">
                    <figcaption class="fm-product-caption">Lorsque le suivi est disponible, les résultats du parcours sont réunis dans une vue dédiée.</figcaption>
                </figure>
            </div>
        </section>

        <section class="fm-section fm-section-soft">
            <div class="fm-wrap">
                <header class="fm-heading fm-heading-centered"><p class="fm-kicker">Questions fréquentes</p><h2>L’essentiel avant de commencer.</h2></header>
                <div class="fm-faq">
                    @foreach($offerFaq as $item)
                        <details><summary>{{ $item['question'] }}</summary><p>{{ $item['answer'] }}</p></details>
                    @endforeach
                </div>
                <div class="fm-scope-note"><i class="fas fa-info-circle" aria-hidden="true"></i><div><h3>Une ouverture progressive</h3><p>Les Parcours d’offre et leurs pages publiques sont activés progressivement pour les comptes éligibles. Les campagnes et relances email peuvent être disponibles séparément ; un parcours peut fonctionner sans suivi marketing automatique.</p></div></div>
            </div>
        </section>

        <section class="fm-cta">
            <div class="fm-wrap fm-cta-inner">
                <div><h2>Donnez une suite claire à chaque lien que vous partagez.</h2><p>Choisissez un objectif, créez votre page et reliez-la à ce que vous proposez déjà dans Olithea.</p></div>
                <div class="fm-actions"><a href="{{ route('register-pro') }}" class="fm-btn fm-btn-primary">Créer mon premier parcours <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
            </div>
        </section>
    </main>
</x-app-layout>

<x-app-layout>
    @section('title', 'Réception de factures électroniques pour praticien | Olithea')
    @section('meta_description', 'Préparez votre entreprise à l’obligation de réception du 1er septembre 2026 et retrouvez vos factures électroniques d’achat dans Olithea.')

    @section('meta_og')
        <meta property="og:type" content="website">
        <meta property="og:locale" content="fr_FR">
        <meta property="og:url" content="{{ route('features.e-invoicing') }}">
        <meta property="og:title" content="Réception de factures électroniques pour praticien | Olithea">
        <meta property="og:description" content="Connectez votre entreprise et centralisez la réception de vos factures électroniques d’achat dans Olithea.">
        <meta property="og:image" content="{{ asset('images/features/facturation-electronique-inbox.webp') }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Facturation électronique avec Olithea">
        <meta name="twitter:description" content="Recevez et consultez vos factures électroniques d’achat dans votre espace professionnel.">
        <meta name="twitter:image" content="{{ asset('images/features/facturation-electronique-inbox.webp') }}">
    @endsection

    @section('structured_data')
        @php
            $invoiceFaq = [
                ['question' => 'Que permet la facturation électronique dans Olithea aujourd’hui ?', 'answer' => 'La fonctionnalité permet de connecter votre entreprise à une plateforme agréée partenaire, d’activer la réception, de synchroniser les factures électroniques d’achat entrantes et de consulter leurs informations principales dans Olithea.'],
                ['question' => 'Quelle obligation est couverte pour le 1er septembre 2026 ?', 'answer' => 'À cette date, toutes les entreprises doivent pouvoir recevoir des factures électroniques. Olithea vous permet d’activer cette réception et de consulter les factures reçues. Le calendrier d’émission dépend de la taille de votre entreprise.'],
                ['question' => 'Dois-je créer un second compte séparé ?', 'answer' => 'L’activation est lancée depuis Olithea. Vous autorisez une connexion sécurisée avec notre plateforme agréée partenaire et choisissez d’activer la réception des factures dans l’application.'],
                ['question' => 'Quelles informations sont affichées ?', 'answer' => 'Olithea affiche notamment le fournisseur, le numéro de facture, la date, le montant TTC, le statut reçu et la date de synchronisation.'],
                ['question' => 'Puis-je couper la réception dans Olithea ?', 'answer' => 'Oui. La préférence de réception peut être désactivée depuis les informations de l’entreprise, et la connexion à la plateforme partenaire peut également être révoquée.'],
            ];
            $invoiceStructuredData = [
                '@context' => 'https://schema.org',
                '@graph' => [
                    ['@type' => 'BreadcrumbList', 'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Fonctionnalités', 'item' => route('features.index')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => 'Facturation électronique', 'item' => route('features.e-invoicing')],
                    ]],
                    ['@type' => 'SoftwareApplication', 'name' => 'Réception de factures électroniques Olithea', 'applicationCategory' => 'FinanceApplication', 'operatingSystem' => 'Web', 'description' => 'Réception des factures électroniques d’achat dans Olithea, compatible avec l’échéance de réception du 1er septembre 2026.', 'url' => route('features.e-invoicing')],
                    ['@type' => 'FAQPage', 'mainEntity' => collect($invoiceFaq)->map(fn ($item) => ['@type' => 'Question', 'name' => $item['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']]])->all()],
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($invoiceStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endsection

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/feature-marketing.css') }}">
    @endpush

    <main class="fm-page">
        <section class="fm-hero" style="background-image:url('{{ asset('images/features/facturation-electronique-inbox.webp') }}')">
            <div class="fm-wrap">
                <div class="fm-hero-content">
                    <nav class="fm-breadcrumb" aria-label="Fil d’Ariane">
                        <a href="{{ url('/') }}">Accueil</a><span>›</span>
                        <a href="{{ route('features.index') }}">Fonctionnalités</a><span>›</span>
                        <span>Facturation électronique</span>
                    </nav>
                    <div class="fm-hero-certification">
                        <img src="{{ asset('images/facturation-electronique-solution-compatible.png') }}" alt="Solution compatible Facturation électronique" width="655" height="305">
                    </div>
                    <p class="fm-eyebrow">Obligation de réception au 1er septembre 2026</p>
                    <h1>Factures électroniques : soyez prêt pour septembre 2026.</h1>
                    <p class="fm-hero-lead">Olithea vous accompagne de l’identification de votre entreprise à la réception de vos factures électroniques d’achat. Une fois activées, elles arrivent dans votre espace avec leur fournisseur, leur montant et leur statut.</p>
                    <div class="fm-actions">
                        <a href="{{ route('register-pro') }}" class="fm-btn fm-btn-primary">Créer mon espace Olithea <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                        <a href="#fonctionnement" class="fm-btn fm-btn-light">Comprendre le fonctionnement</a>
                    </div>
                    <ul class="fm-proofline" aria-label="Points clés">
                        <li>Réception obligatoire 2026</li>
                        <li>Connexion sécurisée</li>
                        <li>Synchronisation dans Olithea</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="fm-flow-band" id="fonctionnement" aria-labelledby="invoice-flow-title">
            <h2 id="invoice-flow-title" class="sr-only">Fonctionnement de la réception des factures électroniques</h2>
            <div class="fm-wrap fm-flow fm-flow-three">
                <div class="fm-flow-item"><span class="fm-flow-number">01</span><strong>Connectez l’entreprise</strong><span>Lancez l’onboarding depuis Olithea.</span></div>
                <div class="fm-flow-item"><span class="fm-flow-number">02</span><strong>Activez la réception</strong><span>Autorisez la connexion sécurisée et l’inscription à l’annuaire.</span></div>
                <div class="fm-flow-item"><span class="fm-flow-number">03</span><strong>Consultez les factures</strong><span>Synchronisez, vérifiez le statut et téléchargez le document.</span></div>
            </div>
        </section>

        <section class="fm-section">
            <div class="fm-wrap fm-two-col">
                <div class="fm-copy">
                    <p class="fm-kicker">Une boîte de réception professionnelle</p>
                    <h2>Vos factures fournisseurs, lisibles au même endroit.</h2>
                    <p>Une fois votre entreprise connectée et la réception activée, Olithea synchronise les factures électroniques d’achat disponibles auprès de sa plateforme agréée partenaire.</p>
                    <ul class="fm-checks">
                        <li>Numéro et date de la facture.</li>
                        <li>Nom du fournisseur.</li>
                        <li>Montant TTC et devise.</li>
                        <li>Dernier statut transmis.</li>
                        <li>Téléchargement du document reçu.</li>
                    </ul>
                    <p>La synchronisation peut être relancée depuis l’application lorsque vous souhaitez actualiser la liste.</p>
                </div>
                <figure class="fm-product-frame">
                    <img src="{{ asset('images/features/facturation-electronique-inbox.webp') }}" alt="Boîte de réception des factures électroniques d’achat dans Olithea" width="1400" height="820" loading="lazy">
                    <figcaption class="fm-product-caption">Une vue dédiée aux factures entrantes, séparée des factures que vous adressez à vos clients.</figcaption>
                </figure>
            </div>
        </section>

        <section class="fm-section fm-section-soft">
            <div class="fm-wrap">
                <header class="fm-heading fm-heading-centered">
                    <p class="fm-kicker">Mise en place</p>
                    <h2>Trois étapes, guidées depuis Olithea.</h2>
                    <p>Vous gardez la maîtrise de la connexion et de la préférence de réception.</p>
                </header>
                <div class="fm-grid-3">
                    <article class="fm-use-case"><span class="fm-use-case-icon"><i class="fas fa-building" aria-hidden="true"></i></span><h3>1. Identifiez votre entreprise</h3><p>Ouvrez l’onglet Facturation électronique dans les informations de l’entreprise et démarrez l’onboarding.</p></article>
                    <article class="fm-use-case"><span class="fm-use-case-icon"><i class="fas fa-shield-alt" aria-hidden="true"></i></span><h3>2. Autorisez la connexion</h3><p>Un flux sécurisé confirme l’entreprise et demande son inscription à l’annuaire de réception.</p></article>
                    <article class="fm-use-case"><span class="fm-use-case-icon"><i class="fas fa-file-download" aria-hidden="true"></i></span><h3>3. Synchronisez</h3><p>Les factures entrantes apparaissent dans Olithea. Vous pouvez consulter leurs informations et télécharger le document associé.</p></article>
                </div>
            </div>
        </section>

        <section class="fm-section fm-section-blue">
            <div class="fm-wrap">
                <header class="fm-heading fm-heading-centered"><p class="fm-kicker">Deux besoins différents</p><h2>Facturer vos clients et recevoir vos achats ne sont pas la même chose.</h2></header>
                <div class="fm-comparison">
                    <article class="fm-comparison-panel"><p class="fm-kicker">Facturation classique</p><h3>Vos devis et factures clients</h3><p>Créez vos documents commerciaux, suivez les règlements et votre livre de recettes avec le module Facturation Olithea.</p><a href="{{ route('features.facturation') }}" class="fm-text-link">Découvrir la facturation client →</a></article>
                    <article class="fm-comparison-panel fm-comparison-panel-accent"><p class="fm-kicker">Facturation électronique</p><h3>Vos factures d’achat reçues</h3><p>Activez la réception pour synchroniser les factures électroniques que vos fournisseurs adressent à votre entreprise.</p><a href="#faq-facturation-electronique" class="fm-text-link">Lire les questions fréquentes →</a></article>
                </div>
            </div>
        </section>

        <section class="fm-section fm-section-dark">
            <div class="fm-wrap">
                <header class="fm-heading"><p class="fm-kicker">Contrôle et transparence</p><h2>Vous choisissez ce qui est connecté.</h2><p>La connexion est autorisée par un flux sécurisé. Vous pouvez couper l’affichage des factures reçues dans Olithea ou déconnecter la plateforme partenaire depuis les informations de l’entreprise.</p></header>
                <div class="fm-integrations fm-integrations-three">
                    <div class="fm-integration"><i class="fas fa-key" aria-hidden="true"></i><h3>Autorisation sécurisée</h3><p>Olithea ne vous demande pas de communiquer le mot de passe de votre plateforme partenaire.</p></div>
                    <div class="fm-integration"><i class="fas fa-toggle-on" aria-hidden="true"></i><h3>Préférence réversible</h3><p>L’affichage des factures reçues peut être désactivé sans masquer le reste de votre comptabilité.</p></div>
                    <div class="fm-integration"><i class="fas fa-sync-alt" aria-hidden="true"></i><h3>Synchronisation maîtrisée</h3><p>La dernière synchronisation et les éventuelles erreurs restent visibles dans votre espace.</p></div>
                </div>
            </div>
        </section>

        <section class="fm-section">
            <div class="fm-wrap">
                <header class="fm-heading fm-heading-centered"><p class="fm-kicker">Échéance du 1er septembre 2026</p><h2>Tout ce qu’il vous faut pour être prêt à recevoir.</h2><p>À cette date, toutes les entreprises devront être en mesure de recevoir leurs factures électroniques. Olithea réunit le parcours d’activation et la consultation dans votre outil quotidien.</p></header>
                <article class="fm-comparison-panel fm-comparison-panel-accent fm-readiness-panel"><h3>Votre réception électronique dans Olithea</h3><ul class="fm-checks"><li>Identification de votre entreprise.</li><li>Inscription à l’annuaire de la facturation électronique.</li><li>Connexion à une plateforme agréée partenaire.</li><li>Activation de la réception des factures entrantes.</li><li>Consultation et téléchargement dans Olithea.</li></ul></article>
                <div class="fm-scope-note"><i class="fas fa-landmark" aria-hidden="true"></i><div><h3>Le calendrier officiel</h3><p>L’obligation de réception concerne toutes les entreprises au 1er septembre 2026. L’obligation d’émission suit un calendrier distinct selon la taille de l’entreprise.</p><p class="fm-legal-source"><a href="https://www.economie.gouv.fr/tout-savoir-sur-la-facturation-electronique-pour-les-entreprises" target="_blank" rel="noopener">Consulter le portail du ministère de l’Économie →</a></p></div></div>
            </div>
        </section>

        <section class="fm-section fm-section-soft" id="faq-facturation-electronique">
            <div class="fm-wrap">
                <header class="fm-heading fm-heading-centered"><p class="fm-kicker">Questions fréquentes</p><h2>Comprendre avant d’activer.</h2></header>
                <div class="fm-faq">
                    @foreach($invoiceFaq as $item)
                        <details><summary>{{ $item['question'] }}</summary><p>{{ $item['answer'] }}</p></details>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="fm-cta">
            <div class="fm-wrap fm-cta-inner">
                <div><h2>Soyez prêt pour la réception obligatoire de septembre 2026.</h2><p>Créez votre espace Olithea, renseignez votre entreprise et activez votre réception électronique.</p></div>
                <div class="fm-actions"><a href="{{ route('register-pro') }}" class="fm-btn fm-btn-primary">Créer mon espace</a><a href="{{ route('features.facturation') }}" class="fm-btn fm-btn-light">Voir la facturation classique</a></div>
            </div>
        </section>
    </main>
</x-app-layout>

<x-app-layout>
    @section('title', 'Page de capture pour praticien | Formulaire Olithea')
    @section('meta_description', 'Créez une page de capture sans code, recueillez une demande avec un formulaire clair et guidez la personne vers la bonne étape dans Olithea.')

    @section('meta_og')
        <meta property="og:type" content="website">
        <meta property="og:locale" content="fr_FR">
        <meta property="og:url" content="{{ route('features.capture-page') }}">
        <meta property="og:title" content="Page de capture pour praticien | Olithea">
        <meta property="og:description" content="Présentez une proposition, recueillez les informations utiles et donnez une suite claire à chaque demande.">
        <meta property="og:image" content="{{ asset('images/features/parcours-offre-formulaire.webp') }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Page de capture Olithea">
        <meta name="twitter:description" content="Une page, un formulaire et une prochaine étape reliés à votre activité de praticien.">
        <meta name="twitter:image" content="{{ asset('images/features/parcours-offre-formulaire.webp') }}">
    @endsection

    @section('structured_data')
        @php
            $captureFaq = [
                ['question' => 'Qu’est-ce qu’une page de capture ?', 'answer' => 'C’est une page publique consacrée à une seule proposition. Elle explique ce que la personne va recevoir ou pouvoir faire, puis recueille les informations nécessaires avant de la guider vers la prochaine étape.'],
                ['question' => 'Quelles informations peut-on demander ?', 'answer' => 'Un formulaire simple peut recueillir le prénom, l’adresse email et, si cela est utile à la demande, un téléphone ou d’autres champs disponibles. Il ne doit pas servir à collecter des informations médicales ou cliniques.'],
                ['question' => 'Le consentement marketing est-il obligatoire ?', 'answer' => 'Non. L’information sur l’utilisation des données accompagne la demande. Le consentement à recevoir des communications marketing reste distinct et facultatif.'],
                ['question' => 'Que se passe-t-il après le formulaire ?', 'answer' => 'Selon le parcours choisi, la personne peut accéder à une ressource, ouvrir une réservation, s’inscrire à un événement, découvrir une formation ou un bon cadeau, ou simplement confirmer une demande de contact.'],
                ['question' => 'Faut-il activer des emails automatiques ?', 'answer' => 'Non. Une page de capture peut fonctionner sans campagne ou relance automatique. Les fonctions d’envoi sont proposées progressivement selon l’activation du compte et le consentement recueilli.'],
            ];
            $captureStructuredData = [
                '@context' => 'https://schema.org',
                '@graph' => [
                    ['@type' => 'BreadcrumbList', 'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Fonctionnalités', 'item' => route('features.index')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => 'Parcours d’offre', 'item' => route('features.offer-journeys')],
                        ['@type' => 'ListItem', 'position' => 4, 'name' => 'Page de capture', 'item' => route('features.capture-page')],
                    ]],
                    ['@type' => 'SoftwareApplication', 'name' => 'Page de capture Olithea', 'applicationCategory' => 'BusinessApplication', 'operatingSystem' => 'Web', 'description' => 'Création de pages de capture et de formulaires reliés aux parcours et aux offres du praticien.', 'url' => route('features.capture-page')],
                    ['@type' => 'FAQPage', 'mainEntity' => collect($captureFaq)->map(fn ($item) => ['@type' => 'Question', 'name' => $item['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']]])->all()],
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($captureStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endsection

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/feature-marketing.css') }}">
    @endpush

    <main class="fm-page">
        <section class="fm-hero" style="background-image:url('{{ asset('images/features/parcours-offre-formulaire.webp') }}')">
            <div class="fm-wrap">
                <div class="fm-hero-content">
                    <nav class="fm-breadcrumb" aria-label="Fil d’Ariane">
                        <a href="{{ url('/') }}">Accueil</a><span>›</span>
                        <a href="{{ route('features.index') }}">Fonctionnalités</a><span>›</span>
                        <a href="{{ route('features.offer-journeys') }}">Parcours d’offre</a><span>›</span>
                        <span>Page de capture</span>
                    </nav>
                    <p class="fm-eyebrow">Page de capture pour praticiens</p>
                    <h1>Créez une page de capture reliée à votre activité.</h1>
                    <p class="fm-hero-lead">Présentez une proposition, recueillez uniquement les informations utiles et guidez la personne vers une ressource, une réservation, une inscription ou une demande.</p>
                    <div class="fm-actions">
                        <a href="{{ route('register-pro') }}" class="fm-btn fm-btn-primary">Créer ma première page <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                        <a href="#fonctionnement" class="fm-btn fm-btn-light">Voir comment elle fonctionne</a>
                    </div>
                    <ul class="fm-proofline" aria-label="Points clés">
                        <li>Sans code</li>
                        <li>Consentement distinct</li>
                        <li>Prochaine étape intégrée</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="fm-section fm-section-soft fm-simple-section" id="fonctionnement">
            <div class="fm-wrap">
                <header class="fm-heading fm-heading-centered">
                    <p class="fm-kicker">Le fonctionnement</p>
                    <h2>Une page courte qui donne une suite à l’intérêt.</h2>
                    <p>Une page de capture ne remplace pas votre site. Elle se concentre sur une proposition précise et sur l’action qui doit naturellement suivre.</p>
                </header>
                <div class="fm-flow fm-flow-four" aria-label="Fonctionnement d’une page de capture">
                    @foreach([
                        ['01', 'Présentez', 'Expliquez clairement la ressource ou l’offre.'],
                        ['02', 'Recueillez', 'Demandez seulement les informations nécessaires.'],
                        ['03', 'Informez', 'Précisez l’usage des données et le consentement.'],
                        ['04', 'Orientez', 'Ouvrez la bonne prochaine étape dans Olithea.'],
                    ] as $step)
                        <div class="fm-flow-item"><span class="fm-flow-number">{{ $step[0] }}</span><strong>{{ $step[1] }}</strong><span>{{ $step[2] }}</span></div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="fm-section">
            <div class="fm-wrap fm-two-col">
                <div class="fm-mobile-showcase">
                    <img src="{{ asset('images/features/parcours-offre-capture-mobile.webp') }}" alt="Exemple de page de capture Olithea avec présentation et formulaire" width="375" height="812" loading="lazy">
                </div>
                <div class="fm-copy">
                    <p class="fm-kicker">Côté visiteur</p>
                    <h2>La personne comprend immédiatement ce qu’elle va recevoir.</h2>
                    <p>Votre nom, votre proposition, une explication courte et un bouton cohérent apparaissent sur une page adaptée au téléphone. Le formulaire reste visible sans détour inutile.</p>
                    <ul class="fm-checks">
                        <li>Une proposition et un bénéfice compréhensibles.</li>
                        <li>Un formulaire limité aux informations réellement utiles.</li>
                        <li>Une explication de l’utilisation des données.</li>
                        <li>Une confirmation claire après l’action.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="fm-section fm-section-soft">
            <div class="fm-wrap fm-two-col">
                <div class="fm-copy">
                    <p class="fm-kicker">Formulaire et consentement</p>
                    <h2>La demande et le marketing restent deux choix séparés.</h2>
                    <p>La personne confirme d’abord avoir compris comment ses informations seront utilisées pour répondre à sa demande. Si vous proposez des communications complémentaires, leur consentement dispose de sa propre case facultative.</p>
                    <ul class="fm-checks">
                        <li>Prénom et adresse email pour identifier et répondre.</li>
                        <li>Téléphone uniquement lorsque le rappel le justifie.</li>
                        <li>Texte de confidentialité affiché avec le formulaire.</li>
                        <li>Accord marketing distinct, traçable et révocable.</li>
                    </ul>
                    <p>Les options de formulaire plus avancées dépendent des fonctionnalités activées sur le compte.</p>
                </div>
                <figure class="fm-product-frame">
                    <img src="{{ asset('images/features/parcours-offre-formulaire.webp') }}" alt="Configuration du formulaire, de la confidentialité et de l’étape suivante dans Olithea" width="1425" height="891" loading="lazy">
                    <figcaption class="fm-product-caption">Le contenu, le formulaire et l’étape suivante sont préparés dans le même écran.</figcaption>
                </figure>
            </div>
        </section>

        <section class="fm-section fm-section-blue">
            <div class="fm-wrap">
                <header class="fm-heading fm-heading-centered">
                    <p class="fm-kicker">Après le formulaire</p>
                    <h2>Chaque demande arrive là où elle doit continuer.</h2>
                    <p>Vous choisissez la destination au moment de créer le parcours, sans reconstruire votre activité dans un outil séparé.</p>
                </header>
                <div class="fm-grid-3">
                    <article class="fm-use-case"><span class="fm-use-case-icon"><i class="fas fa-download" aria-hidden="true"></i></span><h3>Donner accès à une ressource</h3><p>Ouvrez un fichier privé ou un lien après la validation du formulaire.</p></article>
                    <article class="fm-use-case"><span class="fm-use-case-icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></span><h3>Ouvrir une action Olithea</h3><p>Continuez vers une réservation, un événement, une formation ou un bon cadeau existant.</p></article>
                    <article class="fm-use-case"><span class="fm-use-case-icon"><i class="fas fa-address-book" aria-hidden="true"></i></span><h3>Retrouver la demande</h3><p>Consultez la personne intéressée, l’origine de sa demande et les consentements associés.</p></article>
                </div>
            </div>
        </section>

        <section class="fm-section fm-section-soft">
            <div class="fm-wrap">
                <header class="fm-heading fm-heading-centered"><p class="fm-kicker">Questions fréquentes</p><h2>Ce qu’il faut savoir avant de publier.</h2></header>
                <div class="fm-faq">
                    @foreach($captureFaq as $item)
                        <details><summary>{{ $item['question'] }}</summary><p>{{ $item['answer'] }}</p></details>
                    @endforeach
                </div>
                <div class="fm-scope-note"><i class="fas fa-user-shield" aria-hidden="true"></i><div><h3>Vous gardez la maîtrise</h3><p>La page reste en brouillon tant que vous ne la publiez pas. Vous pouvez la prévisualiser, modifier ses textes et interrompre sa diffusion. L’accès aux pages publiques et aux fonctions d’envoi dépend de l’activation du module sur votre compte.</p></div></div>
                <div class="fm-related-links" aria-label="Guides associés">
                    <a href="{{ route('guides.sales-funnel-practitioner') }}"><strong>Comprendre le tunnel de vente</strong><span>Définition, étapes et approche éthique.</span></a>
                    <a href="{{ route('guides.lead-magnet-practitioner') }}"><strong>Trouver une idée de lead magnet</strong><span>PDF, audio, checklist et mini-programme.</span></a>
                    <a href="{{ route('features.offer-journeys') }}"><strong>Découvrir les Parcours d’offre</strong><span>Voir la fonctionnalité complète.</span></a>
                </div>
            </div>
        </section>

        <section class="fm-cta">
            <div class="fm-wrap fm-cta-inner">
                <div><h2>Transformez votre prochain lien en une étape utile.</h2><p>Présentez une proposition claire et reliez-la à ce que vous gérez déjà dans Olithea.</p></div>
                <div class="fm-actions"><a href="{{ route('register-pro') }}" class="fm-btn fm-btn-primary">Créer ma première page <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
            </div>
        </section>
    </main>
</x-app-layout>

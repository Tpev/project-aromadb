<x-app-layout>
    @section('title', 'Lead magnet pour praticien : idées et exemples | Olithea')
    @section('meta_description', 'Découvrez des idées de lead magnets pour praticiens : guide PDF, audio, checklist et mini-programme, avec exemples et prochaine étape vers un rendez-vous.')

    @section('meta_og')
        <meta property="og:type" content="article">
        <meta property="og:locale" content="fr_FR">
        <meta property="og:url" content="{{ route('guides.lead-magnet-practitioner') }}">
        <meta property="og:title" content="Lead magnet pour praticien : idées et exemples">
        <meta property="og:description" content="Quatre formats utiles pour faire découvrir votre approche et guider naturellement vers la prochaine étape.">
        <meta property="og:image" content="{{ asset('images/features/parcours-offre-creation.webp') }}">
        <meta property="article:published_time" content="2026-07-11">
        <meta property="article:modified_time" content="2026-07-11">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Lead magnet pour praticien : le guide pratique">
        <meta name="twitter:description" content="PDF, audio, checklist ou mini-programme : choisissez un format cohérent avec votre activité.">
        <meta name="twitter:image" content="{{ asset('images/features/parcours-offre-creation.webp') }}">
    @endsection

    @section('structured_data')
        @php
            $leadMagnetFaq = [
                ['question' => 'Qu’est-ce qu’un lead magnet ?', 'answer' => 'C’est une ressource gratuite et précise proposée en échange des informations nécessaires à sa remise, généralement un prénom et une adresse email. Elle permet de découvrir votre approche avant de choisir une prochaine étape.'],
                ['question' => 'Quel format est le plus simple pour commencer ?', 'answer' => 'Une checklist ou un guide PDF court est souvent plus rapide à produire et plus facile à utiliser. Le meilleur format reste celui que votre public peut consulter immédiatement et que vous pouvez maintenir à jour.'],
                ['question' => 'Faut-il proposer un rendez-vous juste après le téléchargement ?', 'answer' => 'Vous pouvez présenter une séance ou une demande de contact, mais la transition doit rester naturelle et facultative. La ressource doit déjà tenir la promesse annoncée.'],
                ['question' => 'Puis-je envoyer une séquence de plusieurs emails ?', 'answer' => 'Une séquence peut accompagner un mini-programme si la personne y a consenti. Dans Olithea, les campagnes et envois automatiques dépendent de l’activation du compte et peuvent être proposés séparément de la page de capture.'],
                ['question' => 'Un lead magnet garantit-il de nouveaux clients ?', 'answer' => 'Non. Il peut rendre votre approche plus compréhensible et faciliter une demande, mais il ne garantit ni audience, ni rendez-vous, ni chiffre d’affaires.'],
            ];
            $leadMagnetStructuredData = [
                '@context' => 'https://schema.org',
                '@graph' => [
                    ['@type' => 'BreadcrumbList', 'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Lead magnet pour praticien', 'item' => route('guides.lead-magnet-practitioner')],
                    ]],
                    ['@type' => 'Article', 'headline' => 'Lead magnet pour praticien : idées et exemples pour attirer les bonnes demandes', 'description' => 'Guide pratique des formats de ressources gratuites adaptés aux praticiens, coachs et accompagnants.', 'inLanguage' => 'fr-FR', 'datePublished' => '2026-07-11', 'dateModified' => '2026-07-11', 'mainEntityOfPage' => route('guides.lead-magnet-practitioner'), 'author' => ['@type' => 'Organization', 'name' => 'Olithea'], 'publisher' => ['@type' => 'Organization', 'name' => 'Olithea', 'logo' => ['@type' => 'ImageObject', 'url' => asset('images/brand/olithea-logo-horizontal-green-cropped.png')]], 'image' => asset('images/features/parcours-offre-creation.webp')],
                    ['@type' => 'FAQPage', 'mainEntity' => collect($leadMagnetFaq)->map(fn ($item) => ['@type' => 'Question', 'name' => $item['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']]])->all()],
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($leadMagnetStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endsection

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/marketing-guides.css') }}">
    @endpush

    <main class="mg-page mg-page-lead-magnet">
        <header class="mg-hero">
            <div class="mg-wrap">
                <nav class="mg-breadcrumb" aria-label="Fil d’Ariane">
                    <a href="{{ url('/') }}">Accueil</a><span>›</span><span>Guides</span><span>›</span><span>Lead magnet pour praticien</span>
                </nav>
                <p class="mg-kicker">Guide pratique</p>
                <h1>Lead magnet pour praticien : idées et exemples pour attirer les bonnes demandes</h1>
                <p class="mg-hero-intro">Un bon lead magnet ne promet pas une transformation spectaculaire. Il apporte une première aide concrète, fait découvrir votre manière de travailler et propose une prochaine étape cohérente.</p>
                <p class="mg-meta"><span>Publié le 11 juillet 2026</span><span>Lecture : environ 10 minutes</span><span>PDF, audio, checklist et mini-programme</span></p>
            </div>
        </header>

        <div class="mg-wrap mg-layout">
            <aside class="mg-toc" aria-label="Sommaire">
                <strong>Dans ce guide</strong>
                <a href="#definition">Définition</a>
                <a href="#criteres">Les quatre critères</a>
                <a href="#formats">Les formats et exemples</a>
                <a href="#choisir">Choisir le bon format</a>
                <a href="#donnees">Formulaire et données</a>
                <a href="#erreurs">Erreurs à éviter</a>
                <a href="#olithea">Le mettre en ligne</a>
                <a href="#faq">Questions fréquentes</a>
            </aside>

            <article class="mg-article">
                <section id="definition">
                    <h2>Qu’est-ce qu’un lead magnet ?</h2>
                    <div class="mg-definition">
                        <p><strong>Un lead magnet, ou ressource gratuite, est un contenu précis remis à une personne en échange des informations nécessaires pour le recevoir.</strong></p>
                        <p>Il peut prendre la forme d’un PDF, d’un audio, d’une checklist ou d’un mini-programme. Sa valeur vient de son utilité immédiate, pas de sa longueur.</p>
                    </div>
                    <p>Pour un praticien, cette ressource sert avant tout à créer un premier contact respectueux. Elle permet à une personne de comprendre votre approche et de vérifier si votre manière d’accompagner lui correspond, sans devoir réserver immédiatement.</p>
                    <p>Le lead magnet ne remplace ni votre visibilité ni la qualité de votre offre. Il donne une destination utile aux personnes qui vous découvrent déjà grâce à votre contenu, votre réseau ou une recommandation.</p>
                </section>

                <section id="criteres">
                    <h2>Les quatre critères d’une ressource réellement utile</h2>
                    <ol>
                        <li><strong>Un sujet précis.</strong> « Guide du bien-être » est trop large. « 7 questions pour préparer votre première séance de sophrologie » indique clairement ce que la personne trouvera.</li>
                        <li><strong>Un résultat raisonnable.</strong> La ressource peut aider à comprendre, préparer ou expérimenter. Elle ne doit pas garantir un résultat médical ou une transformation définitive.</li>
                        <li><strong>Un format facile à utiliser.</strong> Le contenu doit pouvoir être consulté rapidement sur téléphone ou téléchargé sans logiciel particulier.</li>
                        <li><strong>Une prochaine étape logique.</strong> Après la ressource, proposez un rendez-vous, un atelier ou une autre offre uniquement si cela prolonge naturellement le sujet.</li>
                    </ol>
                </section>

                <section id="formats">
                    <h2>Quatre formats de lead magnets avec des exemples</h2>
                    <p>Le format doit correspondre à votre manière de transmettre. Inutile d’enregistrer un audio si vous êtes plus à l’aise avec une fiche pratique, ou de produire trente pages lorsqu’une checklist suffit.</p>

                    <div class="mg-format-list">
                        <section class="mg-format">
                            <h3>1. Le guide PDF court</h3>
                            <p>Le PDF convient lorsque vous souhaitez structurer des repères, expliquer une méthode ou aider la personne à préparer une première étape. Il doit rester lisible sur téléphone et aller directement à l’essentiel.</p>
                            <div class="mg-format-grid">
                                <div><strong>Problème traité</strong><p>La personne ne sait pas comment se préparer ou par où commencer.</p></div>
                                <div><strong>Informations demandées</strong><p>Prénom et adresse email pour donner accès au document.</p></div>
                                <div><strong>Exemple</strong><p>Une sophrologue propose « 7 repères pour préparer une première séance ».</p></div>
                                <div><strong>Prochaine étape</strong><p>Découvrir la séance proposée ou consulter les disponibilités, sans obligation.</p></div>
                            </div>
                            <p><strong>Erreur fréquente :</strong> transformer le PDF en brochure commerciale. Le document doit tenir sa promesse avant de présenter votre offre.</p>
                        </section>

                        <section class="mg-format">
                            <h3>2. L’audio court</h3>
                            <p>L’audio fait découvrir votre voix, votre rythme et votre façon de guider. Il convient aux approches où l’expérience orale est importante, à condition de rester court et de ne pas présenter l’exercice comme un traitement.</p>
                            <div class="mg-format-grid">
                                <div><strong>Problème traité</strong><p>La personne souhaite découvrir votre manière de transmettre avant de réserver.</p></div>
                                <div><strong>Informations demandées</strong><p>Une adresse email suffit généralement pour accéder au fichier.</p></div>
                                <div><strong>Exemple</strong><p>Une accompagnante propose « 5 minutes pour découvrir ma façon de guider la respiration ».</p></div>
                                <div><strong>Prochaine étape</strong><p>Lire la présentation d’un atelier ou demander un premier échange.</p></div>
                            </div>
                            <p><strong>Erreur fréquente :</strong> enregistrer un contenu trop long, difficile à écouter immédiatement ou accompagné d’une promesse de résultat.</p>
                        </section>

                        <section class="mg-format">
                            <h3>3. La checklist</h3>
                            <p>La checklist transforme un sujet en liste d’actions faciles à parcourir. C’est souvent le format le plus rapide à produire et à mettre à jour.</p>
                            <div class="mg-format-grid">
                                <div><strong>Problème traité</strong><p>La personne craint d’oublier une étape importante avant un rendez-vous ou un événement.</p></div>
                                <div><strong>Informations demandées</strong><p>Prénom et email, sans question clinique supplémentaire.</p></div>
                                <div><strong>Exemple</strong><p>Une naturopathe propose une checklist pratique pour préparer un premier bilan bien-être.</p></div>
                                <div><strong>Prochaine étape</strong><p>Consulter le déroulement du bilan puis choisir un créneau si elle le souhaite.</p></div>
                            </div>
                            <p><strong>Erreur fréquente :</strong> utiliser des consignes génériques que la personne peut trouver partout, sans lien avec votre approche.</p>
                        </section>

                        <section class="mg-format">
                            <h3>4. Le mini-programme</h3>
                            <p>Un mini-programme répartit un sujet en quelques étapes courtes. Il peut être remis dans un document unique ou, lorsque les envois sont disponibles et consentis, dans une série de messages espacés.</p>
                            <div class="mg-format-grid">
                                <div><strong>Problème traité</strong><p>La personne a besoin d’un cadre progressif pour clarifier une question.</p></div>
                                <div><strong>Informations demandées</strong><p>Email et consentement distinct si plusieurs communications sont prévues.</p></div>
                                <div><strong>Exemple</strong><p>Un coach propose « 3 jours pour clarifier votre objectif d’accompagnement ».</p></div>
                                <div><strong>Prochaine étape</strong><p>Demander un échange ou découvrir le programme complet après la dernière étape.</p></div>
                            </div>
                            <p><strong>Erreur fréquente :</strong> multiplier les messages ou cacher une vente insistante derrière un contenu très léger.</p>
                        </section>
                    </div>
                </section>

                <section id="choisir">
                    <h2>Quel format choisir selon votre objectif ?</h2>
                    <div class="mg-table-wrap">
                        <table class="mg-table">
                            <thead><tr><th>Votre objectif</th><th>Format conseillé</th><th>Pourquoi</th><th>Prochaine étape naturelle</th></tr></thead>
                            <tbody>
                                <tr><td>Préparer une première séance</td><td>Checklist ou PDF</td><td>Les repères restent faciles à relire.</td><td>Voir le déroulement puis réserver.</td></tr>
                                <tr><td>Faire découvrir votre manière de guider</td><td>Audio court</td><td>La personne expérimente votre voix et votre rythme.</td><td>Découvrir un atelier ou demander un échange.</td></tr>
                                <tr><td>Clarifier un sujet en plusieurs étapes</td><td>Mini-programme</td><td>Le contenu suit une progression simple.</td><td>Présenter l’accompagnement complet.</td></tr>
                                <tr><td>Attirer vers un événement</td><td>Checklist ou guide</td><td>La ressource introduit le thème de l’atelier.</td><td>Ouvrir la page d’inscription.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="donnees">
                    <h2>Que demander sur la page de capture ?</h2>
                    <p>Pour remettre une ressource, le prénom et l’adresse email sont généralement suffisants. Le numéro de téléphone n’est utile que si vous proposez explicitement un rappel. Chaque champ supplémentaire doit avoir une finalité compréhensible.</p>
                    <p>L’information nécessaire à la remise de la ressource et le consentement marketing sont deux choses différentes. Une personne peut demander le contenu sans accepter une série de communications facultatives.</p>
                    <div class="mg-warning">
                        <h3>Ne qualifiez pas un prospect avec des données de santé</h3>
                        <p>Ne demandez pas de diagnostic, de traitement, d’antécédents ou de détails cliniques sur une page marketing. Si ces informations deviennent nécessaires à l’accompagnement, elles doivent être recueillies plus tard dans un cadre approprié.</p>
                    </div>
                </section>

                <section id="erreurs">
                    <h2>Les erreurs qui rendent un lead magnet moins utile</h2>
                    <ul>
                        <li><strong>Choisir un sujet trop vaste :</strong> la personne ne comprend pas ce qu’elle va obtenir concrètement.</li>
                        <li><strong>Promettre une transformation :</strong> la ressource crée une attente disproportionnée et peut fragiliser la confiance.</li>
                        <li><strong>Demander trop d’informations :</strong> un formulaire long augmente la friction et recueille des données sans nécessité.</li>
                        <li><strong>Oublier la prochaine étape :</strong> la personne reçoit le contenu mais ne sait pas où découvrir votre activité.</li>
                        <li><strong>Relancer sans cadre :</strong> la remise du fichier n’autorise pas automatiquement des communications marketing répétées.</li>
                        <li><strong>Créer plusieurs ressources à la fois :</strong> commencez par une seule proposition et observez les demandes réelles.</li>
                    </ul>
                </section>

                <section id="olithea">
                    <h2>Mettre un lead magnet en ligne avec Olithea</h2>
                    <p>Un Parcours d’offre peut présenter la ressource, recueillir une demande avec un formulaire, donner accès à un fichier privé ou à un lien, puis afficher une confirmation. La personne intéressée et l’origine de la demande restent rattachées au parcours.</p>
                    <p>Les pages publiques, statistiques, campagnes et emails sont activés progressivement selon le compte. La page de capture et la remise de la ressource peuvent être utilisées sans lancer une campagne marketing automatique.</p>
                    <figure class="mg-phone-figure">
                        <img src="{{ asset('images/features/parcours-offre-capture-mobile.webp') }}" alt="Exemple de page de capture Olithea proposant un guide gratuit" width="375" height="812" loading="lazy">
                        <figcaption>Une ressource, une explication courte et un formulaire limité aux informations utiles.</figcaption>
                    </figure>
                    <div class="mg-inline-cta">
                        <h3>Commencez par la page et la ressource</h3>
                        <p>Choisissez un objectif simple, préparez votre contenu puis vérifiez le parcours complet avant de partager son lien.</p>
                        <a href="{{ route('features.capture-page') }}">Découvrir la page de capture</a>
                    </div>
                </section>

                <section id="faq">
                    <h2>Questions fréquentes</h2>
                    <div class="mg-faq">
                        @foreach($leadMagnetFaq as $item)
                            <details><summary>{{ $item['question'] }}</summary><p>{{ $item['answer'] }}</p></details>
                        @endforeach
                    </div>
                </section>
            </article>
        </div>

        <section class="mg-related">
            <div class="mg-wrap">
                <h2>Construire le parcours autour de votre ressource</h2>
                <div class="mg-related-grid">
                    <a href="{{ route('features.capture-page') }}"><strong>Créer une page de capture</strong><span>Comprendre le formulaire, le consentement et l’étape suivante.</span></a>
                    <a href="{{ route('guides.sales-funnel-practitioner') }}"><strong>Comprendre le tunnel de vente</strong><span>Voir les quatre étapes et l’approche adaptée aux praticiens.</span></a>
                    <a href="{{ route('features.offer-journeys') }}"><strong>Découvrir les Parcours d’offre</strong><span>Relier une ressource à votre agenda et à vos offres Olithea.</span></a>
                </div>
            </div>
        </section>

        <section class="mg-final-cta">
            <div class="mg-wrap">
                <div><h2>Transformez une ressource utile en point de départ clair.</h2><p>Présentez votre contenu, recueillez la demande avec transparence et guidez vers la prochaine étape dans Olithea.</p></div>
                <a href="{{ route('register-pro') }}">Créer mon espace Olithea</a>
            </div>
        </section>
    </main>
</x-app-layout>

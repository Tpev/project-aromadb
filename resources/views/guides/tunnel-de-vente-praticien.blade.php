<x-app-layout>
    @section('title', 'Tunnel de vente pour praticien : définition et exemples | Olithea')
    @section('meta_description', 'Comprenez simplement le tunnel de vente pour praticien : étapes, exemple vers un rendez-vous, marketing éthique et informations à recueillir.')

    @section('meta_og')
        <meta property="og:type" content="article">
        <meta property="og:locale" content="fr_FR">
        <meta property="og:url" content="{{ route('guides.sales-funnel-practitioner') }}">
        <meta property="og:title" content="Tunnel de vente pour praticien : définition, étapes et exemples simples">
        <meta property="og:description" content="Un guide clair pour orienter une personne intéressée sans marketing agressif.">
        <meta property="og:image" content="{{ asset('images/features/parcours-offre-creation.webp') }}">
        <meta property="article:published_time" content="2026-07-11">
        <meta property="article:modified_time" content="2026-07-11">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Tunnel de vente pour praticien : le guide simple">
        <meta name="twitter:description" content="Définition, étapes, exemple concret et principes éthiques pour praticiens et accompagnants.">
        <meta name="twitter:image" content="{{ asset('images/features/parcours-offre-creation.webp') }}">
    @endsection

    @section('structured_data')
        @php
            $funnelGuideFaq = [
                ['question' => 'Un tunnel de vente est-il adapté à un praticien ?', 'answer' => 'Oui, s’il sert à expliquer, rassurer et orienter sans pression. Il peut guider vers une réservation, une inscription, une ressource ou une demande de contact, sans transformer la relation d’accompagnement en démarche commerciale agressive.'],
                ['question' => 'Combien de pages faut-il pour commencer ?', 'answer' => 'Une seule page peut suffire pour présenter une proposition et ouvrir la prochaine étape. Un parcours plus long n’est utile que si chaque étape répond à une question réelle de la personne.'],
                ['question' => 'Faut-il offrir quelque chose gratuitement ?', 'answer' => 'Non. Une ressource gratuite peut aider à faire découvrir une approche, mais un tunnel peut aussi présenter une séance, un atelier, une formation, un bon cadeau ou une demande de rappel.'],
                ['question' => 'Quelles informations faut-il demander ?', 'answer' => 'Demandez uniquement ce qui est nécessaire pour répondre : généralement un prénom, une adresse email et parfois un téléphone. Les informations médicales ou cliniques n’ont pas leur place dans un formulaire marketing.'],
                ['question' => 'Un tunnel garantit-il davantage de rendez-vous ?', 'answer' => 'Non. Il rend la prochaine étape plus claire et permet de mieux suivre les demandes, mais il ne garantit ni trafic, ni rendez-vous, ni chiffre d’affaires.'],
            ];
            $funnelGuideStructuredData = [
                '@context' => 'https://schema.org',
                '@graph' => [
                    ['@type' => 'BreadcrumbList', 'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Tunnel de vente pour praticien', 'item' => route('guides.sales-funnel-practitioner')],
                    ]],
                    ['@type' => 'Article', 'headline' => 'Tunnel de vente pour praticien : définition, étapes et exemples simples', 'description' => 'Guide pédagogique pour créer un parcours commercial clair et respectueux adapté aux praticiens.', 'inLanguage' => 'fr-FR', 'datePublished' => '2026-07-11', 'dateModified' => '2026-07-11', 'mainEntityOfPage' => route('guides.sales-funnel-practitioner'), 'author' => ['@type' => 'Organization', 'name' => 'Olithea'], 'publisher' => ['@type' => 'Organization', 'name' => 'Olithea', 'logo' => ['@type' => 'ImageObject', 'url' => asset('images/brand/olithea-logo-horizontal-green-cropped.png')]], 'image' => asset('images/features/parcours-offre-creation.webp')],
                    ['@type' => 'FAQPage', 'mainEntity' => collect($funnelGuideFaq)->map(fn ($item) => ['@type' => 'Question', 'name' => $item['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']]])->all()],
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($funnelGuideStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endsection

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/marketing-guides.css') }}">
    @endpush

    <main class="mg-page">
        <header class="mg-hero">
            <div class="mg-wrap">
                <nav class="mg-breadcrumb" aria-label="Fil d’Ariane">
                    <a href="{{ url('/') }}">Accueil</a><span>›</span><span>Guides</span><span>›</span><span>Tunnel de vente pour praticien</span>
                </nav>
                <p class="mg-kicker">Guide marketing éthique</p>
                <h1>Tunnel de vente pour praticien : définition, étapes et exemples simples</h1>
                <p class="mg-hero-intro">Un tunnel de vente n’est pas forcément une mécanique agressive. Pour un praticien, c’est surtout une façon d’expliquer une proposition et d’aider une personne intéressée à choisir la prochaine étape.</p>
                <p class="mg-meta"><span>Publié le 11 juillet 2026</span><span>Lecture : environ 9 minutes</span><span>Pour praticiens, coachs et accompagnants</span></p>
            </div>
        </header>

        <div class="mg-wrap mg-layout">
            <aside class="mg-toc" aria-label="Sommaire">
                <strong>Dans ce guide</strong>
                <a href="#definition">Définition simple</a>
                <a href="#etapes">Les quatre étapes</a>
                <a href="#exemple">Exemple vers un rendez-vous</a>
                <a href="#ethique">Une approche éthique</a>
                <a href="#informations">Les informations à demander</a>
                <a href="#demarrer">Comment commencer</a>
                <a href="#faq">Questions fréquentes</a>
            </aside>

            <article class="mg-article">
                <section id="definition">
                    <h2>Qu’est-ce qu’un tunnel de vente ?</h2>
                    <div class="mg-definition">
                        <p><strong>Un tunnel de vente est une succession d’étapes qui conduit une personne depuis un premier point de contact vers une action précise.</strong></p>
                        <p>Cette action peut être une prise de rendez-vous, une inscription à un atelier, le téléchargement d’une ressource, l’achat d’un bon cadeau ou une simple demande de rappel.</p>
                    </div>
                    <p>Le mot « vente » peut sembler mal adapté aux métiers de l’accompagnement. Pourtant, le principe n’impose ni pression ni manipulation. Une personne découvre déjà votre activité sur un réseau social, votre profil, un annuaire ou grâce au bouche-à-oreille. Le tunnel lui évite seulement de devoir deviner quoi faire ensuite.</p>
                    <p>Sans parcours clair, elle peut arriver sur une page d’accueil générale, lire plusieurs textes puis repartir sans savoir quelle prestation choisir. Avec un parcours simple, elle reçoit une explication centrée sur son intention et un bouton cohérent avec cette explication.</p>
                </section>

                <section id="etapes">
                    <h2>Les quatre étapes d’un tunnel adapté aux praticiens</h2>
                    <div class="mg-diagram" aria-label="Les quatre étapes d’un tunnel de vente pour praticien">
                        <div><span>01</span><strong>Attirer l’attention</strong><p>Un contenu, une recommandation ou un lien partagé.</p></div>
                        <div><span>02</span><strong>Expliquer</strong><p>Une page présente une proposition et son cadre.</p></div>
                        <div><span>03</span><strong>Recueillir ou qualifier</strong><p>Un formulaire court si la suite le nécessite.</p></div>
                        <div><span>04</span><strong>Orienter</strong><p>Rendez-vous, inscription, ressource ou échange.</p></div>
                    </div>

                    <h3>1. Attirer l’attention sans promettre l’impossible</h3>
                    <p>Le premier contact doit partir d’un sujet précis que votre public comprend. Il peut s’agir d’un article, d’une publication, d’une intervention, d’un événement ou d’un conseil transmis par une personne de confiance. Le rôle de ce contenu n’est pas de promettre un résultat médical. Il montre votre approche et indique à qui la proposition peut être utile.</p>

                    <h3>2. Expliquer une seule proposition</h3>
                    <p>La page suivante ne doit pas présenter tout votre catalogue. Elle répond à quelques questions simples : qu’est-ce qui est proposé, à qui cela s’adresse, comment cela se déroule et quelle est la prochaine action ? Plus la proposition est précise, moins la personne doit fournir d’effort pour comprendre.</p>

                    <h3>3. Recueillir uniquement ce qui est nécessaire</h3>
                    <p>Un formulaire n’est utile que si vous devez transmettre une ressource, répondre personnellement ou préparer l’étape suivante. Un prénom et une adresse email suffisent souvent. Le téléphone n’est pertinent que si la personne demande à être rappelée.</p>

                    <h3>4. Guider vers une action cohérente</h3>
                    <p>La prochaine étape doit correspondre à la promesse de la page. Après la présentation d’une séance, ouvrez les disponibilités. Après un guide, donnez accès au document. Après un atelier, affichez l’inscription. Évitez d’envoyer automatiquement vers une offre sans rapport.</p>
                </section>

                <section id="exemple">
                    <h2>Exemple complet : d’un lien partagé à une demande de rendez-vous</h2>
                    <div class="mg-example">
                        <h3>Une sophrologue présente une séance découverte</h3>
                        <ol class="mg-example-steps">
                            <li>Elle publie un contenu expliquant comment se déroule une première séance, sans formuler de promesse thérapeutique.</li>
                            <li>Le lien ouvre une page consacrée à la séance découverte : durée, cadre, format et personnes auxquelles elle s’adresse.</li>
                            <li>Le bouton invite à consulter les disponibilités. Si un échange préalable est nécessaire, un formulaire demande le prénom, l’email et la préférence de contact.</li>
                            <li>La personne accède à la réservation ou reçoit la confirmation de sa demande. La praticienne retrouve son origine et peut répondre dans le cadre annoncé.</li>
                        </ol>
                    </div>
                    <p>Ce parcours ne crée pas artificiellement un besoin. Il transforme une communication déjà utile en chemin compréhensible. Une personne qui n’est pas prête peut quitter la page sans pression ; une personne intéressée sait exactement comment avancer.</p>

                    <div class="mg-inline-cta">
                        <h3>Olithea relie la page à votre activité existante</h3>
                        <p>Les Parcours d’offre peuvent conduire vers votre agenda, un atelier, une formation, un bon cadeau, une ressource ou une demande qualifiée.</p>
                        <a href="{{ route('features.offer-journeys') }}">Découvrir les Parcours d’offre</a>
                    </div>
                    <figure class="mg-product-figure">
                        <img src="{{ asset('images/features/parcours-offre-creation.webp') }}" alt="Choix de l’objectif d’un tunnel de vente dans Olithea" width="1425" height="891" loading="lazy">
                        <figcaption>Dans Olithea, le parcours commence par un objectif métier concret plutôt que par une page vide.</figcaption>
                    </figure>
                </section>

                <section id="ethique">
                    <h2>Construire un tunnel compatible avec une pratique éthique</h2>
                    <p>Une page efficace n’a pas besoin d’utiliser la peur, l’urgence ou une promesse de transformation. Dans les métiers du bien-être et de l’accompagnement, la clarté et le consentement sont plus importants que les techniques de pression.</p>
                    <div class="mg-principles">
                        <div>
                            <h3>À privilégier</h3>
                            <ul>
                                <li>Décrire précisément le cadre et la prochaine étape.</li>
                                <li>Laisser une décision libre et réversible.</li>
                                <li>Séparer la réponse demandée des communications marketing.</li>
                                <li>Employer des exemples réalistes et vérifiables.</li>
                            </ul>
                        </div>
                        <div>
                            <h3>À éviter</h3>
                            <ul>
                                <li>Les compteurs ou fausses places limitées.</li>
                                <li>Les garanties de guérison, de résultat ou de revenu.</li>
                                <li>Les formulaires qui demandent des informations sensibles sans nécessité.</li>
                                <li>Les relances répétées sans consentement adapté.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section id="informations">
                    <h2>Quelles informations demander dans le formulaire ?</h2>
                    <p>La bonne question n’est pas « combien d’informations puis-je obtenir ? », mais « de quoi ai-je besoin pour tenir la promesse de cette page ? ».</p>
                    <ul>
                        <li><strong>Pour envoyer une ressource :</strong> un prénom et une adresse email.</li>
                        <li><strong>Pour une demande de rappel :</strong> un prénom, un téléphone et éventuellement une préférence de contact.</li>
                        <li><strong>Pour une inscription :</strong> les informations nécessaires à l’événement, sans anticiper un dossier clinique.</li>
                        <li><strong>Pour une réservation :</strong> laissez le module d’agenda recueillir les informations propres au rendez-vous.</li>
                    </ul>
                    <div class="mg-warning">
                        <h3>Ne collectez pas de données de santé dans un formulaire marketing</h3>
                        <p>Un formulaire de capture ne doit pas demander un diagnostic, des antécédents, des traitements ou un récit clinique. Ces informations appartiennent, lorsqu’elles sont nécessaires, à un espace approprié et sécurisé après l’établissement de la relation.</p>
                    </div>
                </section>

                <section id="demarrer">
                    <h2>Comment créer un premier tunnel simple ?</h2>
                    <ol>
                        <li><strong>Choisissez un seul objectif.</strong> Par exemple : obtenir des demandes de séance découverte.</li>
                        <li><strong>Partez d’une proposition existante.</strong> Évitez de créer une nouvelle offre uniquement pour remplir le tunnel.</li>
                        <li><strong>Rédigez une page courte.</strong> Expliquez le cadre, les personnes concernées et la prochaine action.</li>
                        <li><strong>Ajoutez un formulaire seulement si nécessaire.</strong> La réservation ou l’inscription peut parfois s’ouvrir directement.</li>
                        <li><strong>Prévisualisez le parcours sur téléphone.</strong> Vérifiez le texte, le bouton, la confidentialité et la page de confirmation.</li>
                        <li><strong>Partagez le lien dans un contexte précis.</strong> Une publication, une bio, un email demandé ou un support remis lors d’un événement.</li>
                    </ol>
                    <p>Commencez avec ce parcours unique avant d’ajouter d’autres étapes. Les visites, demandes et retours réels vous montreront ce qui mérite d’être clarifié.</p>
                </section>

                <section id="faq">
                    <h2>Questions fréquentes</h2>
                    <div class="mg-faq">
                        @foreach($funnelGuideFaq as $item)
                            <details><summary>{{ $item['question'] }}</summary><p>{{ $item['answer'] }}</p></details>
                        @endforeach
                    </div>
                </section>
            </article>
        </div>

        <section class="mg-related">
            <div class="mg-wrap">
                <h2>Continuer avec un exemple concret</h2>
                <div class="mg-related-grid">
                    <a href="{{ route('features.capture-page') }}"><strong>Créer une page de capture</strong><span>Voir comment le formulaire et la prochaine étape fonctionnent dans Olithea.</span></a>
                    <a href="{{ route('guides.lead-magnet-practitioner') }}"><strong>Choisir un lead magnet</strong><span>Comparer les formats PDF, audio, checklist et mini-programme.</span></a>
                    <a href="{{ route('features.offer-journeys') }}"><strong>Découvrir les Parcours d’offre</strong><span>Voir la page produit et les destinations disponibles.</span></a>
                </div>
            </div>
        </section>

        <section class="mg-final-cta">
            <div class="mg-wrap">
                <div><h2>Créez un parcours clair, sans transformer votre pratique en machine à vendre.</h2><p>Olithea vous aide à présenter une proposition et à la relier à la prochaine étape déjà gérée dans votre espace.</p></div>
                <a href="{{ route('register-pro') }}">Créer mon espace Olithea</a>
            </div>
        </section>
    </main>
</x-app-layout>

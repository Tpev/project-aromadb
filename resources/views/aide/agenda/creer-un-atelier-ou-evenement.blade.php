<x-app-layout>
    @section('title', 'Créer un atelier ou événement | Agenda AromaMade PRO')
    @section('meta_description')
Créez ateliers et événements (présentiel ou visio), gérez places, réservations, paiement Stripe et publication sur votre Portail Pro avec AromaMade PRO.
    @endsection
    @section('structured_data')
        <script type="application/ld+json">
            {
              "@context": "https://schema.org",
              "@type": "BreadcrumbList",
              "itemListElement": [
                { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "{{ url('/') }}" },
                { "@type": "ListItem", "position": 2, "name": "Agenda & prise de rendez-vous", "item": "{{ url('/fonctionnalites/agenda') }}" },
                { "@type": "ListItem", "position": 3, "name": "Créer un atelier ou un événement", "item": "{{ url('/aide/agenda/creer-un-atelier-ou-evenement') }}" }
              ]
            }
        </script>
        <script type="application/ld+json">
            {
              "@context": "https://schema.org",
              "@type": "FAQPage",
              "mainEntity": [
                {
                  "@type": "Question",
                  "name": "Puis-je créer un événement en présentiel et en visio ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Oui. Vous choisissez le type d'événement lors de la création, avec les champs adaptés à chaque format."
                  }
                },
                {
                  "@type": "Question",
                  "name": "Comment limiter les places ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Activez l'option de places limitées puis définissez le nombre maximal de participants."
                  }
                },
                {
                  "@type": "Question",
                  "name": "Le paiement est-il possible pour un atelier ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Oui, si la réservation est activée et que votre compte Stripe Connect est configuré."
                  }
                }
              ]
            }
        </script>
    @endsection

    <section class="py-16 bg-white">
        <div class="container mx-auto px-4 max-w-4xl">

            <nav class="breadcrumb mb-6 text-sm">
                <a href="{{ url('/') }}">Accueil</a> <span>›</span>
                <a href="{{ url('/fonctionnalites/agenda') }}">Agenda & prise de rendez-vous</a> <span>›</span>
                <span class="current">Créer un atelier ou un événement</span>
            </nav>

            <h1 class="text-3xl md:text-4xl font-bold mb-6">
                Créer un atelier ou un événement dans l’agenda
            </h1>

            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                L’agenda AromaMade PRO permet de gérer vos formats collectifs :
                <strong>atelier</strong>, <strong>stage</strong>, <strong>conférence</strong> ou autre événement ponctuel.
                Vous choisissez le type, le mode (présentiel ou visio), les places disponibles et la publication.
            </p>

            <h2 class="text-2xl font-semibold mb-4">
                Étapes de création
            </h2>
            <ol class="list-decimal pl-6 text-gray-600 leading-relaxed mb-8">
                <li>
                    <strong>Renseignez les informations de base</strong><br>
                    Nom, description, date/heure de début, durée et visuel si besoin.
                </li>
                <li class="mt-3">
                    <strong>Choisissez le format</strong><br>
                    Soit en présentiel (lieu obligatoire), soit en visio.
                </li>
                <li class="mt-3">
                    <strong>Configurez la visio</strong><br>
                    En mode visio, vous pouvez utiliser un lien externe ou générer un accès AromaMade selon le fournisseur sélectionné.
                </li>
                <li class="mt-3">
                    <strong>Activez ou non la réservation</strong><br>
                    Vous pouvez publier un événement informatif, ou activer la réservation avec éventuelle limitation de places.
                </li>
                <li class="mt-3">
                    <strong>Publiez sur votre Portail Pro</strong><br>
                    L’option d’affichage portail permet de rendre l’événement visible côté public.
                </li>
            </ol>

            <h2 class="text-2xl font-semibold mb-4">
                Gestion des places et du paiement
            </h2>
            <ul class="list-disc pl-6 text-gray-600 leading-relaxed mb-8">
                <li>Activez “places limitées” pour fixer une capacité maximale.</li>
                <li>Le paiement peut être activé si la réservation est active.</li>
                <li>Pour un événement payant, votre compte Stripe Connect doit être configuré.</li>
            </ul>

            <h2 class="text-2xl font-semibold mb-4">
                Bloquer l’agenda avec l’événement
            </h2>
            <p class="text-gray-600 leading-relaxed mb-8">
                Si vous activez le blocage du calendrier lors de la création, AromaMade ajoute une indisponibilité
                sur la période de l’événement pour éviter les chevauchements avec vos rendez-vous individuels.
            </p>

            <h2 class="text-2xl font-semibold mb-4">
                Conseils pratiques
            </h2>
            <ul class="list-disc pl-6 text-gray-600 leading-relaxed mb-8">
                <li>Ajoutez une description claire du public visé et du déroulé.</li>
                <li>Précisez les informations de lieu ou de connexion visio.</li>
                <li>Si vous dupliquez souvent un format, utilisez la duplication d’événement pour gagner du temps.</li>
            </ul>

            <h2 class="text-2xl font-semibold mb-4">
                FAQ rapide
            </h2>
            <div class="text-gray-600 leading-relaxed mb-8">
                <p><strong>Un événement peut-il être visible sans réservation ?</strong><br>Oui, vous pouvez publier un événement informatif sans activer la réservation en ligne.</p>
                <p class="mt-3"><strong>Quelle solution visio choisir ?</strong><br>Vous pouvez utiliser un lien externe ou le mode AromaMade selon votre organisation.</p>
                <p class="mt-3"><strong>Pourquoi bloquer le calendrier ?</strong><br>Pour éviter qu’un rendez-vous individuel soit pris pendant votre atelier ou stage.</p>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-10">
                <h3 class="font-semibold mb-3">À lire aussi</h3>
                <ul class="list-disc pl-6 text-gray-600 leading-relaxed">
                    <li>
                        <a href="{{ url('/aide/agenda/creer-un-rendez-vous-en-ligne') }}" class="text-green-700 font-semibold">
                            Créer un rendez-vous en ligne
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/aide/agenda/gerer-indisponibilites') }}" class="text-green-700 font-semibold">
                            Gérer ses indisponibilités
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/fonctionnalites/agenda') }}" class="text-green-700 font-semibold">
                            Revenir à la fonctionnalité Agenda
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/metiers/naturopathe') }}" class="text-green-700 font-semibold">
                            Guide métier : naturopathe
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/metiers/sophrologue') }}" class="text-green-700 font-semibold">
                            Guide métier : sophrologue
                        </a>
                    </li>
                </ul>
            </div>

            <div class="text-center mt-12">
                <a href="{{ route('register-pro') }}" class="btn-primary">
                    Créer mon premier atelier
                </a>
            </div>

        </div>
    </section>
</x-app-layout>

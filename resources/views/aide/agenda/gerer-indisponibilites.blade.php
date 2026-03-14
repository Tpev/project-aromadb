<x-app-layout>
    @section('title', 'Gérer les indisponibilités agenda | AromaMade PRO')
    @section('meta_description')
Bloquez congés, absences et fermetures dans AromaMade PRO pour éviter les réservations non souhaitées et garder un agenda praticien fiable.
    @endsection
    @section('structured_data')
        <script type="application/ld+json">
            {
              "@context": "https://schema.org",
              "@type": "BreadcrumbList",
              "itemListElement": [
                { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "{{ url('/') }}" },
                { "@type": "ListItem", "position": 2, "name": "Agenda & prise de rendez-vous", "item": "{{ url('/fonctionnalites/agenda') }}" },
                { "@type": "ListItem", "position": 3, "name": "Gérer ses indisponibilités", "item": "{{ url('/aide/agenda/gerer-indisponibilites') }}" }
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
                  "name": "Quand utiliser une indisponibilité ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Utilisez une indisponibilité pour bloquer congés, absences, déplacements ou fermetures, afin que ces créneaux ne soient pas réservables."
                  }
                },
                {
                  "@type": "Question",
                  "name": "Quelle différence avec une disponibilité ponctuelle ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "L'indisponibilité retire des créneaux, alors que la disponibilité ponctuelle ajoute une ouverture exceptionnelle."
                  }
                },
                {
                  "@type": "Question",
                  "name": "Que se passe-t-il après suppression d'une indisponibilité ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Les créneaux redeviennent potentiellement réservables s'ils respectent vos autres règles d'agenda."
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
                <span class="current">Gérer ses indisponibilités</span>
            </nav>

            <h1 class="text-3xl md:text-4xl font-bold mb-6">
                Gérer ses indisponibilités (congés, absences, fermetures)
            </h1>

            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                Les indisponibilités permettent de <strong>bloquer des périodes</strong> où vous ne souhaitez pas recevoir de réservation.
                C’est essentiel pour vos congés, formations, déplacements ou toute fermeture ponctuelle.
            </p>

            <h2 class="text-2xl font-semibold mb-4">
                Indisponibilité ou disponibilité ponctuelle : quelle différence ?
            </h2>
            <ul class="list-disc pl-6 text-gray-600 leading-relaxed mb-8">
                <li><strong>Indisponibilité :</strong> retire des créneaux à votre planning (vous bloquez).</li>
                <li><strong>Disponibilité ponctuelle :</strong> ajoute des créneaux exceptionnels en plus de votre semaine type (vous ouvrez).</li>
            </ul>

            <h2 class="text-2xl font-semibold mb-4">
                Comment créer une indisponibilité
            </h2>
            <ol class="list-decimal pl-6 text-gray-600 leading-relaxed mb-8">
                <li>
                    <strong>Ouvrez la section “Indisponibilités”</strong><br>
                    Depuis votre espace pro, accédez à la liste des indisponibilités puis cliquez sur “Créer une indisponibilité”.
                </li>
                <li class="mt-3">
                    <strong>Renseignez la période</strong><br>
                    Indiquez date/heure de début et date/heure de fin.
                    Vous pouvez couvrir quelques heures, une journée entière ou plusieurs jours.
                </li>
                <li class="mt-3">
                    <strong>Ajoutez une raison (facultatif)</strong><br>
                    La raison est utile pour votre organisation interne (vacances, déplacement, fermeture).
                </li>
                <li class="mt-3">
                    <strong>Enregistrez</strong><br>
                    Les créneaux impactés ne seront plus proposés à la réservation en ligne.
                </li>
            </ol>

            <h2 class="text-2xl font-semibold mb-4">
                Modifier ou supprimer une indisponibilité
            </h2>
            <p class="text-gray-600 leading-relaxed mb-8">
                Depuis la liste des indisponibilités, vous pouvez supprimer un bloc devenu inutile.
                Dès suppression, les créneaux potentiellement libérés redeviennent réservables s’ils respectent vos autres règles d’agenda.
            </p>

            <h2 class="text-2xl font-semibold mb-4">
                Bonnes pratiques
            </h2>
            <ul class="list-disc pl-6 text-gray-600 leading-relaxed mb-8">
                <li>Bloquez vos congés le plus tôt possible pour éviter des réservations à annuler.</li>
                <li>Utilisez les indisponibilités pour les fermetures, et les disponibilités ponctuelles pour les ouvertures exceptionnelles.</li>
                <li>Après une modification importante, testez une réservation depuis votre page publique pour valider le résultat.</li>
            </ul>

            <h2 class="text-2xl font-semibold mb-4">
                FAQ rapide
            </h2>
            <div class="text-gray-600 leading-relaxed mb-8">
                <p><strong>Une indisponibilité bloque-t-elle tous les modes ?</strong><br>Oui, la période devient non réservable sur les créneaux concernés.</p>
                <p class="mt-3"><strong>Je peux bloquer seulement quelques heures ?</strong><br>Oui, vous pouvez saisir un bloc horaire précis avec date et heure de début/fin.</p>
                <p class="mt-3"><strong>Pourquoi tester après modification ?</strong><br>Un test rapide depuis la page publique confirme que le planning est conforme avant vos prochaines réservations.</p>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-10">
                <h3 class="font-semibold mb-3">À lire aussi</h3>
                <ul class="list-disc pl-6 text-gray-600 leading-relaxed">
                    <li>
                        <a href="{{ url('/aide/agenda/configurer-disponibilites') }}" class="text-green-700 font-semibold">
                            Configurer ses disponibilités et horaires types
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/aide/agenda/creer-un-rendez-vous-en-ligne') }}" class="text-green-700 font-semibold">
                            Créer un rendez-vous en ligne depuis le Portail Pro
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
                    Organiser mon agenda gratuitement
                </a>
            </div>

        </div>
    </section>
</x-app-layout>

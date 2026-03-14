<x-app-layout>
    @section('title', 'Créer un rendez-vous en ligne | Portail Pro AromaMade')
    @section('meta_description')
Activez la prise de rendez-vous en ligne sur AromaMade PRO : prestations, modes de consultation, disponibilités et créneaux publiés sur votre Portail Pro.
    @endsection
    @section('structured_data')
        <script type="application/ld+json">
            {
              "@context": "https://schema.org",
              "@type": "BreadcrumbList",
              "itemListElement": [
                { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "{{ url('/') }}" },
                { "@type": "ListItem", "position": 2, "name": "Agenda & prise de rendez-vous", "item": "{{ url('/fonctionnalites/agenda') }}" },
                { "@type": "ListItem", "position": 3, "name": "Créer un rendez-vous en ligne", "item": "{{ url('/aide/agenda/creer-un-rendez-vous-en-ligne') }}" }
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
                  "name": "Pourquoi aucune date n'est disponible ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "La prestation ou le mode choisi n'a pas de disponibilité active, ou les créneaux sont bloqués par des indisponibilités, des rendez-vous existants ou des limites quotidiennes."
                  }
                },
                {
                  "@type": "Question",
                  "name": "Le mode cabinet nécessite-t-il un lieu configuré ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Oui. Pour les rendez-vous au cabinet, un lieu de consultation doit être enregistré pour afficher les créneaux côté client."
                  }
                },
                {
                  "@type": "Question",
                  "name": "Les créneaux tiennent-ils compte du préavis et du temps entre rendez-vous ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Oui. AromaMade calcule les créneaux réels en tenant compte du préavis minimum, du buffer entre rendez-vous et de la durée de la prestation."
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
                <span class="current">Créer un rendez-vous en ligne</span>
            </nav>

            <h1 class="text-3xl md:text-4xl font-bold mb-6">
                Créer un rendez-vous en ligne depuis le Portail Pro
            </h1>

            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                Avec AromaMade PRO, vos clients peuvent réserver un créneau depuis votre
                <strong>Portail Pro</strong> en choisissant la prestation, le mode de séance et une date disponible.
                Ce guide vous explique la configuration à faire côté praticien pour obtenir une prise de rendez-vous fluide.
            </p>

            <h2 class="text-2xl font-semibold mb-4">
                Avant de commencer
            </h2>
            <ul class="list-disc pl-6 text-gray-600 leading-relaxed mb-8">
                <li>Créer au moins une prestation dans votre catalogue</li>
                <li>Activer la réservation en ligne sur les prestations concernées</li>
                <li>Définir vos disponibilités hebdomadaires et vos exceptions</li>
                <li>Configurer vos lieux de consultation si vous recevez au cabinet</li>
            </ul>

            <h2 class="text-2xl font-semibold mb-4">
                Étapes pour activer la réservation en ligne
            </h2>
            <ol class="list-decimal pl-6 text-gray-600 leading-relaxed mb-8">
                <li>
                    <strong>Paramétrez votre prestation</strong><br>
                    Dans vos produits, renseignez la durée, le mode de consultation (cabinet, visio, domicile, entreprise)
                    et activez l’option de réservation en ligne.
                </li>
                <li class="mt-3">
                    <strong>Définissez vos plages de disponibilité</strong><br>
                    Ajoutez vos horaires types, puis vos disponibilités ponctuelles si besoin.
                    Le moteur de créneaux ne proposera que les jours et heures réellement compatibles.
                </li>
                <li class="mt-3">
                    <strong>Ajoutez vos cabinets si nécessaire</strong><br>
                    Pour les prestations au cabinet, le client devra choisir un lieu valide avant de voir les créneaux.
                </li>
                <li class="mt-3">
                    <strong>Partagez votre lien de réservation</strong><br>
                    Le client réserve depuis votre page publique pro (ou un lien partenaire dédié si vous utilisez les tokens de booking).
                </li>
            </ol>

            <h2 class="text-2xl font-semibold mb-4">
                Comment se passe la réservation côté client
            </h2>
            <p class="text-gray-600 leading-relaxed mb-4">
                Le parcours client suit un ordre clair : prestation → mode de consultation → date disponible → créneau horaire → informations de contact.
                Le système calcule ensuite la disponibilité réelle en tenant compte :
            </p>
            <ul class="list-disc pl-6 text-gray-600 leading-relaxed mb-8">
                <li>des durées de prestation,</li>
                <li>du préavis minimum de réservation,</li>
                <li>du temps tampon entre rendez-vous,</li>
                <li>des indisponibilités et rendez-vous déjà enregistrés,</li>
                <li>des limites quotidiennes éventuelles sur la prestation.</li>
            </ul>

            <h2 class="text-2xl font-semibold mb-4">
                Cas fréquents et résolution rapide
            </h2>
            <ul class="list-disc pl-6 text-gray-600 leading-relaxed mb-8">
                <li><strong>Aucune date disponible :</strong> vérifiez qu’une disponibilité existe bien pour cette prestation et ce mode.</li>
                <li><strong>Aucun créneau sur une date :</strong> cela signifie souvent que les créneaux restants sont bloqués par indisponibilité, buffer ou rendez-vous existants.</li>
                <li><strong>Le mode cabinet ne s’affiche pas :</strong> confirmez que la prestation a bien le mode “dans le cabinet” et qu’un lieu est enregistré.</li>
            </ul>

            <h2 class="text-2xl font-semibold mb-4">
                FAQ rapide
            </h2>
            <div class="text-gray-600 leading-relaxed mb-8">
                <p><strong>Pourquoi je ne vois pas de dates ?</strong><br>Vérifiez vos disponibilités actives, le mode sélectionné et les éventuelles limites journalières.</p>
                <p class="mt-3"><strong>Dois-je créer plusieurs produits pour plusieurs formats ?</strong><br>Oui, c’est recommandé si vous proposez des durées ou tarifs différents pour une même prestation.</p>
                <p class="mt-3"><strong>Le client peut-il réserver en visio et au cabinet ?</strong><br>Oui, si vous avez activé ces modes sur vos prestations et configuré les éléments nécessaires (lieu, lien visio, etc.).</p>
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
                        <a href="{{ url('/aide/agenda/duree-prestation-temps-de-pause') }}" class="text-green-700 font-semibold">
                            Définir la durée d’une prestation et le temps entre deux rendez-vous
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/metiers/naturopathe') }}" class="text-green-700 font-semibold">
                            Voir un exemple métier : naturopathe
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/metiers/sophrologue') }}" class="text-green-700 font-semibold">
                            Voir un exemple métier : sophrologue
                        </a>
                    </li>
                </ul>
            </div>

            <div class="text-center mt-12">
                <a href="{{ route('register-pro') }}" class="btn-primary">
                    Activer ma prise de rendez-vous en ligne
                </a>
            </div>

        </div>
    </section>
</x-app-layout>

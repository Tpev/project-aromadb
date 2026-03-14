<x-app-layout>
    @section('title', 'Durée prestation et pauses agenda | AromaMade PRO')
    @section('meta_description')
Réglez durée des séances, limite quotidienne, préavis et temps entre deux rendez-vous pour un agenda praticien réaliste dans AromaMade PRO.
    @endsection
    @section('structured_data')
        <script type="application/ld+json">
            {
              "@context": "https://schema.org",
              "@type": "BreadcrumbList",
              "itemListElement": [
                { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "{{ url('/') }}" },
                { "@type": "ListItem", "position": 2, "name": "Agenda & prise de rendez-vous", "item": "{{ url('/fonctionnalites/agenda') }}" },
                { "@type": "ListItem", "position": 3, "name": "Durée prestation et temps de pause", "item": "{{ url('/aide/agenda/duree-prestation-temps-de-pause') }}" }
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
                  "name": "Où régler la durée d'une prestation ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "La durée se règle dans la fiche produit de chaque prestation, en minutes."
                  }
                },
                {
                  "@type": "Question",
                  "name": "Le temps entre deux rendez-vous est-il global ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Oui. Le buffer entre rendez-vous se configure dans Informations d'entreprise et s'applique globalement à l'agenda."
                  }
                },
                {
                  "@type": "Question",
                  "name": "Comment limiter la surcharge d'une journée ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Vous pouvez définir un nombre maximum de séances par jour sur chaque prestation, en complément des durées et du buffer."
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
                <span class="current">Durée prestation et temps de pause</span>
            </nav>

            <h1 class="text-3xl md:text-4xl font-bold mb-6">
                Définir la durée d’une prestation et le temps de pause
            </h1>

            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                Pour garder un agenda cohérent, vous devez paramétrer la <strong>durée réelle</strong> de vos séances
                et prévoir un <strong>temps entre deux rendez-vous</strong>. AromaMade PRO combine ces paramètres pour proposer
                des créneaux réalistes à vos clients.
            </p>

            <h2 class="text-2xl font-semibold mb-4">
                1. Régler la durée de chaque prestation
            </h2>
            <p class="text-gray-600 leading-relaxed mb-4">
                Dans vos produits, chaque prestation possède un champ “Durée (en minutes)”.
                C’est cette valeur qui définit la longueur d’un rendez-vous dans l’agenda.
            </p>
            <ul class="list-disc pl-6 text-gray-600 leading-relaxed mb-8">
                <li>Exemple : Massage californien 60 min</li>
                <li>Exemple : Bilan naturopathie 90 min</li>
                <li>Vous pouvez créer plusieurs formats d’une même prestation (durée/tarif différents)</li>
            </ul>

            <h2 class="text-2xl font-semibold mb-4">
                2. Limiter le nombre de séances par jour
            </h2>
            <p class="text-gray-600 leading-relaxed mb-8">
                Le champ “Nombre maximum de séances par jour” sur une prestation vous permet de plafonner la charge quotidienne.
                Une fois la limite atteinte, aucun nouveau créneau de cette prestation n’est proposé ce jour-là.
            </p>

            <h2 class="text-2xl font-semibold mb-4">
                3. Ajouter un temps entre deux rendez-vous
            </h2>
            <p class="text-gray-600 leading-relaxed mb-4">
                Le temps de pause se règle dans <strong>Informations d’entreprise</strong> via le paramètre
                “durée ajoutée automatiquement entre deux rendez-vous”.
            </p>
            <ul class="list-disc pl-6 text-gray-600 leading-relaxed mb-8">
                <li>Ce réglage est global au praticien</li>
                <li>Il sert pour la préparation de séance, les notes, une pause ou un déplacement</li>
                <li>Il réduit automatiquement les créneaux disponibles quand l’agenda calcule les slots</li>
            </ul>

            <h2 class="text-2xl font-semibold mb-4">
                4. Préavis minimum de réservation
            </h2>
            <p class="text-gray-600 leading-relaxed mb-8">
                Vous pouvez aussi définir un préavis minimum (en heures) dans les informations d’entreprise.
                Cela empêche les réservations trop proches dans le temps, même si une plage semble libre.
            </p>

            <h2 class="text-2xl font-semibold mb-4">
                Exemple concret
            </h2>
            <p class="text-gray-600 leading-relaxed mb-8">
                Si une prestation dure 60 minutes et que votre buffer est de 15 minutes,
                l’agenda traite chaque rendez-vous comme un bloc de 75 minutes pour éviter l’enchaînement sans respiration.
            </p>

            <h2 class="text-2xl font-semibold mb-4">
                FAQ rapide
            </h2>
            <div class="text-gray-600 leading-relaxed mb-8">
                <p><strong>Le buffer remplace-t-il la durée de prestation ?</strong><br>Non, il s’ajoute à la durée pour protéger votre planning entre deux séances.</p>
                <p class="mt-3"><strong>Le préavis minimum bloque quoi exactement ?</strong><br>Il empêche les réservations trop proches de l’heure actuelle, même si un créneau existe théoriquement.</p>
                <p class="mt-3"><strong>Puis-je combiner limite par jour et buffer ?</strong><br>Oui, et c’est recommandé pour garder un agenda durable en période chargée.</p>
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
                        <a href="{{ url('/aide/agenda/gerer-indisponibilites') }}" class="text-green-700 font-semibold">
                            Gérer ses indisponibilités
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
                    Optimiser mon agenda pro
                </a>
            </div>

        </div>
    </section>
</x-app-layout>

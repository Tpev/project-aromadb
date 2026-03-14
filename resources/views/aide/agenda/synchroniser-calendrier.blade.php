<x-app-layout>
    @section('title', 'Synchroniser agenda Google, Apple, Outlook | AromaMade PRO')
    @section('meta_description')
Connectez Google Agenda à AromaMade PRO, gérez la couleur des événements et utilisez l’export ICS pour Apple Calendar et Outlook.
    @endsection
    @section('structured_data')
        <script type="application/ld+json">
            {
              "@context": "https://schema.org",
              "@type": "BreadcrumbList",
              "itemListElement": [
                { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "{{ url('/') }}" },
                { "@type": "ListItem", "position": 2, "name": "Agenda & prise de rendez-vous", "item": "{{ url('/fonctionnalites/agenda') }}" },
                { "@type": "ListItem", "position": 3, "name": "Synchroniser son calendrier", "item": "{{ url('/aide/agenda/synchroniser-calendrier') }}" }
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
                  "name": "Comment connecter Google Agenda à AromaMade ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Depuis Informations d'entreprise, cliquez sur Connecter Google Agenda puis autorisez l'accès."
                  }
                },
                {
                  "@type": "Question",
                  "name": "Apple Calendar et Outlook sont-ils compatibles ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Oui via le fichier ICS disponible sur la confirmation de rendez-vous."
                  }
                },
                {
                  "@type": "Question",
                  "name": "Puis-je modifier la couleur des événements Google ?",
                  "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Oui, après connexion Google, vous pouvez choisir la couleur des événements depuis le profil."
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
                <span class="current">Synchroniser son calendrier</span>
            </nav>

            <h1 class="text-3xl md:text-4xl font-bold mb-6">
                Synchroniser Google Calendar, Apple iCloud ou Outlook
            </h1>

            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                La connexion agenda vous aide à centraliser votre planning.
                Dans AromaMade PRO, la connexion native se fait avec <strong>Google Agenda</strong>,
                et les liens <strong>.ics</strong> permettent d’ajouter les rendez-vous dans d’autres calendriers compatibles.
            </p>

            <h2 class="text-2xl font-semibold mb-4">
                1. Connecter Google Agenda
            </h2>
            <ol class="list-decimal pl-6 text-gray-600 leading-relaxed mb-8">
                <li>
                    Ouvrez la section <strong>Informations d’entreprise</strong> dans votre profil pro.
                </li>
                <li class="mt-3">
                    Cliquez sur <strong>Connecter Google Agenda</strong> puis autorisez l’accès.
                </li>
                <li class="mt-3">
                    Une fois connecté, vous pouvez aussi choisir une couleur d’événement Google pour les rendez-vous créés depuis AromaMade.
                </li>
            </ol>

            <h2 class="text-2xl font-semibold mb-4">
                2. Ce que fait la synchronisation Google
            </h2>
            <ul class="list-disc pl-6 text-gray-600 leading-relaxed mb-8">
                <li>Les rendez-vous créés ou modifiés dans AromaMade peuvent être poussés dans Google Agenda.</li>
                <li>En visio, le système peut tenter d’ajouter un lien Meet lors de la création de l’événement Google.</li>
                <li>Vous pouvez déconnecter Google Agenda à tout moment depuis la même section profil.</li>
            </ul>

            <h2 class="text-2xl font-semibold mb-4">
                3. Apple Calendar et Outlook
            </h2>
            <p class="text-gray-600 leading-relaxed mb-4">
                Sur les confirmations de rendez-vous, un export <strong>ICS</strong> est disponible.
                Ce fichier est compatible avec Apple Calendar, Outlook et la plupart des agendas standards.
            </p>
            <ul class="list-disc pl-6 text-gray-600 leading-relaxed mb-8">
                <li>Le client peut cliquer “Ajouter à votre calendrier” depuis sa page de confirmation.</li>
                <li>Le fichier ICS contient date, durée, résumé et description du rendez-vous.</li>
            </ul>

            <h2 class="text-2xl font-semibold mb-4">
                Dépannage rapide
            </h2>
            <ul class="list-disc pl-6 text-gray-600 leading-relaxed mb-8">
                <li><strong>Google non connecté :</strong> reconnectez le compte depuis Informations d’entreprise.</li>
                <li><strong>Événement absent côté Google :</strong> vérifiez la connexion active et la création effective du rendez-vous.</li>
                <li><strong>Calendrier Apple/Outlook :</strong> utilisez le bouton ICS présent dans la confirmation de rendez-vous.</li>
            </ul>

            <h2 class="text-2xl font-semibold mb-4">
                FAQ rapide
            </h2>
            <div class="text-gray-600 leading-relaxed mb-8">
                <p><strong>La synchronisation Google est-elle obligatoire ?</strong><br>Non, mais elle simplifie fortement la gestion de planning si vous utilisez déjà Google Calendar.</p>
                <p class="mt-3"><strong>Que faire si Google se déconnecte ?</strong><br>Reconnectez le compte depuis votre profil pro et vérifiez les autorisations accordées.</p>
                <p class="mt-3"><strong>Le client peut-il ajouter son rendez-vous sur iPhone ?</strong><br>Oui, via le bouton ICS de la page de confirmation, compatible Apple Calendar.</p>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-10">
                <h3 class="font-semibold mb-3">À lire aussi</h3>
                <ul class="list-disc pl-6 text-gray-600 leading-relaxed">
                    <li>
                        <a href="{{ url('/aide/agenda/configurer-disponibilites') }}" class="text-green-700 font-semibold">
                            Configurer ses disponibilités
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/aide/agenda/creer-un-rendez-vous-en-ligne') }}" class="text-green-700 font-semibold">
                            Créer un rendez-vous en ligne
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
                    Tester la synchronisation agenda
                </a>
            </div>

        </div>
    </section>
</x-app-layout>

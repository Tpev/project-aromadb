<x-app-layout>
    @section('title', 'Configurer ses disponibilités et horaires types | Agenda praticien | AromaMade PRO')
    @section('meta_description')
Apprenez à configurer vos disponibilités et horaires types dans AromaMade PRO : semaines types, créneaux de travail et organisation de l’agenda pour les praticiens et thérapeutes.
    @endsection

    <section class="py-16 bg-white">
        <div class="container mx-auto px-4 max-w-4xl">

            {{-- Breadcrumb --}}
            <nav class="breadcrumb mb-6 text-sm">
                <a href="{{ url('/') }}">Accueil</a> <span>›</span>
                <a href="{{ url('/fonctionnalites/agenda') }}">Agenda & prise de rendez-vous</a> <span>›</span>
                <span class="current">Configurer ses disponibilités</span>
            </nav>

            {{-- H1 --}}
            <h1 class="text-3xl md:text-4xl font-bold mb-6">
                Configurer ses disponibilités et horaires types
            </h1>

            {{-- Intro --}}
            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                Lorsqu’on est <strong>praticien ou thérapeute</strong>, la base d’un agenda efficace repose sur
                des <strong>horaires clairs et cohérents</strong>.
                AromaMade PRO vous permet de définir des <strong>horaires types</strong> (semaines de référence)
                sur lesquels la prise de rendez-vous en ligne s’appuie automatiquement.
            </p>

            {{-- Why --}}
            <h2 class="text-2xl font-semibold mb-4">
                Pourquoi définir des horaires types ?
            </h2>
            <ul class="list-disc pl-6 text-gray-600 leading-relaxed mb-8">
                <li>Éviter les erreurs de réservation hors horaires</li>
                <li>Gagner du temps dans la gestion quotidienne de l’agenda</li>
                <li>Offrir une visibilité claire à vos clients</li>
                <li>Faciliter la gestion des congés et exceptions</li>
            </ul>

            {{-- How --}}
            <h2 class="text-2xl font-semibold mb-4">
                Comment configurer ses disponibilités dans AromaMade PRO
            </h2>

            <ol class="list-decimal pl-6 text-gray-600 leading-relaxed mb-8">
                <li>
                    <strong>Accéder à la gestion des disponibilités</strong><br>
                    Depuis votre espace professionnel, ouvrez la section dédiée aux disponibilités de l’agenda.
                </li>
                <li class="mt-3">
                    <strong>Définir vos jours travaillés</strong><br>
                    Sélectionnez les jours de la semaine pendant lesquels vous recevez des clients
                    (par exemple du lundi au vendredi).
                </li>
                <li class="mt-3">
                    <strong>Configurer les plages horaires</strong><br>
                    Pour chaque jour travaillé, indiquez vos heures de début et de fin,
                    avec la possibilité de prévoir des pauses (ex. pause déjeuner).
                </li>
                <li class="mt-3">
                    <strong>Enregistrer votre semaine type</strong><br>
                    Ces horaires deviennent votre référence.
                    L’agenda les utilise automatiquement pour proposer des créneaux disponibles à la réservation.
                </li>
            </ol>

            {{-- Practical explanation --}}
            <h2 class="text-2xl font-semibold mb-4">
                Comment les horaires types sont utilisés dans l’agenda
            </h2>
            <p class="text-gray-600 leading-relaxed mb-8">
                Les horaires types définissent la structure normale de votre semaine.
                Tant qu’aucune exception n’est ajoutée (congé, fermeture, modification ponctuelle),
                l’agenda considère ces plages comme disponibles pour la prise de rendez-vous en ligne.
            </p>

            {{-- Example --}}
            <h2 class="text-2xl font-semibold mb-4">
                Exemple concret : semaine de consultation en cabinet
            </h2>
            <p class="text-gray-600 leading-relaxed mb-8">
                Vous recevez en cabinet du lundi au jeudi, de 9h à 18h, avec une pause de 12h à 14h.
                Vous configurez ces horaires une seule fois.
                L’agenda propose ensuite automatiquement des créneaux disponibles à vos clients,
                sans que vous ayez à intervenir au quotidien.
            </p>

            {{-- Internal links --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-10">
                <h3 class="font-semibold mb-3">À lire aussi</h3>
                <ul class="list-disc pl-6 text-gray-600 leading-relaxed">
                    <li>
                        <a href="{{ url('/aide/agenda/creer-un-rendez-vous-en-ligne') }}" class="text-green-700 font-semibold">
                            Créer un rendez-vous en ligne depuis le Portail Pro
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/aide/agenda/gerer-indisponibilites') }}" class="text-green-700 font-semibold">
                            Gérer ses indisponibilités (congés, absences)
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/fonctionnalites/agenda') }}" class="text-green-700 font-semibold">
                            Découvrir toutes les fonctionnalités de l’agenda AromaMade
                        </a>
                    </li>
                </ul>
            </div>

            {{-- For who --}}
            <h2 class="text-2xl font-semibold mb-4">
                Pour quels praticiens et thérapeutes ?
            </h2>
            <p class="text-gray-600 leading-relaxed mb-8">
                La configuration des disponibilités est particulièrement utile pour les
                <strong>naturopathes</strong>, <strong>sophrologues</strong>, <strong>réflexologues</strong>,
                <strong>hypnothérapeutes</strong> et plus largement tous les praticiens
                recevant des clients sur rendez-vous.
            </p>

            {{-- CTA --}}
            <div class="text-center mt-12">
                <a href="{{ route('register-pro') }}" class="btn-primary">
                    Configurer mon agenda gratuitement
                </a>
            </div>

        </div>
    </section>
</x-app-layout>

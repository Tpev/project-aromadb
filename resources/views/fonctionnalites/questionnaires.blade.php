<x-app-layout>
    @section('title', 'Questionnaires & formulaires | Pré-séance, suivi, consentements | AromaMade PRO')
    @section('meta_description')
Créez des questionnaires pros pour vos clients : anamnèse, bilans pré/post-séance, consentements signés (SES), pièces jointes. Envoi par email, lien privé sécurisé sans compte, réponses rangées dans le dossier client, exports PDF/CSV. Données hébergées en France (HDS), conforme RGPD.
    @endsection

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/feature-agenda.css') }}">
    @endpush

    <!-- HERO -->
    <section class="hero relative">
        <div class="hero-bg absolute w-full h-full bg-center bg-cover" style="background-image:url('{{ asset('images/questionnaires-hero.webp') }}');">
            <div class="overlay absolute inset-0 bg-gradient-to-b from-black via-transparent to-black opacity-60"></div>
        </div>
        <div class="container mx-auto text-center relative z-10 py-24 px-4">
            <nav class="breadcrumb" aria-label="breadcrumb">
                <a href="{{ url('/') }}">Accueil</a> <span>›</span>
                <a href="{{ url('/fonctionnalites') }}">Fonctionnalités</a> <span>›</span>
                <span class="current">Questionnaires & formulaires</span>
            </nav>
            <h1 class="text-white text-5xl md:text-6xl font-bold mb-6" data-aos="fade-up">
                Questionnaires pro : anamnèse, suivi et consentements
            </h1>
            <p class="text-white text-xl md:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Envoyez un formulaire avant, pendant ou après la séance. Les réponses arrivent dans le dossier client et se réutilisent en un clic.
            </p>
            <div class="cta-group" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register-pro') }}" class="btn-primary">Essai gratuit 14 jours</a>
                <a href="{{ url('/pro') }}" class="btn-secondary">Découvrir AromaMade PRO</a>
            </div>
        </div>
        <div class="overlay absolute inset-0 bg-black opacity-50"></div>
    </section>

    <!-- 3 BENEFITS -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Des formulaires pensés pour votre pratique</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                <div class="card" data-aos="fade-up">
                    <i class="fas fa-paperclip card-icon"></i>
                    <h3 class="card-title">Flexible & complet</h3>
                    <p>Texte, choix multiples, échelles, cases à cocher, dates, fichiers et signature — composez le bon questionnaire.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-link card-icon"></i>
                    <h3 class="card-title">Lien privé sécurisé</h3>
                    <p>Envoi par email ou partage d’un lien privé <strong>sans compte</strong>, avec token et durée de validité.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-folder card-icon"></i>
                    <h3 class="card-title">Dossier client intégré</h3>
                    <p>Chaque réponse est rattachée au <strong>dossier client</strong> et exportable (PDF/CSV) pour vos archives.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURE GRID -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Tout ce qu’il vous faut pour des bilans précis</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-10">
                <div class="feature-tile" data-aos="fade-up">
                    <i class="fas fa-list tile-icon"></i>
                    <h3>Champs variés</h3>
                    <p>Texte court/long, choix unique/multiple, curseur d’échelle, date, nombre, fichier, signature manuscrite.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-file-signature tile-icon"></i>
                    <h3>Consentements (SES eIDAS)</h3>
                    <p>Ajoutez consentements/CG, cases d’acceptation et <strong>signature électronique simple</strong> horodatée.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="150">
                    <i class="fas fa-user-clock tile-icon"></i>
                    <h3>À distance ou en séance</h3>
                    <p>Envoyez le questionnaire par email avant la séance, ou remplissez-le <strong>en direct avec le client</strong> pendant le rendez-vous.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-envelope-open-text tile-icon"></i>
                    <h3>Envois & rappels email</h3>
                    <p>Relance automatique si non complété à l’approche du rendez-vous (ex. J-1). Notification au praticien à la soumission.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="250">
                    <i class="fas fa-stream tile-icon"></i>
                    <h3>Avant / pendant / après</h3>
                    <p>Anamnèse préalable, check-in le jour J, bilan de suivi — tout est enregistré au bon endroit dans le dossier.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-copy tile-icon"></i>
                    <h3>Modèles réutilisables</h3>
                    <p>Créez vos modèles (bilan bien-être, habitudes de vie, précautions) et réutilisez-les en 1 clic.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="350">
                    <i class="fas fa-shield-alt tile-icon"></i>
                    <h3>Traçabilité & versions</h3>
                    <p>Horodatage, auteur, et journal non destructif. Sauvegardez des <em>versions</em> de vos modèles si vous les faites évoluer.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="400">
                    <i class="fas fa-file-export tile-icon"></i>
                    <h3>Exports PDF/CSV</h3>
                    <p>PDF propre pour le dossier ou envoi au client, export CSV pour vos analyses et archivage par période.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="450">
                    <i class="fas fa-lock tile-icon"></i>
                    <h3>RGPD & HDS</h3>
                    <p>Données hébergées en France sur infra <strong>HDS</strong>, chiffrement, accès restreints et suppression sur demande.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="500">
                    <i class="fas fa-calendar-check tile-icon"></i>
                    <h3>Lié aux rendez-vous</h3>
                    <p>Envoi auto à la création/validation d’un RDV, ou rappel J-1/J+1 selon vos préférences.</p>
                </div>
            </div>

            <div class="center mt-10" data-aos="fade-up" data-aos-delay="550">
                <a href="{{ url('/tarifs') }}" class="btn-secondary">Voir les tarifs</a>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Comment ça fonctionne ?</h2>
            <div class="steps mt-10">
                <div class="step" data-aos="fade-right">
                    <span class="bubble">1</span>
                    <div>
                        <h3>Créez votre modèle</h3>
                        <p>Assemblez vos questions, ajoutez consentements/CG (SES) et vos textes d’introduction et de fin.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="100">
                    <span class="bubble">2</span>
                    <div>
                        <h3>Envoyez le lien ou remplissez en direct</h3>
                        <p>Partagez le lien par email ou remplissez le questionnaire en séance avec votre client depuis son dossier.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="200">
                    <span class="bubble">3</span>
                    <div>
                        <h3>Collectez les réponses</h3>
                        <p>Les réponses sont stockées automatiquement dans le dossier client, prêtes à être consultées.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="300">
                    <span class="bubble">4</span>
                    <div>
                        <h3>Exportez & archivez</h3>
                        <p>Générez un PDF propre ou exportez en CSV pour vos analyses et la portabilité RGPD.</p>
                    </div>
                </div>
            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Créer mon premier questionnaire</a>
            </div>
        </div>
    </section>

    <!-- TRUST / SECURITY STRIP -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <h2 class="section-title" data-aos="fade-up">Sécurité & conformité</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Hébergement en France, conformité <strong>RGPD</strong>, infrastructure <strong>HDS</strong>. Contrôles d’accès nominatif, horodatage des consentements et traçabilité complète.
            </p>
            <div class="logo-row mt-8" data-aos="fade-up" data-aos-delay="150">
                <img src="{{ asset('images/security/france.svg') }}" alt="Hébergement en France" />
                <img src="{{ asset('images/security/hds.svg') }}" alt="HDS" />
                <img src="{{ asset('images/security/rgpd.svg') }}" alt="RGPD" />
            </div>
        </div>
    </section>



    <!-- CTA -->
    <section class="py-16 bg-green-100">
        <div class="container mx-auto text-center px-4">
            <h2 class="section-title" data-aos="fade-up">Recueillez les bonnes infos, au bon moment</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Préparez mieux vos séances, structurez votre suivi et gagnez du temps à chaque rendez-vous.
            </p>
            <div class="mt-8" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register-pro') }}" class="btn-primary">Essayer gratuitement</a>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                AOS.init({ once: true });
                document.querySelectorAll('.accordion-item').forEach(item => {
                    const header = item.querySelector('.accordion-header');
                    header.addEventListener('click', () => item.classList.toggle('open'));
                });
            });
        </script>
    @endpush
</x-app-layout>

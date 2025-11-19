<div id="step3" class="border border-[#e4e8d5] rounded-xl p-5 bg-[#fff9f6]">

    <div class="flex items-start justify-between gap-3 mb-4">
        <div class="flex items-start gap-3">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#647a0b] text-white text-sm font-semibold">
                3
            </span>
        </div>

        @if(!$skipStep3)
            <form action="{{ route('onboarding.skipStep3') }}" method="POST" class="ml-auto">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold rounded-lg
                               text-gray-400 border border-gray-200 hover:bg-gray-50 transition-colors">
                    Ignorer pour le moment
                </button>
            </form>
        @endif
    </div>

    <!-- Feature overview -->
    <div class="text-xs text-gray-600 leading-relaxed mb-4">
        Voici un aperçu de tout ce que vous pouvez faire dans AromaMade PRO. 
        Prenez quelques minutes pour explorer — cela vous montrera le véritable potentiel de la plateforme.

        <div class="mt-3 space-y-3">

            <!-- Dossiers & Espace Client -->
            <div>
                <h5 class="font-semibold text-gray-800 text-sm">👤 Dossiers & Espace Client</h5>
                <ul class="list-disc ml-5 mt-1 space-y-0.5">
                    <li>Notes détaillées et notes de séance</li>
                    <li>Espace client sécurisé (documents, factures, conseils…)</li>
                    <li>Signature électronique de documents</li>
                    <li>Stockage de fichiers (PDF, photos, documents divers)</li>
                    <li>Envoi de questionnaires personnalisés</li>
                    <li>Envoi de conseils et protocoles après chaque séance</li>
                    <li>Suivi des mesures, objectifs et évolutions</li>
                </ul>
            </div>

            <!-- Comptabilité -->
            <div>
                <h5 class="font-semibold text-gray-800 text-sm">📊 Comptabilité & Statistiques</h5>
                <ul class="list-disc ml-5 mt-1 space-y-0.5">
                    <li>Création de devis et factures professionnelles</li>
                    <li>Livre de recettes automatique (micro-entreprise)</li>
                    <li>Statistiques mensuelles et suivi du chiffre d’affaires</li>
                </ul>
            </div>

            <!-- Gestion du cabinet -->
            <div>
                <h5 class="font-semibold text-gray-800 text-sm">📅 Gestion & Organisation</h5>
                <ul class="list-disc ml-5 mt-1 space-y-0.5">
                    <li>Ajouter des disponibilités ponctuelles</li>
                    <li>Déclarer des indisponibilités temporaires</li>
                    <li>Créer des événements et gérer les inscriptions</li>
                    <li>Activer les paiements en ligne pour encaisser vos clients</li>
                    <li>Gérer votre inventaire (huiles, produits, consommables…)</li>
                </ul>
            </div>

        </div>
    </div>

</div>

<x-guest-layout>
    <!-- Registration Form Container -->
    <div class="max-w-md mx-auto my-10 p-6 bg-white rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-center text-[#647a0b] mb-4">
            Créez votre compte Practicien
        </h1>
        <p class="text-center text-gray-600 mb-6">
            Rejoignez AromaMade PRO gratuitement et boostez votre visibilité auprès de nouveaux clients.
        </p>
        <form method="POST" action="{{ route('register-pro') }}">
            @csrf

            <!-- Name -->
            <div class="mb-4">
                <x-input-label for="name" :value="__('Nom')" />
                <x-text-input id="name" class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2" 
                              type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-600" />
            </div>

            <!-- Email Address -->
            <div class="mb-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2" 
                              type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600" />
            </div>

            <!-- Password -->
            <div class="mb-4">
                <x-input-label for="password" :value="__('Mot de passe')" />
                <x-text-input id="password" class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2"
                              type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600" />
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2"
                              type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-600" />
            </div>

            <!-- Accept Terms & Privacy Policy -->
            <div class="mb-4">
                <label for="terms" class="flex items-center text-sm text-gray-600">
                    <input id="terms" type="checkbox" class="form-checkbox h-4 w-4 text-[#647a0b]" name="terms" required>
                    <span class="ml-2">
                        {{ __('J\'accepte les') }} 
                        <a href="{{ route('cgu') }}" target="_blank" class="underline text-blue-600 hover:text-blue-800">
                            {{ __('Conditions Générales d’Utilisation') }}
                        </a>
                        {{ __('et la') }}
                        <a href="{{ route('privacypolicy') }}" target="_blank" class="underline text-blue-600 hover:text-blue-800">
                            {{ __('Politique de Confidentialité') }}
                        </a>.
                    </span>
                </label>
                <x-input-error :messages="$errors->get('terms')" class="mt-2 text-red-600" />
            </div>

            <!-- Hidden field to mark user as therapist -->
            <input type="hidden" name="is_therapist" value="true">

            <div class="flex items-center justify-between mt-6">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                    {{ __('Déjà inscrit ?') }}
                </a>
                <x-primary-button class="ml-4 bg-gradient-to-r from-[#647a0b] to-[#854f38] hover:from-[#8ea633] hover:to-[#8ea633]">
                    {{ __('S\'inscrire') }}
                </x-primary-button>
            </div>
        </form>
    </div>

    <!-- Benefits Section -->
    <div class="max-w-md mx-auto my-10 p-6 bg-gray-50 rounded-lg shadow-sm">
        <h2 class="text-xl font-semibold text-center text-[#647a0b] mb-4">
            Pourquoi rejoindre AromaMade PRO ?
        </h2>
        <ul class="list-disc pl-5 text-gray-700 space-y-2">
            <li><strong>Visibilité accrue :</strong> Soyez trouvé par de nouveaux clients recherchant des thérapeutes qualifiés.</li>
            <li><strong>Gestion simplifiée :</strong> Organisez vos rendez-vous et dossiers clients en toute simplicité.</li>
            <li><strong>Référencement gratuit :</strong> Inscrivez-vous sans frais et bénéficiez d’un positionnement optimisé dans notre annuaire.</li>
        </ul>
    </div>

    <!-- 14-Day Trial Section -->
    <div class="max-w-md mx-auto my-10 p-6 bg-blue-50 rounded-lg shadow-sm border border-blue-200">
        <h2 class="text-xl font-semibold text-center text-[#647a0b] mb-2">
            Bonus : Essai gratuit de 14 jours
        </h2>
        <p class="text-center text-gray-700 mb-4">
            Profitez de 14 jours d'accès complet à toutes les fonctionnalités avancées de AromaMade PRO – de la gestion des rendez-vous au suivi des dossiers clients – sans aucun frais.
        </p>
        <div class="text-center">
            <a href="{{ url('/pro') }}" class="underline text-blue-600 hover:text-blue-800 text-sm">
                En savoir plus sur nos fonctionnalités et nos offres
            </a>
        </div>
    </div>

    <!-- Testimonials Section -->
    <div class="max-w-md mx-auto my-10 p-6 bg-gray-50 rounded-lg shadow-sm">
        <h2 class="text-xl font-semibold text-center text-[#647a0b] mb-4">
            Ils nous font confiance
        </h2>
        <div class="space-y-4">
            <!-- Testimonial 1 -->
            <blockquote class="border-l-4 border-green-500 pl-4 italic text-gray-700">
                "En tant que naturopathe praticienne, je trouve la plate-forme Aromamade hyper pratique ! Très intuitive ! Et tout ce dont on a besoin, Aromamade y a pensé ou y pensera pour nous combler.! Je suis ravie d'avoir la chance de gérer plus facilement mes rendez-vous et mon répertoire clients ainsi que d'autres fonctionnalités pratiques. Un grand merci à l'équipe qui gère 100% !!"
                <footer class="mt-2 text-sm text-gray-500">— Ludivine, Naturopathe</footer>
            </blockquote>
            <!-- Testimonial 2 -->
            <blockquote class="border-l-4 border-green-500 pl-4 italic text-gray-700">
                "Étant thérapeute certifiée, je conseille fortement cette plate-forme pour les thérapeutes qui sont à la recherche d'une plate-forme entièrement dédiée à eux avec toutes les fonctionnalités à un prix très attractif. Prise de rendez-vous présentiel et visio, paiement en ligne, mise en avant des événements, facturation, visio intégrée... une équipe à l'écoute et super professionnelle"
                <footer class="mt-2 text-sm text-gray-500">— Marie-Louise, Thérapeute</footer>
            </blockquote>
        </div>
    </div>
</x-guest-layout>

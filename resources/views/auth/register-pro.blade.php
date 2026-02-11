<x-guest-layout>
    @section('meta_description')
Créez votre compte Practicien sur AromaMade PRO, la plateforme gratuite dédiée aux thérapeutes. Boostez votre visibilité, gérez facilement vos rendez-vous et dossiers clients, et profitez d’un essai gratuit de 14 jours pour découvrir toutes nos fonctionnalités.
    @endsection
    @section('title', 'AromaMade PRO : Annuaire Thérapeute et Logiciel de Gestion en Ligne')

    <!-- Registration Form Container -->
    <div class="max-w-md mx-auto my-10 p-6 bg-white rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-center text-[#647a0b] mb-4">
            Créez votre compte Practicien
        </h1>
        <p class="text-center text-gray-600 mb-6">
            Rejoignez AromaMade PRO gratuitement et boostez votre visibilité auprès de nouveaux clients.
        </p>

        <form method="POST" action="{{ route('register-pro') }}" id="regForm">
            @csrf

            {{-- Onboarding mode (new) --}}
            <input type="hidden" name="onboarding_mode" id="onboarding_mode" value="{{ old('onboarding_mode') }}">

            <!-- Step 1: New Information -->
            <fieldset class="step" id="step1">
                <!-- Company Name -->
                <div class="mb-4">
                    <x-input-label for="company_name" :value="__('Nom de l\'entreprise')" />
                    <x-text-input
                        id="company_name"
                        name="company_name"
                        type="text"
                        placeholder="Ex: AromaMade Inc."
                        :value="old('company_name')"
                        class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2"
                    />
                    <x-input-error :messages="$errors->get('company_name')" class="mt-2 text-red-600" />
                </div>

                <!-- Services -->
                <div class="mb-4">
                    <x-input-label for="services" :value="__('Services proposés')" />
                    <x-text-input
                        id="services"
                        name="services"
                        type="text"
                        placeholder="Ex: Naturopathe"
                        :value="old('services')"
                        class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2"
                    />
                    <x-input-error :messages="$errors->get('services')" class="mt-2 text-red-600" />
                </div>

                <!-- About -->
                <div class="mb-4">
                    <x-input-label for="about" :value="__('À propos')" />
                    <textarea
                        id="about"
                        name="about"
                        placeholder="Ex: Nous offrons des services de naturopathie pour améliorer votre bien-être."
                        class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2"
                    >{{ old('about') }}</textarea>
                    <x-input-error :messages="$errors->get('about')" class="mt-2 text-red-600" />
                </div>

                <!-- Navigation: Next -->
                <div class="flex items-center justify-end mt-6">
                    <button type="button" id="next1" class="bg-gradient-to-r from-[#647a0b] to-[#854f38] hover:from-[#8ea633] hover:to-[#8ea633] text-white py-2 px-4 rounded">
                        Suivant
                    </button>
                </div>
            </fieldset>

            <!-- Step 2: Registration Information -->
            <fieldset class="step" id="step2" style="display: none;">
                <!-- Name -->
                <div class="mb-4">
                    <x-input-label for="name" :value="__('Nom')" />
                    <x-text-input
                        id="name"
                        name="name"
                        type="text"
                        placeholder="Ex: Jean Dupont"
                        :value="old('name')"
                        class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-600" />
                </div>

                <!-- Email Address -->
                <div class="mb-4">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        placeholder="Ex: jean.dupont@example.com"
                        :value="old('email')"
                        class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2"
                        required
                        autocomplete="username"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600" />
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <x-input-label for="password" :value="__('Mot de passe')" />
                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2"
                        required
                        autocomplete="new-password"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600" />
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
                    <x-text-input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2"
                        required
                        autocomplete="new-password"
                    />
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

                <!-- Hidden fields -->
                <input type="hidden" name="is_therapist" value="true">
                <input type="hidden" name="ref" value="{{ request('ref') }}">
                <input type="hidden" name="invite" value="{{ request('invite') }}">

                <!-- Navigation: Previous & Next -->
                <div class="flex items-center justify-between mt-6">
                    <button type="button" id="prev1" class="bg-gray-300 text-gray-700 py-2 px-4 rounded">
                        Précédent
                    </button>

                    {{-- instead of submitting, go to step 3 --}}
                    <button type="button" id="next2" class="bg-gradient-to-r from-[#647a0b] to-[#854f38] hover:from-[#8ea633] hover:to-[#8ea633] text-white py-2 px-4 rounded">
                        Continuer
                    </button>
                </div>
            </fieldset>

            <!-- Step 3: Onboarding Choice (NEW) -->
            <fieldset class="step" id="step3" style="display: none;">
                <h2 class="text-xl font-bold text-center text-slate-900 mb-2">
                    Votre compte est presque prêt ✅
                </h2>
                <p class="text-center text-gray-600 mb-6">
                    Comment souhaitez-vous démarrer sur AromaMade PRO ?
                </p>

                <div class="space-y-3">
                    <!-- Self -->
                    <button type="button" class="onboarding-choice w-full text-left border rounded-lg p-4 hover:bg-gray-50 transition"
                            data-value="self">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 h-9 w-9 flex items-center justify-center rounded-full bg-[#f3f9dd] text-[#647a0b] font-bold">
                                🧭
                            </div>
                            <div>
                                <div class="font-semibold text-slate-900">Je découvre par moi-même</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    Accès direct au tableau de bord + checklist de démarrage.
                                </div>
                            </div>
                        </div>
                    </button>

                    <!-- Assisted -->
                    <button type="button" class="onboarding-choice w-full text-left border rounded-lg p-4 hover:bg-gray-50 transition"
                            data-value="assisted">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 h-9 w-9 flex items-center justify-center rounded-full bg-[#fef3c7] text-[#854f38] font-bold">
                                🤝
                            </div>
                            <div>
                                <div class="font-semibold text-slate-900">Je souhaite être accompagné(e)</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    Nous organisons un appel ou une visio avec vous pour vous guider pas à pas et mettre en place une configuration rapide, claire et parfaitement adaptée à votre pratique.
                                </div>
                            </div>
                        </div>
                    </button>
                </div>

                <div id="onboardingHint" class="mt-4 text-sm text-gray-600 hidden">
                    Après la création de votre compte, vous pourrez planifier un appel ou une visio avec notre équipe afin de finaliser votre configuration efficacement.
                </div>

                <div class="flex items-center justify-between mt-6">
                    <button type="button" id="prev2" class="bg-gray-300 text-gray-700 py-2 px-4 rounded">
                        Précédent
                    </button>

                    <x-primary-button id="finalSubmit"
                        class="bg-gradient-to-r from-[#647a0b] to-[#854f38] hover:from-[#8ea633] hover:to-[#8ea633]"
                        disabled>
                        Créer mon compte
                    </x-primary-button>
                </div>

                <p class="text-center text-xs text-gray-500 mt-3">
                    Vous pourrez changer d’avis plus tard depuis votre tableau de bord.
                </p>
            </fieldset>
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
            <blockquote class="border-l-4 border-green-500 pl-4 italic text-gray-700">
                "En tant que naturopathe praticienne, je trouve la plate-forme Aromamade hyper pratique ! Très intuitive ! Et tout ce dont on a besoin, Aromamade y a pensé ou y pensera pour nous combler.! Je suis ravie d'avoir la chance de gérer plus facilement mes rendez-vous et mon répertoire clients ainsi que d'autres fonctionnalités pratiques. Un grand merci à l'équipe qui gère 100% !!"
                <footer class="mt-2 text-sm text-gray-500">— Ludivine, Naturopathe</footer>
            </blockquote>

            <blockquote class="border-l-4 border-green-500 pl-4 italic text-gray-700">
                "Étant thérapeute certifiée, je conseille fortement cette plate-forme pour les thérapeutes qui sont à la recherche d'une plate-forme entièrement dédiée à eux avec toutes les fonctionnalités à un prix très attractif. Prise de rendez-vous présentiel et visio, paiement en ligne, mise en avant des événements, facturation, visio intégrée... une équipe à l'écoute et super professionnelle"
                <footer class="mt-2 text-sm text-gray-500">— Marie-Louise, Thérapeute</footer>
            </blockquote>
        </div>
    </div>

    <!-- jQuery to handle multi-step navigation -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function(){

        function showStep(stepId) {
            $(".step").hide();
            $("#" + stepId).show();
        }

        // Step 1 -> Step 2
        $("#next1").click(function(){
            showStep("step2");
        });

        // Step 2 -> Step 1
        $("#prev1").click(function(){
            showStep("step1");
        });

        // Step 2 -> Step 3
        $("#next2").click(function(){
            showStep("step3");
        });

        // Step 3 -> Step 2
        $("#prev2").click(function(){
            showStep("step2");
        });

        // Choose onboarding mode
        $(".onboarding-choice").click(function(){
            const v = $(this).data("value");
            $("#onboarding_mode").val(v);

            // UI feedback (selected state)
            $(".onboarding-choice").removeClass("border-[#647a0b] ring-2 ring-[#647a0b] bg-[#f3f9dd]/30");
            $(this).addClass("border-[#647a0b] ring-2 ring-[#647a0b] bg-[#f3f9dd]/30");

            // Enable submit
            $("#finalSubmit").prop("disabled", false);

            // hint if assisted
            if (v === "assisted") {
                $("#onboardingHint").removeClass("hidden");
            } else {
                $("#onboardingHint").addClass("hidden");
            }
        });

        // Restore selection if validation error
        @if(old('onboarding_mode'))
            showStep("step3");
            $('.onboarding-choice[data-value="{{ old('onboarding_mode') }}"]').addClass("border-[#647a0b] ring-2 ring-[#647a0b] bg-[#f3f9dd]/30");
            $("#finalSubmit").prop("disabled", false);
            @if(old('onboarding_mode') === 'assisted')
                $("#onboardingHint").removeClass("hidden");
            @endif
        @endif

    });
    </script>
</x-guest-layout>


<x-guest-layout>
    @section('meta_description')
Créez votre compte Practicien sur AromaMade PRO, la plateforme gratuite dédiée aux thérapeutes. Boostez votre visibilité, gérez facilement vos rendez-vous et dossiers clients, et profitez d’un essai gratuit de 14 jours pour découvrir toutes nos fonctionnalités.
    @endsection
    @section('title', 'AromaMade PRO : Annuaire Thérapeute et Logiciel de Gestion en Ligne')

    
    @php
    $stepWithErrors = 'step1';
    $step1Fields = ['company_name','services','about'];
    $step2Fields = ['name','email','password','password_confirmation'];
    $step3Fields = ['onboarding_mode'];

    if ($errors->hasAny($step2Fields) || $errors->has('registration_error')) {
        $stepWithErrors = 'step2';
    }
    if ($errors->hasAny($step3Fields) || old('onboarding_mode')) {
        $stepWithErrors = 'step3';
    }
@endphp

    <!-- Registration Form Container -->
    <div class="max-w-md mx-auto my-10 p-6 bg-white rounded-xl shadow-lg border border-gray-100">
        <h1 class="text-3xl font-extrabold text-center text-[#647a0b] mb-3">
            Créez votre compte Praticien
        </h1>
        <p class="text-center text-gray-600 mb-6">
            Rejoignez AromaMade PRO et boostez votre visibilité auprès de nouveaux clients.
        </p>

        {{-- Global error summary (server-side validation) --}}
        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert" aria-live="polite">
                <div class="font-semibold mb-1">Oups — il manque quelque chose :</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <div class="mt-2 text-xs text-red-700">
                    Astuce : nous vous avons replacé à l’étape concernée.
                </div>
            </div>
        @endif

        {{-- Client-side helper message (filled by JS) --}}
        <div id="clientError" class="hidden mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="alert" aria-live="polite"></div>

        {{-- Progress indicator --}}
        <div class="mb-6">
            <div class="flex items-center justify-between text-xs font-semibold">
                <div class="flex-1 text-left">
                    <span id="pill1" class="inline-flex items-center gap-2 rounded-full px-3 py-1 border">
                        <span class="h-5 w-5 inline-flex items-center justify-center rounded-full bg-[#f3f9dd] text-[#647a0b]">1</span>
                        Profil
                    </span>
                </div>
                <div class="flex-1 text-center">
                    <span id="pill2" class="inline-flex items-center gap-2 rounded-full px-3 py-1 border">
                        <span class="h-5 w-5 inline-flex items-center justify-center rounded-full bg-gray-100 text-gray-700">2</span>
                        Identifiants
                    </span>
                </div>
                <div class="flex-1 text-right">
                    <span id="pill3" class="inline-flex items-center gap-2 rounded-full px-3 py-1 border">
                        <span class="h-5 w-5 inline-flex items-center justify-center rounded-full bg-gray-100 text-gray-700">3</span>
                        Démarrage
                    </span>
                </div>
            </div>
            <div class="mt-3 h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                <div id="progressBar" class="h-full w-1/3 bg-gradient-to-r from-[#647a0b] to-[#854f38] transition-all"></div>
            </div>
        </div>

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
                        autocomplete="organization"
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
                        autocomplete="organization-title"
                        placeholder="Ex: Naturopathe"
                        :value="old('services')"
                        class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2"
                    />
                    <x-input-error :messages="$errors->get('services')" class="mt-2 text-red-600" />
            
                </div>

                <!-- About -->
                <div class="mb-4">
                    <x-input-label for="about" :value="__('Description')" />
                    <textarea
                        id="about"
                        name="about"
                        autocomplete="off"
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
                        autocomplete="name"
                        required
                        placeholder="Ex: Jean Dupont"
                        :value="old('name')"
                        class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2"
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
                        autocomplete="email"
                        required
                        placeholder="Ex: jean.dupont@gmail.com"
                        :value="old('email')"
                        class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600" />
                </div>

<!-- Password -->
<div class="mb-4">
    <x-input-label for="password" :value="__('Mot de passe')" />

    <div class="relative">
        <x-text-input
            id="password"
            name="password"
            type="password"
            autocomplete="new-password"
            required
            placeholder="Choisissez un mot de passe"
            class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2 pr-10"
        />

        <!-- Strength bar -->
        <div class="mt-2 h-2 w-full rounded-full bg-gray-100 overflow-hidden">
            <div id="passwordStrengthBar"
                 class="h-full w-0 bg-red-500 transition-all duration-300"></div>
        </div>

        <!-- Strength text -->
        <div id="passwordStrengthText"
             class="mt-2 text-sm font-medium text-gray-600">
            Force du mot de passe : —
        </div>

        <!-- Requirements -->
        <ul class="mt-2 text-xs space-y-1 text-gray-500" id="passwordRequirements">
            <li data-rule="length">• Au moins 8 caractères</li>
            <li data-rule="uppercase">• Une lettre majuscule</li>
            <li data-rule="number">• Un chiffre</li>
        </ul>
    </div>

    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600" />
</div>
<!-- Confirm Password -->
                <div class="mb-4">
                    <x-input-label for="password_confirmation" :value="__('Confirmez le mot de passe')" />
                    <x-text-input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        placeholder="Répétez votre mot de passe"
                        class="block mt-1 w-full border border-gray-300 rounded-md px-3 py-2"
                    />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-600" />
                </div>

                <!-- Navigation -->
                <div class="flex items-center justify-between mt-6">
                    <button type="button" id="prev1" class="bg-gray-300 text-gray-700 py-2 px-4 rounded">
                        Précédent
                    </button>
                    <button type="button" id="next2" class="bg-gradient-to-r from-[#647a0b] to-[#854f38] hover:from-[#8ea633] hover:to-[#8ea633] text-white py-2 px-4 rounded">
                        Suivant
                    </button>
                </div>
            </fieldset>

            <!-- Step 3: Onboarding choice -->
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
            <li><strong>Outils professionnels :</strong> Facturation, agenda, et même visio-consultations si besoin.</li>
            <li><strong>Essai gratuit :</strong> Testez l’accès complet pendant <strong>14 jours</strong>.</li>
        </ul>
    </div>

    <!-- Testimonials Section -->
    <div class="max-w-md mx-auto my-10 p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-center text-[#647a0b] mb-4">
            Ils utilisent AromaMade PRO
        </h2>

        <div class="space-y-4">
            <blockquote class="border-l-4 border-[#647a0b] pl-4 italic text-gray-700">
                “J’ai gagné un temps fou sur la gestion des rendez-vous. Mes clients adorent !”
                <footer class="mt-2 text-sm font-semibold text-gray-600">— Sophie, Naturopathe</footer>
            </blockquote>

            <blockquote class="border-l-4 border-[#647a0b] pl-4 italic text-gray-700">
                “La facturation est simple et tout est centralisé. Je recommande.”
                <footer class="mt-2 text-sm font-semibold text-gray-600">— Marc, Réflexologue</footer>
            </blockquote>

            <blockquote class="border-l-4 border-[#647a0b] pl-4 italic text-gray-700">
                “J’ai rempli mon planning grâce à la visibilité dans l’annuaire. Très efficace.”
                <footer class="mt-2 text-sm font-semibold text-gray-600">— Claire, Thérapeute</footer>
            </blockquote>
        </div>
    </div>

    <!-- jQuery to handle multi-step navigation -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
<script>
    $(document).ready(function(){

        const stepOrder = ["step1","step2","step3"];

        function setProgress(stepId) {
            const idx = stepOrder.indexOf(stepId);
            const pct = (idx + 1) / stepOrder.length * 100;

            $("#progressBar").css("width", pct + "%");

            // reset
            $("#pill1,#pill2,#pill3").removeClass("border-[#647a0b] text-[#647a0b] bg-[#f3f9dd]/30")
                                   .addClass("border-gray-200 text-gray-600 bg-white");

            // active
            if (stepId === "step1") $("#pill1").addClass("border-[#647a0b] text-[#647a0b] bg-[#f3f9dd]/30");
            if (stepId === "step2") $("#pill2").addClass("border-[#647a0b] text-[#647a0b] bg-[#f3f9dd]/30");
            if (stepId === "step3") $("#pill3").addClass("border-[#647a0b] text-[#647a0b] bg-[#f3f9dd]/30");
        }

        function showStep(stepId) {
            $(".step").hide();
            $("#" + stepId).show();
            setProgress(stepId);
        }

        function showClientError(message, focusSelector = null) {
            $("#clientError").removeClass("hidden").text(message);
            if (focusSelector) {
                const el = $(focusSelector).get(0);
                if (el) {
                    el.scrollIntoView({ behavior: "smooth", block: "center" });
                    el.focus({ preventScroll: true });
                }
            } else {
                $("#clientError").get(0)?.scrollIntoView({ behavior: "smooth", block: "center" });
            }
        }

        function clearClientError() {
            $("#clientError").addClass("hidden").text("");
        }

        function firstInvalidInStep(stepId) {
            const $step = $("#" + stepId);
            // Only validate required + visible inputs in current step
            const $candidates = $step.find("input[required], textarea[required]").filter(":visible");
            for (const el of $candidates.toArray()) {
                if (!el.checkValidity()) return el;
            }
            // Special case: step3 requires onboarding_mode selection
            if (stepId === "step3" && !$("#onboarding_mode").val()) {
                return $step.find(".onboarding-choice").get(0) || null;
            }
            return null;
        }

        function validateStep(stepId) {
            clearClientError();

            const invalid = firstInvalidInStep(stepId);
            if (!invalid) return true;

            // Build a friendly message depending on field
            let msg = "Merci de compléter les champs obligatoires avant de continuer.";
            if (invalid.id === "email") msg = "Indiquez une adresse email valide pour continuer.";
            if (invalid.id === "password") msg = "Choisissez un mot de passe (puis confirmez-le) pour continuer.";
            if (invalid.id === "password_confirmation") msg = "Confirmez votre mot de passe pour continuer.";

            // Trigger native bubble for accessibility (when supported)
            try { invalid.reportValidity(); } catch(e) {}

            showClientError(msg, "#" + (invalid.id || ""));
            return false;
        }

        // Navigation
        $("#next1").click(function(){
            if (!validateStep("step1")) return;
            showStep("step2");
        });

        $("#prev1").click(function(){ clearClientError(); showStep("step1"); });

        $("#next2").click(function(){
            if (!validateStep("step2")) return;
            showStep("step3");
        });

        $("#prev2").click(function(){ clearClientError(); showStep("step2"); });

        // Choose onboarding mode
        $(".onboarding-choice").click(function(){
            const v = $(this).data("value");
            $("#onboarding_mode").val(v);
            clearClientError();

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

        // Prevent submit if onboarding_mode not selected (extra safety)
        $("#regForm").on("submit", function(e){
            if ($("#onboarding_mode").val()) return true;
            e.preventDefault();
            showStep("step3");
            showClientError("Choisissez une option de démarrage (étape 3) pour créer votre compte.");
            return false;
        });

        // Restore step on validation errors
        showStep("{{ $stepWithErrors }}");

        // Restore onboarding selection if validation error
        @if(old('onboarding_mode'))
            $('.onboarding-choice[data-value="{{ old('onboarding_mode') }}"]').addClass("border-[#647a0b] ring-2 ring-[#647a0b] bg-[#f3f9dd]/30");
            $("#finalSubmit").prop("disabled", false);
            @if(old('onboarding_mode') === 'assisted')
                $("#onboardingHint").removeClass("hidden");
            @endif
        @endif

    });
	// PASSWORD STRENGTH CHECK
$("#password").on("input", function () {
    const val = $(this).val();

    let score = 0;

    const rules = {
        length: val.length >= 8,
        uppercase: /[A-Z]/.test(val),
        number: /[0-9]/.test(val),
    };

    // Update requirement colors
    Object.keys(rules).forEach(rule => {
        const el = $(`#passwordRequirements [data-rule='${rule}']`);
        if (rules[rule]) {
            el.removeClass("text-gray-500").addClass("text-green-600");
            score++;
        } else {
            el.removeClass("text-green-600").addClass("text-gray-500");
        }
    });

    const bar = $("#passwordStrengthBar");
    const text = $("#passwordStrengthText");

    if (score === 0) {
        bar.css("width", "0%").removeClass().addClass("h-full bg-red-500");
        text.text("Force du mot de passe : —").removeClass().addClass("mt-2 text-sm font-medium text-gray-600");
    }
    else if (score === 1) {
        bar.css("width", "33%").removeClass().addClass("h-full bg-red-500");
        text.text("Force du mot de passe : Faible").removeClass().addClass("mt-2 text-sm font-medium text-red-600");
    }
    else if (score === 2) {
        bar.css("width", "66%").removeClass().addClass("h-full bg-yellow-500");
        text.text("Force du mot de passe : Moyen").removeClass().addClass("mt-2 text-sm font-medium text-yellow-600");
    }
    else if (score === 3) {
        bar.css("width", "100%").removeClass().addClass("h-full bg-green-600");
        text.text("Force du mot de passe : Fort").removeClass().addClass("mt-2 text-sm font-medium text-green-600");
    }
});

</script>
</x-guest-layout>

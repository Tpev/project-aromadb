{{-- resources/views/onboarding.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Bienvenue sur votre espace professionnel') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Complétez votre profil pour commencer') }}</h1>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Formulaire multi-étapes -->
            <form id="onboarding-form" action="{{ route('onboarding.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Barre de progression améliorée -->
                <div class="progressbar-container">
                    <ul class="progressbar">
                        <li class="progress-step active" data-title="{{ __('Informations de base') }}"></li>
                        <li class="progress-step" data-title="{{ __('Profil de l\'entreprise') }}"></li>
                        <li class="progress-step" data-title="{{ __('Paramètres') }}"></li>
                        <li class="progress-step" data-title="{{ __('Mentions légales') }}"></li>
                    </ul>
                </div>

                <!-- Étapes du formulaire -->
                <!-- Étape 1 : Informations de base -->
                <div class="form-step form-step-active">
                    <h2 class="font-semibold text-lg mb-4">{{ __('Étape 1 : Informations de base de l\'entreprise') }}</h2>

                    <!-- Nom de l'Entreprise -->
                    <div class="details-box">
                        <label class="details-label" for="company_name">{{ __('Nom de l\'Entreprise') }}</label>
                        <input type="text" id="company_name" name="company_name" class="form-control" value="{{ old('company_name', auth()->user()->company_name) }}" required>
                        <small class="text-gray-500">{{ __('Saisissez le nom officiel de votre entreprise.') }}</small>
                        @error('company_name')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Adresse de l'Entreprise -->
                    <div class="details-box">
                        <label class="details-label" for="company_address">{{ __('Adresse de l\'Entreprise') }}</label>
                        <textarea id="company_address" name="company_address" class="form-control" required>{{ old('company_address', auth()->user()->company_address) }}</textarea>
                        <small class="text-gray-500">{{ __('Indiquez l\'adresse où se situe votre entreprise.') }}</small>
                        @error('company_address')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email de l'Entreprise -->
                    <div class="details-box">
                        <label class="details-label" for="company_email">{{ __('Email de l\'Entreprise') }}</label>
                        <input type="email" id="company_email" name="company_email" class="form-control" value="{{ old('company_email', auth()->user()->company_email) }}" required>
                        <small class="text-gray-500">{{ __('Cet email sera utilisé pour les communications officielles.') }}</small>
                        @error('company_email')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Téléphone de l'Entreprise -->
                    <div class="details-box">
                        <label class="details-label" for="company_phone">{{ __('Téléphone de l\'Entreprise') }}</label>
                        <input type="text" id="company_phone" name="company_phone" class="form-control" value="{{ old('company_phone', auth()->user()->company_phone) }}" required>
                        <small class="text-gray-500">{{ __('Numéro de téléphone pour vous joindre.') }}</small>
                        @error('company_phone')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bouton Suivant -->
                    <div class="form-navigation">
                        <button type="button" class="btn-primary btn-next">{{ __('Suivant') }}</button>
                    </div>
                </div>

                <!-- Étape 2 : Profil de l'entreprise -->
                <div class="form-step">
                    <h2 class="font-semibold text-lg mb-4">{{ __('Étape 2 : Profil de l\'entreprise') }}</h2>

                    <!-- À Propos -->
                    <div class="details-box">
                        <label class="details-label" for="about">{{ __('À Propos') }}</label>
                        <textarea id="about" name="about" class="form-control">{{ old('about', auth()->user()->about) }}</textarea>
                        <small class="text-gray-500">{{ __('Présentez brièvement votre entreprise.') }}</small>
                        @error('about')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Services -->
                    <div class="details-box">
                        <label class="details-label" for="service-input">{{ __('Services') }}</label>
                        <div class="flex flex-wrap items-center border border-gray-300 rounded p-2">
                            <div id="services-list" class="flex flex-wrap gap-2">
                                @php
                                    $services = json_decode(old('services', auth()->user()->services), true) ?? [];
                                @endphp

                                @foreach($services as $service)
                                    <span class="service-tag bg-green-500 text-white px-3 py-1 rounded-full flex items-center">
                                        {{ $service }}
                                        <button type="button" class="ml-2 remove-service-btn" aria-label="Remove {{ $service }}">&times;</button>
                                    </span>
                                @endforeach
                            </div>
                            <input type="text" id="service-input" class="flex-grow p-1 border-none focus:outline-none" placeholder="{{ __('Ajouter un service...') }}">
                            <button type="button" id="add-service-btn" class="ml-2 bg-green-500 text-white px-3 py-1 rounded">{{ __('Ajouter') }}</button>
                        </div>
                        <small class="text-gray-500">{{ __('Saisissez un service et appuyez sur Entrée ou cliquez sur "Ajouter" pour l\'ajouter à la liste.') }}</small>
                        @error('services')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                        <input type="hidden" name="services" id="services-input" value='@json($services)'>
                    </div>

                    <!-- Description du Profil -->
                    <div class="details-box">
                        <label class="details-label" for="profile_description">{{ __('Description du Profil') }}</label>
                        <textarea id="profile_description" name="profile_description" class="form-control">{{ old('profile_description', auth()->user()->profile_description) }}</textarea>
                        <small class="text-gray-500">{{ __('Décrivez plus en détail vos activités et compétences.') }}</small>
                        @error('profile_description')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Boutons Précédent et Suivant -->
                    <div class="form-navigation">
                        <button type="button" class="btn-secondary btn-prev">{{ __('Précédent') }}</button>
                        <button type="button" class="btn-primary btn-next">{{ __('Suivant') }}</button>
                    </div>
                </div>

                <!-- Étape 3 : Paramètres et Préférences -->
                <div class="form-step">
                    <h2 class="font-semibold text-lg mb-4">{{ __('Étape 3 : Paramètres et Préférences') }}</h2>

                    <!-- Photo de Profil -->
                    <div class="details-box">
                        <label class="details-label" for="profile_picture">{{ __('Photo de Profil') }}</label>
                        @php
                            $avatarPlaceholder = "data:image/svg+xml;utf8," . rawurlencode(
                                '<svg xmlns="http://www.w3.org/2000/svg" width="320" height="320" viewBox="0 0 320 320">'
                                . '<rect width="320" height="320" fill="#E5E7EB"/>'
                                . '<circle cx="160" cy="130" r="56" fill="#9CA3AF"/>'
                                . '<path d="M54 285c20-52 64-79 106-79s86 27 106 79" fill="#9CA3AF"/>'
                                . '</svg>'
                            );
                            $currentAvatarUrl = auth()->user()->profile_picture
                                ? asset('storage/' . auth()->user()->profile_picture)
                                : $avatarPlaceholder;
                        @endphp

                        <div class="flex items-start gap-4 flex-wrap">
                            <img id="profile-picture-preview"
                                 src="{{ $currentAvatarUrl }}"
                                 alt="{{ __('Photo de Profil') }}"
                                 class="w-24 h-24 rounded-full object-cover border border-[#d8e1b9] shadow-sm">
                            <div class="flex-1 min-w-[240px]">
                                <input type="file"
                                       id="profile_picture"
                                       name="profile_picture"
                                       class="form-control"
                                       accept="image/jpeg,image/jpg,image/png,image/webp,.heic,.heif">
                                <input type="hidden"
                                       id="profile_picture_crop"
                                       name="profile_picture_crop"
                                       value="{{ old('profile_picture_crop') }}">
                                <p id="profile-picture-client-error" class="text-red-500 mt-2 hidden"></p>
                            </div>
                        </div>
                        <small class="text-gray-500">{{ __('Ajoutez une photo pour personnaliser votre profil.') }}</small>
                        @error('profile_picture')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                        @error('profile_picture_crop')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="profile-picture-cropper-modal" class="avatar-cropper-modal hidden" aria-hidden="true">
                        <div class="avatar-cropper-backdrop"></div>
                        <div class="avatar-cropper-dialog" role="dialog" aria-modal="true" aria-label="{{ __('Recadrer la photo de profil') }}">
                            <div class="avatar-cropper-header">
                                <h4 class="font-semibold text-[#647a0b]">{{ __('Recadrer la photo') }}</h4>
                                <button type="button" id="profile-picture-cropper-cancel-top" class="avatar-cropper-close" aria-label="{{ __('Fermer') }}">×</button>
                            </div>

                            <div class="avatar-cropper-body">
                                <div class="avatar-cropper-image-wrap">
                                    <img id="profile-picture-cropper-image" alt="{{ __('Aperçu recadrage') }}">
                                </div>

                                <div class="mt-4">
                                    <label for="profile-picture-cropper-zoom" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('Zoom') }}
                                    </label>
                                    <input type="range"
                                           id="profile-picture-cropper-zoom"
                                           min="0"
                                           max="100"
                                           value="0"
                                           class="w-full">
                                </div>
                            </div>

                            <div class="avatar-cropper-footer">
                                <button type="button" id="profile-picture-cropper-cancel" class="btn-secondary">
                                    {{ __('Annuler') }}
                                </button>
                                <button type="button" id="profile-picture-cropper-apply" class="btn-primary">
                                    {{ __('Utiliser cette photo') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Accepter les rendez-vous en ligne -->
                    <div class="details-box">
                        <label class="flex items-center">
                            <input type="checkbox" name="accept_online_appointments" class="form-checkbox h-5 w-5 text-green-500" 
                            {{ old('accept_online_appointments', auth()->user()->accept_online_appointments) ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700">{{ __('Accepter les rendez-vous en ligne') }}</span>
                        </label>
                        <small class="text-gray-500">{{ __('Permettre aux clients de prendre rendez-vous en ligne.') }}</small>
                        @error('accept_online_appointments')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Préavis Minimum -->
                    <div class="details-box">
                        <label class="details-label" for="minimum_notice_hours">{{ __('Préavis Minimum pour Prendre un Rendez-vous (heures)') }}</label>
                        <input type="number" id="minimum_notice_hours" name="minimum_notice_hours" class="form-control" min="0" value="{{ old('minimum_notice_hours', auth()->user()->minimum_notice_hours) }}">
                        <small class="text-gray-500">{{ __('Indiquez le nombre d\'heures minimum avant qu\'un client puisse réserver.') }}</small>
                        @error('minimum_notice_hours')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Partage d'informations -->
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="share_address_publicly" class="form-checkbox h-5 w-5 text-green-500" 
                            {{ old('share_address_publicly', auth()->user()->share_address_publicly) ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700">{{ __('Partager l\'adresse publiquement') }}</span>
                        </label>
                        <small class="text-gray-500">{{ __('Votre adresse sera visible par les clients.') }}</small>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="share_phone_publicly" class="form-checkbox h-5 w-5 text-green-500" 
                            {{ old('share_phone_publicly', auth()->user()->share_phone_publicly) ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700">{{ __('Partager le téléphone publiquement') }}</span>
                        </label>
                        <small class="text-gray-500">{{ __('Votre numéro de téléphone sera visible par les clients.') }}</small>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="share_email_publicly" class="form-checkbox h-5 w-5 text-green-500" 
                            {{ old('share_email_publicly', auth()->user()->share_email_publicly) ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700">{{ __('Partager l\'email publiquement') }}</span>
                        </label>
                        <small class="text-gray-500">{{ __('Votre adresse email sera visible par les clients.') }}</small>
                    </div>

                    <!-- Boutons Précédent et Suivant -->
                    <div class="form-navigation">
                        <button type="button" class="btn-secondary btn-prev">{{ __('Précédent') }}</button>
                        <button type="button" class="btn-primary btn-next">{{ __('Suivant') }}</button>
                    </div>
                </div>

                <!-- Étape 4 : Mentions Légales -->
                <div class="form-step">
                    <h2 class="font-semibold text-lg mb-4">{{ __('Étape 4 : Mentions Légales') }}</h2>

                    <!-- Mentions Légales -->
                    <div class="details-box">
                        <label class="details-label" for="legal_mentions">{{ __('Mentions Légales') }}</label>
                        <textarea id="legal_mentions" name="legal_mentions" class="form-control">{{ old('legal_mentions', auth()->user()->legal_mentions) }}</textarea>
                        <small class="text-gray-500">{{ __('Insérez vos mentions légales conformément à la législation en vigueur.') }}</small>
                        @error('legal_mentions')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Boutons Précédent et Terminer -->
                    <div class="form-navigation">
                        <button type="button" class="btn-secondary btn-prev">{{ __('Précédent') }}</button>
                        <button type="submit" class="btn-primary">{{ __('Terminer') }}</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" referrerpolicy="no-referrer"></script>

    <!-- JavaScript pour gérer le formulaire multi-étapes et la liste des services -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gestion de la liste des services (identique à votre code initial)
            const addServiceBtn = document.getElementById('add-service-btn');
            const serviceInput = document.getElementById('service-input');
            const servicesList = document.getElementById('services-list');
            const servicesInputHidden = document.getElementById('services-input');

            let services = [];

            // Initialisation des services à partir de l'input caché
            if (servicesInputHidden.value) {
                try {
                    const parsed = JSON.parse(servicesInputHidden.value);
                    services = Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    console.error('Invalid JSON in services-input:', e);
                    services = [];
                }
            }

            // Fonction pour afficher les services
            function renderServices() {
                servicesList.innerHTML = '';
                services.forEach((service, index) => {
                    const tag = document.createElement('span');
                    tag.className = 'service-tag bg-green-500 text-white px-3 py-1 rounded-full flex items-center';
                    tag.textContent = service;

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'ml-2 remove-service-btn';
                    removeBtn.setAttribute('aria-label', `Remove ${service}`);
                    removeBtn.innerHTML = '&times;';
                    removeBtn.addEventListener('click', () => removeService(index));

                    tag.appendChild(removeBtn);
                    servicesList.appendChild(tag);
                });
                servicesInputHidden.value = JSON.stringify(services);
            }

            // Fonction pour ajouter un service
            function addService() {
                const service = serviceInput.value.trim();
                if (service && !services.includes(service)) {
                    services.push(service);
                    serviceInput.value = '';
                    renderServices();
                }
            }

            // Fonction pour supprimer un service
            function removeService(index) {
                services.splice(index, 1);
                renderServices();
            }

            // Écouteurs d'événements pour les services
            addServiceBtn.addEventListener('click', addService);
            serviceInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addService();
                }
            });

            // Affichage initial des services
            renderServices();

            // Avatar upload (preview + crop)
            const fileInput = document.getElementById('profile_picture');
            const cropField = document.getElementById('profile_picture_crop');
            const preview = document.getElementById('profile-picture-preview');
            const clientError = document.getElementById('profile-picture-client-error');
            const modal = document.getElementById('profile-picture-cropper-modal');
            const modalImage = document.getElementById('profile-picture-cropper-image');
            const zoomInput = document.getElementById('profile-picture-cropper-zoom');
            const cancelBtn = document.getElementById('profile-picture-cropper-cancel');
            const cancelTopBtn = document.getElementById('profile-picture-cropper-cancel-top');
            const applyBtn = document.getElementById('profile-picture-cropper-apply');
            let cropper = null;
            let objectUrl = null;

            if (fileInput && cropField && preview && modal && modalImage && zoomInput && applyBtn) {
                const maxBytes = 10 * 1024 * 1024;
                const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];

                function setClientError(message) {
                    if (!clientError) return;
                    if (!message) {
                        clientError.classList.add('hidden');
                        clientError.textContent = '';
                        return;
                    }

                    clientError.textContent = message;
                    clientError.classList.remove('hidden');
                }

                function cleanupCropper() {
                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }
                    if (objectUrl) {
                        URL.revokeObjectURL(objectUrl);
                        objectUrl = null;
                    }
                    zoomInput.value = '0';
                }

                function closeModal(resetInput) {
                    modal.classList.add('hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    cleanupCropper();
                    if (resetInput) {
                        fileInput.value = '';
                        cropField.value = '';
                    }
                }

                function openModal() {
                    modal.classList.remove('hidden');
                    modal.setAttribute('aria-hidden', 'false');
                }

                fileInput.addEventListener('change', function (event) {
                    const file = event.target.files && event.target.files[0];
                    setClientError('');

                    if (!file) {
                        cropField.value = '';
                        return;
                    }

                    if (file.size > maxBytes) {
                        setClientError("{{ __('Le fichier est trop lourd (max 10 Mo).') }}");
                        fileInput.value = '';
                        cropField.value = '';
                        return;
                    }

                    if (file.type && !allowedMimeTypes.includes(file.type)) {
                        setClientError("{{ __('Format non pris en charge. Utilisez JPG, PNG, WebP ou HEIC.') }}");
                        fileInput.value = '';
                        cropField.value = '';
                        return;
                    }

                    if (typeof Cropper === 'undefined') {
                        setClientError("{{ __('L’outil de recadrage n’a pas pu se charger. L’image sera importée et recadrée automatiquement.') }}");
                        cropField.value = '';
                        return;
                    }

                    cleanupCropper();

                    modalImage.onload = function () {
                        cropper = new Cropper(modalImage, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 1,
                            responsive: true,
                            background: false,
                            guides: false,
                            center: true,
                        });
                    };
                    modalImage.onerror = function () {
                        setClientError("{{ __('Votre navigateur ne peut pas recadrer ce format. L’image sera quand même importée et recadrée automatiquement.') }}");
                        closeModal(false);
                    };

                    objectUrl = URL.createObjectURL(file);
                    modalImage.src = objectUrl;
                    openModal();
                });

                zoomInput.addEventListener('input', function () {
                    if (!cropper) return;
                    const value = Number(zoomInput.value) / 100;
                    const imageData = cropper.getImageData();
                    const ratio = imageData.naturalWidth ? (imageData.width / imageData.naturalWidth) : 1;
                    cropper.zoomTo(Math.max(0.05, ratio + value));
                });

                function cancelCropper() {
                    closeModal(true);
                }

                cancelBtn?.addEventListener('click', cancelCropper);
                cancelTopBtn?.addEventListener('click', cancelCropper);
                modal.querySelector('.avatar-cropper-backdrop')?.addEventListener('click', cancelCropper);

                applyBtn.addEventListener('click', function () {
                    if (!cropper) {
                        closeModal(true);
                        return;
                    }

                    const data = cropper.getData(true);
                    const imageData = cropper.getImageData();
                    cropField.value = JSON.stringify({
                        x: data.x,
                        y: data.y,
                        width: data.width,
                        height: data.height,
                        image_width: imageData.naturalWidth,
                        image_height: imageData.naturalHeight,
                    });

                    const canvas = cropper.getCroppedCanvas({
                        width: 320,
                        height: 320,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });
                    if (canvas) {
                        preview.src = canvas.toDataURL('image/webp', 0.9);
                    }

                    setClientError('');
                    closeModal(false);
                });
            }

            // Gestion du formulaire multi-étapes
            const steps = document.querySelectorAll('.form-step');
            const progressSteps = document.querySelectorAll('.progressbar li');
            const nextBtns = document.querySelectorAll('.btn-next');
            const prevBtns = document.querySelectorAll('.btn-prev');

            let formStepNum = 0;

            nextBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    if (validateStep(formStepNum)) {
                        formStepNum++;
                        updateFormSteps();
                        updateProgressBar();
                    }
                });
            });

            prevBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    formStepNum--;
                    updateFormSteps();
                    updateProgressBar();
                });
            });

            function updateFormSteps() {
                steps.forEach((step, index) => {
                    if (index === formStepNum) {
                        step.classList.add('form-step-active');
                    } else {
                        step.classList.remove('form-step-active');
                    }
                });
            }

            function updateProgressBar() {
                progressSteps.forEach((step, index) => {
                    if (index <= formStepNum) {
                        step.classList.add('active');
                    } else {
                        step.classList.remove('active');
                    }
                });
            }

            function validateStep(stepNum) {
                const currentStep = steps[stepNum];
                const inputs = currentStep.querySelectorAll('input[required], textarea[required]');
                let valid = true;

                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        valid = false;
                        input.classList.add('border-red-500');
                    } else {
                        input.classList.remove('border-red-500');
                    }
                });

                return valid;
            }

        });
    </script>

    <!-- Styles personnalisés -->
    <style>
        /* Styles existants */
        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .details-title {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .details-box {
            margin-bottom: 15px;
        }

        .details-label {
            font-weight: bold;
            color: #647a0b;
            display: block;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #854f38;
            border-radius: 5px;
            font-size: 1rem;
            color: #333;
        }

        .form-control:focus {
            border-color: #647a0b;
            outline: none;
            box-shadow: 0 0 5px rgba(100, 122, 11, 0.5);
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            display: inline-block;
            margin-right: 10px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #854f38;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            padding: 10px 20px;
            border: 1px solid #854f38;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            display: inline-block;
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
            transform: translateY(-2px);
        }

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
        }

        .mt-4 {
            margin-top: 1rem;
        }

        .service-tag {
            display: flex;
            align-items: center;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .service-tag:hover {
            transform: translateY(-5px);
            background-color: #2f855a;
        }

        .service-tag button {
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            font-size: 1rem;
            line-height: 1;
        }

        /* Styles pour le formulaire multi-étapes */
        .form-step {
            display: none;
        }

        .form-step-active {
            display: block;
        }

        /* Styles pour la barre de progression améliorée */
        .progressbar-container {
            margin-bottom: 30px;
        }

        .progressbar {
            counter-reset: step;
            display: flex;
            justify-content: space-between;
            align-items: center;
            list-style-type: none;
            padding: 0;
        }

        .progressbar li {
            position: relative;
            width: 100%;
            text-align: center;
            color: #bbb;
        }

        .progressbar li::before {
            content: counter(step);
            counter-increment: step;
            width: 2rem;
            height: 2rem;
            line-height: 2rem;
            border: 2px solid #bbb;
            display: block;
            text-align: center;
            margin: 0 auto 10px;
            border-radius: 50%;
            background-color: white;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }

        .progressbar li::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            background-color: #bbb;
            top: 1rem;
            left: -50%;
            z-index: -1;
            transition: background-color 0.3s;
        }

        .progressbar li:first-child::after {
            content: none;
        }

        .progressbar li.active::before,
        .progressbar li.active ~ li::before {
            background-color: #854f38;
            border-color: #854f38;
            color: white;
        }

        .progressbar li.active::after,
        .progressbar li.active ~ li::after {
            background-color: #854f38;
        }

        .progressbar li[data-title]::after {
            content: attr(data-title);
            position: absolute;
            top: 2.5rem;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.9rem;
            color: #333;
            white-space: nowrap;
        }

        .progressbar li.active + li::after {
            background-color: #bbb;
        }

        .progressbar li.active + li::before {
            background-color: white;
            border-color: #bbb;
            color: #bbb;
        }

        /* Animation */
        .progressbar li::before {
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }

        .progressbar li::after {
            transition: background-color 0.3s;
        }

        .form-navigation {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .border-red-500 {
            border-color: #e3342f;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .details-container {
                padding: 20px;
            }

            .details-title {
                font-size: 1.5rem;
            }

            .progressbar li[data-title]::after {
                font-size: 0.75rem;
            }

            .progressbar li::after {
                top: 3rem;
            }

            .progressbar li::before {
                width: 1.5rem;
                height: 1.5rem;
                line-height: 1.5rem;
                margin-bottom: 5px;
            }
        }

        /* Additional Styles for Profile Picture */
        img.rounded-full {
            object-fit: cover;
        }

        .avatar-cropper-modal {
            position: fixed;
            inset: 0;
            z-index: 1200;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .avatar-cropper-modal.hidden {
            display: none;
        }

        .avatar-cropper-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(17, 24, 39, 0.72);
        }

        .avatar-cropper-dialog {
            position: relative;
            width: min(760px, 100%);
            max-height: 94vh;
            overflow: auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.24);
            border: 1px solid #d9e2ba;
        }

        .avatar-cropper-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.9rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .avatar-cropper-close {
            border: none;
            background: transparent;
            color: #6b7280;
            font-size: 1.6rem;
            line-height: 1;
            cursor: pointer;
        }

        .avatar-cropper-body {
            padding: 1rem;
        }

        .avatar-cropper-image-wrap {
            width: 100%;
            height: min(58vh, 420px);
            background: #f3f4f6;
            border-radius: 10px;
            overflow: hidden;
        }

        .avatar-cropper-image-wrap img {
            display: block;
            max-width: 100%;
        }

        .avatar-cropper-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.6rem;
            padding: 0.9rem 1rem 1rem;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</x-app-layout>

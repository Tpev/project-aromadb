{{-- resources/views/profile/edit-company-info.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Informations de l\'Entreprise') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Mettre à Jour les Informations de l\'Entreprise') }}</h1>

          
            <!-- Updated Form with enctype for file uploads -->
            <form action="{{ route('profile.updateCompanyInfo') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Nom de l'Entreprise -->
                <div class="details-box">
                    <label class="details-label" for="company_name">{{ __('Nom de l\'Entreprise') }}</label>
                    <input type="text" id="company_name" name="company_name" class="form-control" value="{{ old('company_name', auth()->user()->company_name) }}">
                    @error('company_name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Adresse de l'Entreprise -->
                <div class="details-box">
                    <label class="details-label" for="company_address">{{ __('Adresse de l\'Entreprise') }}</label>
                    <textarea id="company_address" name="company_address" class="form-control">{{ old('company_address', auth()->user()->company_address) }}</textarea>
                    @error('company_address')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email de l'Entreprise -->
                <div class="details-box">
                    <label class="details-label" for="company_email">{{ __('Email de l\'Entreprise') }}</label>
                    <input type="email" id="company_email" name="company_email" class="form-control" value="{{ old('company_email', auth()->user()->company_email) }}">
                    @error('company_email')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Téléphone de l'Entreprise -->
                <div class="details-box">
                    <label class="details-label" for="company_phone">{{ __('Téléphone de l\'Entreprise') }}</label>
                    <input type="text" id="company_phone" name="company_phone" class="form-control" value="{{ old('company_phone', auth()->user()->company_phone) }}">
                    @error('company_phone')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

				<!-- About Us (Replaced text area with Quill) -->
				<div class="details-box">
					<label class="details-label" for="about">{{ __('À Propos') }}</label>

					<!-- Hidden input to store the HTML content Quill produces -->
					<input type="hidden" name="about" id="about-input" />

					<!-- Quill Editor Container -->
					<div id="quill-editor" style="height: 200px;"></div>

					<!-- Helper text -->
					<small class="text-gray-500">{{ __('Aidez vos clients à en savoir plus sur vous, vos méthodes, certifications, parcours. Ce texte apparaitra sur votre profile pro.') }}</small>

					@error('about')
						<p class="text-red-500">{{ $message }}</p>
					@enderror
				</div>


                <!-- Services (Enhanced User-Friendly Input) -->
                <div class="details-box">
                    <label class="details-label" for="service-input">{{ __('Services') }}</label>
                    <div class="flex flex-wrap items-center border border-gray-300 rounded p-2">
                        <div id="services-list" class="flex flex-wrap gap-2">
                            @php
                                // Decode the services JSON string into an array
                                $services = json_decode(old('services', auth()->user()->services), true);
                                
                                // Handle cases where services might be a JSON-encoded string
                                if (!is_array($services)) {
                                    $services = json_decode($services, true) ?? [];
                                }
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
                    @error('services')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                    <!-- Ensure the hidden input contains a valid JSON array -->
                    <input type="hidden" name="services" id="services-input" value='@json($services)'>
                </div>

                <!-- Profile Description -->
                <div class="details-box">
                    <label class="details-label" for="profile_description">{{ __('Votre spécialité') }}</label>
                    <textarea id="profile_description" name="profile_description" class="form-control">{{ old('profile_description', auth()->user()->profile_description) }}</textarea>
                    	<!-- Helper text -->
					<small class="text-gray-500">{{ __('Aromathérapeute, Ostéopathe etc.') }}</small>
				
					@error('profile_description')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Profile Picture Upload -->
                <div class="details-box">
                    <label class="details-label" for="profile_picture">{{ __('Photo de Profil') }}</label>
                    <div class="flex items-center">
                        <!-- Display Current Profile Picture or Default -->
                        @if(auth()->user()->profile_picture)
                            <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="{{ __('Photo de Profil') }}" class="w-20 h-20 rounded-full object-cover mr-4">
                        @endif
                        <!-- File Input -->
                        <input type="file" id="profile_picture" name="profile_picture" class="form-control">
                    </div>
                    @error('profile_picture')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

				<!-- Mentions Légales -->
				<div class="details-box">
					<label class="details-label" for="legal_mentions">{{ __('Mentions Légales') }}</label>
					<textarea id="legal_mentions" name="legal_mentions" class="form-control">{{ old('legal_mentions', auth()->user()->legal_mentions) }}</textarea>
					
					<!-- Helper text -->
					<small class="text-gray-500">{{ __('Veuillez entrer les mentions légales de votre entreprise. Elles seront visible en bas de page sur vos factures. Siret,Capital,etc') }}</small>
					
					@error('legal_mentions')
						<p class="text-red-500">{{ $message }}</p>
					@enderror
				</div>


<!-- Willingness to Accept Online Appointments -->
<div class="details-box">
    <label class="flex items-center">
        <input type="checkbox" name="accept_online_appointments" class="form-checkbox h-5 w-5 text-green-500" 
        {{ old('accept_online_appointments', auth()->user()->accept_online_appointments) ? 'checked' : '' }}>
        <span class="ml-2 text-gray-700">{{ __('Accepter les rendez-vous en ligne') }}</span>
    </label>



						
<!-- Helper text -->
<small class="text-gray-500">{{ __('Si vous souhaitez que vos clients puissent prendre rendez-vous en ligne de manière autonome via votre portail pro sur aromamade.com') }}</small>
				
    @error('accept_online_appointments')
        <p class="text-red-500">{{ $message }}</p>
    @enderror
</div>





<!-- Minimum Notice for Booking Appointment -->
<div class="details-box">
    <label class="details-label" for="minimum_notice_hours">{{ __('Préavis Minimum pour Prendre un Rendez-vous (heures)') }}</label>
    <input type="number" id="minimum_notice_hours" name="minimum_notice_hours" class="form-control" min="0" value="{{ old('minimum_notice_hours', auth()->user()->minimum_notice_hours) }}">
    @error('minimum_notice_hours')
        <p class="text-red-500">{{ $message }}</p>
    @enderror
</div>


                <!-- Checkbox fields for sharing info publicly -->
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="share_address_publicly" class="form-checkbox h-5 w-5 text-green-500" 
                        {{ old('share_address_publicly', auth()->user()->share_address_publicly) ? 'checked' : '' }}>
                        <span class="ml-2 text-gray-700">{{ __('Partager l\'adresse publiquement') }}</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="share_phone_publicly" class="form-checkbox h-5 w-5 text-green-500" 
                        {{ old('share_phone_publicly', auth()->user()->share_phone_publicly) ? 'checked' : '' }}>
                        <span class="ml-2 text-gray-700">{{ __('Partager le téléphone publiquement') }}</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="share_email_publicly" class="form-checkbox h-5 w-5 text-green-500" 
                        {{ old('share_email_publicly', auth()->user()->share_email_publicly) ? 'checked' : '' }}>
                        <span class="ml-2 text-gray-700">{{ __('Partager l\'email publiquement') }}</span>
                    </label>
                </div>
	<!-- Helper text -->
					<small class="text-gray-500">{{ __('Ces informations apparaitrons sur votre portail pro, cliquez sur portail dans le menu pour voir votre portail.') }}</small>
				
                <!-- Submit and Cancel buttons -->
                <button type="submit" class="btn-primary mt-4">{{ __('Enregistrer les Modifications') }}</button>
                <a href="{{ route('profile.edit') }}" class="btn-secondary mt-4">{{ __('Annuler') }}</a>
            </form>
        </div>
    </div>
@if (auth()->user()->google_access_token)
    <form method="POST" action="{{ route('google.disconnect') }}">
        @csrf
        <button class="btn btn-danger">Déconnecter Google Agenda</button>
    </form>
@else
    <a href="{{ route('google.connect') }}" class="btn btn-primary">
        Connecter Google Agenda
    </a>
@endif
<br>
<small class="text-gray-500">{{ __('Cliquez sur ce bouton pour lier votre Google Agenda : vos rendez-vous Aromamade y seront ajoutés automatiquement et vos créneaux déjà occupés seront bloqués.') }}</small>
	
    <!-- Add JavaScript to handle dynamic services list -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addServiceBtn = document.getElementById('add-service-btn');
            const serviceInput = document.getElementById('service-input');
            const servicesList = document.getElementById('services-list');
            const servicesInputHidden = document.getElementById('services-input');

            let services = [];

            // Initialize services from hidden input
            if (servicesInputHidden.value) {
                try {
                    const parsed = JSON.parse(servicesInputHidden.value);
                    services = Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    console.error('Invalid JSON in services-input:', e);
                    services = [];
                }
            }

            // Function to render services
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

            // Function to add a service
            function addService() {
                const service = serviceInput.value.trim();
                if (service && !services.includes(service)) {
                    services.push(service);
                    serviceInput.value = '';
                    renderServices();
                }
            }

            // Function to remove a service
            function removeService(index) {
                services.splice(index, 1);
                renderServices();
            }

            // Event listener for add button
            addServiceBtn.addEventListener('click', addService);

            // Event listener for Enter key in input
            serviceInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addService();
                }
            });

            // Initial render
            renderServices();
        });
    </script>

    <!-- Styles personnalisés -->
    <style>
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
            background-color: #2f855a; /* Tailwind's green-600 */
        }

        .service-tag button {
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            font-size: 1rem;
            line-height: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .details-container {
                padding: 20px;
            }

            .details-title {
                font-size: 1.5rem;
            }
        }

        /* Additional Styles for Profile Picture */
        img.rounded-full {
            object-fit: cover;
        }
    </style>
	
	<!-- Quill.js CDN -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Quill
    var quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Rédigez votre texte ici...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote'],
                ['link'],
                ['clean']
            ]
        }
    });

    // If we have old data (e.g. coming back from a validation error) or existing data in `auth()->user()->about`, 
    // load it into Quill
    @if(old('about', auth()->user()->about))
        quill.root.innerHTML = `{!! addslashes(old('about', auth()->user()->about)) !!}`;
    @endif

    // Function to update hidden input from Quill
    function updateHiddenInput() {
        document.getElementById('about-input').value = quill.root.innerHTML;
    }

    // Update hidden input on text-change
    quill.on('text-change', function() {
        updateHiddenInput();
    });

    // Also update hidden input just before form submit (extra safety)
    var form = document.querySelector('form[action="{{ route('profile.updateCompanyInfo') }}"]');
    form.addEventListener('submit', function() {
        updateHiddenInput();
    });
});
</script>

</x-app-layout>

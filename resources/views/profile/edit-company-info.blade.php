{{-- resources/views/profile/edit-company-info.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Informations de l\'Entreprise') }}
        </h2>
    </x-slot>

    @php
        $user = auth()->user();
        $canUseIntegration = $user->canUseFeature('integration');

        // Determine required license family
        $plansConfig = config('license_features.plans', []);
        $familyOrder = ['free', 'starter', 'pro', 'premium']; // ignore trial

        $requiredFamily = null;
        foreach ($familyOrder as $family) {
            if (in_array('integration', $plansConfig[$family] ?? [], true)) {
                $requiredFamily = $family;
                break;
            }
        }

        $familyLabels = [
            'free'    => __('Gratuit'),
            'starter' => __('Starter'),
            'pro'     => __('PRO'),
            'premium' => __('Premium'),
        ];

        $requiredLabel = $requiredFamily
            ? ($familyLabels[$requiredFamily] ?? ucfirst($requiredFamily))
            : __('une formule supérieure');
    @endphp

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Mettre à Jour les Informations de l\'Entreprise') }}</h1>

            <div x-data="{ activeTab: 'company' }">
                {{-- Tabs header --}}
                <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200">
                    <button type="button"
                            class="px-4 py-2 text-sm font-semibold border-b-2 -mb-[1px]"
                            :class="activeTab === 'company'
                                ? 'border-[#647a0b] text-[#647a0b]'
                                : 'border-transparent text-gray-500 hover:text-[#647a0b]'"
                            @click="activeTab = 'company'">
                        {{ __('Entreprise') }}
                    </button>

                    <button type="button"
                            class="px-4 py-2 text-sm font-semibold border-b-2 -mb-[1px]"
                            :class="activeTab === 'public'
                                ? 'border-[#647a0b] text-[#647a0b]'
                                : 'border-transparent text-gray-500 hover:text-[#647a0b]'"
                            @click="activeTab = 'public'">
                        {{ __('Profil public') }}
                    </button>

                    <button type="button"
                            class="px-4 py-2 text-sm font-semibold border-b-2 -mb-[1px]"
                            :class="activeTab === 'booking'
                                ? 'border-[#647a0b] text-[#647a0b]'
                                : 'border-transparent text-gray-500 hover:text-[#647a0b]'"
                            @click="activeTab = 'booking'">
                        {{ __('Prise de RDV & agenda') }}
                    </button>

                    <button type="button"
                            class="px-4 py-2 text-sm font-semibold border-b-2 -mb-[1px]"
                            :class="activeTab === 'legal'
                                ? 'border-[#647a0b] text-[#647a0b]'
                                : 'border-transparent text-gray-500 hover:text-[#647a0b]'"
                            @click="activeTab = 'legal'">
                        {{ __('Mentions légales & CGV') }}
                    </button>

                    <button type="button"
                            class="px-4 py-2 text-sm font-semibold border-b-2 -mb-[1px]"
                            :class="activeTab === 'google'
                                ? 'border-[#647a0b] text-[#647a0b]'
                                : 'border-transparent text-gray-500 hover:text-[#647a0b]'"
                            @click="activeTab = 'google'">
                        {{ __('Connexions & Google') }}
                    </button>
                </div>

                {{-- Main form (all company/profile/booking/legal/google fields) --}}
                <form action="{{ route('profile.updateCompanyInfo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- TAB 1: Entreprise --}}
                    <div x-show="activeTab === 'company'" x-cloak>
                        <!-- Nom de l'Entreprise -->
                        <div class="details-box">
                            <label class="details-label" for="company_name">{{ __('Nom de l\'Entreprise') }}</label>
                            <input type="text" id="company_name" name="company_name" class="form-control"
                                   value="{{ old('company_name', $user->company_name) }}">
                            @error('company_name')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Adresse de l'Entreprise -->
                        <div class="details-box">
                            <label class="details-label" for="company_address">{{ __('Adresse de l\'Entreprise') }}</label>
                            <textarea id="company_address" name="company_address"
                                      class="form-control">{{ old('company_address', $user->company_address) }}</textarea>
                            @error('company_address')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email de l'Entreprise -->
                        <div class="details-box">
                            <label class="details-label" for="company_email">{{ __('Email de l\'Entreprise') }}</label>
                            <input type="email" id="company_email" name="company_email" class="form-control"
                                   value="{{ old('company_email', $user->company_email) }}">
                            @error('company_email')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Téléphone de l'Entreprise -->
                        <div class="details-box">
                            <label class="details-label" for="company_phone">{{ __('Téléphone de l\'Entreprise') }}</label>
                            <input type="text" id="company_phone" name="company_phone" class="form-control"
                                   value="{{ old('company_phone', $user->company_phone) }}">
                            @error('company_phone')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- TAB 2: Profil public --}}
                    <div x-show="activeTab === 'public'" x-cloak>
                        <!-- About Us (Quill) -->
                        <div class="details-box">
                            <label class="details-label" for="about">{{ __('À Propos') }}</label>

                            <!-- Hidden input to store the HTML content Quill produces -->
                            <input type="hidden" name="about" id="about-input" />

                            <!-- Quill Editor Container -->
                            <div id="quill-editor" style="height: 200px;"></div>

                            <!-- Helper text -->
                            <small class="text-gray-500">
                                {{ __('Aidez vos clients à en savoir plus sur vous, vos méthodes, certifications, parcours. Ce texte apparaitra sur votre profile pro.') }}
                            </small>

                            @error('about')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Services -->
                        <div class="details-box">
                            <label class="details-label" for="service-input">{{ __('Services') }}</label>
                            <div class="flex flex-wrap items-center border border-gray-300 rounded p-2 bg-white">
                                <div id="services-list" class="flex flex-wrap gap-2">
                                    @php
                                        // Decode the services JSON string into an array
                                        $services = json_decode(old('services', $user->services), true);

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
                                <input type="text" id="service-input"
                                       class="flex-grow p-1 border-none focus:outline-none text-sm"
                                       placeholder="{{ __('Ajouter un service...') }}">
                                <button type="button" id="add-service-btn"
                                        class="ml-2 bg-green-500 text-white px-3 py-1 rounded text-sm">
                                    {{ __('Ajouter') }}
                                </button>
                            </div>
                            @error('services')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                            <!-- Ensure the hidden input contains a valid JSON array -->
                            <input type="hidden" name="services" id="services-input" value='@json($services)'>
                        </div>

                        <!-- Profile Description -->
                        <div class="details-box">
                            <label class="details-label" for="profile_description">{{ __('Titre et spécialités') }}</label>
                            <textarea id="profile_description" name="profile_description"
                                      class="form-control">{{ old('profile_description', $user->profile_description) }}</textarea>
                            <!-- Helper text -->
                            <small class="text-gray-500">{{ __('Aromathérapeute, Ostéopathe etc.') }}</small>

                            @error('profile_description')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Profile Picture Upload -->
                        <div class="details-box">
                            <label class="details-label" for="profile_picture">{{ __('Photo de Profil') }}</label>
                            <div class="flex items-center gap-4">
                                <!-- Display Current Profile Picture or Default -->
                                @if($user->profile_picture)
                                    <img src="{{ asset('storage/' . $user->profile_picture) }}"
                                         alt="{{ __('Photo de Profil') }}"
                                         class="w-20 h-20 rounded-full object-cover">
                                @endif
                                <!-- File Input -->
                                <input type="file" id="profile_picture" name="profile_picture" class="form-control">
                            </div>
                            @error('profile_picture')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Public sharing checkboxes -->
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="share_address_publicly"
                                       class="form-checkbox h-5 w-5 text-green-500"
                                    {{ old('share_address_publicly', $user->share_address_publicly) ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">{{ __('Partager l\'adresse publiquement') }}</span>
                            </label>
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="share_phone_publicly"
                                       class="form-checkbox h-5 w-5 text-green-500"
                                    {{ old('share_phone_publicly', $user->share_phone_publicly) ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">{{ __('Partager le téléphone publiquement') }}</span>
                            </label>
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="share_email_publicly"
                                       class="form-checkbox h-5 w-5 text-green-500"
                                    {{ old('share_email_publicly', $user->share_email_publicly) ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">{{ __('Partager l\'email publiquement') }}</span>
                            </label>
                        </div>

                        <!-- Helper text portail pro -->
                        <small class="text-gray-500 block mb-2">
                            {{ __('Ces informations apparaitrons sur votre portail pro, cliquez sur portail dans le menu pour voir votre portail.') }}
                        </small>
                    </div>

                    {{-- TAB 3: Prise de RDV & agenda --}}
                    <div x-show="activeTab === 'booking'" x-cloak>
                        <!-- Willingness to Accept Online Appointments -->
                        <div class="details-box">
                            <label class="flex items-center">
                                <input type="checkbox" name="accept_online_appointments"
                                       class="form-checkbox h-5 w-5 text-green-500"
                                    {{ old('accept_online_appointments', $user->accept_online_appointments) ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">{{ __('Accepter les rendez-vous en ligne') }}</span>
                            </label>

                            <!-- Helper text -->
                            <small class="text-gray-500">
                                {{ __('Si vous souhaitez que vos clients puissent prendre rendez-vous en ligne de manière autonome via votre portail pro sur aromamade.com') }}
                            </small>

                            @error('accept_online_appointments')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Minimum Notice for Booking Appointment -->
                        <div class="details-box">
                            <label class="details-label" for="minimum_notice_hours">
                                {{ __('Préavis Minimum pour Prendre un Rendez-vous (heures)') }}
                            </label>
                            <input type="number" id="minimum_notice_hours" name="minimum_notice_hours"
                                   class="form-control" min="0"
                                   value="{{ old('minimum_notice_hours', $user->minimum_notice_hours) }}">
                            @error('minimum_notice_hours')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Buffer Time Between Appointments -->
                        <div class="details-box">
                            <label class="details-label" for="buffer_time_between_appointments">
                                {{ __('Temps de battement entre deux rendez-vous (minutes)') }}
                            </label>
                            <input
                                type="number"
                                id="buffer_time_between_appointments"
                                name="buffer_time_between_appointments"
                                class="form-control"
                                min="0"
                                step="5"
                                value="{{ old('buffer_time_between_appointments', $user->buffer_time_between_appointments) }}"
                            >
                            <small class="text-gray-500">
                                {{ __('Durée ajoutée automatiquement entre deux rendez-vous pour vous laisser du temps (préparation, notes, pause, etc.).') }}
                            </small>
                            @error('buffer_time_between_appointments')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="details-box">
                            <label class="details-label" for="cancellation_notice_hours">
                                {{ __('Délai minimum d\'annulation client (heures avant le rendez-vous)') }}
                            </label>

                            <input type="number"
                                   id="cancellation_notice_hours"
                                   name="cancellation_notice_hours"
                                   class="form-control"
                                   min="0"
                                   step="1"
                                   value="{{ old('cancellation_notice_hours', $user->cancellation_notice_hours ?? 0) }}">

                            <small class="text-gray-500">
                                {{ __('Ex : 24 = annulation possible jusqu\'à 24h avant. 0 = annulation possible à tout moment.') }}
                            </small>

                            @error('cancellation_notice_hours')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- TAB 4: Mentions légales & CGV --}}
                    <div x-show="activeTab === 'legal'" x-cloak>
                        <!-- Mentions Légales -->
                        <div class="details-box">
                            <label class="details-label" for="legal_mentions">{{ __('Mentions Légales') }}</label>
                            <textarea id="legal_mentions" name="legal_mentions"
                                      class="form-control">{{ old('legal_mentions', $user->legal_mentions) }}</textarea>

                            <!-- Helper text -->
                            <small class="text-gray-500">
                                {{ __('Veuillez entrer les mentions légales de votre entreprise. Elles seront visible en bas de page sur vos factures. Siret,Capital,etc') }}
                            </small>

                            @error('legal_mentions')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- CGV PDF Upload -->
                        <div class="details-box">
                            <label class="details-label" for="cgv_pdf">
                                {{ __('Conditions Générales de Vente (CGV) – PDF') }}
                            </label>

                            @if($user->cgv_pdf_path)
                                <p class="mb-2">
                                    {{ __('CGV actuelles :') }}
                                    <a href="{{ asset('storage/' . $user->cgv_pdf_path) }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="text-blue-600 underline">
                                        {{ __('Voir / télécharger vos CGV') }}
                                    </a>
                                </p>
                            @endif

                            <input type="file" id="cgv_pdf" name="cgv_pdf"
                                   class="form-control" accept="application/pdf">

                            <small class="text-gray-500">
                                {{ __('Formats acceptés : PDF uniquement. Taille maximale ~10 Mo.') }}
                            </small>

                            @error('cgv_pdf')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- =========================
                             BRANDING FACTURES / DEVIS
                             ========================= --}}
                        <div class="details-box mt-6">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold text-lg" style="color:#647a0b;">
                                    {{ __('Branding des factures') }}
                                </h3>
                            </div>

                            {{-- Aperçu logo actuel --}}
                            @php
                                $logoPath = $user->invoice_logo_path
                                    ? asset('storage/' . $user->invoice_logo_path)
                                    : null;
                            @endphp

                            @if($logoPath)
                                <div class="mb-3">
                                    <div class="text-sm text-gray-600 mb-2">{{ __('Logo actuel :') }}</div>
                                    <img src="{{ $logoPath }}" alt="Logo" style="max-height:70px; max-width:220px;">
                                </div>

                                <div class="mb-4">
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" name="remove_invoice_logo" value="1">
                                        <span class="text-sm text-gray-700">{{ __('Supprimer le logo') }}</span>
                                    </label>
                                    @error('remove_invoice_logo')
                                        <p class="text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            {{-- Upload nouveau logo --}}
                            <div class="mb-4">
                                <label class="details-label" for="invoice_logo">{{ __('Logo pour factures (PNG/JPG/WebP/SVG)') }}</label>
                                <input type="file" id="invoice_logo" name="invoice_logo" class="form-control"
                                       accept=".png,.jpg,.jpeg,.webp,.svg">
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ __('Recommandé : PNG transparent, largeur ~300–600px. Max 4 Mo.') }}
                                </p>
                                @error('invoice_logo')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Couleur primaire --}}
                            @php
                                $currentColor = old('invoice_primary_color', $user->invoice_primary_color ?: '#647a0b');
                            @endphp

                            <div class="mb-2">
                                <label class="details-label" for="invoice_primary_color">{{ __('Couleur principale (factures & devis)') }}</label>

                                <div class="flex items-center gap-3">
                                    <input type="color"
                                           id="invoice_primary_color_picker"
                                           value="{{ $currentColor }}"
                                           class="h-10 w-14 p-1 border border-gray-300 rounded"
                                           oninput="document.getElementById('invoice_primary_color').value = this.value">

                                    <input type="text"
                                           id="invoice_primary_color"
                                           name="invoice_primary_color"
                                           class="form-control"
                                           style="max-width:160px;"
                                           value="{{ $currentColor }}"
                                           placeholder="#647a0b"
                                           oninput="document.getElementById('invoice_primary_color_picker').value = this.value">
                                </div>

                                <p class="text-xs text-gray-500 mt-1">
                                    {{ __('Format attendu : #RRGGBB') }}
                                </p>

                                @error('invoice_primary_color')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- TAB 5: Connexions & Google (NOW INSIDE FORM so google_event_color_id is submitted) --}}
                    <div x-show="activeTab === 'google'" x-cloak class="mt-2">
                        <!-- Google Connections Section -->
                        <div class="details-box google-section relative">
                            {{-- Lock pill (if feature unavailable) --}}
                            @unless($canUseIntegration)
                                <div style="
                                    position: absolute;
                                    top: -10px;
                                    right: -10px;
                                    background-color: #fff1d6;
                                    border: 1px solid rgba(250,204,21,0.4);
                                    padding: 2px 8px;
                                    font-size: 10px;
                                    border-radius: 9999px;
                                    font-weight: 600;
                                    color: #854f38;
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 4px;
                                    box-shadow: 0 1px 2px rgba(0,0,0,.08);
                                ">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="currentColor"
                                         viewBox="0 0 20 20"
                                         style="width: 12px; height: 12px;">
                                        <path fill-rule="evenodd"
                                              d="M10 2a4 4 0 00-4 4v2H5a2
                                                 2 0 00-2 2v6a2 2 0
                                                 002 2h10a2 2 0
                                                 002-2v-6a2 2 0
                                                 00-2-2h-1V6a4 4
                                                 0 00-4-4zm0 6a2 2
                                                 0 00-2 2v2a2 2
                                                 0 104 0v-2a2 2
                                                 0 00-2-2z"
                                              clip-rule="evenodd" />
                                    </svg>

                                    {{ __('À partir de :') }} <strong>{{ $requiredLabel }}</strong>
                                </div>
                            @endunless

                            <h3 class="details-label mb-2">{{ __('Connexion avec Google') }}</h3>
                            <p class="text-gray-500 text-sm mb-3">
                                {{ __('Connectez votre compte AromaMade à Google pour automatiser encore plus votre organisation.') }}
                            </p>

                            <div class="flex flex-wrap items-center gap-3">
                                @if($canUseIntegration)
                                    {{-- Google Agenda connect / disconnect --}}
                                    @if ($user->google_access_token)
                                        {{-- IMPORTANT: no nested form inside main form --}}
                                        <button type="submit" form="disconnectGoogleForm" class="btn btn-danger">
                                            {{ __('Déconnecter Google Agenda') }}
                                        </button>
                                    @else
                                        <a href="{{ route('google.connect') }}" class="btn btn-primary inline-block">
                                            {{ __('Connecter Google Agenda') }}
                                        </a>
                                    @endif

                                    {{-- Google Reviews --}}
                                    <a href="{{ route('pro.google-reviews.index') }}" class="btn btn-secondary inline-block">
                                        {{ __('Connecter Google Review') }}
                                    </a>
                                @else
                                    {{-- Grey-out all integration buttons --}}
                                    <a href="/license-tiers/pricing"
                                       class="btn"
                                       style="
                                           background:#e5e7eb;
                                           border:1px solid #d1d5db;
                                           color:#6b7280;
                                           padding: 0.45rem 1rem;
                                           border-radius:6px;
                                           white-space: nowrap;
                                           font-weight:600;
                                       ">
                                        {{ __('Connecter Google Agenda') }}
                                    </a>

                                    <a href="/license-tiers/pricing"
                                       class="btn"
                                       style="
                                           background:#f2f2f2;
                                           border:1px solid #d1d5db;
                                           color:#6b7280;
                                           padding: 0.45rem 1rem;
                                           border-radius:6px;
                                           white-space: nowrap;
                                           font-weight:600;
                                       ">
                                        {{ __('Connecter Google Review') }}
                                    </a>
                                @endif
                            </div>

                            <small class="text-gray-500 block mt-3">
                                {{ __('Cliquez sur ce bouton pour lier votre Google Agenda : vos rendez-vous AromaMade y seront ajoutés automatiquement et vos créneaux déjà occupés seront bloqués.') }}
                            </small>
                        </div>

                        {{-- Google event color selector (only if integration is available + google connected) --}}
                        @if($canUseIntegration && $user->google_access_token)
                            @php
                                // Default Google "blue" = 9
                                $currentGoogleColorId = old('google_event_color_id', $user->google_event_color_id ?: '9');

                                // Google-ish palette (visual only). Stored value is the colorId (1..11).
                                $googleColors = [
                                    '1'  => '#a4bdfc', // lavender
                                    '2'  => '#7ae7bf', // green
                                    '3'  => '#dbadff', // purple
                                    '4'  => '#ff887c', // red
                                    '5'  => '#fbd75b', // yellow
                                    '6'  => '#ffb878', // orange
                                    '7'  => '#46d6db', // teal
                                    '8'  => '#e1e1e1', // gray
                                    '9'  => '#5484ed', // blue (default)
                                    '10' => '#51b749', // dark green
                                    '11' => '#dc2127', // dark red
                                ];
                            @endphp

                            <div class="details-box mt-5"
                                 x-data="{ selected: '{{ $currentGoogleColorId }}' }">
                                <label class="details-label mb-2">
                                    {{ __('Couleur des rendez-vous AromaMade dans Google Agenda') }}
                                </label>

                                <p class="text-gray-500 text-sm mb-3">
                                    {{ __('Choisissez la couleur des RDV synchronisés. Par défaut : bleu.') }}
                                </p>

                                {{-- Hidden field actually submitted --}}
                                <input type="hidden" name="google_event_color_id" :value="selected">

                                <div class="flex flex-wrap items-center gap-2">
                                    @foreach($googleColors as $id => $hex)
                                        <button type="button"
                                                class="rounded-full border transition"
                                                :class="selected === '{{ $id }}'
                                                    ? 'ring-2 ring-offset-2 ring-[#647a0b] border-transparent'
                                                    : 'border-gray-300 hover:border-gray-400'"
                                                style="width: 34px; height: 34px; background: {{ $hex }};"
                                                @click="selected = '{{ $id }}'"
                                                title="{{ __('Couleur') }} #{{ $id }}"
                                                aria-label="{{ __('Choisir la couleur') }} #{{ $id }}">
                                        </button>
                                    @endforeach

                                    <span class="ml-2 text-xs text-gray-500">
                                        {{ __('Sélection :') }} <strong x-text="selected"></strong>
                                        <span class="ml-2">•</span>
                                        <span class="ml-2">{{ __('Défaut : 9 (bleu)') }}</span>
                                    </span>
                                </div>

                                @error('google_event_color_id')
                                    <p class="text-red-500 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>

                    {{-- Form actions (always visible) --}}
                    <div class="mt-6 flex items-center gap-3">
                        <button type="submit" class="btn-primary">
                            {{ __('Enregistrer les Modifications') }}
                        </button>
                        <a href="{{ route('profile.edit') }}" class="btn-secondary">
                            {{ __('Annuler') }}
                        </a>
                    </div>
                </form>

                {{-- Separate form to disconnect Google (no nested form inside main form) --}}
                @if($canUseIntegration && $user->google_access_token)
                    <form id="disconnectGoogleForm" method="POST" action="{{ route('google.disconnect') }}" class="hidden">
                        @csrf
                    </form>
                @endif
            </div> {{-- /x-data --}}
        </div>
    </div>

    <!-- Add JavaScript to handle dynamic services list -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addServiceBtn = document.getElementById('add-service-btn');
            const serviceInput = document.getElementById('service-input');
            const servicesList = document.getElementById('services-list');
            const servicesInputHidden = document.getElementById('services-input');

            let services = [];

            // Initialize services from hidden input
            if (servicesInputHidden && servicesInputHidden.value) {
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
                if (!servicesList || !servicesInputHidden) return;

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
                const service = (serviceInput?.value || '').trim();
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
            if (addServiceBtn) addServiceBtn.addEventListener('click', addService);

            // Event listener for Enter key in input
            if (serviceInput) {
                serviceInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addService();
                    }
                });
            }

            // Initial render
            renderServices();
        });
    </script>

    <!-- Styles personnalisés -->
    <style>
        [x-cloak] { display: none !important; }

        .container {
            max-width: 900px;
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
            margin-bottom: 24px;
            text-align: center;
        }

        .details-box {
            margin-bottom: 18px;
        }

        .details-label {
            font-weight: bold;
            color: #647a0b;
            display: block;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #854f38;
            border-radius: 5px;
            font-size: 1rem;
            color: #333;
            background-color: #fff;
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

        .service-tag {
            display: flex;
            align-items: center;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .service-tag:hover {
            transform: translateY(-2px);
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

        img.rounded-full {
            object-fit: cover;
        }

        @media (max-width: 640px) {
            .details-container {
                padding: 20px;
            }

            .details-title {
                font-size: 1.5rem;
            }
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

            // Load existing data
            @if(old('about', $user->about))
                quill.root.innerHTML = `{!! addslashes(old('about', $user->about)) !!}`;
            @endif

            function updateHiddenInput() {
                var el = document.getElementById('about-input');
                if (el) el.value = quill.root.innerHTML;
            }

            quill.on('text-change', function() {
                updateHiddenInput();
            });

            var form = document.querySelector('form[action="{{ route('profile.updateCompanyInfo') }}"]');
            if (form) {
                form.addEventListener('submit', function() {
                    updateHiddenInput();
                });
            }
        });
    </script>

</x-app-layout>

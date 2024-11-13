<!-- resources/views/your-navigation-file.blade.php -->

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Inclure Alpine.js si ce n'est pas déjà fait -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Menu de Navigation Principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Contenu de la Navigation -->
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard-pro') }}">
                        <x-application-logo class="block h-12 w-auto" style="color: #647a0b;" />
                    </a>
                </div>

                <!-- Liens de Navigation -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard-pro')" :active="request()->routeIs('dashboard-pro')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Tableau de Bord') }}
                    </x-nav-link>
                    <x-nav-link :href="route('client_profiles.index')" :active="request()->routeIs('client_profiles.*')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Clients') }}
                    </x-nav-link>
                    <x-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.*')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Rendez-vous') }}
                    </x-nav-link>
                    <x-nav-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Factures') }}
                    </x-nav-link>

                    <!-- Menu déroulant Configuration avec Alpine.js -->
                    <div class="relative flex items-center" x-data="{ openConfig: false, timeout: null }" @mouseenter="clearTimeout(timeout); openConfig = true" @mouseleave="timeout = setTimeout(() => openConfig = false, 200)">
                        <!-- Lien Configuration -->
                        <x-nav-link href="#" class="text-[#647a0b] hover:text-[#854f38] flex items-center" @click.prevent="openConfig = !openConfig" x-bind:aria-expanded="openConfig.toString()" aria-haspopup="true">
                            {{ __('Configuration') }}
                            <!-- Indicateur de Dropdown -->
                            <svg class="ml-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                <path d="M5.25 7.5L10 12.25L14.75 7.5H5.25Z"></path>
                            </svg>
                        </x-nav-link>

                        <!-- Menu déroulant -->
                        <div x-show="openConfig" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-1" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-1" class="absolute left-0 top-full mt-2 w-48 bg-white rounded-md shadow-lg z-20" x-cloak @mouseenter="clearTimeout(timeout); openConfig = true" @mouseleave="timeout = setTimeout(() => openConfig = false, 200)">
                            <!-- Éléments du Menu -->
                            <a href="{{ route('availabilities.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Disponibilités') }}
                            </a>
                            <a href="{{ route('unavailabilities.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Indisponibilités') }}
                            </a>
                            <a href="{{ route('products.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Prestations') }}
                            </a>
                            <a href="{{ route('events.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Evénements') }}
                            </a>
                            <a href="{{ route('questionnaires.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Questionnaires') }}
                            </a>
                            <a href="{{ route('profile.editCompanyInfo') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Informations de l\'entreprise') }}
                            </a>
                            <a href="{{ route('therapist.stripe') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Paiement en ligne') }}
                            </a>
                        </div>
                    </div>

                    <x-nav-link href="{{ url('/pro/' . auth()->user()->slug) }}" :active="request()->routeIs('invoices.*')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Portail') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Dropdown des Paramètres -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-[#647a0b] bg-white hover:text-[#854f38] focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ml-1"></div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Profil -->
                        <x-dropdown-link :href="route('profile.edit')" class="text-[#647a0b] hover:text-[#854f38]">
                            {{ __('Profil') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('contact.show')" class="text-[#647a0b] hover:text-[#854f38]">
                            {{ __('Support') }}
                        </x-dropdown-link>
                        <div class="border-t border-gray-100"></div>

                        <!-- Déconnexion -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" class="text-[#647a0b] hover:text-[#854f38]"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                                {{ __('Déconnexion') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Menu Hamburger (Responsive) -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-[#647a0b] hover:text-[#854f38] hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6L18 18" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menu de Navigation Réactif -->
    <div :class="{ 'block': open, 'hidden': ! open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard-pro')" :active="request()->routeIs('dashboard-pro')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Tableau de Bord') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('client_profiles.index')" :active="request()->routeIs('client_profiles.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Clients') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Rendez-vous') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Prestations') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('events.index')" :active="request()->routeIs('products.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Evénements') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('availabilities.index')" :active="request()->routeIs('availabilities.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Disponibilités') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('unavailabilities.index')" :active="request()->routeIs('availabilities.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Indisponibilités') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('questionnaires.index')" :active="request()->routeIs('questionnaires.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Questionnaires') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('profile.editCompanyInfo')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Informations de l\'entreprise') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('therapist.stripe')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Paiement en ligne') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Factures') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ url('/pro/' . auth()->user()->slug) }}" :active="request()->routeIs('invoices.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Portail') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-[#647a0b]">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-[#647a0b] hover:text-[#854f38]">
                    {{ __('Profil') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('contact.show')" class="text-[#647a0b] hover:text-[#854f38]">
                    {{ __('Support') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" class="text-[#647a0b] hover:text-[#854f38]"
                        onclick="event.preventDefault();
                                    this.closest('form').submit();">
                        {{ __('Déconnexion') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Bannière d'essai gratuit -->
@if(Auth::user()->isTherapist() && Auth::user()->license && Auth::user()->license->licenseTier->is_trial)
    @php
        $trialExpirationDate = \Carbon\Carbon::parse(Auth::user()->license->expiration_date);
        $remainingDays = (int) $trialExpirationDate->diffInDays(now());
        $remainingDays = -$remainingDays;
    @endphp

    <div id="trial-banner" class="bg-yellow-200 border-l-4 border-yellow-500 text-yellow-700 p-4 relative shadow-md mx-auto max-w-7xl mt-6 rounded-md" style="display: none;">
        <button id="dismiss-trial-banner" class="absolute right-4 top-4 text-2xl text-yellow-700 hover:text-yellow-900">&times;</button>
        <div class="flex justify-between items-center">
            <div class="text-left">
                <h3 class="font-semibold text-lg">{{ __('Version d\'essai') }}</h3>
                <p class="mt-1">
                    {{ __('Il vous reste') }}
                    <span class="font-semibold">{{ $remainingDays }} {{ __('jours') }}</span>
                    {{ __('dans votre version d\'essai.') }}
                </p>
            </div>
            <div class="ml-6">
                <a href="{{ route('license-tiers.pricing') }}" class="inline-block bg-blue-600 text-white font-semibold py-2 px-4 rounded-md shadow hover:bg-blue-700 transition duration-200">
                    {{ __('Mettre à niveau') }}
                </a>
            </div>
        </div>
    </div>

    <script>
        // Fonction pour obtenir la date d'aujourd'hui au format YYYY-MM-DD
        function getTodayDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = (today.getMonth() + 1).toString().padStart(2, '0');
            const day = today.getDate().toString().padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Vérifier si la bannière a été rejetée aujourd'hui
        const lastDismissDate = localStorage.getItem('trial-banner-dismissed-date');
        const today = getTodayDate();

        if (lastDismissDate !== today) {
            // Afficher la bannière si elle n'a pas été rejetée aujourd'hui
            document.getElementById('trial-banner').style.display = 'block';
        }

        // Gérer le rejet de la bannière
        document.getElementById('dismiss-trial-banner').addEventListener('click', function () {
            // Cacher la bannière et enregistrer la date actuelle comme date de rejet
            document.getElementById('trial-banner').style.display = 'none';
            localStorage.setItem('trial-banner-dismissed-date', today);
        });
    </script>
@endif


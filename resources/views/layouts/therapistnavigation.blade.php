<!-- resources/views/layouts/therapistnavigation.blade.php -->

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Inclure Alpine.js si ce n'est pas déjà fait ailleurs -->
  
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
                    <!-- Tableau de bord -->
                    <x-nav-link :href="route('dashboard-pro')"
                                :active="request()->routeIs('dashboard-pro')"
                                class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Tableau de Bord') }}
                    </x-nav-link>

                    <!-- ======= Clients (submenu particuliers + corporate) ======= -->
                    <div class="relative flex items-center"
                         x-data="{ openClients: false, timeoutClients: null }"
                         @mouseenter="clearTimeout(timeoutClients); openClients = true"
                         @mouseleave="timeoutClients = setTimeout(() => openClients = false, 200)">
                        <x-nav-link href="#"
                                    :active="request()->routeIs('client_profiles.*') || request()->routeIs('corporate-clients.*')"
                                    class="text-[#647a0b] hover:text-[#854f38] flex items-center"
                                    @click.prevent="openClients = !openClients"
                                    x-bind:aria-expanded="openClients.toString()" aria-haspopup="true">
                            {{ __('Clients') }}
                            <svg class="ml-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                <path d="M5.25 7.5L10 12.25L14.75 7.5H5.25Z"></path>
                            </svg>
                        </x-nav-link>

                        <div x-show="openClients"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform translate-y-1"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform translate-y-1"
                             class="absolute left-0 top-full mt-2 w-64 bg-white rounded-md shadow-lg z-20"
                             x-cloak
                             @mouseenter="clearTimeout(timeoutClients); openClients = true"
                             @mouseleave="timeoutClients = setTimeout(() => openClients = false, 200)">

                            <a href="{{ route('client_profiles.index') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100
                                      {{ request()->routeIs('client_profiles.*') ? 'bg-gray-50 font-medium' : '' }}">
                                {{ __('Clients particuliers') }}
                            </a>

                            <a href="{{ route('corporate-clients.index') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100
                                      {{ request()->routeIs('corporate-clients.*') ? 'bg-gray-50 font-medium' : '' }}">
                                {{ __('Clients entreprises') }}
                            </a>
                        </div>
                    </div>
                    <!-- ======= /Clients ======= -->

                    <!-- Rendez-vous -->
                    <x-nav-link :href="route('appointments.index')"
                                :active="request()->routeIs('appointments.*')"
                                class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Rendez-vous') }}
                    </x-nav-link>

                    <!-- ======= Comptabilité ======= -->
                    <div class="relative flex items-center"
                         x-data="{ openAccounting: false, timeoutAcc: null }"
                         @mouseenter="clearTimeout(timeoutAcc); openAccounting = true"
                         @mouseleave="timeoutAcc = setTimeout(() => openAccounting = false, 200)">
                        <x-nav-link href="#"
                                    class="text-[#647a0b] hover:text-[#854f38] flex items-center"
                                    @click.prevent="openAccounting = !openAccounting"
                                    :active="request()->routeIs('invoices.*') || request()->routeIs('receipts.*')"
                                    x-bind:aria-expanded="openAccounting.toString()" aria-haspopup="true">
                            {{ __('Comptabilité') }}
                            <svg class="ml-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                <path d="M5.25 7.5L10 12.25L14.75 7.5H5.25Z"></path>
                            </svg>
                        </x-nav-link>

                        <div x-show="openAccounting"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform translate-y-1"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform translate-y-1"
                             class="absolute left-0 top-full mt-2 w-56 bg-white rounded-md shadow-lg z-20"
                             x-cloak
                             @mouseenter="clearTimeout(timeoutAcc); openAccounting = true"
                             @mouseleave="timeoutAcc = setTimeout(() => openAccounting = false, 200)">

                            <a href="{{ route('invoices.index') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('invoices.*') ? 'bg-gray-50 font-medium' : '' }}">
                                {{ __('Factures & Devis') }}
                            </a>

                            <a href="{{ route('receipts.index') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('receipts.index') ? 'bg-gray-50 font-medium' : '' }}">
                                {{ __('Livre de recettes') }}
                            </a>

                            <a href="{{ route('receipts.caMonthly') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('receipts.caMonthly') ? 'bg-gray-50 font-medium' : '' }}">
                                {{ __('Statistiques (CA mensuel)') }}
                            </a>
                        </div>
                    </div>
                    <!-- ======= /Comptabilité ======= -->

                    <!-- Menu déroulant Configuration -->
                    <div class="relative flex items-center"
                         x-data="{ openConfig: false, timeout: null }"
                         @mouseenter="clearTimeout(timeout); openConfig = true"
                         @mouseleave="timeout = setTimeout(() => openConfig = false, 200)">
                        <x-nav-link href="#"
                                    class="text-[#647a0b] hover:text-[#854f38] flex items-center"
                                    @click.prevent="openConfig = !openConfig"
                                    :active="request()->routeIs('availabilities.*')
                                              || request()->routeIs('practice-locations.*')
                                              || request()->routeIs('unavailabilities.*')
                                              || request()->routeIs('products.*')
                                              || request()->routeIs('events.*')
                                              || request()->routeIs('questionnaires.*')
                                              || request()->routeIs('profile.editCompanyInfo')
                                              || request()->routeIs('therapist.stripe')
                                              || request()->routeIs('conseils.*')
                                              || request()->routeIs('inventory_items.*')"
                                    x-bind:aria-expanded="openConfig.toString()" aria-haspopup="true">
                            {{ __('Configuration') }}
                            <svg class="ml-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                <path d="M5.25 7.5L10 12.25L14.75 7.5H5.25Z"></path>
                            </svg>
                        </x-nav-link>

                        <div x-show="openConfig"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform translate-y-1"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform translate-y-1"
                             class="absolute left-0 top-full mt-2 w-56 bg-white rounded-md shadow-lg z-20"
                             x-cloak
                             @mouseenter="clearTimeout(timeout); openConfig = true"
                             @mouseleave="timeout = setTimeout(() => openConfig = false, 200)">
                            <a href="{{ route('availabilities.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Disponibilités') }}
                            </a>
                            <a href="{{ route('practice-locations.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Cabinets & Lieux') }}
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
                            <a href="{{ route('conseils.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Biblio Conseils') }}
                            </a>
                            <a href="{{ route('inventory_items.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Inventaire') }}
                            </a>                            
							<a href="{{ route('digital-trainings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Formation') }}
                            </a>							
							<a href="{{ route('newsletters.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Newsletter') }}
                            </a>
                        </div>
                    </div>

                    <!-- Portail public -->
                    <x-nav-link href="{{ url('/pro/' . auth()->user()->slug) }}"
                                :active="request()->is('pro/' . auth()->user()->slug)"
                                class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Portail') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Dropdown des Paramètres et Notifications -->
            <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-4">
                <!-- Notification Bell Icon -->
                <div class="relative">
                    <button id="notificationButton" class="relative text-gray-700 hover:text-[#854f38] focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full transform translate-x-1/2 -translate-y-1/2">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </button>

                    <!-- Notifications Dropdown -->
                    <div id="notificationsDropdown" class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-md shadow-lg z-50 hidden">
                        <div class="px-4 py-2 border-b border-gray-200 flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Notifications</span>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <form id="markAllAsReadForm" method="POST" action="{{ route('notifications.markAllAsRead') }}">
                                    @csrf
                                    <button type="submit" class="text-sm text-blue-600 hover:underline">Mark all as read</button>
                                </form>
                            @endif
                        </div>
                        <div class="max-h-60 overflow-y-auto">
                            @forelse(auth()->user()->unreadNotifications as $notification)
                                <a href="{{ $notification->data['url'] }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ $notification->data['message'] }}
                                    <br>
                                    <small class="text-gray-500">
                                        {{ $notification->data['amount_paid'] ?? '' }}
                                        @if(!empty($notification->data['amount_paid']) && !empty($notification->data['appointment_date'])) – @endif
                                        {{ $notification->data['appointment_date'] ?? '' }}
                                    </small>
                                </a>
                            @empty
                                <div class="px-4 py-2 text-sm text-gray-500">Aucune notification</div>
                            @endforelse
                        </div>
                        <div class="px-4 py-2 border-t border-gray-200 text-center">
                            <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:underline">Tout voir</a>
                        </div>
                    </div>
                </div>

                <!-- User Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-[#647a0b] bg-white hover:text-[#854f38] focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M5.23 7.21a.75.75 0 011.06.02L10 11.584l3.71-4.354a.75.75 0 111.14.976l-4.25 5a.75.75 0 01-1.14 0l-4.25-5a.75.75 0 01.02-1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="text-[#647a0b] hover:text-[#854f38]">
                            {{ __('Profil') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('profile.license')" class="text-[#647a0b] hover:text-[#854f38]">
                            {{ __('License') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('contact.show')" class="text-[#647a0b] hover:text-[#854f38]">
                            {{ __('Support') }}
                        </x-dropdown-link>

                        <div class="border-t border-gray-100"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" class="text-[#647a0b] hover:text-[#854f38]"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Déconnexion') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Menu Hamburger (Responsive) -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-[#647a0b] hover:text-[#854f38] hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': ! open }"
                              class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': ! open, 'inline-flex': open }"
                              class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
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

            <!-- Clients (mobile) -->
            <div class="px-4 pt-4 pb-1 text-xs font-semibold uppercase text-gray-400">Clients</div>
            <x-responsive-nav-link :href="route('client_profiles.index')"
                                   :active="request()->routeIs('client_profiles.*')"
                                   class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Clients particuliers') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('corporate-clients.index')"
                                   :active="request()->routeIs('corporate-clients.*')"
                                   class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Clients entreprises') }}
            </x-responsive-nav-link>

            <!-- Rendez-vous -->
            <x-responsive-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Rendez-vous') }}
            </x-responsive-nav-link>

            <!-- Comptabilité (mobile) -->
            <div class="px-4 pt-4 pb-1 text-xs font-semibold uppercase text-gray-400">Comptabilité</div>
            <x-responsive-nav-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Factures & Devis') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('receipts.index')" :active="request()->routeIs('receipts.index')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Livre de recettes') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('receipts.caMonthly')" :active="request()->routeIs('receipts.caMonthly')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Statistiques (CA mensuel)') }}
            </x-responsive-nav-link>

            <!-- Config (mobile) -->
            <div class="px-4 pt-4 pb-1 text-xs font-semibold uppercase text-gray-400">Configuration</div>
            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Prestations') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Evénements') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('availabilities.index')" :active="request()->routeIs('availabilities.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Disponibilités') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('practice-locations.index')" :active="request()->routeIs('practice-locations.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Cabinets & Lieux') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('unavailabilities.index')" :active="request()->routeIs('unavailabilities.*')" class="text-[#647a0b] hover:text-[#854f38]">
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
            <x-responsive-nav-link :href="route('conseils.index')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Biblio Conseils') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('inventory_items.index')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Inventaire') }}
            </x-responsive-nav-link>            
			<x-responsive-nav-link :href="route('digital-trainings.index')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Formation') }}
            </x-responsive-nav-link>			
			<x-responsive-nav-link :href="route('newsletters.index')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Newsletter') }}
            </x-responsive-nav-link>

            <!-- Portail -->
            <x-responsive-nav-link href="{{ url('/pro/' . auth()->user()->slug) }}"
                                   :active="request()->is('pro/' . auth()->user()->slug)"
                                   class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Portail') }}
            </x-responsive-nav-link>
        </div>

        <!-- Bloc utilisateur (mobile) -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-[#647a0b]">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-[#647a0b] hover:text-[#854f38]">
                    {{ __('Profil') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.license')" class="text-[#647a0b] hover:text-[#854f38]">
                    {{ __('License') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('contact.show')" class="text-[#647a0b] hover:text-[#854f38]">
                    {{ __('Support') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" class="text-[#647a0b] hover:text-[#854f38]"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Déconnexion') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

@php
    $user = Auth::user();
@endphp

@if($user && $user->is_therapist && $user->license_status === 'expirée')
    <script>
        if (window.location.pathname !== "{{ route('license-tiers.pricing', [], false) }}") {
            window.location.href = "{{ route('license-tiers.pricing') }}";
        }
    </script>
    <div class="alert alert-warning">
        Votre licence a expiré. Veuillez
        <a href="{{ route('license-tiers.pricing') }}">renouveler votre licence</a>
        pour continuer à utiliser l'application.
    </div>
@endif

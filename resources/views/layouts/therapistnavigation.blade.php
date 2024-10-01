<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}"> <!-- Assurez-vous que cette route existe ou remplacez-la par la route appropriée -->
                        <x-application-logo class="block h-12 w-auto" style="color: #647a0b;" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <!-- Clients -->
                    <x-nav-link :href="route('dashboard-pro')" :active="request()->routeIs('dashboard-pro')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Tableau de Bord') }}
                    </x-nav-link>
                                        <x-nav-link :href="route('client_profiles.index')" :active="request()->routeIs('client_profiles.*')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Clients') }}
                    </x-nav-link>
                    
                    <!-- Rendez-vous -->
                    <x-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.*')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Rendez-vous') }}
                    </x-nav-link>
                    
                    <!-- Produits -->
                    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Préstations') }}
                    </x-nav-link>
                    
                    <!-- Disponibilités -->
                    <x-nav-link :href="route('availabilities.index')" :active="request()->routeIs('availabilities.*')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Disponibilités') }}
                    </x-nav-link>
                    
                    <!-- Factures -->
                    <x-nav-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Factures') }}
                    </x-nav-link>
                    

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-[#647a0b] bg-white hover:text-[#854f38] focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Profil -->
                        <x-dropdown-link :href="route('profile.edit')" class="text-[#647a0b] hover:text-[#854f38]">
                            {{ __('Profil') }}
                        </x-dropdown-link>
                        
                        <!-- Informations de l'entreprise -->
                        <x-dropdown-link :href="route('profile.editCompanyInfo')" class="text-[#647a0b] hover:text-[#854f38]">
                            {{ __('Informations de l\'entreprise') }}
                        </x-dropdown-link>

                        <!-- Séparateur -->
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

            <!-- Hamburger Menu (Responsive) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-[#647a0b] hover:text-[#854f38] hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <!-- Clients -->
            <x-responsive-nav-link :href="route('client_profiles.index')" :active="request()->routeIs('client_profiles.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Clients') }}
            </x-responsive-nav-link>
            
            <!-- Rendez-vous -->
            <x-responsive-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Rendez-vous') }}
            </x-responsive-nav-link>
            
            <!-- Produits -->
            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Préstations') }}
            </x-responsive-nav-link>
            
            <!-- Disponibilités -->
            <x-responsive-nav-link :href="route('availabilities.index')" :active="request()->routeIs('availabilities.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Disponibilités') }}
            </x-responsive-nav-link>
            
            <!-- Factures -->
            <x-responsive-nav-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Factures') }}
            </x-responsive-nav-link>
            
            <!-- Notes de Session -->

        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-[#647a0b]">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Profil -->
                <x-responsive-nav-link :href="route('profile.edit')" class="text-[#647a0b] hover:text-[#854f38]">
                    {{ __('Profil') }}
                </x-responsive-nav-link>

                <!-- Informations de l'entreprise -->
                <x-responsive-nav-link :href="route('profile.editCompanyInfo')" class="text-[#647a0b] hover:text-[#854f38]">
                    {{ __('Informations de l\'entreprise') }}
                </x-responsive-nav-link>

                <!-- Déconnexion -->
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
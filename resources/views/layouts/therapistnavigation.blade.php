<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard-pro') }}">
                        <x-application-logo class="block h-12 w-auto" style="color: #647a0b;" />
                    </a>
                </div>

                <!-- Navigation Links -->
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
									<div class="relative group flex items-center">
					<!-- Navigation link for Huiles Essentielles -->
					<x-nav-link :href="route('availabilities.index')" :active="request()->routeIs('availabilities.index')" class="text-[#647a0b] hover:text-[#854f38]">
						{{ __('Configuration') }}
					</x-nav-link>

					<!-- Dropdown Menu (hidden by default) -->
					<div class="absolute left-0 top-full mt-1 w-48 bg-white rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10">
						<a href="{{ route('availabilities.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
							{{ __('Disponibilités') }}
						</a>
						<a href="{{ route('products.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
							{{ __('Prestations') }}
						</a>						
						<a href="{{ route('questionnaires.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
							{{ __('Questionnaires') }}
						</a>						
						<a href="{{ route('profile.editCompanyInfo') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
							{{ __('Informations de l\'entreprise') }}
						</a>
					</div>
				</div>


                    <x-nav-link href="{{ url('/pro/' . auth()->user()->slug) }}" :active="request()->routeIs('invoices.*')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Portail') }}
                    </x-nav-link>
                </div>
            </div>


			
            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-[#647a0b] bg-white hover:text-[#854f38] focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ml-1"></div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Profile -->
                        <x-dropdown-link :href="route('profile.edit')" class="text-[#647a0b] hover:text-[#854f38]">
                            {{ __('Profil') }}
                        </x-dropdown-link>
                        <div class="border-t border-gray-100"></div>

                        <!-- Logout -->
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
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-[#647a0b] hover:text-[#854f38] hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6L18 18" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('client_profiles.index')" :active="request()->routeIs('client_profiles.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Clients') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Rendez-vous') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Prestations') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('availabilities.index')" :active="request()->routeIs('availabilities.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Disponibilités') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('questionnaires.index')" :active="request()->routeIs('questionnaires.*')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Questionnaires') }}
            </x-responsive-nav-link>
			<x-responsive-nav-link :href="route('profile.editCompanyInfo')" class="text-[#647a0b] hover:text-[#854f38]">
                    {{ __('Informations de l\'entreprise') }}
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

<!-- Trial CTA Banner -->
@if(Auth::user()->isTherapist() && Auth::user()->license && Auth::user()->license->licenseTier->is_trial)
    @php
        $trialExpirationDate = \Carbon\Carbon::parse(Auth::user()->license->expiration_date);
        $remainingDays = (int) $trialExpirationDate->diffInDays(now()); // Make sure it's an integer
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
                <a href="{{ route('dashboard-pro') }}" class="inline-block bg-blue-600 text-white font-semibold py-2 px-4 rounded-md shadow hover:bg-blue-700 transition duration-200">
                    {{ __('Mettre à niveau') }}
                </a>
            </div>
        </div>
    </div>

    <script>
        // Function to get today's date in YYYY-MM-DD format
        function getTodayDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = (today.getMonth() + 1).toString().padStart(2, '0');
            const day = today.getDate().toString().padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Check if the banner has been dismissed today
        const lastDismissDate = localStorage.getItem('trial-banner-dismissed-date');
        const today = getTodayDate();

        if (lastDismissDate !== today) {
            // Show the banner if it hasn't been dismissed today
            document.getElementById('trial-banner').style.display = 'block';
        }

        // Handle banner dismissal
        document.getElementById('dismiss-trial-banner').addEventListener('click', function () {
            // Hide the banner and save the current date as the dismissal date
            document.getElementById('trial-banner').style.display = 'none';
            localStorage.setItem('trial-banner-dismissed-date', today);
        });
    </script>
@endif

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('welcome') }}">
                        <x-application-logo class="block h-12 w-auto" style="color: #647a0b;" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('huilehes.index')" :active="request()->routeIs('huilehes.index')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Huiles Essentielles') }}
                    </x-nav-link>
                    <x-nav-link :href="route('huilehvs.index')" :active="request()->routeIs('huilehvs.index')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Huiles Végétales') }}
                    </x-nav-link>
                    <x-nav-link :href="route('tisanes.index')" :active="request()->routeIs('tisanes.index')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Tisanes') }}
                    </x-nav-link>
                    <x-nav-link :href="route('recettes.index')" :active="request()->routeIs('recettes.index')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Recettes') }}
                    </x-nav-link>                    
					<x-nav-link :href="route('blog.index')" :active="request()->routeIs('blog.index')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Articles') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @if(Auth::check())
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
                            <x-dropdown-link :href="route('profile.edit')" class="text-[#647a0b] hover:text-[#854f38]">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('dashboard')" class="text-[#647a0b] hover:text-[#854f38]">
                                {{ __('Favoris') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" class="text-[#647a0b] hover:text-[#854f38]"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-sm font-medium text-[#647a0b] hover:text-[#854f38]">
                            {{ __('Se connecter') }}
                        </a>
                        <a href="{{ route('register') }}" class="text-sm font-medium text-[#647a0b] hover:text-[#854f38]">
                            {{ __('S\'inscrire') }}
                        </a>
                    </div>
                @endif
            </div>

            <!-- Hamburger -->
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
            <x-responsive-nav-link :href="route('huilehes.index')" :active="request()->routeIs('huilehes.index')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Huiles Essentielles') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('huilehvs.index')" :active="request()->routeIs('huilehvs.index')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Huiles Végétales') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('tisanes.index')" :active="request()->routeIs('tisanes.index')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Tisanes') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('recettes.index')" :active="request()->routeIs('recettes.index')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Recettes') }}
            </x-responsive-nav-link>            
			<x-responsive-nav-link :href="route('blog.index')" :active="request()->routeIs('blog.index')" class="text-[#647a0b] hover:text-[#854f38]">
                {{ __('Articles') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            @if(Auth::check())
                <div class="px-4">
                    <div class="font-medium text-base text-[#647a0b]">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('dashboard')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Favoris') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" class="text-[#647a0b] hover:text-[#854f38]"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else


                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('Se connecter') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')" class="text-[#647a0b] hover:text-[#854f38]">
                        {{ __('S\'inscrire') }}
                    </x-responsive-nav-link>
                </div>
            @endif
        </div>
    </div>
</nav>

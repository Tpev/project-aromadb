<x-client-app-layout :title="'Connexion client'">
    <h1 class="text-xl font-bold mb-6">Connexion</h1>

    <form method="POST" action="{{ route('client.login.post') }}" class="max-w-md">
        @csrf
        <label class="block mb-1 font-semibold">E-mail</label>
        <input name="email" type="email" required class="w-full rounded border-gray-300 mb-4" />

        <label class="block mb-1 font-semibold">Mot de passe</label>
        <x-password-toggle-input
            id="client_login_password"
            name="password"
            required
            autocomplete="current-password"
            class="w-full rounded border-gray-300 mb-6 focus:border-[#647a0b] focus:ring-[#647a0b]"
        />

        <button class="bg-lime-700 text-white px-4 py-2 rounded">Se connecter</button>

        @error('email') <p class="text-red-600 mt-2">{{ $message }}</p> @enderror
    </form>
	
	<div class="mt-4 space-y-2 text-sm">
        <div>
            <a href="{{ route('client.password.request') }}" class="text-[#647a0b] underline">Mot de passe oublié ?</a>
        </div>
        <div>
            <span class="text-gray-600">Vous êtes praticien ?</span>
            <a href="{{ route('login.practitioner') }}" class="text-[#854f38] underline">Accéder à l’espace praticien</a>
        </div>
        <div>
            <a href="{{ route('login') }}" class="text-gray-500 underline">Retour au choix de connexion</a>
        </div>
    </div>

</x-client-app-layout>

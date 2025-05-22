<x-client-app-layout :title="'Connexion client'">
    <h1 class="text-xl font-bold mb-6">Connexion</h1>

    <form method="POST" action="{{ route('client.login.post') }}" class="max-w-md">
        @csrf
        <label class="block mb-1 font-semibold">E-mail</label>
        <input name="email" type="email" required class="w-full rounded border-gray-300 mb-4" />

        <label class="block mb-1 font-semibold">Mot de passe</label>
        <input name="password" type="password" required class="w-full rounded border-gray-300 mb-6" />

        <button class="bg-lime-700 text-white px-4 py-2 rounded">Se connecter</button>

        @error('email') <p class="text-red-600 mt-2">{{ $message }}</p> @enderror
    </form>
</x-client-app-layout>

<x-client-app-layout :title="'Créer votre mot de passe'">
    <h1 class="text-xl font-bold mb-6">Bienvenue {{ $name }} !</h1>

    <form method="POST" action="{{ route('client.setup.store', $token) }}" class="max-w-md">
        @csrf
        <label class="block mb-1 font-semibold">E-mail</label>
        <input type="email" value="{{ $email }}" disabled class="w-full rounded border-gray-300 mb-4" />

        <label class="block mb-1 font-semibold">Mot de passe</label>
        <input name="password" type="password" required class="w-full rounded border-gray-300 mb-4" />

        <label class="block mb-1 font-semibold">Confirmez</label>
        <input name="password_confirmation" type="password" required class="w-full rounded border-gray-300 mb-6" />

        <button class="bg-lime-700 text-white px-4 py-2 rounded">Enregistrer</button>
        @error('password') <p class="text-red-600 mt-2">{{ $message }}</p> @enderror
    </form>
</x-client-app-layout>

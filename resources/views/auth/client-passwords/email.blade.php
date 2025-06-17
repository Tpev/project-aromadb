<x-guest-layout>
    <div class="max-w-md mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-center text-[#647a0b] mb-4">
            🔐 Réinitialiser le mot de passe
        </h1>

        @if (session('status'))
            <div class="mb-4 text-green-600 font-semibold">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('client.password.email') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="block font-semibold mb-1">Adresse email</label>
                <input id="email" name="email" type="email" required autofocus class="w-full border border-gray-300 rounded px-3 py-2" />
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="text-right">
                <button class="bg-[#647a0b] text-white px-4 py-2 rounded hover:bg-[#4e6407]">
                    Envoyer le lien
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>

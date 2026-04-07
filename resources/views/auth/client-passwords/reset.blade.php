<x-guest-layout>
    <div class="max-w-md mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-center text-[#647a0b] mb-4">
            🔐 Nouveau mot de passe
        </h1>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">Merci de corriger les points ci-dessous :</p>
                <ul class="mt-2 list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('client.password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-4">
                <label for="email" class="block font-semibold mb-1">Adresse email</label>
                <input id="email" name="email" type="email" required value="{{ old('email', $email) }}" class="w-full border border-gray-300 rounded px-3 py-2" />
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block font-semibold mb-1">Nouveau mot de passe</label>
                <x-password-toggle-input
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:border-[#647a0b] focus:ring-[#647a0b]"
                />
                <p class="mt-2 text-xs text-gray-500">
                    8 caractères minimum. Pour plus de sécurité, utilisez aussi une majuscule, une minuscule et un chiffre.
                </p>
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="block font-semibold mb-1">Confirmer le mot de passe</label>
                <x-password-toggle-input
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:border-[#647a0b] focus:ring-[#647a0b]"
                />
                <p class="mt-2 text-xs text-gray-500">
                    Saisissez exactement le même mot de passe pour confirmer.
                </p>
                @error('password_confirmation')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="text-right">
                <button class="bg-[#647a0b] text-white px-4 py-2 rounded hover:bg-[#4e6407]">
                    Réinitialiser
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>

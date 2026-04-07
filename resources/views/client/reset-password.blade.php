<x-client-app-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#f7f8f3] px-4">
        <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

            <h1 class="text-xl font-semibold text-gray-800">
                Nouveau mot de passe
            </h1>

            <p class="mt-2 text-sm text-gray-600">
                Choisissez un nouveau mot de passe pour votre espace client.
            </p>

            @if ($errors->any())
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold">Merci de corriger les points ci-dessous :</p>
                    <ul class="mt-2 list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('client.password.update') }}" class="mt-6 space-y-4">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Adresse e-mail
                    </label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $email) }}"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nouveau mot de passe
                    </label>
                    <x-password-toggle-input
                        id="client_reset_password"
                        name="password"
                        required
                        autocomplete="new-password"
                        class="w-full rounded-lg border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"
                    />
                    <p class="mt-2 text-xs text-gray-500">
                        8 caractères minimum. Pour plus de sécurité, utilisez aussi une majuscule, une minuscule et un chiffre.
                    </p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmation
                    </label>
                    <x-password-toggle-input
                        id="client_reset_password_confirmation"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="w-full rounded-lg border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"
                    />
                    <p class="mt-2 text-xs text-gray-500">
                        Saisissez exactement le même mot de passe pour confirmer.
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center rounded-lg bg-[#647a0b] px-4 py-2 font-semibold text-white hover:opacity-90 transition"
                >
                    Enregistrer
                </button>
            </form>

        </div>
    </div>
</x-client-app-layout>

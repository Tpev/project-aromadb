<x-client-app-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#f7f8f3] px-4">
        <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

            <h1 class="text-xl font-semibold text-gray-800">
                Bienvenue {{ $name }}
            </h1>

            <p class="mt-2 text-sm text-gray-600">
                Choisissez un mot de passe pour activer votre espace client.
            </p>

            <form method="POST" action="{{ route('client.setup.store', $token) }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Adresse e-mail
                    </label>
                    <input
                        type="email"
                        value="{{ $email }}"
                        disabled
                        class="w-full rounded-lg border-gray-200 bg-gray-100 text-gray-600"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Mot de passe
                    </label>
                    <input
                        type="password"
                        name="password"
                        required
                        autofocus
                        class="w-full rounded-lg border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmation
                    </label>
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center rounded-lg bg-[#647a0b] px-4 py-2 font-semibold text-white hover:opacity-90 transition"
                >
                    Activer mon espace client
                </button>
            </form>

        </div>
    </div>
</x-client-app-layout>

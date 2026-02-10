<x-client-app-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#f7f8f3] px-4">
        <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

            <h1 class="text-xl font-semibold text-gray-800">
                Mot de passe oublié
            </h1>

            <p class="mt-2 text-sm text-gray-600">
                Entrez votre adresse e-mail pour recevoir un lien de réinitialisation.
            </p>

            @if (session('status'))
                <div class="mt-4 rounded-lg bg-green-50 border border-green-200 p-3 text-green-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('client.password.email') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Adresse e-mail
                    </label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full rounded-lg border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center rounded-lg bg-[#647a0b] px-4 py-2 font-semibold text-white hover:opacity-90 transition"
                >
                    Envoyer le lien
                </button>

                <div class="pt-2 text-center text-sm">
                    <a href="{{ route('client.login') }}" class="text-gray-600 hover:text-gray-900 underline">
                        Retour à la connexion
                    </a>
                </div>
            </form>

        </div>
    </div>
</x-client-app-layout>

<x-mobile-client-layout title="Connexion client" :show-nav="false">
    <div class="mx-auto flex min-h-[calc(100vh-64px)] max-w-lg items-center px-4 py-8">
        <div class="w-full space-y-6">
            <div class="space-y-3">
                <span class="inline-flex items-center rounded-full bg-[#f7faef] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#647a0b]">
                    Espace client
                </span>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Connexion</h1>
                <p class="text-sm leading-6 text-gray-600">
                    Retrouvez vos documents, vos messages, vos rendez-vous et vos communautes depuis votre telephone.
                </p>
            </div>

            @if(session('status'))
                <div class="rounded-xl border border-[#dfe8c8] bg-[#f7faef] px-4 py-3 text-sm text-[#4f6508]">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('mobile.client.login.store') }}" class="space-y-4 rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
                @csrf

                <div>
                    <label for="email" class="text-sm font-semibold text-gray-800">E-mail</label>
                    <input id="email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           autocomplete="email"
                           required
                           class="mt-2 w-full rounded-xl border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <label for="password" class="text-sm font-semibold text-gray-800">Mot de passe</label>
                    <x-password-toggle-input
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="mt-2 w-full rounded-xl border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]">
                    Rester connecte
                </label>

                <button type="submit"
                        class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-[#647a0b] px-4 py-3 text-sm font-semibold text-white shadow-sm">
                    Se connecter
                </button>
            </form>

            <div class="space-y-2 text-sm">
                <a href="{{ route('client.password.request') }}" class="block text-[#647a0b] underline">Mot de passe oublie ?</a>
                <a href="{{ route('mobile.login') }}" class="block text-gray-500 underline">Je suis praticien</a>
            </div>
        </div>
    </div>
</x-mobile-client-layout>

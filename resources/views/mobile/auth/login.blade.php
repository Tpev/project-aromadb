<x-mobile-layout title="Connexion">
    <div class="min-h-[calc(100vh-5rem)] px-5 py-6"
         style="background: radial-gradient(circle at top, #fffaf3 0, #f7f4ec 44%, #eee7dc 100%);">
        <div class="mx-auto flex w-full max-w-sm flex-col gap-5">
            <div class="pt-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#854f38]">
                    Espace praticien
                </p>
                <h1 class="mt-2 text-2xl font-extrabold text-[#647a0b]">
                    Connexion
                </h1>
            </div>

            <form method="POST"
                  action="{{ route('mobile.login.store') }}"
                  class="space-y-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                @csrf

                <div class="space-y-1.5">
                    <label for="mobile-email" class="text-sm font-semibold text-gray-700">Email</label>
                    <input id="mobile-email"
                           type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           autocomplete="email"
                           inputmode="email"
                           class="h-12 w-full rounded-lg border border-gray-300 px-3 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    @error('email')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="mobile-password" class="text-sm font-semibold text-gray-700">Mot de passe</label>
                    <input id="mobile-password"
                           type="password"
                           name="password"
                           required
                           autocomplete="current-password"
                           class="h-12 w-full rounded-lg border border-gray-300 px-3 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    @error('password')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-3 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]">
                    Se souvenir de moi
                </label>

                <button type="submit"
                        class="flex h-12 w-full items-center justify-center rounded-lg bg-[#647a0b] text-base font-semibold text-white active:scale-[0.99]">
                    Se connecter
                </button>
            </form>

            <div class="space-y-3 text-center text-sm text-gray-600">
                <a href="{{ route('password.request') }}" class="font-medium underline">
                    Mot de passe oublie ?
                </a>

                <p>
                    Pas encore de compte ?
                    <a href="{{ route('register-pro') }}" class="font-semibold text-[#647a0b] underline">
                        Creer un compte praticien
                    </a>
                </p>
            </div>
        </div>
    </div>
</x-mobile-layout>

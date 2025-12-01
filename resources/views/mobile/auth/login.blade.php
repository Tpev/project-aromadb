{{-- resources/views/mobile/auth/login.blade.php --}}
<x-mobile-layout title="Connexion">

    <div class="min-h-screen flex flex-col px-5 py-8 justify-center"
         style="background: radial-gradient(circle at top, #fffaf3 0, #f7f4ec 40%, #eee7dc 100%);">

        <div class="w-full max-w-sm mx-auto space-y-6">

            <h1 class="text-2xl font-extrabold text-center text-[#647a0b]">
                Connexion
            </h1>

            <form method="POST" action="{{ route('mobile.login.store') }}" class="space-y-5">
                @csrf

                {{-- EMAIL --}}
                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email"
                           value="{{ old('email') }}"
                           required autofocus
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-[#647a0b]">
                    @error('email')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- PASSWORD --}}
                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700">Mot de passe</label>
                    <input type="password" name="password"
                           required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-[#647a0b]">
                    @error('password')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- REMEMBER ME --}}
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300">
                    Se souvenir de moi
                </label>

                {{-- SUBMIT --}}
                <button type="submit"
                        class="w-full bg-[#647a0b] text-white py-3 rounded-xl font-semibold text-base hover:bg-[#8ea633]">
                    Se connecter
                </button>

            </form>

            {{-- LINKS --}}
            <div class="text-center text-sm text-gray-600 mt-4">
                <a href="{{ route('password.request') }}" class="underline">Mot de passe oublié ?</a>
            </div>

            <div class="text-center text-sm text-gray-600">
                Pas encore de compte ?
                <a href="{{ route('register') }}" class="underline text-[#647a0b]">
                    Créer un compte
                </a>
            </div>

        </div>

    </div>

</x-mobile-layout>

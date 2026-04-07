<x-guest-layout>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <div class="rounded-3xl border border-[#dbe4ba] bg-white p-8 shadow-sm">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[#854f38]">
                    AromaMade
                </p>
                <h1 class="mt-3 text-3xl font-bold text-[#647a0b]">
                    Choisissez votre espace de connexion
                </h1>
                <p class="mt-3 text-base text-gray-600">
                    Pour vous rediriger vers le bon formulaire, indiquez si vous êtes client ou praticien.
                </p>
            </div>

            <div class="mt-10 grid gap-5 md:grid-cols-2">
                <a href="{{ route('client.login') }}"
                   class="group rounded-2xl border border-gray-200 bg-[#f7f8f3] p-6 transition hover:-translate-y-0.5 hover:border-[#647a0b] hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#e8efd0] text-2xl">
                            👤
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Je suis client</h2>
                            <p class="text-sm text-gray-600">Accéder à mes rendez-vous, documents et messages.</p>
                        </div>
                    </div>

                    <div class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-[#647a0b] ring-1 ring-[#dbe4ba]">
                        Ouvrir l’espace client
                        <span aria-hidden="true" class="transition group-hover:translate-x-0.5">→</span>
                    </div>
                </a>

                <a href="{{ route('login.practitioner') }}"
                   class="group rounded-2xl border border-gray-200 bg-[#fcf8f6] p-6 transition hover:-translate-y-0.5 hover:border-[#854f38] hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#f1e2db] text-2xl">
                            🌿
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Je suis praticien</h2>
                            <p class="text-sm text-gray-600">Accéder à mon agenda, mes clients et mon tableau de bord.</p>
                        </div>
                    </div>

                    <div class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-[#854f38] ring-1 ring-[#ead7cd]">
                        Ouvrir l’espace praticien
                        <span aria-hidden="true" class="transition group-hover:translate-x-0.5">→</span>
                    </div>
                </a>
            </div>

            <p class="mt-8 text-center text-sm text-gray-500">
                Vous n’avez pas encore de compte praticien ?
                <a href="{{ route('register-pro') }}" class="font-semibold text-[#647a0b] underline underline-offset-2">
                    Créer un compte
                </a>
            </p>
        </div>
    </div>
</x-guest-layout>

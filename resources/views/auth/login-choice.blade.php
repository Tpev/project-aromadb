<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Choix de connexion - {{ config('app.name', 'AromaMade') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f4f1e8] font-sans text-slate-900 antialiased">
        <div class="relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(100,122,11,0.18),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(133,79,56,0.16),_transparent_34%)]"></div>
            <div class="absolute left-0 top-20 h-64 w-64 rounded-full bg-[#dce7bd]/40 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-[#ead9cf]/60 blur-3xl"></div>

            <main class="relative mx-auto flex min-h-screen max-w-7xl items-center px-4 py-10 sm:px-6 lg:px-8">
                <div class="w-full overflow-hidden rounded-[32px] border border-white/70 bg-white/85 shadow-[0_30px_80px_rgba(52,60,28,0.12)] backdrop-blur">
                    <div class="grid lg:grid-cols-[1.15fr_0.85fr]">
                        <section class="relative overflow-hidden border-b border-[#ebe6da] px-6 py-8 sm:px-10 sm:py-12 lg:border-b-0 lg:border-r">
                            <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(247,248,243,0.96),rgba(255,255,255,0.9))]"></div>
                            <div class="absolute -left-20 top-24 h-52 w-52 rounded-full border border-[#dbe4ba] bg-[#f3f7e7]/80"></div>
                            <div class="absolute bottom-[-60px] right-[-40px] h-56 w-56 rounded-full border border-[#f0ddd3] bg-[#fcf5f1]/90"></div>

                            <div class="relative">
                                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                                    <x-application-logo class="h-16 w-16" />
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#854f38]">
                                            Aromamade
                                        </p>
                                        <p class="mt-1 text-sm text-slate-500">
                                            Connexion guidée
                                        </p>
                                    </div>
                                </a>

                                <div class="mt-10 max-w-xl">
                                    <p class="inline-flex items-center rounded-full border border-[#d9e3b7] bg-[#f4f8e9] px-4 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-[#647a0b]">
                                        Un seul clic, le bon espace
                                    </p>

                                    <h1 class="mt-6 max-w-lg text-4xl font-extrabold leading-tight text-slate-900 sm:text-5xl">
                                        Choisissez votre espace de connexion sans vous tromper
                                    </h1>

                                    <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                                        Selon votre profil, nous vous envoyons soit vers l’espace client, soit vers l’espace praticien. Le but est simple : arriver tout de suite sur le bon formulaire.
                                    </p>
                                </div>

                                <div class="mt-10 grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-2xl border border-[#dde5c6] bg-white/80 p-5 shadow-sm">
                                        <p class="text-sm font-semibold text-[#647a0b]">Espace client</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-600">
                                            Pour consulter vos rendez-vous, messages, documents et accéder à votre portail personnel.
                                        </p>
                                    </div>
                                    <div class="rounded-2xl border border-[#ead9cf] bg-white/80 p-5 shadow-sm">
                                        <p class="text-sm font-semibold text-[#854f38]">Espace praticien</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-600">
                                            Pour gérer votre agenda, vos clients, vos contenus, votre portail pro et votre activité.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="bg-[linear-gradient(180deg,rgba(255,255,255,0.94),rgba(248,247,241,0.98))] px-6 py-8 sm:px-8 sm:py-10 lg:px-10 lg:py-12">
                            <div class="mx-auto max-w-xl">
                                <div class="rounded-[28px] border border-[#ece7db] bg-white/90 p-4 shadow-[0_18px_45px_rgba(35,35,35,0.06)] sm:p-5">
                                    <a href="{{ route('client.login') }}"
                                       class="group block rounded-[24px] border border-[#dfe7ca] bg-[linear-gradient(135deg,#f8fbf0,#f1f5e5)] p-6 transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm ring-1 ring-[#dce7bd]">
                                                👤
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center justify-between gap-3">
                                                    <h2 class="text-2xl font-bold text-slate-900">Je suis client</h2>
                                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#647a0b] ring-1 ring-[#d8e2b7]">
                                                        Espace client
                                                    </span>
                                                </div>

                                                <p class="mt-3 text-sm leading-6 text-slate-600 sm:text-base">
                                                    Accéder à mes rendez-vous, à mes documents, à mes messages et aux informations partagées par mon praticien.
                                                </p>

                                                <div class="mt-5 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-[#647a0b] ring-1 ring-[#d8e2b7]">
                                                    Ouvrir l’espace client
                                                    <span aria-hidden="true" class="transition group-hover:translate-x-0.5">→</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>

                                    <a href="{{ route('login.practitioner') }}"
                                       class="group mt-4 block rounded-[24px] border border-[#ecd8cf] bg-[linear-gradient(135deg,#fffaf7,#fbf0ea)] p-6 transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm ring-1 ring-[#efd9cf]">
                                                🌿
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center justify-between gap-3">
                                                    <h2 class="text-2xl font-bold text-slate-900">Je suis praticien</h2>
                                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#854f38] ring-1 ring-[#ecd8cf]">
                                                        Espace praticien
                                                    </span>
                                                </div>

                                                <p class="mt-3 text-sm leading-6 text-slate-600 sm:text-base">
                                                    Accéder à mon agenda, à mes clients, à mon tableau de bord, à mes ventes et à tous mes outils AromaMade PRO.
                                                </p>

                                                <div class="mt-5 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-[#854f38] ring-1 ring-[#ecd8cf]">
                                                    Ouvrir l’espace praticien
                                                    <span aria-hidden="true" class="transition group-hover:translate-x-0.5">→</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="mt-6 rounded-2xl border border-[#ece7db] bg-white/70 px-5 py-4 text-sm text-slate-600">
                                    Vous n’avez pas encore de compte praticien ?
                                    <a href="{{ route('register-pro') }}" class="font-semibold text-[#647a0b] underline underline-offset-2">
                                        Créer un compte
                                    </a>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>

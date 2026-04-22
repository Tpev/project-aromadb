<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#854f38]">Communautés privées</p>
                <h2 class="mt-2 text-2xl font-semibold text-[#647a0b]">Animez vos groupes d’échange</h2>
                <p class="mt-2 max-w-2xl text-sm text-gray-600">Créez des espaces privés sur invitation, mettez en avant vos annonces et partagez des ressources dans un cadre plus chaleureux qu’une simple messagerie.</p>
            </div>
            <a href="{{ route('communities.create') }}" class="inline-flex items-center justify-center rounded-full bg-[#647a0b] px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-lime-900/10 transition hover:-translate-y-0.5 hover:bg-[#55670a]">
                Créer une communauté
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <section class="overflow-hidden rounded-[2rem] border border-[#dfe7c7] bg-[radial-gradient(circle_at_top_left,_rgba(216,230,181,0.55),_rgba(255,255,255,0.95)_48%,_rgba(245,241,231,0.95)_100%)] p-8 shadow-sm">
            <div class="grid gap-6 lg:grid-cols-[1.4fr_0.9fr] lg:items-center">
                <div>
                    <div class="inline-flex items-center rounded-full border border-[#d6dfbc] bg-white/75 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.22em] text-[#647a0b]">
                        Communautés AromaMade
                    </div>
                    <h3 class="mt-5 max-w-2xl text-3xl font-semibold tracking-tight text-gray-900">Faites vivre vos accompagnements dans un espace plus structuré et plus premium.</h3>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-gray-600">Chaque communauté reste fermée, réservée à vos invités, avec des salons pour distinguer annonces, discussion et ressources clés du groupe.</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-3xl bg-white/85 p-5 shadow-sm ring-1 ring-[#ebf0dd] backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Communautés</p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $communities->count() }}</p>
                    </div>
                    <div class="rounded-3xl bg-white/85 p-5 shadow-sm ring-1 ring-[#ebf0dd] backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Membres actifs</p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $communities->sum('active_members_count') }}</p>
                    </div>
                    <div class="rounded-3xl bg-white/85 p-5 shadow-sm ring-1 ring-[#ebf0dd] backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Salons</p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $communities->sum('channels_count') }}</p>
                    </div>
                    <div class="rounded-3xl bg-white/85 p-5 shadow-sm ring-1 ring-[#ebf0dd] backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Messages</p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $communities->sum('messages_count') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($communities as $community)
                <article class="group overflow-hidden rounded-[2rem] border border-[#e8eadf] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg hover:shadow-lime-900/5">
                    <div class="bg-[linear-gradient(135deg,_rgba(236,243,219,0.95),_rgba(255,255,255,0.95))] px-6 pb-5 pt-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#647a0b] text-sm font-semibold text-white shadow-sm shadow-lime-900/15">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($community->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="truncate text-lg font-semibold text-gray-900">{{ $community->name }}</h3>
                                        <p class="mt-1 text-xs uppercase tracking-[0.18em] text-gray-500">{{ $community->is_archived ? 'Archivée' : 'Active' }}</p>
                                    </div>
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $community->is_archived ? 'bg-gray-100 text-gray-600' : 'bg-lime-100 text-lime-700' }}">
                                {{ $community->is_archived ? 'En pause' : 'Ouverte' }}
                            </span>
                        </div>
                        <p class="mt-5 line-clamp-3 text-sm leading-7 text-gray-600">{{ $community->description ?: 'Aucune description pour le moment. Utilisez cette communauté pour publier des annonces, partager des ressources et animer vos échanges.' }}</p>
                    </div>

                    <div class="px-6 py-5">
                        <dl class="grid grid-cols-2 gap-3 text-sm text-gray-600">
                            <div class="rounded-2xl bg-[#f8f9f4] px-4 py-3">
                                <dt class="text-xs uppercase tracking-[0.16em] text-gray-500">Actifs</dt>
                                <dd class="mt-2 text-2xl font-semibold text-gray-900">{{ $community->active_members_count }}</dd>
                            </div>
                            <div class="rounded-2xl bg-[#f8f9f4] px-4 py-3">
                                <dt class="text-xs uppercase tracking-[0.16em] text-gray-500">Invités</dt>
                                <dd class="mt-2 text-2xl font-semibold text-gray-900">{{ $community->invited_members_count }}</dd>
                            </div>
                            <div class="rounded-2xl bg-[#f8f9f4] px-4 py-3">
                                <dt class="text-xs uppercase tracking-[0.16em] text-gray-500">Salons</dt>
                                <dd class="mt-2 text-2xl font-semibold text-gray-900">{{ $community->channels_count }}</dd>
                            </div>
                            <div class="rounded-2xl bg-[#f8f9f4] px-4 py-3">
                                <dt class="text-xs uppercase tracking-[0.16em] text-gray-500">Messages</dt>
                                <dd class="mt-2 text-2xl font-semibold text-gray-900">{{ $community->messages_count }}</dd>
                            </div>
                        </dl>

                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center rounded-full bg-[#647a0b] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#55670a]">
                                Ouvrir la communauté
                            </a>
                            <a href="{{ route('communities.manage', $community) }}" class="inline-flex items-center rounded-full border border-[#d7ddc8] px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-[#647a0b] hover:text-[#647a0b]">
                                Gérer
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="md:col-span-2 xl:col-span-3 overflow-hidden rounded-[2rem] border border-dashed border-[#cfd8b5] bg-[linear-gradient(135deg,_rgba(247,250,239,1),_rgba(255,255,255,1))] p-12 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-2xl shadow-sm shadow-lime-900/10">#</div>
                    <h3 class="mt-6 text-xl font-semibold text-[#647a0b]">Aucune communauté pour le moment</h3>
                    <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-gray-600">Créez un premier espace privé pour rassembler vos clients autour d’un accompagnement, d’un programme ou d’un groupe de discussion encadré.</p>
                    <a href="{{ route('communities.create') }}" class="mt-6 inline-flex items-center rounded-full bg-[#647a0b] px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-lime-900/10 transition hover:-translate-y-0.5 hover:bg-[#55670a]">
                        Créer ma première communauté
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>

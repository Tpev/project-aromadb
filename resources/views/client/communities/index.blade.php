<x-client-app-layout>
    <div class="mx-auto max-w-6xl space-y-8 p-4 sm:p-6 lg:p-8">
        <section class="overflow-hidden rounded-[2rem] border border-[#dfe7c7] bg-[radial-gradient(circle_at_top_left,_rgba(224,235,198,0.72),_rgba(255,255,255,0.95)_45%,_rgba(244,240,231,0.95)_100%)] p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#854f38]">Espace privé</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-gray-900">Mes communautés</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-gray-600">Retrouvez ici les espaces de discussion privés auxquels votre praticien vous a invité, avec des salons lisibles, des ressources partagées et des annonces bien mises en avant.</p>
                </div>
                <a href="{{ route('client.home') }}" class="inline-flex items-center rounded-full border border-[#d7ddc8] bg-white/90 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:border-[#647a0b] hover:text-[#647a0b]">
                    Retour à l’accueil
                </a>
            </div>
        </section>

        @if($pendingInvites->isNotEmpty())
            <section class="rounded-[2rem] border border-amber-200 bg-[linear-gradient(135deg,_rgba(255,248,230,1),_rgba(255,255,255,1))] p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Invitations en attente</h2>
                        <p class="mt-2 text-sm leading-7 text-gray-600">Chaque invitation reste visible ici jusqu’à votre réponse. Une fois acceptée, la communauté apparaît dans vos accès actifs.</p>
                    </div>
                    <span class="rounded-full bg-white px-4 py-1.5 text-sm font-semibold text-amber-700 shadow-sm">{{ $pendingInvites->count() }} en attente</span>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach($pendingInvites as $membership)
                        <article class="rounded-[1.8rem] border border-amber-200/70 bg-white/90 p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $membership->group->name }}</h3>
                                    <p class="mt-2 text-sm leading-7 text-gray-600">{{ $membership->group->description ?: 'Votre praticien vous invite à rejoindre cet espace de discussion privé.' }}</p>
                                </div>
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 shadow-sm">+</span>
                            </div>
                            <div class="mt-5 rounded-2xl bg-[#fffaf0] px-4 py-3 ring-1 ring-amber-100">
                                <p class="text-xs uppercase tracking-[0.16em] text-amber-700">Praticien</p>
                                <p class="mt-2 text-sm font-semibold text-gray-900">{{ $membership->group->user->company_name ?? $membership->group->user->name }}</p>
                            </div>
                            <form method="POST" action="{{ route('client.communities.accept', $membership->group) }}" class="mt-5">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-full bg-[#647a0b] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-lime-900/10 transition hover:-translate-y-0.5 hover:bg-[#55670a]">
                                    Rejoindre la communauté
                                </button>
                            </form>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="rounded-[2rem] border border-[#eceee5] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Communautés actives</h2>
                    <p class="mt-2 text-sm leading-7 text-gray-600">Accédez à vos salons, relisez les annonces du praticien et poursuivez les échanges du groupe dans un environnement plus structuré.</p>
                </div>
                <span class="rounded-full bg-[#f7faef] px-4 py-1.5 text-sm font-semibold text-[#647a0b]">{{ $communities->count() }} active(s)</span>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($communities as $membership)
                    <article class="group overflow-hidden rounded-[1.8rem] border border-[#eceee5] bg-[linear-gradient(180deg,_rgba(249,250,246,1),_rgba(255,255,255,1))] p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg hover:shadow-lime-900/5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#647a0b] text-sm font-semibold text-white shadow-sm shadow-lime-900/15">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($membership->group->name, 0, 2)) }}
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ $membership->group->name }}</h3>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-500 shadow-sm">{{ $membership->group->channels->count() }} salon(s)</span>
                        </div>
                        <p class="mt-4 line-clamp-3 text-sm leading-7 text-gray-600">{{ $membership->group->description ?: 'Communauté privée animée par votre praticien.' }}</p>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl bg-white/80 px-4 py-3 ring-1 ring-[#eef1e5]">
                                <p class="text-xs uppercase tracking-[0.16em] text-gray-500">Praticien</p>
                                <p class="mt-2 text-sm font-semibold text-gray-900">{{ $membership->group->user->company_name ?? $membership->group->user->name }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/80 px-4 py-3 ring-1 ring-[#eef1e5]">
                                <p class="text-xs uppercase tracking-[0.16em] text-gray-500">Ressources</p>
                                <p class="mt-2 text-sm font-semibold text-gray-900">{{ $membership->group->channels->filter(fn ($channel) => $channel->pinnedMessage)->count() }} épinglée(s)</p>
                            </div>
                        </div>
                        <a href="{{ route('client.communities.show', $membership->group) }}" class="mt-5 inline-flex items-center rounded-full bg-[#647a0b] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#55670a]">
                            Ouvrir la communauté
                        </a>
                    </article>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-[1.8rem] border border-dashed border-[#d7dccb] bg-[#fafbf7] px-8 py-14 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-white text-xl font-semibold text-[#647a0b] shadow-sm">#</div>
                        <p class="mt-5 text-base font-semibold text-gray-900">Aucune communauté active pour le moment</p>
                        <p class="mt-2 text-sm leading-7 text-gray-500">Lorsque votre praticien vous invitera, la communauté apparaîtra ici et vous pourrez la rejoindre depuis cette page.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-client-app-layout>

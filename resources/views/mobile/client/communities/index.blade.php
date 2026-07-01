<x-mobile-client-layout title="Communautes">
    <div class="mx-auto max-w-lg space-y-5 px-4 py-5">
        <section class="space-y-2">
            <a href="{{ route('mobile.client.home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#647a0b]">
                <i class="fas fa-chevron-left text-xs"></i>
                Accueil
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Communautes</h1>
            <p class="text-sm leading-6 text-gray-600">Vos groupes prives, salons et annonces partages par votre praticien.</p>
        </section>

        @if(session('success'))
            <div class="rounded-xl border border-[#dfe8c8] bg-[#f7faef] px-4 py-3 text-sm font-semibold text-[#4f6508]">
                {{ session('success') }}
            </div>
        @endif

        @if($pendingInvites->isNotEmpty())
            <section class="space-y-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Invitations</h2>
                        <p class="text-xs text-amber-700">{{ $pendingInvites->count() }} en attente</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($pendingInvites as $membership)
                        <article class="space-y-3 rounded-xl bg-white p-3 ring-1 ring-amber-100">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">{{ $membership->group->name }}</h3>
                                <p class="mt-1 text-sm leading-6 text-gray-600">
                                    {{ $membership->group->description ?: 'Votre praticien vous invite a rejoindre cet espace prive.' }}
                                </p>
                            </div>
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">
                                {{ $membership->group->user->company_name ?? $membership->group->user->name ?? 'Praticien' }}
                            </p>
                            <form method="POST" action="{{ route('mobile.client.communities.accept', $membership->group) }}">
                                @csrf
                                <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white">
                                    Rejoindre
                                </button>
                            </form>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="space-y-3 rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Groupes actifs</h2>
                    <p class="text-xs text-gray-500">{{ $communities->count() }} communaute(s)</p>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($communities as $membership)
                    <a href="{{ route('mobile.client.communities.show', $membership->group) }}" class="block rounded-xl bg-[#fafbf7] p-3 ring-1 ring-[#eef1e5]">
                        <div class="flex items-start gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#647a0b] text-sm font-bold text-white">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($membership->group->name, 0, 2)) }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-bold text-gray-900">{{ $membership->group->name }}</span>
                                <span class="mt-1 block text-xs leading-5 text-gray-500">
                                    {{ $membership->group->channels->count() }} salon(s) - {{ $membership->group->channels->filter(fn ($channel) => $channel->pinnedMessage)->count() }} ressource(s)
                                </span>
                                <span class="mt-2 block text-sm leading-6 text-gray-600">{{ \Illuminate\Support\Str::limit($membership->group->description ?: 'Communaute privee animee par votre praticien.', 110) }}</span>
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="rounded-xl bg-gray-50 px-4 py-8 text-center">
                        <p class="text-sm font-semibold text-gray-900">Aucune communaute active</p>
                        <p class="mt-1 text-sm text-gray-500">Les invitations de votre praticien apparaitront ici.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-mobile-client-layout>

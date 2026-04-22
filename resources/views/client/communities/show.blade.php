<x-client-app-layout>
    @php
        $adminName = $community->user->company_name ?? $community->user->name ?? 'Praticien';
        $activeMembersCount = $community->members()->where('status', \App\Models\CommunityMember::STATUS_ACTIVE)->count();
        $pinnedMessage = $selectedChannel?->pinnedMessage;
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6 lg:p-8">
        <section class="overflow-hidden rounded-[2rem] border border-[#dfe7c7] bg-[radial-gradient(circle_at_top_left,_rgba(224,235,198,0.72),_rgba(255,255,255,0.95)_42%,_rgba(244,240,231,0.95)_100%)] p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#854f38]">
                        <span class="rounded-full border border-[#d8dfc7] bg-white/80 px-4 py-1.5 text-[#647a0b]">Communauté privée</span>
                        <span>{{ $community->channels->count() }} salons</span>
                        <span>{{ $activeMembersCount }} membres actifs</span>
                    </div>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900">{{ $community->name }}</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-gray-600">
                        {{ $community->description ?: 'Un espace privé pour suivre les annonces importantes, partager des ressources et continuer les échanges du groupe.' }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-white/90 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-[#e7eadf]">{{ $adminName }} | Admin</span>
                    <a href="{{ route('client.communities.index') }}" class="inline-flex items-center rounded-full bg-white/90 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-[#e7eadf] hover:text-[#647a0b]">
                        Toutes mes communautés
                    </a>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[280px_minmax(0,1fr)_300px] xl:items-start">
            <aside class="space-y-5 xl:sticky xl:top-24">
                <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                    <div class="border-b border-[#f0f1ea] px-5 py-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Salons</p>
                        <p class="mt-3 text-sm leading-7 text-gray-600">Passez facilement d’un salon d’annonces à un espace de discussion sans perdre le fil.</p>
                    </div>
                    <div class="space-y-3 px-5 py-5">
                        @foreach($community->channels as $channel)
                            @php($isSelected = $selectedChannel && $selectedChannel->id === $channel->id)
                            <a href="{{ route('client.communities.show', ['community' => $community->id, 'channel' => $channel->id]) }}"
                               class="block rounded-[1.6rem] border px-4 py-4 transition {{ $isSelected ? 'border-[#cfdbaf] bg-[linear-gradient(135deg,_rgba(245,250,234,1),_rgba(255,255,255,1))] shadow-sm' : 'border-[#eef1e6] bg-[#fafbf8] hover:border-[#dfe5cf] hover:bg-white' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900"># {{ $channel->name }}</div>
                                        <div class="mt-1 text-xs leading-5 {{ $isSelected ? 'text-lime-700' : 'text-gray-500' }}">
                                            {{ $channel->channel_type === 'annonces' ? 'Annonces du praticien' : 'Discussion du groupe' }}
                                        </div>
                                        @if($channel->pinnedMessage)
                                            <div class="mt-2 inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold text-amber-700">1 ressource épinglée</div>
                                        @endif
                                    </div>
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ $channel->channel_type === 'annonces' ? 'bg-[#854f38] text-white' : 'bg-white text-[#647a0b]' }} shadow-sm">
                                        {{ $channel->channel_type === 'annonces' ? '!' : '#' }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            </aside>

            <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                @if($selectedChannel)
                    <div class="border-b border-[#f0f1ea] px-6 py-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex items-start gap-4">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl {{ $selectedChannel->channel_type === 'annonces' ? 'bg-[#854f38] text-white' : 'bg-[#f7faef] text-[#647a0b]' }} shadow-sm">
                                    {{ $selectedChannel->channel_type === 'annonces' ? '!' : '#' }}
                                </span>
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h2 class="text-2xl font-semibold text-gray-900"># {{ $selectedChannel->name }}</h2>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $selectedChannel->channel_type === 'annonces' ? 'bg-amber-100 text-amber-700' : 'bg-lime-100 text-lime-700' }}">
                                            {{ $selectedChannel->channel_type === 'annonces' ? 'Lecture prioritaire' : 'Échange ouvert' }}
                                        </span>
                                    </div>
                                    <p class="mt-3 max-w-3xl text-sm leading-7 text-gray-500">
                                        {{ $selectedChannel->description ?: ($selectedChannel->channel_type === 'annonces' ? 'Retrouvez ici les messages importants publiés par votre praticien.' : 'Échangez librement avec les autres membres dans un cadre privé et bienveillant.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="rounded-[1.6rem] bg-[#fafbf7] px-4 py-3 ring-1 ring-[#eef1e5]">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Cadre</p>
                                <p class="mt-2 text-sm font-semibold text-gray-900">{{ $selectedChannel->channel_type === 'annonces' ? 'Messages réservés à l’admin' : 'Discussion visible aux membres actifs' }}</p>
                            </div>
                        </div>

                        @if($pinnedMessage)
                            <div class="mt-5 rounded-[1.8rem] border border-amber-200 bg-[linear-gradient(135deg,_rgba(255,248,230,1),_rgba(255,255,255,1))] p-5 shadow-sm">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Message épinglé</span>
                                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">{{ $pinnedMessage->authorName() }}</span>
                                </div>
                                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-gray-700">{{ $pinnedMessage->content }}</p>
                                @include('communities.partials.attachment-list', [
                                    'attachments' => $pinnedMessage->attachments,
                                    'downloadRouteName' => 'client.communities.attachments.download',
                                ])
                            </div>
                        @endif
                    </div>

                    <div class="bg-[linear-gradient(180deg,_rgba(249,250,246,1),_rgba(255,255,255,1))] px-6 py-6">
                        <div class="space-y-4">
                            @forelse($messages as $message)
                                @include('communities.partials.message-card', [
                                    'message' => $message,
                                    'community' => $community,
                                    'adminName' => $adminName,
                                    'downloadRouteName' => 'client.communities.attachments.download',
                                    'canPin' => false,
                                ])
                            @empty
                                <div class="rounded-[1.8rem] border border-dashed border-[#d7dccb] bg-white/85 px-8 py-14 text-center shadow-sm">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-[#f7faef] text-xl font-semibold text-[#647a0b]">#</div>
                                    <p class="mt-5 text-base font-semibold text-gray-900">Aucun message pour le moment</p>
                                    <p class="mt-2 text-sm leading-7 text-gray-500">Le salon est prêt. Vous pouvez attendre la prochaine annonce du praticien ou lancer le premier échange du groupe.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    @if($selectedChannel->channel_type === 'annonces')
                        <div class="border-t border-[#f0f1ea] bg-amber-50 px-6 py-4 text-sm leading-6 text-amber-700">
                            Ce salon est réservé aux annonces de votre praticien.
                        </div>
                    @elseif($community->is_archived)
                        <div class="border-t border-[#f0f1ea] bg-gray-50 px-6 py-4 text-sm leading-6 text-gray-600">
                            Cette communauté est archivée. Vous pouvez relire les échanges mais vous ne pouvez plus envoyer de message.
                        </div>
                    @else
                        <div class="border-t border-[#f0f1ea] bg-white px-6 py-5">
                            <form method="POST" action="{{ route('client.communities.messages.store', $community) }}" enctype="multipart/form-data" class="rounded-[1.8rem] border border-[#eceee5] bg-[linear-gradient(180deg,_rgba(250,251,247,1),_rgba(255,255,255,1))] p-4 shadow-inner shadow-[#f3f4ef]">
                                @csrf
                                <input type="hidden" name="community_channel_id" value="{{ $selectedChannel->id }}">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Votre message</label>
                                        <p class="mt-1 text-xs text-gray-500">Partagez un retour, une question ou une ressource simple. Limite {{ $attachmentLimitLabel }} par fichier.</p>
                                    </div>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-500 ring-1 ring-[#eceee5]">Visible aux membres actifs</span>
                                </div>

                                <textarea name="content" rows="4" class="mt-4 w-full rounded-[1.4rem] border-gray-300 bg-white focus:border-[#647a0b] focus:ring-[#647a0b]" placeholder="Partagez votre retour, posez une question ou répondez au groupe.">{{ old('content') }}</textarea>
                                <x-input-error :messages="$errors->get('content')" class="mt-2" />

                                <div class="mt-4 rounded-[1.4rem] border border-dashed border-[#dfe5cf] bg-white px-4 py-4">
                                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800">Joindre des fichiers</p>
                                            <p class="mt-1 text-xs leading-6 text-gray-500">PDF, images, documents bureautiques et audio acceptés.</p>
                                        </div>
                                        <label class="inline-flex cursor-pointer items-center rounded-full border border-[#d7ddc8] px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-[#647a0b] hover:text-[#647a0b]">
                                            <input type="file" name="attachments[]" multiple class="hidden">
                                            Ajouter des fichiers
                                        </label>
                                    </div>
                                    <x-input-error :messages="$errors->get('attachments')" class="mt-2" />
                                    <x-input-error :messages="$errors->get('attachments.*')" class="mt-2" />
                                </div>

                                <div class="mt-4 flex items-center justify-between gap-3">
                                    <p class="text-xs text-gray-500">Gardez un ton simple, bienveillant et utile pour tous les membres.</p>
                                    <button type="submit" class="inline-flex items-center rounded-full bg-[#647a0b] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#55670a]">
                                        Envoyer dans le salon
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                @endif
            </section>

            <aside class="space-y-5 xl:sticky xl:top-24">
                <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                    <div class="border-b border-[#f0f1ea] px-5 py-5">
                        <h3 class="text-base font-semibold text-gray-900">Le cadre du groupe</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">Vous êtes dans une communauté privée. Les échanges restent visibles uniquement par les membres admis.</p>
                    </div>
                    <div class="space-y-3 px-5 py-5 text-sm leading-7 text-gray-600">
                        <div class="rounded-[1.4rem] bg-[#fafbf7] px-4 py-3 ring-1 ring-[#eef1e5]">
                            <p class="text-xs uppercase tracking-[0.16em] text-gray-500">Admin</p>
                            <p class="mt-2 font-semibold text-gray-900">{{ $adminName }}</p>
                            <span class="mt-3 inline-flex rounded-full bg-[#f7faef] px-3 py-1 text-xs font-semibold text-[#647a0b]">{{ $adminName }} | Admin</span>
                        </div>
                        <div class="rounded-[1.4rem] bg-[#fafbf7] px-4 py-3 ring-1 ring-[#eef1e5]">
                            <p class="text-xs uppercase tracking-[0.16em] text-gray-500">Statut</p>
                            <p class="mt-2 font-semibold text-gray-900">{{ $community->is_archived ? 'Communauté archivée' : 'Communauté active' }}</p>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-client-app-layout>

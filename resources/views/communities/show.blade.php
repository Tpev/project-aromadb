<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <div class="inline-flex items-center rounded-full border border-[#d8dfc7] bg-[#f7faef] px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.22em] text-[#647a0b]">
                    Communaute privee
                </div>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900">{{ $community->name }}</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-gray-600">Un espace d'echange ferme, pense pour des conversations calmes, des annonces bien visibles et une vraie sensation de cadre premium pour vos invites.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('communities.edit', $community) }}" class="inline-flex items-center rounded-full border border-[#d7ddc8] bg-white/90 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:border-[#647a0b] hover:text-[#647a0b]">Modifier</a>
                <a href="{{ route('communities.index') }}" class="inline-flex items-center rounded-full bg-[#f4f5ef] px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-[#ebede4] hover:text-gray-800">Retour</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <section class="overflow-hidden rounded-[2rem] border border-[#dfe7c7] bg-[radial-gradient(circle_at_top_left,_rgba(224,235,198,0.72),_rgba(255,255,255,0.95)_42%,_rgba(244,240,231,0.95)_100%)] p-6 shadow-sm">
            <div class="grid gap-4 xl:grid-cols-[1.35fr_repeat(4,minmax(0,1fr))]">
                <div class="rounded-[1.8rem] bg-white/85 p-6 ring-1 ring-[#e8ecd9] backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#854f38]">Direction editoriale</p>
                    <h3 class="mt-3 text-xl font-semibold text-gray-900">Un forum prive qui reste chaleureux</h3>
                    <p class="mt-3 text-sm leading-7 text-gray-600">Les annonces restent mises en avant, les discussions gardent une lecture apaisante et chaque salon conserve un ton clairement identifiable.</p>
                </div>
                <div class="rounded-[1.8rem] bg-white/85 p-5 ring-1 ring-[#e8ecd9] backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Salons</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $community->channels->count() }}</p>
                    <p class="mt-2 text-xs text-gray-500">espaces de conversation</p>
                </div>
                <div class="rounded-[1.8rem] bg-white/85 p-5 ring-1 ring-[#e8ecd9] backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Membres actifs</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $community->members->where('status', 'active')->count() }}</p>
                    <p class="mt-2 text-xs text-gray-500">participants actuellement admis</p>
                </div>
                <div class="rounded-[1.8rem] bg-white/85 p-5 ring-1 ring-[#e8ecd9] backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Invitations</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $community->members->where('status', 'invited')->count() }}</p>
                    <p class="mt-2 text-xs text-gray-500">en attente de reponse</p>
                </div>
                <div class="rounded-[1.8rem] bg-white/85 p-5 ring-1 ring-[#e8ecd9] backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Statut</p>
                    <p class="mt-3 text-lg font-semibold {{ $community->is_archived ? 'text-gray-700' : 'text-[#647a0b]' }}">{{ $community->is_archived ? 'Archivee' : 'Active' }}</p>
                    <p class="mt-2 text-xs text-gray-500">{{ $community->is_archived ? 'lecture seule' : 'ouverte aux nouveaux messages' }}</p>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[300px_minmax(0,1fr)_360px]">
            <aside class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                <div class="border-b border-[#f0f1ea] px-5 py-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">A propos</p>
                    <p class="mt-3 text-sm leading-7 text-gray-600">{{ $community->description ?: 'Decrivez ici la promesse de la communaute, la posture attendue et la facon dont les membres sont invites a participer.' }}</p>
                </div>

                <div class="px-5 py-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Salons</p>
                        <span class="rounded-full bg-[#f7faef] px-3 py-1 text-xs font-semibold text-[#647a0b]">{{ $community->channels->count() }}</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach($community->channels as $channel)
                            <a href="{{ route('communities.show', ['community' => $community->id, 'channel' => $channel->id]) }}"
                               class="block rounded-[1.6rem] border px-4 py-4 transition {{ $selectedChannel && $selectedChannel->id === $channel->id ? 'border-[#cfdbaf] bg-[linear-gradient(135deg,_rgba(245,250,234,1),_rgba(255,255,255,1))] shadow-sm' : 'border-[#eef1e6] bg-[#fafbf8] hover:border-[#dfe5cf] hover:bg-white' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900"># {{ $channel->name }}</div>
                                        <div class="mt-1 text-xs leading-5 {{ $selectedChannel && $selectedChannel->id === $channel->id ? 'text-lime-700' : 'text-gray-500' }}">
                                            {{ $channel->channel_type === 'annonces' ? 'Annonces reservees au praticien' : 'Discussion ouverte aux membres' }}
                                        </div>
                                    </div>
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ $channel->channel_type === 'annonces' ? 'bg-[#854f38] text-white' : 'bg-white text-[#647a0b]' }} shadow-sm">
                                        {{ $channel->channel_type === 'annonces' ? '!' : '#' }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                @if($community->is_archived)
                    <div class="border-t border-[#f0f1ea] bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-700">
                        Cette communaute est archivee. Les membres peuvent relire les echanges, mais aucun nouveau message n'est autorise.
                    </div>
                @endif
            </aside>

            <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                @if($selectedChannel)
                    <div class="border-b border-[#f0f1ea] px-6 py-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-start gap-4">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl {{ $selectedChannel->channel_type === 'annonces' ? 'bg-[#854f38] text-white' : 'bg-[#f7faef] text-[#647a0b]' }} shadow-sm">
                                    {{ $selectedChannel->channel_type === 'annonces' ? '!' : '#' }}
                                </span>
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-xl font-semibold text-gray-900"># {{ $selectedChannel->name }}</h3>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $selectedChannel->channel_type === 'annonces' ? 'bg-amber-100 text-amber-700' : 'bg-lime-100 text-lime-700' }}">
                                            {{ $selectedChannel->channel_type === 'annonces' ? 'Annonce praticien' : 'Discussion' }}
                                        </span>
                                    </div>
                                    <p class="mt-2 text-sm leading-7 text-gray-500">{{ $selectedChannel->description ?: ($selectedChannel->channel_type === 'annonces' ? 'Ce salon met en avant vos informations importantes et vos messages cadres.' : 'Ce salon accueille les echanges des membres dans un cadre prive et serein.') }}</p>
                                </div>
                            </div>
                            <div class="rounded-[1.4rem] bg-[#fafbf7] px-4 py-3 text-right ring-1 ring-[#eef1e5]">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Rythme du salon</p>
                                <p class="mt-2 text-sm font-semibold text-gray-900">{{ $selectedChannel->channel_type === 'annonces' ? 'Prise de parole reservee' : 'Prise de parole partagee' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="max-h-[720px] overflow-y-auto bg-[linear-gradient(180deg,_rgba(249,250,246,1),_rgba(255,255,255,1))] px-6 py-6">
                        <div class="space-y-4">
                            @forelse($messages as $message)
                                <article class="rounded-[1.8rem] border px-5 py-4 shadow-sm {{ $message->sender_type === 'practitioner' ? 'border-[#dbe6c1] bg-[linear-gradient(135deg,_rgba(245,250,234,1),_rgba(255,255,255,1))]' : 'border-[#eceee5] bg-white' }}">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl font-semibold {{ $message->sender_type === 'practitioner' ? 'bg-[#647a0b] text-white' : 'bg-[#f2f3ee] text-gray-700' }}">
                                                {{ $message->sender_type === 'practitioner' ? 'P' : \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($message->authorName(), 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="text-sm font-semibold text-gray-900">{{ $message->authorName() }}</p>
                                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $message->sender_type === 'practitioner' ? 'bg-[#647a0b] text-white' : 'bg-gray-100 text-gray-600' }}">
                                                        {{ $message->sender_type === 'practitioner' ? 'Praticien' : 'Membre' }}
                                                    </span>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500">{{ $message->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 whitespace-pre-line text-sm leading-7 text-gray-700">{{ $message->content }}</div>
                                </article>
                            @empty
                                <div class="rounded-[1.8rem] border border-dashed border-[#d7dccb] bg-white/85 px-8 py-14 text-center shadow-sm">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-[#f7faef] text-xl font-semibold text-[#647a0b]">#</div>
                                    <p class="mt-5 text-base font-semibold text-gray-900">Aucun message dans ce salon</p>
                                    <p class="mt-2 text-sm leading-7 text-gray-500">Vous pouvez lancer la premiere conversation, partager une ressource ou poser le cadre de ce salon en un message.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="border-t border-[#f0f1ea] bg-white px-6 py-5">
                        <form method="POST" action="{{ route('communities.messages.store', $community) }}" class="rounded-[1.8rem] border border-[#eceee5] bg-[linear-gradient(180deg,_rgba(250,251,247,1),_rgba(255,255,255,1))] p-4 shadow-inner shadow-[#f3f4ef]">
                            @csrf
                            <input type="hidden" name="community_channel_id" value="{{ $selectedChannel->id }}">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <label class="text-sm font-semibold text-gray-700">Publier dans # {{ $selectedChannel->name }}</label>
                                    <p class="mt-1 text-xs text-gray-500">Votre message sera visible par tous les membres actifs de cette communaute.</p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-500 ring-1 ring-[#eceee5]">{{ $selectedChannel->channel_type === 'annonces' ? 'Annonce forte visibilite' : 'Fil de discussion du groupe' }}</span>
                            </div>
                            <textarea name="content" rows="4" class="mt-4 w-full rounded-[1.4rem] border-gray-300 bg-white focus:border-[#647a0b] focus:ring-[#647a0b]" placeholder="Partagez une annonce, une ressource utile, une consigne ou ouvrez une discussion du groupe."></textarea>
                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                            <div class="mt-4 flex items-center justify-between gap-3">
                                <p class="text-xs text-gray-500">{{ $selectedChannel->channel_type === 'annonces' ? 'Ce salon donne plus de poids a vos informations importantes.' : 'Ce salon favorise les echanges entre vous et vos invites.' }}</p>
                                <button type="submit" class="inline-flex items-center rounded-full bg-[#647a0b] px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-lime-900/10 transition hover:-translate-y-0.5 hover:bg-[#55670a]" @disabled($community->is_archived)>
                                    Envoyer
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </section>

            <aside class="space-y-5">
                <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                    <div class="border-b border-[#f0f1ea] px-5 py-5">
                        <h3 class="text-base font-semibold text-gray-900">Inviter un client</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">Ajoutez un membre parmi vos clients existants. L'acces reste ferme tant qu'il n'a pas rejoint la communaute.</p>
                    </div>
                    <form method="POST" action="{{ route('communities.members.store', $community) }}" class="space-y-4 px-5 py-5">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="client_profile_id">Client</label>
                            <select id="client_profile_id" name="client_profile_id" class="mt-2 w-full rounded-[1.2rem] border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">
                                @foreach($availableClients as $client)
                                    <option value="{{ $client->id }}">{{ trim($client->first_name . ' ' . $client->last_name) }}@if($client->email) - {{ $client->email }}@endif</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('client_profile_id')" class="mt-2" />
                        </div>
                        <button type="submit" class="inline-flex items-center rounded-full bg-[#854f38] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#73432f]">
                            Inviter dans la communaute
                        </button>
                    </form>
                </section>

                <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                    <div class="border-b border-[#f0f1ea] px-5 py-5">
                        <h3 class="text-base font-semibold text-gray-900">Ajouter un salon</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">Creez un nouvel espace pour separer un theme, un suivi de programme ou une rubrique d'information.</p>
                    </div>
                    <form method="POST" action="{{ route('communities.channels.store', $community) }}" class="space-y-4 px-5 py-5">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="channel_name">Nom du salon</label>
                            <input id="channel_name" name="name" type="text" class="mt-2 w-full rounded-[1.2rem] border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="channel_type">Type</label>
                            <select id="channel_type" name="channel_type" class="mt-2 w-full rounded-[1.2rem] border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">
                                <option value="discussion">Discussion</option>
                                <option value="annonces">Annonces</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="channel_description">Description</label>
                            <textarea id="channel_description" name="description" rows="3" class="mt-2 w-full rounded-[1.2rem] border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"></textarea>
                        </div>
                        <button type="submit" class="inline-flex items-center rounded-full border border-[#d7ddc8] px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-[#647a0b] hover:text-[#647a0b]">
                            Ajouter le salon
                        </button>
                    </form>
                </section>

                <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                    <div class="border-b border-[#f0f1ea] px-5 py-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Membres</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-500">Suivez les membres actifs, les invitations en attente et les retraits.</p>
                            </div>
                            <span class="rounded-full bg-[#f7faef] px-3 py-1 text-xs font-semibold text-[#647a0b]">{{ $community->members->where('status', 'active')->count() }} actifs</span>
                        </div>
                    </div>
                    <div class="space-y-3 px-5 py-5">
                        @forelse($community->members as $member)
                            <div class="rounded-[1.6rem] border border-[#eceee5] bg-[#fcfcfa] px-4 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#f2f3ee] text-sm font-semibold text-gray-700">
                                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim(($member->clientProfile->first_name ?? '') . ' ' . ($member->clientProfile->last_name ?? '')), 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ trim(($member->clientProfile->first_name ?? '') . ' ' . ($member->clientProfile->last_name ?? '')) }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $member->clientProfile->email }}</p>
                                        </div>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $member->status === 'active' ? 'bg-lime-100 text-lime-700' : ($member->status === 'invited' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500') }}">
                                        {{ $member->status === 'active' ? 'Actif' : ($member->status === 'invited' ? 'Invite' : 'Retire') }}
                                    </span>
                                </div>
                                @if($member->status !== 'removed')
                                    <form method="POST" action="{{ route('communities.members.destroy', [$community, $member]) }}" class="mt-4">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700">Retirer de la communaute</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Aucun membre invite pour le moment.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>

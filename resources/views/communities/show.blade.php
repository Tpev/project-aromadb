<x-app-layout>
    <x-slot name="header">

        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#854f38]">
                    <span class="rounded-full border border-[#d8dfc7] bg-[#f7faef] px-4 py-1.5 text-[#647a0b]">Communauté privée</span>
                    <span>{{ $community->channels->count() }} salons</span>
                    <span>{{ $community->members->where('status', \App\Models\CommunityMember::STATUS_ACTIVE)->count() }} membres actifs</span>
                </div>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900">{{ $community->name }}</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-gray-600">
                    {{ $community->description ?: 'Un espace d’échange privé, pensé pour garder vos annonces visibles, vos ressources accessibles et une conversation plus chaleureuse qu’un simple back-office.' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('communities.manage', $community) }}" class="inline-flex items-center rounded-full bg-[#647a0b] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#55670a]">
                    Gérer la communauté
                </a>
                <a href="{{ route('communities.edit', $community) }}" class="inline-flex items-center rounded-full border border-[#d7ddc8] bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-[#647a0b] hover:text-[#647a0b]">
                    Réglages
                </a>
                <a href="{{ route('communities.index') }}" class="inline-flex items-center rounded-full bg-[#f4f5ef] px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-[#ebede4] hover:text-gray-800">
                    Retour
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $activeMembers = $community->members->where('status', \App\Models\CommunityMember::STATUS_ACTIVE);
        $invitedMembers = $community->members->where('status', \App\Models\CommunityMember::STATUS_INVITED);
        $pinnedMessage = $selectedChannel?->pinnedMessage;
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-6 xl:grid-cols-[280px_minmax(0,1fr)_320px] xl:items-start">
            <aside class="space-y-5 xl:sticky xl:top-24">
                <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                    <div class="border-b border-[#f0f1ea] px-5 py-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Vue d’ensemble</p>
                        <div class="mt-4 rounded-[1.6rem] bg-[linear-gradient(135deg,_rgba(245,250,234,1),_rgba(255,255,255,1))] px-4 py-4 ring-1 ring-[#e9eddc]">
                            <p class="text-sm font-semibold text-gray-900">{{ $community->user->company_name ?? $community->user->name ?? 'Praticien' }} | Admin</p>
                            <p class="mt-2 text-sm leading-6 text-gray-600">Vous gardez le cadre, les invitations et les ressources clés du groupe.</p>
                        </div>
                    </div>

                    <div class="px-5 py-5">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Salons</p>
                            <span class="rounded-full bg-[#f7faef] px-3 py-1 text-xs font-semibold text-[#647a0b]">{{ $community->channels->count() }}</span>
                        </div>
                        <div class="mt-4 space-y-3">
                            @foreach($community->channels as $channel)
                                @php($isSelected = $selectedChannel && $selectedChannel->id === $channel->id)
                                <a href="{{ route('communities.show', ['community' => $community->id, 'channel' => $channel->id]) }}"
                                   class="block rounded-[1.6rem] border px-4 py-4 transition {{ $isSelected ? 'border-[#cfdbaf] bg-[linear-gradient(135deg,_rgba(245,250,234,1),_rgba(255,255,255,1))] shadow-sm' : 'border-[#eef1e6] bg-[#fafbf8] hover:border-[#dfe5cf] hover:bg-white' }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900"># {{ $channel->name }}</div>
                                            <div class="mt-1 text-xs leading-5 {{ $isSelected ? 'text-lime-700' : 'text-gray-500' }}">
                                                {{ $channel->channel_type === 'annonces' ? 'Salon d’annonces réservé à l’admin' : 'Discussion ouverte aux membres' }}
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
                    </div>
                </section>
            </aside>

            <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                @if($selectedChannel)
                    <div class="border-b border-[#f0f1ea] px-6 py-5">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex items-start gap-4">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl {{ $selectedChannel->channel_type === 'annonces' ? 'bg-[#854f38] text-white' : 'bg-[#f7faef] text-[#647a0b]' }} shadow-sm">
                                    {{ $selectedChannel->channel_type === 'annonces' ? '!' : '#' }}
                                </span>
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-2xl font-semibold text-gray-900"># {{ $selectedChannel->name }}</h3>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $selectedChannel->channel_type === 'annonces' ? 'bg-amber-100 text-amber-700' : 'bg-lime-100 text-lime-700' }}">
                                            {{ $selectedChannel->channel_type === 'annonces' ? 'Annonces' : 'Discussion' }}
                                        </span>
                                        <span class="rounded-full bg-[#f5f6f0] px-3 py-1 text-xs font-semibold text-gray-600">{{ $community->user->company_name ?? $community->user->name ?? 'Praticien' }} | Admin</span>
                                    </div>
                                    <p class="mt-3 max-w-3xl text-sm leading-7 text-gray-500">
                                        {{ $selectedChannel->description ?: ($selectedChannel->channel_type === 'annonces' ? 'Un salon réservé à vos messages structurants, ressources importantes et rappels de cadre.' : 'L’espace central de discussion du groupe, pensé pour favoriser les échanges utiles et apaisés.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 rounded-[1.6rem] bg-[#fafbf7] p-4 ring-1 ring-[#eef1e5]">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Messages</p>
                                    <p class="mt-2 text-xl font-semibold text-gray-900">{{ $messages->count() }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Visibilité</p>
                                    <p class="mt-2 text-sm font-semibold text-gray-900">{{ $selectedChannel->channel_type === 'annonces' ? 'Admin uniquement' : 'Membres actifs' }}</p>
                                </div>
                            </div>
                        </div>

                        @if($pinnedMessage)
                            <div class="mt-5 rounded-[1.8rem] border border-amber-200 bg-[linear-gradient(135deg,_rgba(255,248,230,1),_rgba(255,255,255,1))] p-5 shadow-sm">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Message épinglé</span>
                                            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">{{ $pinnedMessage->authorName() }}</span>
                                        </div>
                                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-gray-700">{{ $pinnedMessage->content }}</p>
                                        @include('communities.partials.attachment-list', [
                                            'attachments' => $pinnedMessage->attachments,
                                            'downloadRouteName' => 'communities.attachments.download',
                                        ])
                                    </div>
                                    <form method="POST" action="{{ route('communities.channels.unpin', [$community, $selectedChannel]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-full border border-amber-200 bg-white px-4 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-50">
                                            Retirer l’épingle
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="bg-[linear-gradient(180deg,_rgba(249,250,246,1),_rgba(255,255,255,1))] px-6 py-6">
                        <div class="space-y-4">
                            @forelse($messages as $message)
                                @include('communities.partials.message-card', [
                                    'message' => $message,
                                    'community' => $community,
                                    'adminName' => ($community->user->company_name ?? $community->user->name ?? 'Praticien'),
                                    'downloadRouteName' => 'communities.attachments.download',
                                    'canPin' => true,
                                ])
                            @empty
                                <div class="rounded-[1.8rem] border border-dashed border-[#d7dccb] bg-white/85 px-8 py-14 text-center shadow-sm">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-[#f7faef] text-xl font-semibold text-[#647a0b]">#</div>
                                    <p class="mt-5 text-base font-semibold text-gray-900">Aucun message dans ce salon</p>
                                    <p class="mt-2 text-sm leading-7 text-gray-500">Ouvrez la conversation, partagez une ressource ou publiez la première annonce qui donnera le ton du groupe.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="border-t border-[#f0f1ea] bg-white px-6 py-5">
                        @if($community->is_archived)
                            <div class="rounded-[1.6rem] border border-gray-200 bg-gray-50 px-5 py-4 text-sm leading-6 text-gray-600">
                                Cette communauté est archivée. Les membres peuvent relire les échanges, mais aucun nouveau message n’est autorisé.
                            </div>
                        @else
                            <form method="POST" action="{{ route('communities.messages.store', $community) }}" enctype="multipart/form-data" class="rounded-[1.8rem] border border-[#eceee5] bg-[linear-gradient(180deg,_rgba(250,251,247,1),_rgba(255,255,255,1))] p-4 shadow-inner shadow-[#f3f4ef]">
                                @csrf
                                <input type="hidden" name="community_channel_id" value="{{ $selectedChannel->id }}">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Publier dans # {{ $selectedChannel->name }}</label>
                                        <p class="mt-1 text-xs text-gray-500">Visible par tous les membres actifs. Vous pouvez joindre jusqu’à 4 fichiers, limite {{ $attachmentLimitLabel }} par fichier.</p>
                                    </div>
                                    <a href="{{ route('communities.manage', $community) }}#invite" class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-500 ring-1 ring-[#eceee5] transition hover:text-[#647a0b]">Inviter des membres</a>
                                </div>

                                <textarea name="content" rows="4" class="mt-4 w-full rounded-[1.4rem] border-gray-300 bg-white focus:border-[#647a0b] focus:ring-[#647a0b]" placeholder="Partagez une annonce, une ressource utile, un rappel de cadre ou ouvrez une discussion du groupe.">{{ old('content') }}</textarea>
                                <x-input-error :messages="$errors->get('content')" class="mt-2" />

                                <div class="mt-4 rounded-[1.4rem] border border-dashed border-[#dfe5cf] bg-white px-4 py-4">
                                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800">Partager des fichiers</p>
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
                                    <p class="text-xs text-gray-500">{{ $selectedChannel->channel_type === 'annonces' ? 'Ce salon donne plus de poids à vos informations importantes.' : 'Ce salon favorise les échanges entre vous et vos invités.' }}</p>
                                    <button type="submit" class="inline-flex items-center rounded-full bg-[#647a0b] px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-lime-900/10 transition hover:-translate-y-0.5 hover:bg-[#55670a]">
                                        Envoyer
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endif
            </section>

            <aside class="space-y-5 xl:sticky xl:top-24">
                <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                    <div class="border-b border-[#f0f1ea] px-5 py-5">
                        <h3 class="text-base font-semibold text-gray-900">Actions rapides</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">La gestion détaillée a été déplacée pour garder ici une vraie vue de discussion.</p>
                    </div>
                    <div class="space-y-3 px-5 py-5">
                        <a href="{{ route('communities.manage', $community) }}#invite" class="flex items-center justify-between rounded-[1.5rem] border border-[#eceee5] bg-[#fafbf7] px-4 py-4 transition hover:border-[#647a0b]">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Inviter un client</p>
                                <p class="mt-1 text-xs text-gray-500">Ajoutez un membre ou relancez une invitation.</p>
                            </div>
                            <span class="text-[#647a0b]">→</span>
                        </a>
                        <a href="{{ route('communities.manage', $community) }}#salons" class="flex items-center justify-between rounded-[1.5rem] border border-[#eceee5] bg-[#fafbf7] px-4 py-4 transition hover:border-[#647a0b]">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Créer un salon</p>
                                <p class="mt-1 text-xs text-gray-500">Séparez les thèmes et les ressources du groupe.</p>
                            </div>
                            <span class="text-[#647a0b]">→</span>
                        </a>
                    </div>
                </section>

                <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                    <div class="border-b border-[#f0f1ea] px-5 py-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Membres</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-500">Suivi rapide de la communauté sans surcharger la conversation.</p>
                            </div>
                            <span class="rounded-full bg-[#f7faef] px-3 py-1 text-xs font-semibold text-[#647a0b]">{{ $activeMembers->count() }} actifs</span>
                        </div>
                    </div>
                    <div class="space-y-3 px-5 py-5">
                        @foreach($community->members->take(4) as $member)
                            @php($memberName = trim(($member->clientProfile->first_name ?? '') . ' ' . ($member->clientProfile->last_name ?? '')) ?: 'Client')
                            <div class="rounded-[1.5rem] border border-[#eceee5] bg-[#fcfcfa] px-4 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[#f2f3ee] text-sm font-semibold text-gray-700">
                                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($memberName, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-gray-900">{{ $memberName }}</p>
                                            <p class="mt-1 truncate text-xs text-gray-500">{{ $member->clientProfile->email }}</p>
                                        </div>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $member->status === \App\Models\CommunityMember::STATUS_ACTIVE ? 'bg-lime-100 text-lime-700' : ($member->status === \App\Models\CommunityMember::STATUS_INVITED ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500') }}">
                                        {{ $member->status === \App\Models\CommunityMember::STATUS_ACTIVE ? 'Actif' : ($member->status === \App\Models\CommunityMember::STATUS_INVITED ? 'Invité' : 'Retiré') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        <a href="{{ route('communities.manage', $community) }}#members" class="inline-flex items-center rounded-full border border-[#d7ddc8] px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-[#647a0b] hover:text-[#647a0b]">
                            Voir tous les membres
                        </a>
                    </div>
                </section>

                <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                    <div class="border-b border-[#f0f1ea] px-5 py-5">
                        <h3 class="text-base font-semibold text-gray-900">Invitations</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">Gardez un œil sur les personnes qui n’ont pas encore rejoint le groupe.</p>
                    </div>
                    <div class="px-5 py-5">
                        <div class="rounded-[1.6rem] bg-[linear-gradient(135deg,_rgba(255,248,230,1),_rgba(255,255,255,1))] px-4 py-4 ring-1 ring-amber-100">
                            <p class="text-sm font-semibold text-gray-900">{{ $invitedMembers->count() }} invitation(s) en attente</p>
                            <p class="mt-2 text-sm text-gray-600">Depuis la page de gestion, vous pouvez relancer les invitations ou retirer un accès non utilisé.</p>
                            <a href="{{ route('communities.manage', $community) }}#invite" class="mt-4 inline-flex items-center rounded-full bg-white px-4 py-2 text-sm font-semibold text-amber-700 shadow-sm ring-1 ring-amber-100 transition hover:bg-amber-50">
                                Ouvrir les invitations
                            </a>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>

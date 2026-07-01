@php
    $memberStatus = [
        \App\Models\CommunityMember::STATUS_ACTIVE => ['label' => 'Actif', 'class' => 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]'],
        \App\Models\CommunityMember::STATUS_INVITED => ['label' => 'Invite', 'class' => 'border-amber-200 bg-amber-50 text-amber-700'],
        \App\Models\CommunityMember::STATUS_REMOVED => ['label' => 'Retire', 'class' => 'border-gray-200 bg-gray-50 text-gray-600'],
    ];
    $activeMembers = $community->members->where('status', \App\Models\CommunityMember::STATUS_ACTIVE)->values();
    $invitedMembers = $community->members->where('status', \App\Models\CommunityMember::STATUS_INVITED)->values();
    $blockedClientIds = $community->members
        ->whereIn('status', [\App\Models\CommunityMember::STATUS_ACTIVE, \App\Models\CommunityMember::STATUS_INVITED])
        ->pluck('client_profile_id')
        ->map(fn ($id) => (int) $id)
        ->all();
    $inviteClients = $availableClients->reject(fn ($client) => in_array((int) $client->id, $blockedClientIds, true))->values();
@endphp

<x-mobile-layout :title="$community->name">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.communities.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Communautes
            </a>

            <div class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                        <i class="fas fa-comments text-sm"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <h1 class="min-w-0 text-xl font-semibold leading-tight text-gray-900">
                                {{ $community->name }}
                            </h1>
                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $community->is_archived ? 'border-gray-200 bg-gray-50 text-gray-600' : 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' }}">
                                {{ $community->is_archived ? 'Archivee' : 'Active' }}
                            </span>
                        </div>
                        <p class="mt-1 line-clamp-3 text-sm leading-snug text-gray-600">
                            {{ $community->description ?: 'Communaute sans description' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Actifs</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $activeMembers->count() }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Invites</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $invitedMembers->count() }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Salons</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $community->channels->count() }}</div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('mobile.communities.edit', $community) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-edit mr-1.5 text-[11px]"></i>
                        Modifier
                    </a>
                    <a href="{{ route('communities.show', $community) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-external-link-alt mr-1.5 text-[10px]"></i>
                        Vue web
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <div class="font-semibold">A corriger</div>
                <ul class="mt-1 list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Messages</h2>
                    @if($selectedChannel)
                        <span class="rounded-full bg-[#f7f8f1] px-2 py-1 text-[11px] text-gray-600">
                            {{ $messages->count() }} visibles
                        </span>
                    @endif
                </div>

                @if($community->channels->isEmpty())
                    <div class="mt-3 rounded-lg border border-dashed border-[#d7ddc6] bg-[#fbfcf7] p-4 text-center">
                        <h3 class="text-sm font-semibold text-gray-900">Aucun salon</h3>
                        <p class="mt-1 text-sm leading-snug text-gray-600">
                            Ouvrez la vue web pour creer un salon dans cette communaute.
                        </p>
                    </div>
                @else
                    <div class="mt-3 flex gap-2 overflow-x-auto pb-1">
                        @foreach($community->channels as $channel)
                            <a href="{{ route('mobile.communities.show', ['community' => $community->id, 'channel' => $channel->id]) }}"
                               class="inline-flex h-9 shrink-0 items-center rounded-lg border px-3 text-xs font-semibold {{ $selectedChannel && $selectedChannel->is($channel) ? 'border-[#647a0b] bg-[#647a0b]/10 text-[#647a0b]' : 'border-[#e4e8d5] bg-white text-gray-600' }}">
                                {{ $channel->name }}
                            </a>
                        @endforeach
                    </div>

                    @if($selectedChannel?->pinnedMessage)
                        <div class="mt-3 rounded-lg border border-amber-100 bg-amber-50 p-3">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-amber-700">Epingle</div>
                            <div class="mt-1 text-sm leading-snug text-gray-800">
                                {{ Str::limit($selectedChannel->pinnedMessage->content, 180) }}
                            </div>
                        </div>
                    @endif

                    <div class="mt-3 max-h-[480px] space-y-2 overflow-y-auto pr-1">
                        @forelse($messages as $message)
                            @php
                                $isPractitioner = $message->sender_type === \App\Models\CommunityMessage::SENDER_PRACTITIONER;
                            @endphp

                            <article class="rounded-lg border {{ $isPractitioner ? 'border-[#d7dfaa] bg-[#f7faef]' : 'border-[#f1f3e6] bg-[#fbfcf7]' }} p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="truncate text-xs font-semibold text-gray-900">
                                            {{ $message->authorName() }}
                                        </div>
                                        <div class="mt-0.5 text-[11px] text-gray-500">
                                            {{ $message->created_at?->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[10px] text-gray-500">
                                        {{ $isPractitioner ? 'Vous' : 'Client' }}
                                    </span>
                                </div>

                                <p class="mt-2 whitespace-pre-line text-sm leading-snug text-gray-700">{{ $message->content }}</p>

                                @if($message->attachments->isNotEmpty())
                                    <div class="mt-3 space-y-1.5">
                                        @foreach($message->attachments as $attachment)
                                            <a href="{{ route('mobile.communities.attachments.download', $attachment) }}"
                                               class="flex items-center gap-2 rounded-lg border border-[#e4e8d5] bg-white px-3 py-2 text-xs font-semibold text-gray-700">
                                                <i class="fas fa-paperclip text-[11px] text-[#647a0b]"></i>
                                                <span class="min-w-0 flex-1 truncate">{{ $attachment->original_name }}</span>
                                                <span class="shrink-0 text-[10px] text-gray-500">{{ \App\Support\UploadLimit::formatBytes($attachment->size) }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @empty
                            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-[#fbfcf7] p-4 text-center">
                                <h3 class="text-sm font-semibold text-gray-900">Aucun message</h3>
                                <p class="mt-1 text-sm leading-snug text-gray-600">Publiez la premiere information du salon.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($community->is_archived)
                        <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-600">
                            Cette communaute est archivee. Les messages restent disponibles en lecture seule.
                        </div>
                    @elseif($selectedChannel)
                        <form method="POST"
                              action="{{ route('mobile.communities.messages.store', $community) }}"
                              enctype="multipart/form-data"
                              class="mt-3 space-y-3 rounded-lg bg-[#f7f8f1] p-3">
                            @csrf
                            <input type="hidden" name="community_channel_id" value="{{ $selectedChannel->id }}">

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Nouveau message</span>
                                <textarea name="content"
                                          rows="3"
                                          required
                                          class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]"
                                          placeholder="Ecrire aux membres du salon...">{{ old('content') }}</textarea>
                            </label>

                            <label class="block">
                                <span class="text-xs font-medium text-gray-600">Pieces jointes optionnelles ({{ $attachmentLimitLabel }} max)</span>
                                <input type="file"
                                       name="attachments[]"
                                       multiple
                                       class="mt-1 block w-full text-xs text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:text-xs file:font-semibold file:text-gray-700">
                            </label>

                            <button type="submit"
                                    class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-[#647a0b] text-xs font-semibold text-white">
                                Envoyer
                            </button>
                        </form>
                    @endif
                @endif
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Membres</h2>
                    <span class="rounded-full bg-[#f7f8f1] px-2 py-1 text-[11px] text-gray-600">
                        {{ $community->members->where('status', '!=', \App\Models\CommunityMember::STATUS_REMOVED)->count() }} invites
                    </span>
                </div>

                <form method="POST" action="{{ route('mobile.communities.members.store', $community) }}" class="mt-3 space-y-2 rounded-lg bg-[#f7f8f1] p-3">
                    @csrf
                    @if($inviteClients->isEmpty())
                        <div class="text-sm leading-snug text-gray-600">
                            Aucun client disponible a inviter pour le moment.
                        </div>
                        <a href="{{ route('mobile.clients.create') }}"
                           class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                            Creer un client
                        </a>
                    @else
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Inviter un client</span>
                            <select name="client_profile_id"
                                    required
                                    class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                                <option value="">Choisir un client</option>
                                @foreach($inviteClients as $client)
                                    @php
                                        $clientName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')) ?: 'Client #' . $client->id;
                                    @endphp
                                    <option value="{{ $client->id }}">{{ $clientName }}{{ $client->email ? ' - ' . $client->email : '' }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="submit"
                                class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-[#647a0b] text-xs font-semibold text-white">
                            Inviter
                        </button>
                    @endif
                </form>

                @if($community->members->isEmpty())
                    <div class="mt-3 rounded-lg border border-dashed border-[#d7ddc6] bg-[#fbfcf7] p-4 text-center">
                        <h3 class="text-sm font-semibold text-gray-900">Aucun membre</h3>
                        <p class="mt-1 text-sm leading-snug text-gray-600">Ajoutez un client pour ouvrir l acces.</p>
                    </div>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach($community->members->sortByDesc('updated_at') as $member)
                            @php
                                $client = $member->clientProfile;
                                $clientName = $client ? trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')) : '';
                                $status = $memberStatus[$member->status] ?? $memberStatus[\App\Models\CommunityMember::STATUS_REMOVED];
                            @endphp

                            <article class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-gray-900">
                                            {{ $clientName ?: 'Client #' . $member->client_profile_id }}
                                        </div>
                                        <div class="mt-1 truncate text-xs text-gray-600">
                                            {{ $client?->email ?: ($client?->phone ?: 'Coordonnees manquantes') }}
                                        </div>
                                    </div>

                                    <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $status['class'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                </div>

                                @if($member->status !== \App\Models\CommunityMember::STATUS_REMOVED)
                                    <form method="POST"
                                          action="{{ route('mobile.communities.members.destroy', [$community, $member]) }}"
                                          class="mt-3"
                                          onsubmit="return confirm('Retirer ce membre de la communaute ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-xs font-semibold text-red-600">
                                            Retirer
                                        </button>
                                    </form>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <form method="POST"
                  action="{{ route('mobile.communities.destroy', $community) }}"
                  onsubmit="return confirm('Supprimer cette communaute ? Les salons, membres et messages seront supprimes.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-sm font-semibold text-red-600">
                    <i class="fas fa-trash-alt mr-1.5 text-[11px]"></i>
                    Supprimer la communaute
                </button>
            </form>
        </div>
    </div>
</x-mobile-layout>

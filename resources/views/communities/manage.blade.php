<x-app-layout>
    <x-slot name="header">

        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#854f38]">Gestion de communauté</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900">{{ $community->name }}</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-gray-600">Invitez des membres, relancez les accès en attente et créez de nouveaux salons sans surcharger la page de discussion.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="rounded-full bg-[#f7faef] px-4 py-2 text-sm font-semibold text-[#647a0b]">{{ $community->user->company_name ?? $community->user->name ?? 'Praticien' }} | Admin</span>
                <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center rounded-full bg-[#f4f5ef] px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-[#ebede4] hover:text-gray-800">
                    Retour à la conversation
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $activeMembers = $community->members->where('status', \App\Models\CommunityMember::STATUS_ACTIVE)->values();
        $invitedMembers = $community->members->where('status', \App\Models\CommunityMember::STATUS_INVITED)->values();
        $removedMembers = $community->members->where('status', \App\Models\CommunityMember::STATUS_REMOVED)->values();
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[1.8rem] border border-[#e8ecd9] bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Salons</p>
                <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $community->channels->count() }}</p>
            </div>
            <div class="rounded-[1.8rem] border border-[#e8ecd9] bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Actifs</p>
                <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $activeMembers->count() }}</p>
            </div>
            <div class="rounded-[1.8rem] border border-[#e8ecd9] bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Invitations</p>
                <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $invitedMembers->count() }}</p>
            </div>
            <div class="rounded-[1.8rem] border border-[#e8ecd9] bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Retirés</p>
                <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $removedMembers->count() }}</p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-6">
                <section id="members" class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                    <div class="border-b border-[#f0f1ea] px-6 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">Membres actifs</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">Les personnes qui voient la communauté, les fichiers partagés et les annonces.</p>
                    </div>
                    <div class="space-y-4 px-6 py-6">
                        @forelse($activeMembers as $member)
                            @php($memberName = trim(($member->clientProfile->first_name ?? '') . ' ' . ($member->clientProfile->last_name ?? '')) ?: 'Client')
                            <div class="flex flex-col gap-4 rounded-[1.6rem] border border-[#eceee5] bg-[#fcfcfa] px-4 py-4 md:flex-row md:items-center md:justify-between">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#f2f3ee] text-sm font-semibold text-gray-700">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($memberName, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $memberName }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $member->clientProfile->email }}</p>
                                        @if($member->joined_at)
                                            <p class="mt-2 text-xs text-gray-500">A rejoint le {{ $member->joined_at->format('d/m/Y à H:i') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('communities.members.destroy', [$community, $member]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center rounded-full border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                                        Retirer de la communauté
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="rounded-[1.6rem] border border-dashed border-[#d7dccb] bg-[#fafbf7] px-6 py-10 text-center text-sm text-gray-500">
                                Aucun membre actif pour le moment.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                    <div class="border-b border-[#f0f1ea] px-6 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">Invitations en attente</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">Relancez par email si besoin. L’invitation reste visible aussi dans l’espace client.</p>
                    </div>
                    <div class="space-y-4 px-6 py-6">
                        @forelse($invitedMembers as $member)
                            @php($memberName = trim(($member->clientProfile->first_name ?? '') . ' ' . ($member->clientProfile->last_name ?? '')) ?: 'Client')
                            <div class="rounded-[1.6rem] border border-amber-200 bg-[linear-gradient(135deg,_rgba(255,248,230,1),_rgba(255,255,255,1))] px-4 py-4">
                                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $memberName }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $member->clientProfile->email ?: 'Pas d’email renseigné' }}</p>
                                        <p class="mt-2 text-xs text-gray-500">
                                            Invitation envoyée le {{ optional($member->invitation_email_sent_at ?? $member->invited_at)->format('d/m/Y à H:i') ?: 'non renseigné' }}
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('communities.members.resend', [$community, $member]) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-full bg-white px-4 py-2 text-sm font-semibold text-amber-700 shadow-sm ring-1 ring-amber-200 transition hover:bg-amber-50">
                                                Relancer l’invitation
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('communities.members.destroy', [$community, $member]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-full border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                                                Retirer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-[1.6rem] border border-dashed border-[#d7dccb] bg-[#fafbf7] px-6 py-10 text-center text-sm text-gray-500">
                                Aucune invitation en attente.
                            </div>
                        @endforelse
                    </div>
                </section>

                @if($removedMembers->isNotEmpty())
                    <section class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                        <div class="border-b border-[#f0f1ea] px-6 py-5">
                            <h3 class="text-lg font-semibold text-gray-900">Historique des retraits</h3>
                        </div>
                        <div class="space-y-3 px-6 py-6">
                            @foreach($removedMembers as $member)
                                @php($memberName = trim(($member->clientProfile->first_name ?? '') . ' ' . ($member->clientProfile->last_name ?? '')) ?: 'Client')
                                <div class="rounded-[1.4rem] bg-[#fafbf7] px-4 py-3 text-sm text-gray-600 ring-1 ring-[#eef1e5]">
                                    <span class="font-semibold text-gray-900">{{ $memberName }}</span>
                                    @if($member->removed_at)
                                        — retiré le {{ $member->removed_at->format('d/m/Y à H:i') }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="space-y-6 xl:sticky xl:top-24">
                <section id="invite" class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                    <div class="border-b border-[#f0f1ea] px-5 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">Inviter un client</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">L’accès reste fermé tant que la personne n’a pas rejoint la communauté.</p>
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
                            Envoyer l’invitation
                        </button>
                    </form>
                </section>

                <section id="salons" class="overflow-hidden rounded-[2rem] border border-[#eceee5] bg-white shadow-sm">
                    <div class="border-b border-[#f0f1ea] px-5 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">Ajouter un salon</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">Séparez une thématique, une ressource ou un type d’échange sans alourdir le fil principal.</p>
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
                        <h3 class="text-lg font-semibold text-gray-900">Salons existants</h3>
                    </div>
                    <div class="space-y-3 px-5 py-5">
                        @foreach($community->channels as $channel)
                            <div class="rounded-[1.4rem] border border-[#eceee5] bg-[#fafbf7] px-4 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900"># {{ $channel->name }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $channel->channel_type === 'annonces' ? 'Annonces' : 'Discussion' }}</p>
                                    </div>
                                    @if($channel->pinnedMessage)
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold text-amber-700">Épinglé</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>

<x-mobile-client-layout :title="$community->name">
    @php
        $adminName = $community->user->company_name ?? $community->user->name ?? 'Praticien';
        $pinnedMessage = $selectedChannel?->pinnedMessage;
    @endphp

    <div class="mx-auto max-w-lg space-y-5 px-4 py-5">
        <section class="space-y-3">
            <a href="{{ route('mobile.client.communities.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#647a0b]">
                <i class="fas fa-chevron-left text-xs"></i>
                Communautes
            </a>
            <div class="space-y-2">
                <span class="inline-flex rounded-full bg-[#f7faef] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#647a0b]">
                    Groupe prive
                </span>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $community->name }}</h1>
                <p class="text-sm leading-6 text-gray-600">
                    {{ $community->description ?: 'Un espace prive pour suivre les annonces, relire les ressources et echanger avec le groupe.' }}
                </p>
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">{{ $adminName }}</p>
            </div>
        </section>

        @if(session('success'))
            <div class="rounded-xl border border-[#dfe8c8] bg-[#f7faef] px-4 py-3 text-sm font-semibold text-[#4f6508]">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if($community->channels->isNotEmpty())
            <section class="-mx-4 overflow-x-auto px-4">
                <div class="flex gap-2 pb-1">
                    @foreach($community->channels as $channel)
                        @php($isSelected = $selectedChannel && $selectedChannel->id === $channel->id)
                        <a href="{{ route('mobile.client.communities.show', ['community' => $community->id, 'channel' => $channel->id]) }}"
                           class="shrink-0 rounded-full border px-4 py-2 text-sm font-semibold {{ $isSelected ? 'border-[#647a0b] bg-[#647a0b] text-white' : 'border-[#e4e8d5] bg-white text-gray-600' }}">
                            {{ $channel->channel_type === 'annonces' ? '!' : '#' }} {{ $channel->name }}
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($selectedChannel)
            <section class="space-y-4 rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-bold text-gray-900"># {{ $selectedChannel->name }}</h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">
                                {{ $selectedChannel->description ?: ($selectedChannel->channel_type === 'annonces' ? 'Messages importants publies par votre praticien.' : 'Discussion visible par les membres actifs.') }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full px-3 py-1 text-[11px] font-semibold {{ $selectedChannel->channel_type === 'annonces' ? 'bg-amber-100 text-amber-700' : 'bg-[#f7faef] text-[#647a0b]' }}">
                            {{ $selectedChannel->channel_type === 'annonces' ? 'Annonces' : 'Discussion' }}
                        </span>
                    </div>
                </div>

                @if($pinnedMessage)
                    <article class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[11px] font-semibold uppercase tracking-[0.12em] text-amber-700">Epingle</span>
                            <span class="text-xs text-gray-500">{{ $pinnedMessage->authorName() }}</span>
                        </div>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-800">{{ $pinnedMessage->content }}</p>
                        @if($pinnedMessage->attachments->isNotEmpty())
                            <div class="mt-3 space-y-2">
                                @foreach($pinnedMessage->attachments as $attachment)
                                    <a href="{{ route('mobile.client.communities.attachments.download', $attachment) }}" class="block truncate rounded-lg bg-white px-3 py-2 text-sm font-semibold text-[#647a0b]">
                                        {{ $attachment->original_name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endif

                <div class="space-y-3">
                    @forelse($messages as $message)
                        @php($isClient = $message->sender_type === \App\Models\CommunityMessage::SENDER_CLIENT)
                        <article class="rounded-xl {{ $isClient ? 'bg-[#f7faef]' : 'bg-gray-50' }} px-3 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <span class="min-w-0 truncate text-sm font-bold text-gray-900">{{ $message->authorName() }}</span>
                                <span class="shrink-0 text-[11px] text-gray-500">{{ $message->created_at?->format('d/m H:i') }}</span>
                            </div>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-800">{{ $message->content }}</p>
                            @if($message->attachments->isNotEmpty())
                                <div class="mt-3 space-y-2">
                                    @foreach($message->attachments as $attachment)
                                        <a href="{{ route('mobile.client.communities.attachments.download', $attachment) }}" class="block truncate rounded-lg bg-white px-3 py-2 text-sm font-semibold text-[#647a0b] ring-1 ring-[#eef1e5]">
                                            {{ $attachment->original_name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-xl bg-gray-50 px-4 py-8 text-center">
                            <p class="text-sm font-semibold text-gray-900">Aucun message</p>
                            <p class="mt-1 text-sm text-gray-500">Le salon est pret pour les prochains echanges.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            @if($selectedChannel->channel_type === 'annonces')
                <section class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm leading-6 text-amber-800">
                    Ce salon est reserve aux annonces de votre praticien.
                </section>
            @elseif($community->is_archived)
                <section class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 text-sm leading-6 text-gray-600">
                    Cette communaute est archivee. Vous pouvez relire les messages, mais plus en envoyer.
                </section>
            @else
                <form method="POST" action="{{ route('mobile.client.communities.messages.store', $community) }}" enctype="multipart/form-data" class="space-y-3 rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
                    @csrf
                    <input type="hidden" name="community_channel_id" value="{{ $selectedChannel->id }}">

                    <div>
                        <label for="content" class="text-sm font-semibold text-gray-800">Votre message</label>
                        <p class="mt-1 text-xs leading-5 text-gray-500">Vous pouvez aussi joindre jusqu'a 4 fichiers. Limite {{ $attachmentLimitLabel }} par fichier.</p>
                    </div>

                    <textarea id="content"
                              name="content"
                              rows="4"
                              required
                              class="w-full rounded-xl border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]"
                              placeholder="Ecrivez au groupe...">{{ old('content') }}</textarea>
                    <x-input-error :messages="$errors->get('content')" class="mt-2" />

                    <input type="file" name="attachments[]" multiple class="block w-full rounded-xl border border-gray-300 bg-white text-sm file:mr-3 file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-gray-700">
                    <x-input-error :messages="$errors->get('attachments')" class="mt-2" />
                    <x-input-error :messages="$errors->get('attachments.*')" class="mt-2" />

                    <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-[#647a0b] px-4 py-3 text-sm font-semibold text-white">
                        Envoyer dans le salon
                    </button>
                </form>
            @endif
        @else
            <section class="rounded-2xl border border-[#e4e8d5] bg-white px-4 py-8 text-center shadow-sm">
                <p class="text-sm font-semibold text-gray-900">Aucun salon disponible</p>
                <p class="mt-1 text-sm text-gray-500">Votre praticien pourra ajouter des salons a cette communaute.</p>
            </section>
        @endif
    </div>
</x-mobile-client-layout>

@php
    $authorName = $message->authorName();
    $authorInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($authorName, 0, 1));
    $isPractitioner = $message->sender_type === \App\Models\CommunityMessage::SENDER_PRACTITIONER;
    $isPinned = $message->isPinned();
@endphp

<article class="rounded-[1.8rem] border px-5 py-4 shadow-sm {{ $isPractitioner ? 'border-[#dbe6c1] bg-[linear-gradient(135deg,_rgba(245,250,234,1),_rgba(255,255,255,1))]' : 'border-[#eceee5] bg-white' }}">
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl font-semibold {{ $isPractitioner ? 'bg-[#647a0b] text-white' : 'bg-[#f2f3ee] text-gray-700' }}">
                {{ $isPractitioner ? 'A' : $authorInitial }}
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm font-semibold text-gray-900">{{ $authorName }}</p>
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $isPractitioner ? 'bg-[#647a0b] text-white' : 'bg-gray-100 text-gray-600' }}">
                        {{ $isPractitioner ? $adminName . ' | Admin' : 'Membre' }}
                    </span>
                    @if($isPinned)
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold text-amber-700">Épinglé</span>
                    @endif
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ $message->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        @if(($canPin ?? false) && !$isPinned)
            <form method="POST" action="{{ route('communities.messages.pin', [$community, $message]) }}">
                @csrf
                <button type="submit" class="rounded-full border border-[#d7ddc8] px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:border-[#647a0b] hover:text-[#647a0b]">
                    Épingler
                </button>
            </form>
        @endif
    </div>

    <div class="mt-4 whitespace-pre-line text-sm leading-7 text-gray-700">{{ $message->content }}</div>

    @include('communities.partials.attachment-list', [
        'attachments' => $message->attachments,
        'downloadRouteName' => $downloadRouteName,
    ])
</article>

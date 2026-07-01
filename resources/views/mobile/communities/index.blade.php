@php
    $total = $communities->count();
    $activeMembers = $communities->sum('active_members_count');
    $messages = $communities->sum('messages_count');
@endphp

<x-mobile-layout title="Communautes">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-comments text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Communautes</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Groupes clients, messages, annonces et invitations.
                </p>
            </div>

            <a href="{{ route('mobile.menu') }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500 shadow-sm"
               aria-label="Retour au menu">
                <i class="fas fa-bars text-xs"></i>
            </a>
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

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Groupes</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Membres</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $activeMembers }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Messages</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $messages }}</div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            <a href="{{ route('mobile.communities.create') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                Ajouter
            </a>
            <a href="{{ route('communities.index') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Vue web
            </a>
        </div>

        @if($communities->isNotEmpty())
            <div class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <label class="flex h-10 items-center gap-2 rounded-lg bg-[#f7f8f1] px-3">
                    <i class="fas fa-search text-[11px] text-gray-400"></i>
                    <input type="search"
                           id="mobileCommunitySearch"
                           placeholder="Rechercher une communaute"
                           class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:ring-0"
                           oninput="filterMobileCommunities()">
                </label>
            </div>
        @endif

        @if($communities->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-comments text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucune communaute</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Creez un espace prive pour suivre un groupe, un programme ou une promotion.
                </p>
            </div>
        @else
            <div id="mobileCommunityList" class="space-y-2">
                @foreach($communities as $community)
                    @php
                        $statusClass = $community->is_archived
                            ? 'border-gray-200 bg-gray-50 text-gray-600'
                            : 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]';
                        $searchText = trim($community->name . ' ' . ($community->description ?? ''));
                    @endphp

                    <a href="{{ route('mobile.communities.show', $community) }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]"
                       data-community="{{ Str::lower($searchText) }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">
                                    {{ $community->name }}
                                </h2>
                                <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                    {{ $community->description ?: 'Communaute sans description' }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $statusClass }}">
                                {{ $community->is_archived ? 'Archivee' : 'Active' }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-3 gap-2">
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Actifs</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $community->active_members_count }}</div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Invites</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $community->invited_members_count }}</div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Salons</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $community->channels_count }}</div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $community->messages_count }} messages
                            </span>
                            @if($community->channels->whereNotNull('pinned_community_message_id')->isNotEmpty())
                                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                    Message epingle
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function filterMobileCommunities() {
            const input = document.getElementById('mobileCommunitySearch');
            const filter = input ? input.value.toLowerCase() : '';
            const items = document.querySelectorAll('#mobileCommunityList > a');

            items.forEach((item) => {
                const text = item.getAttribute('data-community') || '';
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>

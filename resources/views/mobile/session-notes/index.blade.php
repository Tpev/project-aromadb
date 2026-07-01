@php
    use Illuminate\Support\Str;

    $fullName = trim(($clientProfile->first_name ?? '') . ' ' . ($clientProfile->last_name ?? '')) ?: 'Client sans nom';
    $total = $sessionNotes->count();
    $latest = $sessionNotes->first();
    $templateCount = $sessionNotes->filter(fn ($note) => (bool) $note->template)->count();
@endphp

<x-mobile-layout :title="'Notes - ' . $fullName">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <a href="{{ route('mobile.clients.show', $clientProfile) }}"
                   class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                    <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                    Fiche client
                </a>
                <h1 class="break-words text-xl font-semibold leading-tight text-gray-900">Notes de seance</h1>
                <p class="mt-1 break-words text-sm leading-snug text-gray-600">{{ $fullName }}</p>
            </div>

            <a href="{{ route('mobile.session-notes.create', $clientProfile) }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#647a0b] text-white shadow-sm"
               aria-label="Creer une note">
                <i class="fas fa-plus text-xs"></i>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Notes</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Derniere</div>
                <div class="mt-1 truncate text-sm font-semibold text-gray-900">
                    {{ $latest?->created_at?->format('d/m/Y') ?: '-' }}
                </div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Templates</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $templateCount }}</div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            <a href="{{ route('mobile.session-notes.create', $clientProfile) }}"
               class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                Ajouter
            </a>
            <a href="{{ route('session_notes.index', $clientProfile) }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Vue web
            </a>
        </div>

        @if($sessionNotes->isNotEmpty())
            <div class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <label class="flex h-10 items-center gap-2 rounded-lg bg-[#f7f8f1] px-3">
                    <i class="fas fa-search text-[11px] text-gray-400"></i>
                    <input type="search"
                           id="mobileSessionNoteSearch"
                           placeholder="Rechercher une note"
                           class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:ring-0"
                           oninput="filterMobileSessionNotes()">
                </label>
            </div>
        @endif

        @if($sessionNotes->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-notes-medical text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucune note</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Ajoutez une note pour garder le suivi de vos seances avec ce client.
                </p>
            </div>
        @else
            <div id="mobileSessionNoteList" class="space-y-2">
                @foreach($sessionNotes as $note)
                    @php
                        $plain = trim(strip_tags($note->note ?? ''));
                        $preview = Str::limit($plain, 150);
                        $templateTitle = $note->template?->title;
                        $searchText = Str::lower(trim(($note->created_at?->format('d/m/Y') ?? '') . ' ' . $plain . ' ' . ($templateTitle ?? '')));
                    @endphp

                    <a href="{{ route('mobile.session-notes.show', $note) }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]"
                       data-session-note="{{ $searchText }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-medium text-gray-500">
                                    {{ $note->created_at?->format('d/m/Y H:i') ?: '-' }}
                                </p>
                                <h2 class="mt-1 line-clamp-2 text-sm font-semibold leading-snug text-gray-900">
                                    {{ $preview ?: '(note vide)' }}
                                </h2>
                            </div>
                            <i class="fas fa-chevron-right shrink-0 pt-1 text-[10px] text-gray-300"></i>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @if($templateTitle)
                                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-[#647a0b]">
                                    {{ $templateTitle }}
                                </span>
                            @else
                                <span class="rounded-full bg-gray-50 px-2 py-1 text-[11px] text-gray-500">
                                    Sans template
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function filterMobileSessionNotes() {
            const input = document.getElementById('mobileSessionNoteSearch');
            const filter = input ? input.value.toLowerCase() : '';
            const items = document.querySelectorAll('#mobileSessionNoteList > a');

            items.forEach((item) => {
                const text = item.getAttribute('data-session-note') || '';
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>

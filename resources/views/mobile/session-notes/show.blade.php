@php
    $clientProfile = $sessionNote->clientProfile;
    $fullName = trim(($clientProfile->first_name ?? '') . ' ' . ($clientProfile->last_name ?? '')) ?: 'Client sans nom';
    $raw = $sessionNote->note ?? '';
    $hasContent = trim(strip_tags($raw)) !== '';
    $textOnly = trim(strip_tags($raw)) === trim($raw);
@endphp

<x-mobile-layout :title="'Note - ' . $fullName">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.session-notes.index', $clientProfile) }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Notes de seance
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">
                Note du {{ $sessionNote->created_at?->format('d/m/Y') ?: '-' }}
            </h1>
            <p class="mt-1 break-words text-sm leading-snug text-gray-600">{{ $fullName }}</p>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        <section class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-gray-500">Creee le</p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-900">
                        {{ $sessionNote->created_at?->format('d/m/Y H:i') ?: '-' }}
                    </p>
                </div>

                @if($sessionNote->template?->title)
                    <span class="shrink-0 rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] font-medium text-[#647a0b]">
                        {{ $sessionNote->template->title }}
                    </span>
                @else
                    <span class="shrink-0 rounded-full bg-gray-50 px-2 py-1 text-[11px] font-medium text-gray-500">
                        Sans template
                    </span>
                @endif
            </div>
        </section>

        <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            @if(! $hasContent)
                <p class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-4 text-center text-sm text-gray-600">
                    Cette note ne contient pas encore de contenu.
                </p>
            @else
                <div class="prose prose-sm max-w-none text-gray-800 [&_img]:max-w-full [&_img]:rounded-lg">
                    @if($textOnly)
                        {!! nl2br(e($raw)) !!}
                    @else
                        {!! $raw !!}
                    @endif
                </div>
            @endif
        </section>

        <div class="mt-4 grid grid-cols-2 gap-2">
            <a href="{{ route('mobile.session-notes.edit', $sessionNote) }}"
               class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                Modifier
            </a>

            <form method="POST"
                  action="{{ route('mobile.session-notes.destroy', $sessionNote) }}"
                  onsubmit="return confirm('Supprimer cette note ?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 text-sm font-semibold text-red-700 shadow-sm active:scale-[0.99]">
                    Supprimer
                </button>
            </form>
        </div>
    </div>
</x-mobile-layout>

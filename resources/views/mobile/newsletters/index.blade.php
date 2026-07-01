@php
    $total = $newsletters->count();
    $sent = $newsletters->where('status', 'sent')->count();
    $drafts = $newsletters->where('status', '!=', 'sent')->count();
    $remaining = max(0, $quotaLimit - $quotaUsed);
@endphp

<x-mobile-layout title="Newsletters">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-envelope-open-text text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Newsletters</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Redigez, ciblez et envoyez vos emails clients.
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

        @unless($canUseNewsletters)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-amber-700">
                        <i class="fas fa-lock text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-semibold">Module newsletter reserve Premium</h2>
                        <p class="mt-1 leading-snug">
                            Les actions de creation, modification et envoi sont disponibles avec l offre Premium.
                        </p>
                    </div>
                </div>
                <a href="{{ url('/license-tiers/pricing') }}"
                   class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg bg-white px-3 text-xs font-semibold text-amber-900 shadow-sm">
                    Voir les offres
                </a>
            </div>
        @endunless

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Newsletters</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Envoyees</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $sent }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Brouillons</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $drafts }}</div>
            </div>
        </div>

        <section class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Quota {{ $monthKey }}</h2>
                    <p class="mt-1 text-xs leading-snug text-gray-500">
                        {{ number_format($quotaUsed) }} / {{ number_format($quotaLimit) }} emails envoyes.
                    </p>
                </div>
                <span class="shrink-0 rounded-full border border-[#647a0b]/20 bg-[#647a0b]/10 px-2 py-0.5 text-[10px] font-medium text-[#647a0b]">
                    Reste {{ number_format($remaining) }}
                </span>
            </div>
            <div class="mt-3 h-2 rounded-full bg-gray-100">
                <div class="h-2 rounded-full bg-[#647a0b]" style="width: {{ $quotaPercent }}%;"></div>
            </div>
        </section>

        <div class="mb-4 grid grid-cols-2 gap-2">
            @if($canUseNewsletters)
                <a href="{{ route('mobile.newsletters.create') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                    Ajouter
                </a>
            @else
                <a href="{{ url('/license-tiers/pricing') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg bg-amber-100 px-3 text-sm font-semibold text-amber-900 shadow-sm active:scale-[0.99]">
                    Debloquer
                </a>
            @endif
            <a href="{{ route('newsletters.index') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Vue web
            </a>
        </div>

        @if($newsletters->isNotEmpty())
            <div class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <label class="flex h-10 items-center gap-2 rounded-lg bg-[#f7f8f1] px-3">
                    <i class="fas fa-search text-[11px] text-gray-400"></i>
                    <input type="search"
                           id="mobileNewsletterSearch"
                           placeholder="Rechercher une newsletter"
                           class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:ring-0"
                           oninput="filterMobileNewsletters()">
                </label>
            </div>
        @endif

        @if($newsletters->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-envelope-open-text text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucune newsletter</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Preparez un email simple, puis choisissez tous vos clients ou une audience.
                </p>
            </div>
        @else
            <div id="mobileNewsletterList" class="space-y-2">
                @foreach($newsletters as $newsletter)
                    @php
                        $isSent = $newsletter->status === 'sent';
                        $badgeClass = $isSent
                            ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]'
                            : 'border-gray-200 bg-gray-50 text-gray-600';
                        $searchText = trim($newsletter->title . ' ' . $newsletter->subject . ' ' . ($newsletter->audience?->name ?? ''));
                        $href = $canUseNewsletters ? route('mobile.newsletters.show', $newsletter) : url('/license-tiers/pricing');
                    @endphp

                    <a href="{{ $href }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]"
                       data-newsletter="{{ Str::lower($searchText) }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">{{ $newsletter->title }}</h2>
                                <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                    {{ $newsletter->subject ?: 'Sujet non renseigne' }}
                                </p>
                            </div>
                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $badgeClass }}">
                                {{ $isSent ? 'Envoyee' : 'Brouillon' }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Destinataires</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $newsletter->recipients_count }}</div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Audience</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                    {{ $newsletter->audience?->name ?: 'Tous' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $newsletter->updated_at?->format('d/m/Y') ?: 'Date inconnue' }}
                            </span>
                            @if($newsletter->sent_at)
                                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                    Envoyee le {{ $newsletter->sent_at->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function filterMobileNewsletters() {
            const input = document.getElementById('mobileNewsletterSearch');
            const filter = input ? input.value.toLowerCase() : '';
            const items = document.querySelectorAll('#mobileNewsletterList > a');

            items.forEach((item) => {
                const text = item.getAttribute('data-newsletter') || '';
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>

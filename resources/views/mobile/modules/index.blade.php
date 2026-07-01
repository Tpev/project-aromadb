@php
    $toneClasses = [
        'green' => 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
        'red' => 'border-red-200 bg-red-50 text-red-700',
        'slate' => 'border-gray-200 bg-gray-50 text-gray-600',
    ];
@endphp

<x-mobile-layout :title="$title">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas {{ $icon ?? 'fa-layer-group' }} text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">
                    {{ $title }}
                </h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    {{ $subtitle ?? '' }}
                </p>
            </div>

            <a href="{{ route('mobile.menu') }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500 shadow-sm"
               aria-label="{{ __('Retour au menu') }}">
                <i class="fas fa-bars text-xs"></i>
            </a>
        </div>

        @if(!empty($stats))
            <div class="mb-4 grid grid-cols-3 gap-2">
                @foreach($stats as $stat)
                    <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                        <div class="text-[11px] font-medium leading-tight text-gray-500">
                            {{ $stat['label'] }}
                        </div>
                        <div class="mt-1 truncate text-base font-semibold text-gray-900">
                            {{ $stat['value'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mb-4 grid grid-cols-2 gap-2">
            @if($primaryAction)
                <a href="{{ $primaryAction['href'] }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                    {{ $primaryAction['label'] }}
                </a>
            @endif

            @if($webAction)
                <a href="{{ $webAction['href'] }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                    {{ $webAction['label'] }}
                </a>
            @endif
        </div>

        @if($items->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas {{ $icon ?? 'fa-layer-group' }} text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">
                    {{ $emptyTitle ?? 'Aucun element' }}
                </h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    {{ $emptyBody ?? 'Les elements apparaitront ici.' }}
                </p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($items as $item)
                    @php
                        $href = $item['href'] ?? null;
                        $badgeTone = $item['badgeTone'] ?? 'slate';
                        $badgeClass = $toneClasses[$badgeTone] ?? $toneClasses['slate'];
                    @endphp

                    <a href="{{ $href ?: '#' }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99] {{ $href ? '' : 'pointer-events-none' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">
                                    {{ $item['title'] ?? 'Sans titre' }}
                                </h2>
                                @if(!empty($item['subtitle']))
                                    <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                        {{ $item['subtitle'] }}
                                    </p>
                                @endif
                            </div>

                            @if(!empty($item['badge']))
                                <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $badgeClass }}">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </div>

                        @if(!empty($item['meta']))
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach(array_filter($item['meta']) as $meta)
                                    <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                        {{ $meta }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-mobile-layout>

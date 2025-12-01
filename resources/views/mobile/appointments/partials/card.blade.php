@php
    $isExternal = $appointment->external;
    $date       = $appointment->appointment_date;
    $isPast     = $date->isPast();

    $clientName = $isExternal
        ? ($appointment->notes ?: 'Occupé')
        : trim(
            optional($appointment->clientProfile)->first_name . ' ' .
            optional($appointment->clientProfile)->last_name
        );

    $productName = optional($appointment->product)->name ?? '—';

    $status = ucfirst($appointment->status ?? 'en attente');

    $statusClasses = match ($appointment->status) {
        'Complété'    => 'bg-green-50 text-green-700 border-green-100',
        'Annulé'      => 'bg-red-50 text-red-700 border-red-100',
        'En attente',
        'pending'     => 'bg-amber-50 text-amber-700 border-amber-100',
        default       => 'bg-slate-50 text-slate-700 border-slate-100',
    };

    $cardBorder = $isExternal
        ? 'border-slate-200 bg-slate-50/60'
        : ($isPast
            ? 'border-[#854f38]/20 bg-white'
            : 'border-[#647a0b]/15 bg-white');

    $textMuted = $isPast ? 'text-gray-400' : 'text-gray-500';
@endphp

{{-- Corrected href for mobile --}}
<a href="{{ $isExternal ? '#' : route('mobile.appointments.show', $appointment) }}"

   @if(!$isExternal)
       class="block rounded-2xl border {{ $cardBorder }} p-4 shadow-sm active:scale-[0.99] transition transform"
   @else
       class="block rounded-2xl border {{ $cardBorder }} p-4 opacity-80 pointer-events-none"
   @endif
>

    {{-- Date + status --}}
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-[11px] uppercase tracking-wide text-gray-400">
                <i class="fas fa-calendar-alt mr-1.5"></i>
                {{ $date->translatedFormat('d M Y') }}
            </p>
            <p class="mt-0.5 text-sm font-semibold text-gray-900 flex items-center gap-1.5">
                <i class="fas fa-clock text-[11px] text-[#647a0b]"></i>
                {{ $date->format('H:i') }}
                <span class="mx-1 text-gray-300">•</span>
                <span class="text-xs text-gray-500">{{ $appointment->duration }} min</span>
            </p>
        </div>

        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold border {{ $statusClasses }}">
            {{ $status }}
        </span>
    </div>

    {{-- Client + prestation --}}
    <div class="mt-3 space-y-1.5">
        <p class="text-sm font-medium text-gray-900 flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full
                         {{ $isExternal ? 'bg-slate-200 text-slate-600' : 'bg-[#647a0b]/10 text-[#647a0b]' }}">
                <i class="fas {{ $isExternal ? 'fa-lock' : 'fa-user' }} text-[11px]"></i>
            </span>
            <span>{{ $clientName }}</span>
        </p>

        <p class="text-xs {{ $textMuted }} flex items-center gap-2">
            <i class="fas fa-spa text-[11px]"></i>
            <span class="truncate">{{ $productName }}</span>
        </p>
    </div>

    {{-- Footer mini-infos --}}
    <div class="mt-3 flex items-center justify-between text-[11px] text-gray-400">
        <div class="flex items-center gap-2">
            @if($appointment->type === 'visio' || optional($appointment->product)->visio)
                <span class="inline-flex items-center gap-1">
                    <i class="fas fa-video text-[10px]"></i> Visio
                </span>
            @elseif($appointment->type === 'domicile' || optional($appointment->product)->adomicile)
                <span class="inline-flex items-center gap-1">
                    <i class="fas fa-home text-[10px]"></i> Domicile
                </span>
            @elseif(optional($appointment->product)->dans_le_cabinet)
                <span class="inline-flex items-center gap-1">
                    <i class="fas fa-map-marker-alt text-[10px]"></i> Cabinet
                </span>
            @endif
        </div>

        @unless($isExternal)
            <span class="inline-flex items-center gap-1 text-[11px] text-[#647a0b]">
                Voir le détail
                <i class="fas fa-chevron-right text-[9px]"></i>
            </span>
        @endunless
    </div>

</a>

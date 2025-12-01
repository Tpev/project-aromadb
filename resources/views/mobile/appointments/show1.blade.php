{{-- resources/views/mobile/appointments/show.blade.php --}}
@php
    use Carbon\Carbon;

    $date    = $appointment->appointment_date;
    $isPast  = $date->isPast();

    $client  = $appointment->clientProfile;
    $product = $appointment->product;

    $statusLabel = ucfirst($appointment->status ?? 'En attente');

    $statusClasses = match ($appointment->status) {
        'Complété'    => 'bg-green-50 text-green-700 border-green-100',
        'Annulé'      => 'bg-red-50 text-red-700 border-red-100',
        'En attente',
        'pending'     => 'bg-amber-50 text-amber-700 border-amber-100',
        'Payée',
        'paid'        => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        default       => 'bg-slate-50 text-slate-700 border-slate-100',
    };

    $modeIcon = 'fa-question-circle';
    if (str_contains(strtolower($mode), 'visio')) {
        $modeIcon = 'fa-video';
    } elseif (str_contains(strtolower($mode), 'domicile')) {
        $modeIcon = 'fa-home';
    } elseif (str_contains(strtolower($mode), 'cabinet')) {
        $modeIcon = 'fa-map-marker-alt';
    }

    $whenLabel = $isPast ? 'Rendez-vous passé' : 'Rendez-vous à venir';
    $whenBadge = $isPast
        ? 'bg-slate-100 text-slate-600 border-slate-200'
        : 'bg-[#647a0b]/10 text-[#647a0b] border-[#d7dfaa]';
@endphp

<x-mobile-layout :title="__('Détails du rendez-vous')">
    <div class="px-4 pt-4 pb-24 space-y-4">

        {{-- Top header card --}}
        <div class="rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-gray-400 flex items-center gap-1.5">
                        <i class="fas fa-calendar-alt text-[11px]"></i>
                        {{ $date->translatedFormat('d M Y') }}
                    </p>
                    <p class="mt-1 flex items-center gap-2 text-sm font-semibold text-gray-900">
                        <i class="fas fa-clock text-[11px] text-[#647a0b]"></i>
                        {{ $date->format('H:i') }}
                        <span class="mx-1 text-gray-300">•</span>
                        <span class="text-xs text-gray-500">{{ $appointment->duration }} min</span>
                    </p>

                    @if($product)
                        <p class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-[#f5f7eb] px-2.5 py-1 text-[10px] font-medium text-[#4b5722]">
                            <i class="fas fa-spa text-[10px]"></i>
                            {{ $product->name }}
                        </p>
                    @endif
                </div>

                <div class="flex flex-col items-end gap-1">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold border {{ $statusClasses }}">
                        {{ $statusLabel }}
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-medium border {{ $whenBadge }}">
                        <i class="fas {{ $isPast ? 'fa-history' : 'fa-hourglass-half' }} text-[9px] mr-1"></i>
                        {{ $whenLabel }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Client card --}}
        <div class="rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-[#647a0b]/10 flex items-center justify-center text-[#647a0b]">
                    <i class="fas fa-user text-sm"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $client->first_name }} {{ $client->last_name }}
                    </p>
                    <p class="text-[11px] text-gray-500">
                        Détails du client
                    </p>
                </div>
            </div>

            <div class="mt-3 space-y-2 text-xs">
                <div class="flex items-center gap-2">
                    <i class="fas fa-phone text-[11px] text-gray-400 w-4"></i>
                    <span class="text-gray-700">
                        {{ $client->phone ?? 'Téléphone non renseigné' }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-envelope text-[11px] text-gray-400 w-4"></i>
                    <span class="text-gray-700 break-all">
                        {{ $client->email ?? 'Email non renseigné' }}
                    </span>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('client_profiles.show', $client->id) }}"
                   class="inline-flex items-center gap-1.5 text-[11px] text-[#647a0b] font-medium">
                    Voir la fiche client
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </a>
            </div>
        </div>

        {{-- Prestation / mode --}}
        <div class="rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                Prestation
            </h2>

            <p class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-spa text-[11px]"></i>
                </span>
                <span>{{ $product->name ?? 'Aucune prestation' }}</span>
            </p>

            <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                <div class="flex flex-col gap-1">
                    <span class="text-[11px] text-gray-400 uppercase tracking-wide">Durée</span>
                    <span class="text-gray-800">{{ $appointment->duration }} minutes</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-[11px] text-gray-400 uppercase tracking-wide">Mode</span>
                    <span class="text-gray-800 flex items-center gap-1.5">
                        <i class="fas {{ $modeIcon }} text-[11px] text-[#647a0b]"></i>
                        {{ $mode }}
                    </span>
                </div>
            </div>

            @if($appointment->practiceLocation && str_contains(strtolower($mode), 'cabinet'))
                <div class="mt-3 border-t border-[#f1f3e6] pt-3 text-xs">
                    <span class="text-[11px] text-gray-400 uppercase tracking-wide">Cabinet</span>
                    <p class="text-gray-800 mt-0.5">
                        {{ $appointment->practiceLocation->label ?? 'Cabinet' }}<br>
                        <span class="text-gray-500">
                            {{ $appointment->practiceLocation->address ?? '' }}
                        </span>
                    </p>
                </div>
            @endif
        </div>

        {{-- Visio links (if any) --}}
        @if($meetingLink || $meetingLinkPatient)
            <div class="rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm space-y-3">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                    Liens de connexion
                </h2>

                @if($meetingLink)
                    <div class="text-xs">
                        <p class="text-gray-500 mb-1">Lien thérapeute</p>
                        <a href="{{ $meetingLink }}"
                           class="inline-flex items-center justify-between w-full px-3 py-2 rounded-xl bg-[#647a0b] text-white text-[11px] font-medium active:scale-[0.99] transition">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-video text-[11px]"></i>
                                Ouvrir la salle de visio
                            </span>
                            <i class="fas fa-external-link-alt text-[10px]"></i>
                        </a>
                    </div>
                @endif

                @if($meetingLinkPatient)
                    <div class="text-xs">
                        <p class="text-gray-500 mb-1">Lien client</p>
                        <div class="flex flex-col gap-2">
                            <button
                                type="button"
                                x-data="{ copied: false }"
                                @click="
                                    navigator.clipboard.writeText('{{ $meetingLinkPatient }}');
                                    copied = true;
                                    setTimeout(() => copied = false, 2000);
                                "
                                class="inline-flex items-center justify-between w-full px-3 py-2 rounded-xl bg-white border border-[#e4e8d5] text-[11px] text-gray-700 active:scale-[0.99] transition">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-link text-[11px] text-[#647a0b]"></i>
                                    Copier le lien pour le client
                                </span>
                                <span x-show="!copied" class="text-[10px] text-gray-400">Copier</span>
                                <span x-show="copied" x-cloak class="text-[10px] text-[#647a0b] font-semibold">Copié ✓</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Notes --}}
        <div class="rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                Notes
            </h2>
            <p class="text-sm text-gray-800 whitespace-pre-line">
                {{ $appointment->notes ?: 'Aucune note pour ce rendez-vous.' }}
            </p>
        </div>

        {{-- Actions --}}
        <div class="space-y-3">
            {{-- Primary actions --}}
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('appointments.edit', $appointment->id) }}"
                   class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-white border border-[#e4e8d5] text-[12px] font-medium text-gray-800 active:scale-[0.99] transition">
                    <i class="fas fa-edit text-[11px] mr-1.5"></i>
                    Modifier
                </a>

                <a href="{{ route('mobile.appointments.index') }}"
                   class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-white border border-[#e4e8d5] text-[12px] font-medium text-gray-800 active:scale-[0.99] transition">
                    <i class="fas fa-list text-[11px] mr-1.5"></i>
                    Tous les rendez-vous
                </a>
            </div>

            {{-- Secondary actions list --}}
            <div class="rounded-2xl border border-[#e4e8d5] bg-white p-2 shadow-sm text-[12px]">
                {{-- Mark as completed --}}
                @if($appointment->status !== 'Complété')
                    <form action="{{ route('appointments.complete', $appointment->id) }}"
                          method="POST"
                          onsubmit="return confirm('Marquer ce rendez-vous comme complété ?')">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-left text-gray-800">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-[11px] text-green-600"></i>
                                Marquer comme complété
                            </span>
                            <i class="fas fa-chevron-right text-[9px] text-gray-300"></i>
                        </button>
                    </form>
                @endif

                {{-- Delete --}}
                <form action="{{ route('appointments.destroy', $appointment->id) }}"
                      method="POST"
                      onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-left text-red-600 mt-1.5">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-trash text-[11px]"></i>
                            Annuler / supprimer le rendez-vous
                        </span>
                        <i class="fas fa-chevron-right text-[9px] text-red-300"></i>
                    </button>
                </form>

                {{-- Emargement --}}
                @can('update', $appointment)
                    @if($appointment->product?->requires_emargement && !$appointment->emargement_sent)
                        <form action="{{ route('emargement.send', $appointment->id) }}"
                              method="POST"
                              class="mt-1.5">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-left text-[#647a0b]">
                                <span class="flex items-center gap-2">
                                    <span class="text-lg leading-none">📄</span>
                                    Envoyer la feuille d’émargement
                                </span>
                                <i class="fas fa-chevron-right text-[9px] text-[#c3cc8b]"></i>
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</x-mobile-layout>

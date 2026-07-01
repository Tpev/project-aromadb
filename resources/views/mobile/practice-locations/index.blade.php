@php
    $total = $locations->count();
    $primaryCount = $locations->where('is_primary', true)->count();
    $sharedCount = $locations->where('is_shared', true)->count();
@endphp

<x-mobile-layout title="Lieux de pratique">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-map-marker-alt text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Lieux de pratique</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Cabinets et adresses utilises dans vos disponibilites et rendez-vous.
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

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Lieux</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Principal</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $primaryCount }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Partages</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $sharedCount }}</div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            <a href="{{ route('mobile.practice-locations.create') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                Ajouter
            </a>
            <a href="{{ route('mobile.availabilities.index') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Disponibilites
            </a>
        </div>

        @if($locations->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-map-marker-alt text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucun lieu</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Ajoutez votre cabinet principal pour organiser vos reservations.
                </p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($locations as $location)
                    @php
                        $isOwner = (int) $location->user_id === (int) auth()->id();
                        $appointmentsCount = $location->appointments_count ?? 0;
                        $availabilitiesCount = $location->availabilities_count ?? 0;
                    @endphp

                    <article class="rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">
                                    {{ $location->label ?: 'Lieu sans nom' }}
                                </h2>
                                <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                    {{ $location->full_address ?: 'Adresse non renseignee' }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $location->is_primary ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' : 'border-gray-200 bg-gray-50 text-gray-600' }}">
                                {{ $location->is_primary ? 'Principal' : ($location->is_shared ? 'Partage' : 'Cabinet') }}
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $availabilitiesCount }} disponibilite{{ $availabilitiesCount > 1 ? 's' : '' }}
                            </span>
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $appointmentsCount }} RDV lie{{ $appointmentsCount > 1 ? 's' : '' }}
                            </span>
                            @unless($isOwner)
                                <span class="rounded-full bg-amber-50 px-2 py-1 text-[11px] text-amber-700">
                                    Membre
                                </span>
                            @endunless
                        </div>

                        @if($isOwner)
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <a href="{{ route('mobile.practice-locations.edit', $location) }}"
                                   class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                                    <i class="fas fa-edit mr-1.5 text-[11px]"></i>
                                    Modifier
                                </a>
                                <form method="POST"
                                      action="{{ route('mobile.practice-locations.destroy', $location) }}"
                                      onsubmit="return confirm('Supprimer ce lieu ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-xs font-semibold text-red-600">
                                        <i class="fas fa-trash-alt mr-1.5 text-[11px]"></i>
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-mobile-layout>

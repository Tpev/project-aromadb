@php
    $start = \Carbon\Carbon::parse($event->start_date_time);
    $isVisio = ($event->event_type ?? 'in_person') === 'visio';
    $capacity = $event->limited_spot && $event->number_of_spot ? $event->number_of_spot : 'Illimite';
    $remaining = $event->limited_spot && $event->number_of_spot
        ? max(0, (int) $event->number_of_spot - (int) $event->reservations_count)
        : null;
    $description = $event->description;
    $descriptionLooksHtml = $description && preg_match('/<\/?[a-z][\s\S]*>/i', $description);
    $publicUrl = route('events.reserve.create', $event);
    $visioLink = $event->visio_host_link;
@endphp

<x-mobile-layout :title="$event->name">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.events.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Evenements
            </a>

            <div class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                        <i class="fas {{ $isVisio ? 'fa-video' : 'fa-calendar-plus' }} text-sm"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl font-semibold leading-tight text-gray-900">
                            {{ $event->name }}
                        </h1>
                        <p class="mt-1 text-sm leading-snug text-gray-600">
                            {{ $start->format('d/m/Y H:i') }} - {{ $event->duration }} min
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Format</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $isVisio ? 'Visio' : 'Lieu' }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Places</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $event->reservations_count }} / {{ $capacity }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Portail</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $event->showOnPortail ? 'Oui' : 'Non' }}</div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('mobile.events.edit', $event) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-edit mr-1.5 text-[11px]"></i>
                        Modifier
                    </a>
                    <a href="{{ $publicUrl }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-external-link-alt mr-1.5 text-[10px]"></i>
                        Lien public
                    </a>
                </div>
            </div>
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

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Infos pratiques</h2>

                <div class="mt-3 space-y-2">
                    <div class="rounded-lg bg-[#fbfcf7] p-3">
                        <div class="text-[11px] font-medium text-gray-500">{{ $isVisio ? 'Visio' : 'Lieu' }}</div>
                        <div class="mt-0.5 break-words text-sm font-semibold text-gray-900">
                            {{ $event->location ?: ($isVisio ? 'En ligne' : 'Lieu non renseigne') }}
                        </div>
                    </div>

                    @if($isVisio && $visioLink)
                        <a href="{{ $visioLink }}"
                           class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                            Ouvrir le lien visio
                        </a>
                    @endif

                    @if($event->associatedProduct)
                        <div class="rounded-lg bg-[#fbfcf7] p-3">
                            <div class="text-[11px] font-medium text-gray-500">Prestation liee</div>
                            <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                {{ $event->associatedProduct->name }}
                            </div>
                        </div>
                    @endif

                    @if($event->collect_payment)
                        <div class="rounded-lg bg-[#fbfcf7] p-3">
                            <div class="text-[11px] font-medium text-gray-500">Paiement</div>
                            <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                {{ number_format((float) $event->price, 2, ',', ' ') }} EUR TTC
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            @if($description)
                <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-900">Description</h2>
                    <div class="mt-2 text-sm leading-relaxed text-gray-700">
                        @if($descriptionLooksHtml)
                            {!! $description !!}
                        @else
                            {!! nl2br(e($description)) !!}
                        @endif
                    </div>
                </section>
            @endif

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Participants</h2>
                    @if($remaining !== null)
                        <span class="text-xs font-semibold text-[#647a0b]">{{ $remaining }} restantes</span>
                    @endif
                </div>

                @if($clients->isNotEmpty())
                    <form method="POST" action="{{ route('mobile.events.participants.add-client', $event) }}" class="mt-3">
                        @csrf
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Ajouter un client</span>
                            <select name="client_profile_id"
                                    required
                                    class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                                <option value="">Choisir</option>
                                @foreach($clients as $client)
                                    @php
                                        $clientName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
                                    @endphp
                                    <option value="{{ $client->id }}">{{ $clientName ?: $client->email }} - {{ $client->email }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="submit"
                                class="mt-2 inline-flex h-10 w-full items-center justify-center rounded-lg bg-[#647a0b] text-xs font-semibold text-white">
                            Ajouter au groupe
                        </button>
                    </form>
                @endif

                @if($event->reservations->isEmpty())
                    <div class="mt-3 rounded-lg border border-dashed border-[#d7ddc6] bg-[#fbfcf7] p-4 text-center">
                        <h3 class="text-sm font-semibold text-gray-900">Aucune reservation</h3>
                        <p class="mt-1 text-sm leading-snug text-gray-600">
                            Les inscriptions apparaitront ici.
                        </p>
                    </div>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach($event->reservations as $reservation)
                            <article class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate text-sm font-semibold text-gray-900">{{ $reservation->full_name }}</div>
                                        <div class="mt-1 truncate text-xs text-gray-600">{{ $reservation->email }}</div>
                                    </div>
                                    <span class="shrink-0 rounded-full border border-[#e4e8d5] bg-white px-2 py-0.5 text-[10px] font-medium text-gray-600">
                                        {{ $reservation->status ?: 'confirme' }}
                                    </span>
                                </div>
                                @if($reservation->phone)
                                    <div class="mt-2 text-xs text-gray-500">{{ $reservation->phone }}</div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <form method="POST"
                  action="{{ route('mobile.events.destroy', $event) }}"
                  onsubmit="return confirm('Supprimer cet evenement ? Les reservations liees seront supprimees.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-sm font-semibold text-red-600">
                    <i class="fas fa-trash-alt mr-1.5 text-[11px]"></i>
                    Supprimer l evenement
                </button>
            </form>
        </div>
    </div>
</x-mobile-layout>

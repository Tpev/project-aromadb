@php
    $formatMoney = fn ($value) => number_format((float) $value, 2, ',', ' ') . ' EUR';
    $modeLabels = [
        'visio' => ['label' => 'Visio', 'icon' => 'fa-video'],
        'adomicile' => ['label' => 'Domicile', 'icon' => 'fa-home'],
        'en_entreprise' => ['label' => 'Entreprise', 'icon' => 'fa-building'],
        'dans_le_cabinet' => ['label' => 'Cabinet', 'icon' => 'fa-map-marker-alt'],
    ];
    $modeMeta = $modeLabels[$mode] ?? $modeLabels['dans_le_cabinet'];
@endphp

<x-mobile-layout :title="$product->name">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.products.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Prestations
            </a>

            <div class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}"
                             alt=""
                             class="h-14 w-14 shrink-0 rounded-lg object-cover">
                    @else
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                            <i class="fas fa-spa text-sm"></i>
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl font-semibold leading-tight text-gray-900">
                            {{ $product->name }}
                        </h1>
                        <p class="mt-1 line-clamp-3 text-sm leading-snug text-gray-600">
                            {{ $product->description ?: 'Prestation sans description' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Prix TTC</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $formatMoney($product->price_incl_tax) }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Duree</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $product->duration ?: '-' }} min</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">RDV</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $appointmentsCount }}</div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span class="rounded-full border border-[#647a0b]/20 bg-[#647a0b]/10 px-2 py-1 text-[11px] font-medium text-[#647a0b]">
                        <i class="fas {{ $modeMeta['icon'] }} mr-1 text-[10px]"></i>
                        {{ $modeMeta['label'] }}
                    </span>
                    <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                        {{ $product->can_be_booked_online ? 'Reservable en ligne' : 'Interne' }}
                    </span>
                    <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                        {{ $product->visible_in_portal ? 'Portail visible' : 'Portail masque' }}
                    </span>
                    @if($product->collect_payment)
                        <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                            Paiement en ligne
                        </span>
                    @endif
                    @if($product->requires_emargement)
                        <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                            Emargement requis
                        </span>
                    @endif
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('mobile.products.edit', $product) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-edit mr-1.5 text-[11px]"></i>
                        Modifier
                    </a>
                    <a href="{{ route('products.show', $product) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-external-link-alt mr-1.5 text-[10px]"></i>
                        Vue web
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Reservation</h2>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-3">
                        <div class="text-[11px] font-medium text-gray-500">A venir</div>
                        <div class="mt-0.5 text-base font-semibold text-gray-900">{{ $upcomingAppointmentsCount }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-3">
                        <div class="text-[11px] font-medium text-gray-500">Par jour max</div>
                        <div class="mt-0.5 text-base font-semibold text-gray-900">{{ $product->max_per_day ?: '-' }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-3">
                        <div class="text-[11px] font-medium text-gray-500">Creneaux</div>
                        <div class="mt-0.5 text-base font-semibold text-gray-900">{{ $product->availabilities_count }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-3">
                        <div class="text-[11px] font-medium text-gray-500">TVA</div>
                        <div class="mt-0.5 text-base font-semibold text-gray-900">{{ number_format((float) $product->tax_rate, 2, ',', ' ') }}%</div>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="{{ route('mobile.appointments.create') }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg bg-[#647a0b] text-xs font-semibold text-white">
                        <i class="fas fa-calendar-plus mr-1.5 text-[11px]"></i>
                        Nouveau RDV
                    </a>
                    <a href="{{ route('mobile.availabilities.index') }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-clock mr-1.5 text-[11px]"></i>
                        Creneaux
                    </a>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Automatisations</h2>

                <div class="mt-3 space-y-2">
                    <div class="flex items-start justify-between gap-3 rounded-lg bg-[#f7f8f1] p-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900">Questionnaire automatique</div>
                            <div class="mt-1 text-xs leading-snug text-gray-600">
                                @if($product->booking_questionnaire_enabled && $product->bookingQuestionnaire)
                                    {{ $product->bookingQuestionnaire->title }}
                                @else
                                    Aucun questionnaire lie.
                                @endif
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $product->booking_questionnaire_enabled ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' : 'border-gray-200 bg-white text-gray-600' }}">
                            {{ $product->booking_questionnaire_enabled ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>

                    <div class="flex items-start justify-between gap-3 rounded-lg bg-[#f7f8f1] p-3">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Prix visible</div>
                            <div class="mt-1 text-xs text-gray-600">Controle l affichage du tarif sur le portail.</div>
                        </div>
                        <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $product->price_visible_in_portal ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' : 'border-gray-200 bg-white text-gray-600' }}">
                            {{ $product->price_visible_in_portal ? 'Oui' : 'Non' }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Creneaux lies</h2>

                @if($product->availabilities->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">Aucun creneau n est rattache a cette prestation.</p>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach($product->availabilities->take(6) as $availability)
                            <article class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">
                                            Jour {{ $availability->day_of_week }} · {{ substr((string) $availability->start_time, 0, 5) }}-{{ substr((string) $availability->end_time, 0, 5) }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-600">
                                            {{ $availability->practiceLocation?->label ?: 'Lieu non renseigne' }}
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <form method="POST"
                  action="{{ route('mobile.products.destroy', $product) }}"
                  onsubmit="return confirm('Supprimer cette prestation ? Les liens existants seront detaches.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-sm font-semibold text-red-600">
                    <i class="fas fa-trash-alt mr-1.5 text-[11px]"></i>
                    Supprimer la prestation
                </button>
            </form>
        </div>
    </div>
</x-mobile-layout>

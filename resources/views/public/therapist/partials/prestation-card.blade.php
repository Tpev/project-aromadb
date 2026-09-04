@php
    /** @var \App\Models\Product $prestation */
    $prestation = $group->first();
    $truncatedDescription = \Illuminate\Support\Str::limit($prestation->description, 200);
    $hasCabinet = $group->contains(fn ($product) => (bool) $product->dans_le_cabinet);
    $hasDomicile = $group->contains(fn ($product) => (bool) $product->adomicile);
    $hasVisio = $group->contains(fn ($product) => (bool) $product->visio);
    $hasEntreprise = $group->contains(fn ($product) => (bool) $product->en_entreprise);
    $locationBadges = [];
    if ($hasCabinet) $locationBadges[] = ['📍', __('Cabinet')];
    if ($hasDomicile) $locationBadges[] = ['🏠', __('À domicile')];
    if ($hasVisio) $locationBadges[] = ['💻', __('Visio')];
    if ($hasEntreprise) $locationBadges[] = ['🏢', __('Entreprise')];
    $canCollectOnline = $group->contains(fn ($product) => (bool) $product->collect_payment);
@endphp

<div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 prestation-item bg-[#f9fafb]">
    @if($prestation->image)
        <img src="{{ asset('storage/' . $prestation->image) }}" alt="{{ $prestation->name }}" class="w-full h-48 object-cover">
    @endif

    <div class="p-6">
        <h4 class="text-2xl font-semibold text-[#647a0b]">{{ $prestation->name }}</h4>

        @if(count($locationBadges) > 0 || !is_null($prestation->duration) || $canCollectOnline)
            <div class="mt-3 flex flex-wrap gap-2 text-xs sm:text-sm">
                @foreach($locationBadges as [$icon, $label])
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-white border border-[#e4e8d5] text-[#647a0b]">
                        <span class="mr-1">{{ $icon }}</span> {{ $label }}
                    </span>
                @endforeach

                @if(!is_null($prestation->duration))
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-white border border-[#e4e8d5] text-gray-700">
                        <span class="mr-1">⏱</span> {{ $prestation->duration }} {{ __('min') }}
                    </span>
                @endif

                @if($canCollectOnline)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-white border border-[#e4e8d5] text-[#854f38]">
                        <span class="mr-1">💳</span> {{ __('Paiement en ligne possible') }}
                    </span>
                @endif
            </div>
        @endif

        @if($prestation->price_visible_in_portal && $prestation->price > 0)
            <p class="mt-3 text-gray-600 font-semibold">
                {{ __('Prix :') }} {{ number_format($prestation->price_incl_tax ?? $prestation->price, 2, ',', ' ') }} €
            </p>
        @endif

        @if(filled($prestation->description))
            <p class="mt-4 text-gray-700 prestation-description"
               data-full-text="{{ e($prestation->description) }}"
               data-truncated-text="{{ e($truncatedDescription) }}">
                {!! nl2br(e($truncatedDescription)) !!}
                @if(\Illuminate\Support\Str::length($prestation->description) > 200)
                    <span class="text-[#854f38] cursor-pointer voir-plus">{{ __('Voir plus') }}</span>
                @endif
            </p>
        @endif

        @if($prestation->brochure)
            <a href="{{ asset('storage/' . $prestation->brochure) }}" target="_blank"
               class="mt-4 inline-block text-[#854f38] hover:text-[#6a3f2c]">
                {{ __('Télécharger la brochure') }}
            </a>
        @endif

        @if(app(\App\Support\BookingV2Access::class)->enabledFor($therapist) && $therapist->accept_online_appointments)
            @php
                $bookableVariants = $group->filter(fn ($variant) =>
                    $variant->can_be_booked_online
                    && in_array((int) $variant->id, $directlyBookableProductIds ?? [], true)
                );
            @endphp
            @if($bookableVariants->isNotEmpty())
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach($bookableVariants as $variant)
                        <a href="{{ route('appointments.createPatient', ['therapist' => $therapist->id, 'product_id' => $variant->id]) }}"
                           class="inline-flex items-center justify-center rounded-full bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#8ea633]">
                            {{ $bookableVariants->count() === 1 ? __('Voir les créneaux') : $variant->direct_booking_variant_label }}
                        </a>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>

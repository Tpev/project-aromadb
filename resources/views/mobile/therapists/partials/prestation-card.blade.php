@php
    /** @var \App\Models\Product $prestation */
    $prestation = $group->first();
    $hasCabinet = $group->contains(fn ($product) => (bool) $product->dans_le_cabinet);
    $hasDomicile = $group->contains(fn ($product) => (bool) $product->adomicile);
    $hasVisio = $group->contains(fn ($product) => (bool) $product->visio);
    $hasEntreprise = $group->contains(fn ($product) => (bool) $product->en_entreprise);
    $canCollectOnline = $group->contains(fn ($product) => (bool) $product->collect_payment);
    $truncated = \Illuminate\Support\Str::limit(strip_tags($prestation->description ?? ''), 120);
@endphp

<div class="border border-gray-100 rounded-2xl px-3 py-3 bg-[#fdfbf8] space-y-2">
    <div class="flex items-start justify-between gap-2">
        <div class="space-y-1 min-w-0">
            <p class="text-[15px] font-semibold text-gray-900 break-words">{{ $prestation->name }}</p>
            <div class="flex flex-wrap gap-1.5 mt-1 text-[11px]">
                @if($hasCabinet)<span class="inline-flex items-center px-2 py-[3px] rounded-full bg-white text-gray-800 border border-[#e4e8d5]">📍 {{ __('Cabinet') }}</span>@endif
                @if($hasDomicile)<span class="inline-flex items-center px-2 py-[3px] rounded-full bg-white text-gray-800 border border-[#e4e8d5]">🏠 {{ __('À domicile') }}</span>@endif
                @if($hasVisio)<span class="inline-flex items-center px-2 py-[3px] rounded-full bg-white text-gray-800 border border-[#e4e8d5]">💻 {{ __('Visio') }}</span>@endif
                @if($hasEntreprise)<span class="inline-flex items-center px-2 py-[3px] rounded-full bg-white text-gray-800 border border-[#e4e8d5]">🏢 {{ __('Entreprise') }}</span>@endif
                @if(!is_null($prestation->duration))<span class="inline-flex items-center px-2 py-[3px] rounded-full bg-white text-gray-800 border border-[#e4e8d5]">⏱ {{ $prestation->duration }} {{ __('min') }}</span>@endif
                @if($canCollectOnline)<span class="inline-flex items-center px-2 py-[3px] rounded-full bg-white text-[#854f38] border border-[#e4e8d5]">💳 {{ __('Paiement en ligne possible') }}</span>@endif
            </div>
        </div>

        @if($prestation->price_visible_in_portal && $prestation->price > 0)
            <p class="text-[14px] font-semibold text-[#854f38] whitespace-nowrap">
                {{ number_format($prestation->price_incl_tax ?? $prestation->price, 2, ',', ' ') }} €
            </p>
        @endif
    </div>

    @if($truncated)
        <p class="mt-1 text-[13px] text-gray-700 leading-snug break-words">{{ $truncated }}</p>
    @endif

    @if(app(\App\Support\BookingV2Access::class)->enabledFor($therapist) && $therapist->accept_online_appointments)
        @php
            $bookableVariants = $group->filter(fn ($variant) =>
                $variant->can_be_booked_online
                && in_array((int) $variant->id, $directlyBookableProductIds ?? [], true)
            );
        @endphp
        <div class="flex flex-wrap gap-2 pt-1">
            @foreach($bookableVariants as $variant)
                <a href="{{ route('mobile.appointments.create_from_therapist', ['slug' => $therapist->slug, 'product_id' => $variant->id]) }}"
                   class="inline-flex min-h-10 items-center justify-center rounded-full bg-[#647a0b] px-4 py-2 text-xs font-semibold text-white">
                    {{ $bookableVariants->count() === 1 ? __('Voir les créneaux') : $variant->direct_booking_variant_label }}
                </a>
            @endforeach
        </div>
    @endif
</div>

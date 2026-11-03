@php
    $portalOfferOrder = old('portal_offer_order', $user->portal_offer_order === 'prestations_first' ? 'prestations_first' : 'packs_first');
    $portalOrderOptions = [
        'prestations_first' => ['title' => 'Prestations individuelles d’abord', 'steps' => ['Prestations', 'Packs']],
        'packs_first' => ['title' => 'Packs d’abord', 'steps' => ['Packs', 'Prestations']],
    ];
@endphp
<div id="portal-offer-order-settings" class="mb-6 rounded-2xl border border-[#dfe6c7] bg-white p-5 sm:p-6">
    <fieldset aria-describedby="portal-offer-order-help">
        <legend class="text-lg font-semibold text-[#647a0b]">Ordre des offres sur le portail</legend>
        <p id="portal-offer-order-help" class="mt-2 text-sm leading-relaxed text-gray-600">
            Choisissez les offres à présenter en premier. Ce choix s’applique aussi à l’intérieur de chaque catégorie.
            Vos catégories conservent leur ordre.
        </p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @foreach($portalOrderOptions as $value => $option)
                <label class="min-w-0 cursor-pointer">
                    <input type="radio" name="portal_offer_order" value="{{ $value }}"
                           class="portal-order-input sr-only" @checked($portalOfferOrder === $value)>
                    <span class="portal-order-option">
                        <span class="flex items-start gap-3">
                            <span class="portal-order-dot" aria-hidden="true"></span>
                            <span class="text-sm font-semibold leading-5 text-gray-800">{{ $option['title'] }}</span>
                        </span>
                        <span class="mt-4 flex flex-wrap items-center gap-2 text-xs text-gray-600" aria-hidden="true">
                            <span class="rounded-md border border-gray-200 bg-white px-2 py-1.5">1. {{ $option['steps'][0] }}</span>
                            <span>→</span>
                            <span class="rounded-md border border-gray-200 bg-white px-2 py-1.5">2. {{ $option['steps'][1] }}</span>
                        </span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('portal_offer_order')
            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </fieldset>
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm">
        <p class="text-gray-500">Enregistrez vos modifications pour appliquer ce choix.</p>
        @if($user->slug)
            <a href="{{ route('therapist.show', $user->slug) }}" target="_blank" rel="noopener noreferrer"
               class="font-semibold text-[#647a0b] underline underline-offset-2">Voir mon portail <span aria-hidden="true">↗</span></a>
        @endif
    </div>
</div>
<style>
    #portal-offer-order-settings .portal-order-option {
        display: block;
        height: 100%;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 16px;
        background: #fff;
        transition: border-color 150ms, background-color 150ms;
    }
    #portal-offer-order-settings .portal-order-dot {
        flex-shrink: 0;
        width: 18px;
        height: 18px;
        margin-top: 1px;
        border: 1px solid #9ca3af;
        border-radius: 50%;
        background: #fff;
    }
    #portal-offer-order-settings .portal-order-input:checked + .portal-order-option {
        border-color: #647a0b;
        background: #f5f7eb;
        box-shadow: 0 0 0 1px #647a0b;
    }
    #portal-offer-order-settings .portal-order-input:checked + .portal-order-option .portal-order-dot {
        border: 5px solid #647a0b;
    }
    #portal-offer-order-settings .portal-order-input:focus-visible + .portal-order-option {
        outline: 3px solid #854f38;
        outline-offset: 3px;
    }
</style>

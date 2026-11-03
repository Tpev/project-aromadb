@php
    $selectedCalendarColor = \App\Support\ProductCalendarColor::normalize(old('calendar_color', ($product ?? null)?->calendar_color)) ?? '';
@endphp

<section class="details-box product-calendar-color" aria-labelledby="calendar-color-heading" x-data="{
    color: @js($selectedCalendarColor),
    textColor() {
        const hex = this.color || '#647A0B';
        const channels = [1, 3, 5].map(offset => {
            const value = parseInt(hex.slice(offset, offset + 2), 16) / 255;
            return value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4;
        });
        const luminance = channels[0] * 0.2126 + channels[1] * 0.7152 + channels[2] * 0.0722;
        return (luminance + 0.05) / 0.05 >= 1.05 / (luminance + 0.05) ? '#000000' : '#FFFFFF';
    }
}">
    <h3 id="calendar-color-heading" class="details-label" style="margin:0;font-size:14px;font-weight:600;color:#374151;">Couleur dans l’agenda</h3>
    <p style="margin:6px 0 12px;font-size:13px;color:#6b7280;">Repérez cette prestation dans vos rendez-vous passés et à venir.</p>

    <input type="hidden" name="calendar_color" value="{{ $selectedCalendarColor }}" :value="color">
    <div class="product-calendar-palette" role="group" aria-label="Couleurs proposées">
        @foreach(\App\Support\ProductCalendarColor::PALETTE as $hex => $label)
            <button type="button" title="{{ $label }}" aria-label="{{ $label }}" :aria-pressed="color === @js($hex)"
                    @click="color = @js($hex)" class="product-calendar-swatch"
                    style="background-color:{{ $hex }};color:{{ \App\Support\ProductCalendarColor::textColor($hex) }};">
                <span x-show="color === @js($hex)" aria-hidden="true" x-cloak>✓</span>
            </button>
        @endforeach
    </div>

    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin-top:14px;">
        <label for="calendar-color-picker" style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;cursor:pointer;">
            <input type="color" id="calendar-color-picker" :value="color || '#647A0B'" value="{{ $selectedCalendarColor ?: '#647A0B' }}"
                   @input="color = $event.target.value.toUpperCase()" aria-label="Choisir une couleur personnalisée"
                   style="width:40px;height:36px;padding:2px;border:1px solid #d1d5db;border-radius:8px;background:white;cursor:pointer;">
            Personnaliser
        </label>
        <button type="button" @click="color = ''" :disabled="!color" class="product-calendar-reset">Couleur par défaut</button>
    </div>

    <div x-show="color" x-cloak class="product-calendar-preview" :style="{ backgroundColor: color, color: textColor() }" aria-label="Aperçu du rendez-vous">
        <span style="font-size:11px;">09:00 – 10:00</span>
        <strong style="display:block;font-size:13px;">Exemple de rendez-vous</strong>
    </div>
    <p x-show="!color" style="margin:12px 0 0;font-size:12px;color:#6b7280;">Sans couleur personnalisée, l’agenda conserve ses couleurs habituelles.</p>
    @error('calendar_color')
        <p role="alert" style="margin-top:8px;color:#b91c1c;font-size:13px;">Choisissez une couleur au format #RRGGBB ou revenez à la couleur par défaut.</p>
    @enderror
</section>

@once
    <style>
        .product-calendar-color { text-align:left; }
        .product-calendar-palette { display:flex;flex-wrap:wrap;gap:8px; }
        .product-calendar-swatch { display:inline-flex;align-items:center;justify-content:center;flex:none;width:36px;height:36px;border:1px solid rgb(0 0 0 / 15%);border-radius:50%;font-size:20px;cursor:pointer; }
        .product-calendar-swatch[aria-pressed="true"] { outline:2px solid #374151;outline-offset:3px; }
        .product-calendar-swatch:focus-visible { outline:3px solid #374151;outline-offset:3px; }
        .product-calendar-reset { border:0;background:transparent;color:#647a0b;font-size:13px;text-decoration:underline;cursor:pointer; }
        .product-calendar-reset:disabled { opacity:.5;cursor:default; }
        .product-calendar-preview { margin-top:14px;border-radius:8px;padding:10px 12px;max-width:280px;border:1px solid rgb(0 0 0 / 12%); }
    </style>
@endonce

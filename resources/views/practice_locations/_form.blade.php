@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">{{ __('Nom du cabinet') }}</label>
        <input name="label" value="{{ old('label', $location->label ?? '') }}" class="w-full border rounded px-3 py-2" required>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">{{ __('Adresse (ligne 1)') }}</label>
        <input name="address_line1" value="{{ old('address_line1', $location->address_line1 ?? '') }}" class="w-full border rounded px-3 py-2" required>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">{{ __('Adresse (ligne 2)') }}</label>
        <input name="address_line2" value="{{ old('address_line2', $location->address_line2 ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('Code postal') }}</label>
        <input name="postal_code" value="{{ old('postal_code', $location->postal_code ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('Ville') }}</label>
        <input name="city" value="{{ old('city', $location->city ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('Pays (code ISO, ex: FR)') }}</label>
        <input name="country" value="{{ old('country', $location->country ?? 'FR') }}" class="w-full border rounded px-3 py-2" maxlength="2" required>
    </div>

    <div class="md:col-span-2 mt-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_primary" value="1" @checked(old('is_primary', ($location->is_primary ?? false)))>
            <span>{{ __('Définir comme cabinet principal') }}</span>
        </label>
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="px-4 py-2 rounded-lg bg-[#647a0b] text-white hover:bg-[#8ea633] transition">
        {{ __('Enregistrer') }}
    </button>
    <a href="{{ route('practice-locations.index') }}" class="px-4 py-2 rounded-lg border hover:bg-gray-50">
        {{ __('Annuler') }}
    </a>
</div>

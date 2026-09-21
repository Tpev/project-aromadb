<div class="details-box my-4">
    <input type="hidden" name="booking_phone_required" value="0">
    <label class="flex items-start gap-3" for="booking_phone_required">
        <input type="checkbox" id="booking_phone_required" name="booking_phone_required" value="1"
               @checked(old('booking_phone_required', $user->booking_phone_required))>
        <span>{{ __('Rendre le numéro de téléphone obligatoire lors de la prise de rendez-vous') }}</span>
    </label>
    <p class="mt-1 text-sm text-gray-600">{{ __('Ce réglage s’applique aux réservations en ligne, y compris depuis un lien partenaire.') }}</p>
    @error('booking_phone_required')<p class="text-red-600">{{ $message }}</p>@enderror
</div>

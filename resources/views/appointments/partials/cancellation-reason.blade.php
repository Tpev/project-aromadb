<div class="appointment-reason my-3">
    <label for="cancellation_reason">{{ __('Motif de l’annulation') }} <span aria-hidden="true">*</span></label>
    <textarea id="cancellation_reason" name="cancellation_reason" maxlength="500" required rows="3"
              aria-describedby="cancellation-reason-help" class="w-full rounded-lg border border-gray-300 p-3"
              placeholder="{{ __('Ex. : empêchement personnel') }}">{{ old('cancellation_reason') }}</textarea>
    <p id="cancellation-reason-help" class="text-sm text-gray-600">{{ __('Indiquez brièvement votre motif, sans information médicale.') }}</p>
    @error('cancellation_reason')<p class="text-red-600" role="alert">{{ $message }}</p>@enderror
</div>

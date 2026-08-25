@if(app(\App\Support\BookingV2Access::class)->enabledFor(auth()->user()))
    @php($bookingProduct = $product ?? null)
    <div x-data="{
        confirmationNote: @js(old('confirmation_email_note', $bookingProduct?->confirmation_email_note)),
        reminderNote: @js(old('reminder_email_note', $bookingProduct?->reminder_email_note))
    }">
    <div class="extra-settings-wrapper">
        <h3 class="extra-settings-title">{{ __('Organisation du rendez-vous') }}</h3>
        <div class="extra-settings-box">
            @foreach([
                'preparation_time_minutes' => 'Temps de préparation avant le rendez-vous',
                'buffer_time_after_minutes' => 'Temps de battement après le rendez-vous',
            ] as $field => $label)
                <div class="details-box">
                    <label class="details-label" for="{{ $field }}">{{ __($label) }}</label>
                    <select id="{{ $field }}" name="{{ $field }}" class="form-control">
                        <option value="">{{ $field === 'preparation_time_minutes' ? __('Aucun temps de préparation') : __('Utiliser le réglage général') }}</option>
                        @foreach([0, 5, 10, 15, 20, 30, 45, 60] as $minutes)
                            <option value="{{ $minutes }}" @selected((string) old($field, $bookingProduct?->{$field}) === (string) $minutes)>{{ $minutes }} min</option>
                        @endforeach
                    </select>
                    @error($field) <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            @endforeach
            <small class="text-gray-500">{{ __('Ces temps protègent votre agenda sans modifier la durée affichée au client.') }}</small>
        </div>
    </div>

    <div class="extra-settings-wrapper">
        <h3 class="extra-settings-title">{{ __('Communication avec le client') }}</h3>
        <div class="extra-settings-box">
            <div class="details-box">
                <label class="details-label" for="booking_notes_placeholder">{{ __('Question affichée dans « Informations complémentaires »') }}</label>
                <input id="booking_notes_placeholder" name="booking_notes_placeholder" type="text" class="form-control" maxlength="255"
                       value="{{ old('booking_notes_placeholder', $bookingProduct?->booking_notes_placeholder) }}"
                       placeholder="{{ auth()->user()->resolvedBookingNotesPlaceholder() }}">
                <small class="text-gray-500">{{ __('Laissez vide pour utiliser la question générale définie dans les informations de votre entreprise.') }}</small>
                @error('booking_notes_placeholder') <p class="text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="details-box">
                <label class="details-label" for="confirmation_email_note">{{ __('Message ajouté à l’email de confirmation') }}</label>
                <textarea id="confirmation_email_note" name="confirmation_email_note" class="form-control" rows="3" maxlength="2000" x-model="confirmationNote">{{ old('confirmation_email_note', $bookingProduct?->confirmation_email_note) }}</textarea>
                @error('confirmation_email_note') <p class="text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="details-box">
                <label class="details-label" for="reminder_email_note">{{ __('Message ajouté à l’email de rappel') }}</label>
                <textarea id="reminder_email_note" name="reminder_email_note" class="form-control" rows="3" maxlength="2000" x-model="reminderNote">{{ old('reminder_email_note', $bookingProduct?->reminder_email_note) }}</textarea>
                @error('reminder_email_note') <p class="text-red-500">{{ $message }}</p> @enderror
            </div>
            <small class="text-gray-500">{{ __('Texte simple uniquement. Un aperçu fidèle apparaît directement dans l’email reçu par le client.') }}</small>
            <div style="margin-top:14px;padding:14px;border:1px solid #e4e8d5;border-radius:8px;background:#f9faf7;">
                <strong style="display:block;color:#374151;margin-bottom:6px;">{{ __('Aperçu dans les emails') }}</strong>
                <p style="margin:0 0 8px;color:#6b7280;font-size:.85rem;">{{ __('Confirmation') }}</p>
                <p x-text="confirmationNote || '{{ __('Aucun message personnalisé') }}'" style="white-space:pre-line;margin:0 0 12px;color:#374151;"></p>
                <p style="margin:0 0 8px;color:#6b7280;font-size:.85rem;">{{ __('Rappel') }}</p>
                <p x-text="reminderNote || '{{ __('Aucun message personnalisé') }}'" style="white-space:pre-line;margin:0;color:#374151;"></p>
            </div>
        </div>
    </div>
    </div>
@endif

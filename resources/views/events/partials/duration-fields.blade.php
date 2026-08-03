@php
    $durationEvent = $event ?? null;
    $durationInput = \App\Support\EventDuration::inputForMinutes(
        (int) old('duration', $durationEvent?->duration ?? 60)
    );
    $durationValue = old('duration_value', $durationInput['value']);
    $durationUnit = old('duration_unit', $durationInput['unit']);
    $isMobileDuration = (bool) ($mobile ?? false);
@endphp

<div class="{{ $isMobileDuration ? 'block' : 'details-box' }} event-duration-field" data-event-duration>
    <label class="{{ $isMobileDuration ? 'text-sm font-medium text-gray-700' : 'details-label' }}" for="duration_value">
        {{ __('Durée') }}
    </label>
    <div class="event-duration-controls">
        <input type="number"
               id="duration_value"
               name="duration_value"
               value="{{ $durationValue }}"
               min="0.01"
               step="any"
               inputmode="decimal"
               required
               class="{{ $isMobileDuration ? 'h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]' : 'form-control' }}">
        <select id="duration_unit"
                name="duration_unit"
                required
                class="{{ $isMobileDuration ? 'h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]' : 'form-control' }}">
            <option value="minutes" {{ $durationUnit === 'minutes' ? 'selected' : '' }}>{{ __('Minutes') }}</option>
            <option value="hours" {{ $durationUnit === 'hours' ? 'selected' : '' }}>{{ __('Heures') }}</option>
            <option value="days" {{ $durationUnit === 'days' ? 'selected' : '' }}>{{ __('Jours') }}</option>
        </select>
    </div>
    <p class="event-duration-preview" data-event-end-preview aria-live="polite">{{ __('Fin prévue : à calculer') }}</p>
    @error('duration_value') <p class="text-red-500">{{ $message }}</p> @enderror
    @error('duration_unit') <p class="text-red-500">{{ $message }}</p> @enderror
    @error('duration') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

@once
    <style>
        .event-duration-controls {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(130px, 0.75fr);
            gap: 10px;
            margin-top: 5px;
        }

        .event-duration-preview {
            margin-top: 7px;
            color: #64748b;
            font-size: 0.8rem;
            line-height: 1.4;
        }

        @media (max-width: 480px) {
            .event-duration-controls { grid-template-columns: 1fr; }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const factors = { minutes: 1, hours: 60, days: 1440 };
            const dateFormatter = new Intl.DateTimeFormat('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const timeFormatter = new Intl.DateTimeFormat('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });

            document.querySelectorAll('[data-event-duration]').forEach(function (container) {
                const valueInput = container.querySelector('[name="duration_value"]');
                const unitInput = container.querySelector('[name="duration_unit"]');
                const startInput = document.querySelector('[name="start_date_time"]');
                const preview = container.querySelector('[data-event-end-preview]');

                function refreshEndPreview() {
                    const value = Number.parseFloat(valueInput?.value || '');
                    const factor = factors[unitInput?.value] || 0;
                    const start = startInput?.value ? new Date(startInput.value) : null;

                    if (!preview || !start || Number.isNaN(start.getTime()) || !Number.isFinite(value) || value <= 0 || factor === 0) {
                        if (preview) preview.textContent = 'Fin prévue : à calculer';
                        return;
                    }

                    const minutes = Math.round(value * factor);
                    const end = new Date(start.getTime() + minutes * 60 * 1000);
                    preview.textContent = 'Fin prévue : ' + dateFormatter.format(end) + ' à ' + timeFormatter.format(end);
                }

                valueInput?.addEventListener('input', refreshEndPreview);
                unitInput?.addEventListener('change', refreshEndPreview);
                startInput?.addEventListener('input', refreshEndPreview);
                refreshEndPreview();
            });
        });
    </script>
@endonce

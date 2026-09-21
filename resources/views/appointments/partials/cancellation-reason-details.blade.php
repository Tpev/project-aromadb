@if($appointment->isCancelled() && filled($appointment->cancellation_reason))
    <div class="my-4 rounded-lg border border-gray-200 bg-white p-4">
        <strong>{{ __('Motif de l’annulation') }}</strong>
        <p class="mt-2 whitespace-pre-line">{{ $appointment->cancellation_reason }}</p>
    </div>
@endif

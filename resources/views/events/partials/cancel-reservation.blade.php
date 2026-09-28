@php
    $cancellationMessage = 'Annuler la réservation de '.$reservation->full_name.' ? Sa place sera libérée.';
    if ($reservation->status === 'paid') {
        $cancellationMessage .= ' Aucun remboursement automatique ne sera effectué.';
    }
@endphp
<form method="POST" action="{{ route('reservations.destroy', $reservation->id) }}"
      data-confirm="{{ $cancellationMessage }}" onsubmit="return confirm(this.dataset.confirm)">
    @csrf
    @method('DELETE')
    <button type="submit" class="inline-flex min-h-[44px] items-center rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-300">
        Annuler la réservation
    </button>
</form>

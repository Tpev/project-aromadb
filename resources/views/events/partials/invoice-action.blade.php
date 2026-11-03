@if($reservation->invoice && (int) $reservation->invoice->user_id === (int) auth()->id())
    <a href="{{ route('invoices.show', $reservation->invoice) }}" class="inline-flex items-center rounded-full border border-[#647a0b] px-3 py-2 text-xs font-semibold text-[#647a0b] hover:bg-[#f5f7eb]">Voir la facture</a>
@elseif($reservation->isActive())
    @if($reservation->status === 'pending_payment' || ($reservation->stripe_session_id && $reservation->status !== 'paid'))
        <span class="text-xs text-gray-500" title="La facturation sera disponible après confirmation du paiement en ligne.">Paiement en cours</span>
    @else
        <a href="{{ route('invoices.create', ['event_reservation_id' => $reservation->id]) }}" class="inline-flex items-center rounded-full border border-[#647a0b] px-3 py-2 text-xs font-semibold text-[#647a0b] hover:bg-[#f5f7eb]">Facturer</a>
    @endif
@endif

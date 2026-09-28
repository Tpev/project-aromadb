@php
    $inactiveReservations = $event->reservations->reject(fn ($reservation) => $reservation->isActive());
@endphp
@if($inactiveReservations->isNotEmpty())
    <details class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-3">
        <summary class="cursor-pointer text-sm font-medium text-gray-600">Réservations annulées ou non abouties ({{ $inactiveReservations->count() }})</summary>
        <ul class="mt-3 space-y-3">
            @foreach($inactiveReservations as $inactiveReservation)
                <li class="text-sm text-gray-600">
                    <span class="font-semibold">{{ $inactiveReservation->full_name }}</span>
                    <span class="block break-words text-xs">{{ $inactiveReservation->email }}</span>
                    <span class="block text-xs">
                        @if($inactiveReservation->cancelled_at)
                            Annulée le {{ $inactiveReservation->cancelled_at->format('d/m/Y à H:i') }}
                        @elseif($inactiveReservation->status === 'expired')
                            Paiement expiré
                        @elseif($inactiveReservation->status === 'failed')
                            Paiement échoué
                        @elseif($inactiveReservation->status === 'refunded')
                            Remboursée
                        @else
                            Annulée
                        @endif
                        @if($inactiveReservation->status === 'paid')
                            · Paiement reçu : {{ number_format((float) $inactiveReservation->amount_ttc, 2, ',', ' ') }} {{ strtoupper($inactiveReservation->currency ?? 'eur') }} · Remboursement à gérer séparément
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    </details>
@endif

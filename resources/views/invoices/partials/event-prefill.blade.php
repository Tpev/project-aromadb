<input type="hidden" name="event_reservation_id" value="{{ $selectedReservation->id }}">
<div class="mb-5 rounded-xl border border-[#dfe6c7] bg-[#fbfcf6] p-4" id="event-invoice-context">
    <p class="font-semibold text-[#647a0b]">Facturer une participation</p>
    <p class="mt-1 font-semibold">{{ $selectedReservation->event->name }}</p>
    <p class="text-sm text-gray-600">{{ $selectedReservation->event->formatted_period }}</p>
    <p class="text-sm text-gray-600">{{ $selectedReservation->event->location }}</p>
    <p class="mt-2 text-sm">Participant : <strong>{{ $selectedReservation->full_name }}</strong> · {{ $selectedReservation->email }}</p>
    <p class="mt-2 text-sm text-gray-600">Vérifiez le destinataire, le prix et la TVA avant d’enregistrer. Les informations de l’événement seront conservées sur la facture.</p>
    @if($selectedReservation->status === 'paid')
        <p class="mt-3 text-sm font-semibold text-[#647a0b]">Déjà payé en ligne : {{ number_format($selectedReservation->amount_ttc, 2, ',', ' ') }} € TTC. Ce paiement sera enregistré sur la facture, sans nouvel encaissement.</p>
        <p class="mt-1 text-sm text-gray-600">La TVA est préremplie d’après l’événement actuel. Vérifiez le taux applicable au paiement d’origine.</p>
        @if(!$selectedReservation->payment_confirmation_requested_at)
            <label class="am-label mt-3" for="event_payment_date">Date du paiement reçu</label>
            <input type="date" id="event_payment_date" name="event_payment_date" value="{{ old('event_payment_date') }}" max="{{ now()->toDateString() }}" class="am-input" required>
            <p class="mt-1 text-sm text-gray-600">Cette ancienne réservation ne contient pas la date du paiement. Renseignez la date réelle de l’encaissement.</p>
        @endif
    @endif
    @if($eventClients->isEmpty() && filled($selectedReservation->email))
        <div class="mt-3">
            <p id="create-event-client-help" class="mb-2 text-sm">Aucun dossier client ne correspond à cet email.</p>
            <button type="button" id="create-event-client" data-url="{{ route('reservations.createClient', [$selectedReservation->event_id, $selectedReservation->id]) }}" class="am-btn am-btn-secondary">Créer le dossier client du participant</button>
            <p id="create-event-client-status" class="mt-2 text-sm" role="status"></p>
        </div>
    @elseif($eventClients->count() > 1 || blank($selectedReservation->email))
        <p class="mt-3 text-sm font-semibold">Sélectionnez le dossier client du participant dans la liste ci-dessous.</p>
    @endif
    <a href="{{ route('events.show', $selectedReservation->event_id) }}" class="mt-3 inline-block text-sm font-semibold text-[#647a0b] underline">Retour à l’événement</a>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('create-event-client');
    if (!button) return;
    button.addEventListener('click', async function () {
        button.disabled = true;
        const status = document.getElementById('create-event-client-status');
        try {
            const response = await fetch(button.dataset.url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('#invoiceForm input[name="_token"]').value }
            });
            const data = await response.json();
            if (!response.ok || !data.client_profile_id) throw new Error(data.message || 'Impossible de créer le dossier client.');
            const select = document.getElementById('client_profile_id');
            let option = Array.from(select.options).find(option => option.value === String(data.client_profile_id));
            if (!option) {
                option = new Option(@js($selectedReservation->full_name), String(data.client_profile_id));
                select.add(option);
            }
            select.value = String(data.client_profile_id);
            if (window.jQuery) window.jQuery(select).trigger('change');
            status.textContent = 'Dossier client sélectionné. Vous pouvez maintenant enregistrer la facture.';
            document.getElementById('create-event-client-help').remove();
            button.remove();
        } catch (error) {
            status.textContent = error.message || 'Impossible de créer le dossier client. Réessayez.';
            button.disabled = false;
        }
    });
});
</script>

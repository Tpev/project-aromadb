@php($billingInvoices = $appointment->billingInvoices)

@if($billingInvoices->isEmpty())
    <a href="{{ route('invoices.create', [
            'client_id' => $appointment->client_profile_id,
            'product_id' => $appointment->product_id,
            'appointment_id' => $appointment->id,
        ]) }}" class="btn-invoice">
        <i class="fas fa-file-invoice-dollar"></i> Créer la facture
    </a>
@elseif($billingInvoices->count() === 1)
    @php($linkedInvoice = $billingInvoices->first())
    <a href="{{ route('invoices.show', $linkedInvoice) }}" class="btn-invoice">
        <i class="fas fa-file-invoice"></i> Voir facture n°{{ $linkedInvoice->invoice_number }}
    </a>
@else
    @foreach($billingInvoices as $linkedInvoice)
        <a href="{{ route('invoices.show', $linkedInvoice) }}" class="btn-invoice" title="Plusieurs factures sont associées à ce rendez-vous">
            <i class="fas fa-exclamation-triangle"></i> Facture n°{{ $linkedInvoice->invoice_number }}
        </a>
    @endforeach
@endif

@if($appointment->sessionNotes->isEmpty())
    <a href="{{ route('session_notes.create', [
            'clientProfile' => $appointment->client_profile_id,
            'appointment_id' => $appointment->id,
        ]) }}" class="btn-invoice">
        <i class="fas fa-notes-medical"></i> Ajouter une note
    </a>
@else
    <a href="{{ route('session_notes.show', $appointment->sessionNotes->first()) }}" class="btn-invoice">
        <i class="fas fa-notes-medical"></i> Voir la note
    </a>
@endif

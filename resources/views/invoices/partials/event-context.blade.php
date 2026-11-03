@if($invoice->event_snapshot)
    <div style="margin: 16px 0; padding: 12px 16px; border: 1px solid #dfe6c7; border-radius: 8px; background: #fbfcf6; font-size: 13px; line-height: 1.5;">
        <strong style="color: #647a0b;">Événement : {{ $invoice->event_snapshot['name'] ?? '' }}</strong><br>
        {{ $invoice->event_snapshot['period'] ?? '' }}<br>
        Participant : {{ $invoice->event_snapshot['participant_name'] ?? '' }}
        @if(!empty($invoice->event_snapshot['location']))
            <br>Lieu : {{ $invoice->event_snapshot['location'] }}
        @endif
    </div>
@endif

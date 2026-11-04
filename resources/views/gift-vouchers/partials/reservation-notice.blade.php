@if($reservedCents = $voucher->reservedAmountCents())
    <p style="margin:12px 0;padding:12px;background:#fffbeb;color:#854d0e;border:1px solid #fde68a;border-radius:8px;font-size:13px;line-height:1.5;">
        {{ $voucher->money($reservedCents) }} temporairement réservés pour un paiement en cours.
        Le solde affiché exclut ce montant, qui n’est pas encore une utilisation confirmée.
    </p>
@endif

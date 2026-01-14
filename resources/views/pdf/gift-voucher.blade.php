<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon cadeau</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111; }
        .wrap { padding: 24px; }
        .card { border: 1px solid #e5e7eb; border-radius: 14px; padding: 22px; }
        .muted { color: #6b7280; font-size: 12px; }
        .title { font-size: 26px; font-weight: 700; margin: 0; }
        .big { font-size: 34px; font-weight: 800; margin: 8px 0 0 0; }
        .code { font-size: 16px; font-weight: 700; letter-spacing: 1px; }
        .row { margin-top: 14px; }
        .grid { width: 100%; }
        .grid td { vertical-align: top; }
        .pill { display: inline-block; padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 999px; font-size: 12px; }
        .footer { margin-top: 18px; font-size: 11px; color: #6b7280; }
        .qr { width: 140px; height: 140px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <table class="grid">
            <tr>
                <td>
                    <p class="title">Bon cadeau</p>
                    <p class="muted" style="margin-top:6px;">
                        Émis par <strong>{{ $therapist->company_name ?? $therapist->name ?? 'Votre thérapeute' }}</strong>
                    </p>

                    <div class="row">
                        <span class="pill">Montant</span>
                        <div class="big">{{ $voucher->originalAmountStr() }}</div>
                    </div>

                    <div class="row">
                        <div class="muted">Code secret</div>
                        <div class="code">{{ $voucher->code }}</div>
                    </div>

                    @if($voucher->expires_at)
                        <div class="row">
                            <div class="muted">Valable jusqu’au</div>
                            <div style="font-weight:700;">{{ $voucher->expiresAtStr() }}</div>
                        </div>
                    @endif

                    @if($voucher->message)
                        <div class="row">
                            <div class="muted">Message</div>
                            <div style="margin-top:4px;">{{ $voucher->message }}</div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="muted">Réservation</div>
                        <div style="margin-top:4px;">
                            Scannez le QR code ou rendez-vous sur le portail du thérapeute.
                        </div>
                    </div>
                </td>

                <td style="width:180px; text-align:right;">
                    <img class="qr" src="{{ $qrBase64 }}" alt="QR code">
                    <div class="muted" style="margin-top:8px;">
                        Portail : {{ $portalUrl }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            Bon utilisable en une ou plusieurs fois selon disponibilité. Non remboursable.
            Le paiement du bon cadeau est réalisé en dehors d’AromaMade (espèces, virement, terminal CB, etc.).
        </div>
    </div>
</div>
</body>
</html>

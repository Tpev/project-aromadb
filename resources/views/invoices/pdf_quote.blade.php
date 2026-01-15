<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis #{{ $invoice->quote_number ?? '—' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color:#333; line-height:1.2; margin:0; padding:0; font-size:12px; }
        .invoice-container { padding: 10px; }
        .header, .footer { background-color:#854f38; color:#fff; padding:5px; text-align:center; }
        .company-details, .client-details, .invoice-details { margin-bottom:10px; }
        h1 { font-size:18px; margin:0; }
        h2 { font-size:14px; margin-bottom:5px; }
        p { margin:2px 0; }
        table { width:100%; border-collapse:collapse; margin-bottom:10px; }
        table thead { background-color:#854f38; color:#fff; }
        table th, table td { padding:4px; border:1px solid #ddd; font-size:11px; text-align:left; }
        .total { text-align:right; font-size:12px; font-weight:bold; margin-top:10px; }
        .notes { margin-top:10px; font-size:11px; }
        .legal-mentions { font-size:9px; color:#555; margin-top:10px; border-top:1px solid #ddd; padding-top:5px; }
        .column { width:48%; display:inline-block; vertical-align:top; }
        .column-left { margin-right:2%; }
        .column-right { margin-left:2%; }
    </style>
</head>
<body>
<div class="invoice-container">
    <div class="header">
        <h1>Devis #{{ $invoice->quote_number ?? '—' }}</h1>
    </div>

    @php
        $user    = $invoice->user;
        $cp      = $invoice->clientProfile;
        $company = $cp->company ?? null;

        $billingFirst = $cp->first_name_billing ?: $cp->first_name;
        $billingLast  = $cp->last_name_billing  ?: $cp->last_name;
    @endphp

    <div class="company-client-details">
        <div class="column column-left">
            <div class="company-details">
                <h2>{{ $user->company_name ?? 'Votre Entreprise' }}</h2>
                @if($user->company_address)<p>{{ $user->company_address }}</p>@endif
                @if($user->company_email)<p>Email : {{ $user->company_email }}</p>@endif
                @if($user->company_phone)<p>Téléphone : {{ $user->company_phone }}</p>@endif
            </div>
        </div>

        <div class="column column-right">
            <div class="client-details">
                <h2>Bénéficiaire :</h2>
                <p>{{ $cp->first_name }} {{ $cp->last_name }}</p>

                <h2 style="margin-top:6px;">Facturé à :</h2>
                @if($company)
                    <p><strong>{{ $company->name }}</strong></p>
                    <p>À l’attention de {{ $billingFirst }} {{ $billingLast }}</p>
                @else
                    <p>{{ $billingFirst }} {{ $billingLast }}</p>
                @endif

                @if($cp->address)<p>{{ $cp->address }}</p>@endif
                @if($cp->email)<p>Email : {{ $cp->email }}</p>@endif
                @if($cp->phone)<p>Téléphone : {{ $cp->phone }}</p>@endif
            </div>
        </div>
    </div>

    <div class="invoice-details">
        <p><strong>Date du devis :</strong> {{ optional($invoice->invoice_date)->format('d/m/Y') }}</p>
        <p><strong>Statut :</strong> {{ $invoice->status ?? 'Devis' }}</p>
        @if($invoice->due_date)
            <p><strong>Valable jusqu’au :</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
        @endif
    </div>

    <table>
        <thead>
        <tr>
            <th>Nom</th>
            <th>Description</th>
            <th>Qté</th>
            <th>P.U. HT (€)</th>
            <th>TVA (%)</th>
            <th>Remise HT (€)</th>
            <th>Total HT (€)</th>
            <th>Montant TVA (€)</th>
            <th>Total TTC (€)</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoice->items as $item)
            @php
                $name = $item->description;
                if($item->type === 'product' && $item->product) $name = $item->product->name;
                if($item->type === 'inventory' && $item->inventoryItem) $name = $item->inventoryItem->name;

                $remiseHt = (float)($item->line_discount_amount_ht ?? 0) + (float)($item->global_discount_amount_ht ?? 0);
            @endphp
            <tr>
                <td>{{ $name }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ number_format((float)$item->quantity, 2, ',', ' ') }}</td>
                <td>{{ number_format((float)$item->unit_price, 2, ',', ' ') }}</td>
                <td>{{ number_format((float)$item->tax_rate, 2, ',', ' ') }}%</td>
                <td>{{ number_format($remiseHt, 2, ',', ' ') }}</td>
                <td>{{ number_format((float)$item->total_price, 2, ',', ' ') }}</td>
                <td>{{ number_format((float)$item->tax_amount, 2, ',', ' ') }}</td>
                <td>{{ number_format((float)$item->total_price_with_tax, 2, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if((float)($invoice->global_discount_amount_ht ?? 0) > 0)
        <div class="total">Remise globale HT : -{{ number_format((float)$invoice->global_discount_amount_ht, 2, ',', ' ') }} €</div>
    @endif
    <div class="total">Total HT : {{ number_format((float)$invoice->total_amount, 2, ',', ' ') }} €</div>
    <div class="total">Total TVA : {{ number_format((float)$invoice->total_tax_amount, 2, ',', ' ') }} €</div>
    <div class="total">Total TTC : {{ number_format((float)$invoice->total_amount_with_tax, 2, ',', ' ') }} €</div>

    @if($invoice->notes)
        <div class="notes">
            <strong>Notes :</strong>
            <div>{{ $invoice->notes }}</div>
        </div>
    @endif

    <div class="legal-mentions">
        Merci pour votre confiance.
    </div>

    <div class="footer">
        <p>{{ $user->company_name ?? '' }}</p>
    </div>
</div>
</body>
</html>

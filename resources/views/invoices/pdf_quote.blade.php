<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis #{{ $invoice->quote_number ?? '—' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        .invoice-container {
            padding: 10px;
        }
        .header, .footer {
            background-color: #854f38;
            color: #fff;
            padding: 5px;
            text-align: center;
        }
        .company-details, .client-details, .invoice-details {
            margin-bottom: 10px;
        }
        h1 {
            font-size: 18px;
            margin: 0;
        }
        h2 {
            font-size: 14px;
            margin-bottom: 5px;
        }
        p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table thead {
            background-color: #854f38;
            color: #fff;
        }
        table th, table td {
            padding: 4px;
            border: 1px solid #ddd;
            font-size: 11px;
            text-align: left;
        }
        .total {
            text-align: right;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        .notes {
            margin-top: 10px;
            font-size: 11px;
        }
        .legal-mentions {
            font-size: 9px;
            color: #555;
            margin-top: 10px;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        .column {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        .column-left {
            margin-right: 2%;
        }
        .column-right {
            margin-left: 2%;
        }
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

        <!-- Détails entreprise + bénéficiaire / facturation -->
        <div class="company-client-details">
            <div class="column column-left">
                <div class="company-details">
                    <h2>{{ $user->company_name ?? 'Votre Entreprise' }}</h2>
                    @if($user->company_address)
                        <p>{{ $user->company_address }}</p>
                    @endif
                    @if($user->company_email)
                        <p>Email : {{ $user->company_email }}</p>
                    @endif
                    @if($user->company_phone)
                        <p>Téléphone : {{ $user->company_phone }}</p>
                    @endif
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

                    @if($cp->address)
                        <p>{{ $cp->address }}</p>
                    @endif
                    @if($cp->email)
                        <p>Email : {{ $cp->email }}</p>
                    @endif
                    @if($cp->phone)
                        <p>Téléphone : {{ $cp->phone }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Détails du devis -->
        <div class="invoice-details">
            <p><strong>Date du devis :</strong> {{ $invoice->invoice_date->format('d/m/Y') }}</p>
            <p><strong>Statut :</strong> {{ ucfirst($invoice->status) }}</p>
            @if($invoice->due_date)
                <p><strong>Valable jusqu’au :</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
            @endif
        </div>

        <!-- Tableau des lignes -->
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Qté</th>
                    <th>P.U. HT (€)</th>
                    <th>TVA (%)</th>
                    <th>Total HT (€)</th>
                    <th>Montant TVA (€)</th>
                    <th>Total TTC (€)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>
                            @if($item->type === 'product' && $item->product)
                                {{ $item->product->name }}
                            @elseif($item->type === 'inventory' && $item->inventoryItem)
                                {{ $item->inventoryItem->name }}
                            @else
                                {{ $item->description }}
                            @endif
                        </td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2, ',', ' ') }}</td>
                        <td>{{ number_format($item->tax_rate, 2, ',', ' ') }}%</td>
                        <td>{{ number_format($item->total_price, 2, ',', ' ') }}</td>
                        <td>{{ number_format($item->tax_amount, 2, ',', ' ') }}</td>
                        <td>{{ number_format($item->total_price_with_tax, 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totaux -->
        <p class="total"><strong>Total HT :</strong> {{ number_format($invoice->total_amount, 2, ',', ' ') }} €</p>
        <p class="total"><strong>Total TVA :</strong> {{ number_format($invoice->total_tax_amount, 2, ',', ' ') }} €</p>
        <p class="total"><strong>Total TTC :</strong> {{ number_format($invoice->total_amount_with_tax, 2, ',', ' ') }} €</p>

        @if($invoice->notes)
            <div class="notes">
                <h3>Notes :</h3>
                <p>{{ $invoice->notes }}</p>
            </div>
        @endif

        <!-- Mentions légales et pied -->
        <div class="legal-mentions">
            @if($invoice->due_date)
                <p>Ce devis est valable jusqu'au {{ $invoice->due_date->translatedFormat('d F Y') }}.</p>
            @endif
            @if($invoice->user->legal_mentions)
                <p>{{ $invoice->user->legal_mentions }}</p>
            @endif
        </div>

        <div class="footer">
            <p>Merci pour votre confiance.</p>
        </div>
    </div>
</body>
</html>

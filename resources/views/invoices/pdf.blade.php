<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #{{ $invoice->invoice_number }}</title>
    <style>
        /* Styles pour le PDF */
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
            background-color: #647a0b;
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
            background-color: #647a0b;
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
        <!-- En-tête de la facture -->
        <div class="header">
            <h1>Facture #{{ $invoice->invoice_number }}</h1>
        </div>

        <!-- Détails de l'entreprise et du client côte à côte -->
        <div class="company-client-details">
            <div class="column column-left">
                <!-- Détails de l'entreprise -->
                @php
                    $user = $invoice->user; // Assurez-vous que la relation 'user' est chargée
                @endphp
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
                <!-- Détails du client -->
                <div class="client-details">
                    <h2>Facturé à :</h2>
                    <!-- If billing names exist, use them, otherwise fallback to normal names -->
                    @if($invoice->clientProfile->first_name_billing || $invoice->clientProfile->last_name_billing)
                        <p>
                            {{ $invoice->clientProfile->first_name_billing }} {{ $invoice->clientProfile->last_name_billing }}
                        </p>
                    @else
                        <p>
                            {{ $invoice->clientProfile->first_name }} {{ $invoice->clientProfile->last_name }}
                        </p>
                    @endif

                    <!-- Adresse (toujours la même, si vous avez un champ d'adresse de facturation distinct, adaptez) -->
                    @if($invoice->clientProfile->address)
                        <p>{{ $invoice->clientProfile->address }}</p>
                    @endif

                    <!-- Email -->
                    @if($invoice->clientProfile->email)
                        <p>Email : {{ $invoice->clientProfile->email }}</p>
                    @endif

                    <!-- Téléphone -->
                    @if($invoice->clientProfile->phone)
                        <p>Téléphone : {{ $invoice->clientProfile->phone }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Détails de la facture -->
        <div class="invoice-details">
            <p><strong>Date de Facture :</strong> {{ $invoice->invoice_date }}</p>
            @if($invoice->due_date)
                <p><strong>Date d'échéance :</strong> {{ $invoice->due_date }}</p>
            @endif
            <p><strong>Statut :</strong> {{ ucfirst($invoice->status) }}</p>
        </div>

        <!-- Articles de la facture -->
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Description</th>
                    <th>Qté</th>
                    <th>P.U. (€)</th>
                    <th>TVA (%)</th>
                    <th>Total HT (€)</th>
                    <th>Montant TVA (€)</th>
                    <th>Total TTC (€)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
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

        <!-- Montant total -->
        <p class="total"><strong>Total HT :</strong> {{ number_format($invoice->total_amount, 2, ',', ' ') }} €</p>
        <p class="total"><strong>Total TVA :</strong> {{ number_format($invoice->total_tax_amount, 2, ',', ' ') }} €</p>
        <p class="total"><strong>Total TTC :</strong> {{ number_format($invoice->total_amount_with_tax, 2, ',', ' ') }} €</p>

        <!-- Notes -->
        @if($invoice->notes)
            <div class="notes">
                <h3>Notes :</h3>
                <p>{{ $invoice->notes }}</p>
            </div>
        @endif

        <!-- Mentions Légales -->
        @if($user->legal_mentions)
            <div class="legal-mentions">
                <p>{{ $user->legal_mentions }}</p>
            </div>
        @endif

        <!-- Pied de page -->
        <div class="footer">
            <p>Merci pour votre confiance.</p>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #{{ $invoice->invoice_number }}</title>

    @php
        $user = $invoice->user;

        // Branding
        $brandColor = $user?->invoice_primary_color ?: '#647a0b';

        // Logo
        $logoFile = null;
        if (!empty($user?->invoice_logo_path)) {
            $candidate = public_path('storage/' . $user->invoice_logo_path);
            if (is_file($candidate)) {
                $logoFile = $candidate;
            }
        }

        // Dates safe
        $invoiceDate = $invoice->invoice_date ? \Illuminate\Support\Carbon::parse($invoice->invoice_date) : null;
        $dueDate     = $invoice->due_date ? \Illuminate\Support\Carbon::parse($invoice->due_date) : null;

        // Remise globale (si ton modèle la stocke)
        $globalDiscountHt = (float) ($invoice->global_discount_amount_ht ?? 0);
    @endphp

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            line-height: 1.25;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        .invoice-container { padding: 14px; }

        /* Header */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-table td { vertical-align: top; }
        .header-logo-cell { width: 48%; }
        .header-meta-cell { width: 52%; text-align: right; }

        .logo-img { max-height: 62px; max-width: 240px; }

        /* Badge meta (top-right) */
        .meta-badge {
            display: inline-block;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            overflow: hidden;
            min-width: 260px;
        }
        .meta-badge .meta-title {
            background: {{ $brandColor }};
            color: #fff;
            padding: 8px 10px;
            font-weight: bold;
            font-size: 14px;
        }
        .meta-badge table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        .meta-badge td {
            padding: 6px 10px;
            font-size: 11px;
            border-top: 1px solid #eeeeee;
        }
        .meta-label { color: #666; width: 42%; }
        .meta-value { text-align: right; font-weight: 600; }

        .header-sep {
            height: 2px;
            background-color: {{ $brandColor }};
            margin: 10px 0 14px 0;
        }

        /* Cards */
        .row-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .row-table td { width: 50%; vertical-align: top; }
        .card {
            background: #f7f7f7;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 10px 12px;
        }
        .card-title {
            font-weight: 700;
            color: {{ $brandColor }};
            font-size: 12px;
            margin: 0 0 6px 0;
        }
        .card p { margin: 2px 0; font-size: 11px; }

        /* Items table */
        table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items thead { background: {{ $brandColor }}; color: #fff; }
        table.items th, table.items td {
            padding: 6px;
            border: 1px solid #e5e5e5;
            font-size: 10.8px;
        }
        table.items th { font-weight: 700; }
        table.items td.num, table.items th.num { text-align: right; }
        table.items tbody tr:nth-child(even) { background: #fafafa; }

        /* Totals box */
        .totals-wrap { margin-top: 10px; width: 100%; }
        .totals-table {
            width: 320px;
            border-collapse: collapse;
            margin-left: auto;
        }
        .totals-table td {
            padding: 6px 8px;
            font-size: 11px;
            border: 1px solid #e5e5e5;
        }
        .totals-table td.label { background: #f7f7f7; color: #555; }
        .totals-table td.val { text-align: right; font-weight: 600; background: #fff; }
        .totals-table tr.total-ttc td {
            font-weight: 800;
            border-top: 2px solid {{ $brandColor }};
        }
        .totals-table tr.total-ttc td.label { color: {{ $brandColor }}; }

        /* Notes / legal */
        .notes {
            margin-top: 12px;
            font-size: 11px;
        }
        .notes-title {
            margin: 0 0 4px 0;
            font-weight: 700;
            color: {{ $brandColor }};
        }
        .legal-mentions {
            font-size: 9px;
            color: #666;
            margin-top: 14px;
            border-top: 1px solid #e5e5e5;
            padding-top: 8px;
        }
        .footer {
            margin-top: 10px;
            background: {{ $brandColor }};
            color: #fff;
            padding: 7px;
            text-align: center;
            border-radius: 6px;
            font-size: 10.5px;
        }
    </style>
</head>
<body>
<div class="invoice-container">

    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                @if($logoFile)
                    <img class="logo-img" src="{{ $logoFile }}" alt="Logo">
                @endif
            </td>
            <td class="header-meta-cell">
                <div class="meta-badge">
                    <div class="meta-title">FACTURE</div>
                    <table>
                        <tr>
                            <td class="meta-label">N° facture</td>
                            <td class="meta-value">#{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Date</td>
                            <td class="meta-value">{{ $invoiceDate ? $invoiceDate->format('d/m/Y') : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Échéance</td>
                            <td class="meta-value">{{ $dueDate ? $dueDate->format('d/m/Y') : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Statut</td>
                            <td class="meta-value">{{ $invoice->status ? ucfirst($invoice->status) : '—' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="header-sep"></div>

    {{-- EMETTEUR + CLIENT (CARDS) --}}
    @php
        $cp = $invoice->clientProfile;

        $corp = $invoice->corporateClient ?? null;
        if (!$corp && !empty($invoice->corporate_client_id)) {
            $corp = \App\Models\CorporateClient::where('id', $invoice->corporate_client_id)->first();
        }
        $isCorporate = (bool) $corp;

        $company = $isCorporate ? $corp : ($cp?->company ?? null);

        $billingFirst = $isCorporate
            ? ($corp->main_contact_first_name ?? '')
            : ($cp?->first_name_billing ?: ($cp?->first_name ?? ''));

        $billingLast  = $isCorporate
            ? ($corp->main_contact_last_name ?? '')
            : ($cp?->last_name_billing ?: ($cp?->last_name ?? ''));

        $billingAddress = $isCorporate
            ? ($corp->billing_address ?? null)
            : ($cp?->billing_address ?? ($cp?->address ?? null));

        $billingZip = $isCorporate
            ? ($corp->billing_zip ?? null)
            : ($cp?->billing_zip ?? ($cp?->zip ?? null));

        $billingCity = $isCorporate
            ? ($corp->billing_city ?? null)
            : ($cp?->billing_city ?? ($cp?->city ?? null));

        $billingEmail = $isCorporate
            ? ($corp->billing_email ?: ($corp->main_contact_email ?? null))
            : ($cp?->email ?? null);

        $billingPhone = $isCorporate
            ? (($corp->billing_phone ?? null) ?: ($corp->main_contact_phone ?? null))
            : ($cp?->phone ?? null);
    @endphp

    <table class="row-table">
        <tr>
            <td style="padding-right:8px;">
                <div class="card">
                    <div class="card-title">ÉMETTEUR</div>
                    <p><strong>{{ $user->company_name ?? 'Votre Entreprise' }}</strong></p>
                    @if($user->company_address)<p>{{ $user->company_address }}</p>@endif
                    @if($user->company_email)<p>Email : {{ $user->company_email }}</p>@endif
                    @if($user->company_phone)<p>Téléphone : {{ $user->company_phone }}</p>@endif
                </div>
            </td>
            <td style="padding-left:8px;">
                <div class="card">
                    <div class="card-title">CLIENT</div>

                    @if($company)
                        <p><strong>{{ $company->name }}</strong></p>
                        <p>À l’attention de {{ trim($billingFirst . ' ' . $billingLast) }}</p>
                    @else
                        <p><strong>{{ trim($billingFirst . ' ' . $billingLast) }}</strong></p>
                    @endif

                    @if($billingAddress)<p>{{ $billingAddress }}</p>@endif
                    @if($billingZip || $billingCity)<p>{{ trim(($billingZip ?? '').' '.($billingCity ?? '')) }}</p>@endif
                    @if($billingEmail)<p>Email : {{ $billingEmail }}</p>@endif
                    @if($billingPhone)<p>Téléphone : {{ $billingPhone }}</p>@endif
                </div>
            </td>
        </tr>
    </table>

    {{-- LIGNES + REMISES --}}
    <table class="items">
        <thead>
        <tr>
            <th>Nom</th>
            <th>Description</th>
            <th class="num">Qté</th>
            <th class="num">P.U. HT</th>
            <th class="num">TVA</th>
            <th class="num">Remise HT</th>
            <th class="num">Total HT</th>
            <th class="num">Montant TVA</th>
            <th class="num">Total TTC</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoice->items as $item)
            @php
                $name = $item->description;
                if($item->type === 'product' && $item->product) $name = $item->product->name;
                if($item->type === 'inventory' && $item->inventoryItem) $name = $item->inventoryItem->name;

                // Remises par ligne (compat avec ton devis)
                $lineDiscount = (float) ($item->line_discount_amount_ht ?? 0);
                $globalDiscountOnLine = (float) ($item->global_discount_amount_ht ?? 0);
                $discountHt = $lineDiscount + $globalDiscountOnLine;
            @endphp
            <tr>
                <td>{{ $name }}</td>
                <td>{{ $item->description }}</td>
                <td class="num">{{ number_format((float)$item->quantity, 2, ',', ' ') }}</td>
                <td class="num">{{ number_format((float)$item->unit_price, 2, ',', ' ') }} €</td>
                <td class="num">{{ number_format((float)$item->tax_rate, 2, ',', ' ') }}%</td>
                <td class="num">{{ number_format($discountHt, 2, ',', ' ') }} €</td>
                <td class="num">{{ number_format((float)$item->total_price, 2, ',', ' ') }} €</td>
                <td class="num">{{ number_format((float)$item->tax_amount, 2, ',', ' ') }} €</td>
                <td class="num">{{ number_format((float)$item->total_price_with_tax, 2, ',', ' ') }} €</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- TOTAUX + REMISE GLOBALE --}}
    <div class="totals-wrap">
        <table class="totals-table">
            @if($globalDiscountHt > 0)
                <tr>
                    <td class="label">Remise globale HT</td>
                    <td class="val">-{{ number_format($globalDiscountHt, 2, ',', ' ') }} €</td>
                </tr>
            @endif
            <tr>
                <td class="label">Total HT</td>
                <td class="val">{{ number_format((float)$invoice->total_amount, 2, ',', ' ') }} €</td>
            </tr>
            <tr>
                <td class="label">Total TVA</td>
                <td class="val">{{ number_format((float)$invoice->total_tax_amount, 2, ',', ' ') }} €</td>
            </tr>
            <tr class="total-ttc">
                <td class="label">Total TTC</td>
                <td class="val">{{ number_format((float)$invoice->total_amount_with_tax, 2, ',', ' ') }} €</td>
            </tr>
        </table>
    </div>

    {{-- Notes --}}
    @if($invoice->notes)
        <div class="notes">
            <div class="notes-title">Notes</div>
            <div>{{ $invoice->notes }}</div>
        </div>
    @endif

    {{-- Mentions légales --}}
    @if($user->legal_mentions)
        <div class="legal-mentions">
            {{ $user->legal_mentions }}
        </div>
    @endif

    <div class="footer">
        Merci pour votre confiance.
    </div>

</div>
</body>
</html>

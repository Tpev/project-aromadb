@extends('admin.finance.layout')

@section('title', 'Payouts & frais')
@section('page-title', 'Payouts & frais Stripe')
@section('page-subtitle', 'Vue cash déposé: payouts bancaires, brut traité, frais Stripe, remboursements, litiges et net.')

@section('content')
    <form method="GET" action="{{ route('admin.finance.payouts') }}" class="filters">
        <label>
            Depuis
            <input type="date" name="from" value="{{ $filters['from'] }}">
        </label>
        <label>
            Jusqu’au
            <input type="date" name="to" value="{{ $filters['to'] }}">
        </label>
        <div class="button-row">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i>Filtrer</button>
        </div>
    </form>

    <section class="table-panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Résumé mensuel cash</h2>
                <p class="panel-subtitle">Les payouts représentent le cash réellement envoyé vers la banque.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mois</th>
                        <th>Brut</th>
                        <th>Frais Stripe</th>
                        <th>Remboursements</th>
                        <th>Litiges</th>
                        <th>Net transactions</th>
                        <th>Payouts banque</th>
                        <th># payouts</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthly as $row)
                        <tr>
                            <td><strong>{{ $row['label'] }}</strong></td>
                            <td>{{ $money($row['gross_cents']) }}</td>
                            <td>{{ $money($row['fees_cents']) }}</td>
                            <td>{{ $money($row['refunds_cents']) }}</td>
                            <td>{{ $money($row['disputes_cents']) }}</td>
                            <td>{{ $money($row['net_cents']) }}</td>
                            <td>{{ $money($row['payout_cents']) }}</td>
                            <td>{{ $row['payout_count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="table-panel" style="margin-top:14px;">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Payouts</h2>
                <p class="panel-subtitle">Dépôts Stripe vers la banque et statut de rapprochement.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Arrivée banque</th>
                        <th>Payout</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Rapprochement</th>
                        <th>Stripe</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payouts as $payout)
                        <tr>
                            <td>{{ $payout->arrival_date?->format('d/m/Y') ?? '-' }}</td>
                            <td><strong>{{ $payout->stripe_payout_id }}</strong><br>{{ $payout->description }}</td>
                            <td>{{ $money($payout->amount_cents, $payout->currency) }}</td>
                            <td><span class="status-pill {{ $payout->status === 'paid' ? 'green' : 'amber' }}">{{ $payout->status_label }}</span></td>
                            <td>{{ $payout->reconciliation_label }}</td>
                            <td><a class="btn btn-small" href="{{ $payout->stripe_dashboard_url }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i>Ouvrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Aucun payout synchronisé sur cette période.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="table-panel" style="margin-top:14px;">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Dernières balance transactions</h2>
                <p class="panel-subtitle">Source de vérité pour brut, frais, remboursements et net.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Brut</th>
                        <th>Frais</th>
                        <th>Net</th>
                        <th>Payout</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->stripe_created_at?->format('d/m/Y') }}</td>
                            <td>{{ $transaction->type ?: $transaction->reporting_category }}</td>
                            <td>{{ $money($transaction->amount_cents, $transaction->currency) }}</td>
                            <td>{{ $money($transaction->fee_cents, $transaction->currency) }}</td>
                            <td>{{ $money($transaction->net_cents, $transaction->currency) }}</td>
                            <td>{{ $transaction->stripe_payout_id ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Aucune transaction synchronisée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

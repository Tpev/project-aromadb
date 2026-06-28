@extends('admin.finance.layout')

@section('title', 'Vue finance')
@section('page-title', 'Vue finance Stripe')
@section('page-subtitle', 'Revenu récurrent, encaissements, frais Stripe, payouts bancaires et alertes de paiement sur une seule vue.')

@section('content')
    <section class="metric-grid">
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-redo"></i>MRR</div>
            <div class="metric-value">{{ $money($metrics['mrr_cents']) }}</div>
            <div class="metric-note">Revenu mensuel récurrent actif.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-calendar-check"></i>ARR</div>
            <div class="metric-value">{{ $money($metrics['arr_cents']) }}</div>
            <div class="metric-note">Projection annuelle récurrente.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-file-invoice"></i>Revenu facturé</div>
            <div class="metric-value">{{ $money($metrics['booked_revenue_month_cents']) }}</div>
            <div class="metric-note">Factures Stripe créées ce mois-ci.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-file-invoice-dollar"></i>Cash attendu</div>
            <div class="metric-value">{{ $money($metrics['expected_cash_month_cents']) }}</div>
            <div class="metric-note">Prévision brute du mois courant.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-check-circle"></i>Cash encaissé</div>
            <div class="metric-value">{{ $money($metrics['actual_collected_month_cents']) }}</div>
            <div class="metric-note">Factures payées ce mois-ci.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-percentage"></i>Frais Stripe</div>
            <div class="metric-value">{{ $money($metrics['stripe_fees_month_cents']) }}</div>
            <div class="metric-note">Frais issus des balance transactions.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-university"></i>Payout net</div>
            <div class="metric-value">{{ $money($metrics['net_payout_month_cents']) }}</div>
            <div class="metric-note">Montant déposé en banque ce mois-ci.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-exclamation-triangle"></i>Échecs</div>
            <div class="metric-value">{{ $metrics['failed_payments'] }}</div>
            <div class="metric-note">Factures tentées encore ouvertes.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-hourglass-half"></i>Essais à suivre</div>
            <div class="metric-value">{{ $metrics['trials_ending'] }}</div>
            <div class="metric-note">Trials qui se terminent sous 14 jours.</div>
        </article>
    </section>

    <section class="metric-grid">
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-chart-line"></i>Prévision 30j</div>
            <div class="metric-value">{{ $money($metrics['forecast_30_cents']) }}</div>
            <div class="metric-note">Net estimé après frais moyens.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-chart-line"></i>Prévision 60j</div>
            <div class="metric-value">{{ $money($metrics['forecast_60_cents']) }}</div>
            <div class="metric-note">Renouvellements, essais et risque inclus.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-chart-line"></i>Prévision 90j</div>
            <div class="metric-value">{{ $money($metrics['forecast_90_cents']) }}</div>
            <div class="metric-note">Projection nette glissante.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-database"></i>Dernière sync</div>
            <div class="metric-value" style="font-size:18px;">
                {{ $latestSync?->finished_at?->format('d/m/Y H:i') ?? 'Jamais' }}
            </div>
            <div class="metric-note">{{ $syncConfigured ? 'Stripe configuré' : 'Clé Stripe manquante' }}</div>
        </article>
    </section>

    <section class="content-grid">
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Brut, frais, remboursements, net</h2>
                    <p class="panel-subtitle">Lecture mensuelle basée sur les balance transactions, avec fallback factures payées.</p>
                </div>
            </div>

            <div class="chart-list">
                @foreach($monthly as $row)
                    <div class="chart-row">
                        <strong>{{ $row['label'] }}</strong>
                        <div class="bar-track">
                            <span class="bar-fill" style="--bar: {{ $row['bar_percent'] }}%;"></span>
                        </div>
                        <div class="money-list">
                            <span>Brut <b>{{ $money($row['gross_cents']) }}</b></span>
                            <span>Frais <b>{{ $money($row['fees_cents']) }}</b></span>
                            <span>Remboursements <b>{{ $money($row['refunds_cents']) }}</b></span>
                            <span>Litiges <b>{{ $money($row['disputes_cents']) }}</b></span>
                            <span>Net <b>{{ $money($row['net_cents']) }}</b></span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Alertes rapides</h2>
                    <p class="panel-subtitle">Paiements à récupérer et essais à convertir.</p>
                </div>
            </div>

            @forelse($failedInvoices as $invoice)
                <article class="sub-card">
                    <div class="sub-title">
                        <strong>{{ $invoice->customer?->display_name ?? $invoice->stripe_customer_id }}</strong>
                        <span class="status-pill red">{{ $money($invoice->amount_remaining_cents, $invoice->currency) }}</span>
                    </div>
                    <div class="sub-meta">
                        <span>Facture <b>{{ $invoice->number ?: $invoice->stripe_invoice_id }}</b></span>
                        <span>Statut <b>{{ $invoice->status_label }}</b></span>
                        <span>Relance paiement <b>{{ $invoice->next_payment_attempt?->format('d/m/Y') ?? 'Non planifiée' }}</b></span>
                    </div>
                    <div class="button-row" style="margin-top:10px;">
                        @if($invoice->customer)
                            <a class="btn btn-small" href="{{ route('admin.finance.customers.show', $invoice->customer) }}"><i class="fas fa-eye"></i>Voir</a>
                        @endif
                        @if($invoice->hosted_invoice_url)
                            <a class="btn btn-small" href="{{ $invoice->hosted_invoice_url }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i>Stripe</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="empty-state">Aucun paiement échoué synchronisé.</div>
            @endforelse

            <div style="height:14px;"></div>

            @forelse($trialsEnding as $subscription)
                <article class="sub-card">
                    <div class="sub-title">
                        <strong>{{ $subscription->customer?->display_name ?? $subscription->stripe_customer_id }}</strong>
                        <span class="status-pill blue">Essai</span>
                    </div>
                    <div class="sub-meta">
                        <span>Licence <b>{{ $subscription->license_display }}</b></span>
                        <span>Fin d’essai <b>{{ $subscription->trial_end?->format('d/m/Y') }}</b></span>
                        <span>MRR <b>{{ $money($subscription->mrr_cents, $subscription->currency) }}</b></span>
                    </div>
                </article>
            @empty
                <div class="empty-state">Aucun essai arrivant à échéance.</div>
            @endforelse
        </div>
    </section>
@endsection

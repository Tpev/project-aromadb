@extends('admin.finance.layout')

@section('title', 'Paiements échoués')
@section('page-title', 'Centre paiements échoués')
@section('page-subtitle', 'Factures en échec, abonnements à risque, montants à récupérer et actions rapides.')

@section('content')
    <section class="metric-grid">
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-times-circle"></i>Factures échouées</div>
            <div class="metric-value">{{ $metrics['failed_count'] }}</div>
            <div class="metric-note">Tentées, ouvertes ou irrécouvrables.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-euro-sign"></i>Montant à risque</div>
            <div class="metric-value">{{ $money($metrics['at_risk_cents']) }}</div>
            <div class="metric-note">Somme encore due sur ces factures.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-user-clock"></i>Abonnements past_due</div>
            <div class="metric-value">{{ $metrics['past_due_count'] }}</div>
            <div class="metric-note">Statuts past_due, unpaid ou incomplete.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-redo"></i>MRR exposé</div>
            <div class="metric-value">{{ $money($metrics['past_due_mrr_cents']) }}</div>
            <div class="metric-note">MRR des licences en retard.</div>
        </article>
    </section>

    <section class="table-panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Factures à récupérer</h2>
                <p class="panel-subtitle">Relance paiement, raison d’échec et lien direct vers Stripe.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Facture</th>
                        <th>Restant</th>
                        <th>Relance paiement</th>
                        <th>Raison</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($failedInvoices as $invoice)
                        <tr>
                            <td>
                                <strong>{{ $invoice->customer?->display_name ?? $invoice->stripe_customer_id }}</strong><br>
                                {{ $invoice->customer?->email }}
                            </td>
                            <td>{{ $invoice->number ?: $invoice->stripe_invoice_id }}<br><span class="status-pill red">{{ $invoice->status_label }}</span></td>
                            <td>{{ $money($invoice->amount_remaining_cents, $invoice->currency) }}</td>
                            <td>{{ $invoice->next_payment_attempt?->format('d/m/Y H:i') ?? 'Non planifiée' }}</td>
                            <td>{{ $invoice->last_payment_error_message ?: $invoice->last_payment_error_code ?: 'Non renseignée' }}</td>
                            <td>
                                <div class="button-row">
                                    @if($invoice->customer)
                                        <a class="btn btn-small" href="{{ route('admin.finance.customers.show', $invoice->customer) }}"><i class="fas fa-eye"></i>Voir</a>
                                    @endif
                                    @if($invoice->hosted_invoice_url)
                                        <a class="btn btn-small" href="{{ $invoice->hosted_invoice_url }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i>Stripe</a>
                                    @endif
                                    @if($invoice->customer?->email)
                                        <a class="btn btn-small" href="mailto:{{ $invoice->customer->email }}"><i class="fas fa-envelope"></i>Email</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Aucune facture échouée synchronisée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="table-panel" style="margin-top:14px;">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Abonnements en retard</h2>
                <p class="panel-subtitle">Clients dont la licence mérite une relance commerciale ou support.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Licence</th>
                        <th>Statut</th>
                        <th>MRR</th>
                        <th>Fin période</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pastDueSubscriptions as $subscription)
                        <tr>
                            <td><strong>{{ $subscription->customer?->display_name ?? $subscription->stripe_customer_id }}</strong><br>{{ $subscription->customer?->email }}</td>
                            <td>{{ $subscription->license_display }}<br>{{ $subscription->interval_label }}</td>
                            <td><span class="status-pill red">{{ $subscription->status_label }}</span></td>
                            <td>{{ $money($subscription->mrr_cents, $subscription->currency) }}</td>
                            <td>{{ $subscription->current_period_end?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <div class="button-row">
                                    @if($subscription->customer)
                                        <a class="btn btn-small" href="{{ route('admin.finance.customers.show', $subscription->customer) }}"><i class="fas fa-eye"></i>Voir</a>
                                    @endif
                                    <a class="btn btn-small" href="{{ $subscription->stripe_dashboard_url }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i>Stripe</a>
                                    @if($subscription->customer?->email)
                                        <a class="btn btn-small" href="mailto:{{ $subscription->customer->email }}"><i class="fas fa-envelope"></i>Email</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Aucun abonnement past_due synchronisé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@extends('admin.finance.layout')

@section('title', 'Fiche client finance')
@section('page-title', 'Fiche client finance')
@section('page-subtitle', 'Vue consolidée du client Stripe, de ses licences, factures, frais, refunds et notes de suivi.')

@section('content')
    <section class="panel">
        <div class="detail-header">
            <div class="identity">
                <h2>{{ $customer->display_name }}</h2>
                <span>{{ $customer->email ?: 'Email non renseigné' }}</span>
                <span>Client Stripe: {{ $customer->stripe_customer_id }}</span>
                @if($customer->user)
                    <span>Utilisateur Aromadb: {{ $customer->user->name }} · {{ $customer->user->license_product ?: 'licence non définie' }}</span>
                @endif
            </div>
            <div class="button-row">
                <a class="btn" href="{{ $customer->stripe_dashboard_url }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i>Stripe</a>
                @if($customer->user)
                    <a class="btn" href="{{ route('admin.therapists.show', $customer->user) }}"><i class="fas fa-user"></i>Utilisateur Aromadb</a>
                @endif
                <a class="btn" href="{{ route('admin.finance.customers') }}"><i class="fas fa-arrow-left"></i>Tableau</a>
            </div>
        </div>
    </section>

    <section class="metric-grid">
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-cash-register"></i>Total payé</div>
            <div class="metric-value">{{ $money($metrics['gross_paid_cents']) }}</div>
            <div class="metric-note">Somme des factures payées.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-wallet"></i>Net estimé</div>
            <div class="metric-value">{{ $money($metrics['net_revenue_cents']) }}</div>
            <div class="metric-note">Net issu des balance transactions si disponible.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-percentage"></i>Frais</div>
            <div class="metric-value">{{ $money($metrics['fees_cents']) }}</div>
            <div class="metric-note">Frais Stripe liés au client.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-undo"></i>Remboursements</div>
            <div class="metric-value">{{ $money($metrics['refunds_cents']) }}</div>
            <div class="metric-note">Remboursements détectés.</div>
        </article>
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-exclamation-triangle"></i>Échecs</div>
            <div class="metric-value">{{ $metrics['failed_count'] }}</div>
            <div class="metric-note">Factures tentées non réglées.</div>
        </article>
    </section>

    <section class="content-grid">
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Licences</h2>
                    <p class="panel-subtitle">Statut, terme, prochain paiement, promo et lien Stripe.</p>
                </div>
            </div>
            @forelse($customer->subscriptions as $subscription)
                <article class="sub-card">
                    <div class="sub-title">
                        <strong>{{ $subscription->license_display }}</strong>
                        <span class="status-pill {{ $subscription->is_failed_payment ? 'red' : 'green' }}">{{ $subscription->status_label }}</span>
                    </div>
                    <div class="sub-meta">
                        <span>Subscription <b>{{ $subscription->stripe_subscription_id }}</b></span>
                        <span>Terme <b>{{ $subscription->interval_label }}</b></span>
                        <span>Montant <b>{{ $money($subscription->amount_cents, $subscription->currency) }}</b></span>
                        <span>Prochain paiement <b>{{ $subscription->current_period_end?->format('d/m/Y') ?? 'Non défini' }}</b></span>
                        <span>Fin d’essai <b>{{ $subscription->trial_end?->format('d/m/Y') ?? 'Aucune' }}</b></span>
                        <span>Promo <b>{{ $subscription->promotion_code ?: ($subscription->coupon_name ?: 'Aucune') }}</b></span>
                        <span>Moyen de paiement <b>{{ $subscription->default_payment_method_label ?: $customer->default_payment_method_label ?: 'Non synchronisé' }}</b></span>
                    </div>
                    <div class="button-row" style="margin-top:10px;">
                        <a class="btn btn-small" href="{{ $subscription->stripe_dashboard_url }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i>Stripe</a>
                    </div>
                </article>
            @empty
                <div class="empty-state">Aucune licence synchronisée.</div>
            @endforelse
        </div>

        <aside class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Notes admin</h2>
                    <p class="panel-subtitle">Suivi interne du compte finance.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.finance.customers.notes.store', $customer) }}" class="form-grid">
                @csrf
                <label>
                    Type
                    <select name="type">
                        <option value="note">Note</option>
                        <option value="relance">Relance</option>
                        <option value="risque">Risque</option>
                    </select>
                </label>
                <label>
                    Échéance
                    <input type="datetime-local" name="due_at">
                </label>
                <label style="grid-column:1 / -1;">
                    Note
                    <textarea name="body" required placeholder="Ex: relancer avant le prochain retry Stripe."></textarea>
                </label>
                <div class="button-row" style="grid-column:1 / -1;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i>Ajouter</button>
                </div>
            </form>

            @forelse($customer->notes as $note)
                <article class="sub-card">
                    <div class="sub-title">
                        <strong>{{ ucfirst($note->type) }}</strong>
                        <span class="count-pill">{{ $note->created_at?->format('d/m/Y') }}</span>
                    </div>
                    <p style="margin:8px 0;color:#334155;line-height:1.45;">{{ $note->body }}</p>
                    <div class="sub-meta">
                        <span>Par <b>{{ $note->creator?->name ?? 'Admin' }}</b></span>
                        <span>Échéance <b>{{ $note->due_at?->format('d/m/Y H:i') ?? 'Aucune' }}</b></span>
                    </div>
                    <form method="POST" action="{{ route('admin.finance.customers.notes.destroy', [$customer, $note]) }}" onsubmit="return confirm('Supprimer cette note ?');" style="margin-top:10px;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-small btn-danger"><i class="fas fa-trash"></i>Supprimer</button>
                    </form>
                </article>
            @empty
                <div class="empty-state" style="margin-top:12px;">Aucune note finance.</div>
            @endforelse
        </aside>
    </section>

    <section class="table-panel" style="margin-top:14px;">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Historique factures</h2>
                <p class="panel-subtitle">Montants, statuts, retries et documents Stripe.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Facture</th>
                        <th>Statut</th>
                        <th>Montant</th>
                        <th>Payé</th>
                        <th>Restant</th>
                        <th>Retry</th>
                        <th>Stripe</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customer->invoices as $invoice)
                        <tr>
                            <td><strong>{{ $invoice->number ?: $invoice->stripe_invoice_id }}</strong><br>{{ $invoice->stripe_created_at?->format('d/m/Y') }}</td>
                            <td><span class="status-pill {{ $invoice->is_failed ? 'red' : ($invoice->status === 'paid' ? 'green' : 'amber') }}">{{ $invoice->status_label }}</span></td>
                            <td>{{ $money($invoice->total_cents, $invoice->currency) }}</td>
                            <td>{{ $money($invoice->amount_paid_cents, $invoice->currency) }}</td>
                            <td>{{ $money($invoice->amount_remaining_cents, $invoice->currency) }}</td>
                            <td>{{ $invoice->next_payment_attempt?->format('d/m/Y H:i') ?? 'Aucun' }}</td>
                            <td>
                                @if($invoice->hosted_invoice_url)
                                    <a class="btn btn-small" href="{{ $invoice->hosted_invoice_url }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i>Ouvrir</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Aucune facture synchronisée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="table-panel" style="margin-top:14px;">
        <div class="panel-header">
            <div>
                    <h2 class="panel-title">Transactions nettes</h2>
                    <p class="panel-subtitle">Frais, remboursements et net par transaction Stripe liée au client.</p>
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
                        <th>Description</th>
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
                            <td>{{ $transaction->description ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Aucune balance transaction liée à ce client.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

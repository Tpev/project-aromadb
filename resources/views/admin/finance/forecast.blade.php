@extends('admin.finance.layout')

@section('title', 'Prévisions')
@section('page-title', 'Prévisions cashflow')
@section('page-subtitle', 'Prévision conservatrice, attendue et optimiste à partir des renouvellements, prochaines factures, essais, annulations et paiements à récupérer.')

@section('content')
    <section class="metric-grid">
        @foreach($windows as $days => $forecast)
            <article class="metric-card">
                <div class="metric-label"><i class="fas fa-calendar-alt"></i>{{ $days }} jours</div>
                <div class="metric-value">{{ $money($forecast['expected_net_cents']) }}</div>
                <div class="metric-note">Net attendu après {{ $percent($feeRate) }} de frais moyens.</div>
            </article>
        @endforeach
        <article class="metric-card">
            <div class="metric-label"><i class="fas fa-forward"></i>Mois prochain</div>
            <div class="metric-value">{{ $money($nextMonth['expected_net_cents']) }}</div>
            <div class="metric-note">Prévision nette attendue.</div>
        </article>
    </section>

    <section class="table-panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Prévision mensuelle</h2>
                <p class="panel-subtitle">Trois scénarios nets après frais moyens Stripe.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mois</th>
                        <th>Conservateur</th>
                        <th>Attendu</th>
                        <th>Optimiste</th>
                        <th>Prévisualisations</th>
                        <th>Renouvellements</th>
                        <th>Essais</th>
                        <th>Risque past_due</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyForecast as $forecast)
                        <tr>
                            <td><strong>{{ $forecast['label'] }}</strong></td>
                            <td>{{ $money($forecast['conservative_net_cents']) }}</td>
                            <td>{{ $money($forecast['expected_net_cents']) }}</td>
                            <td>{{ $money($forecast['optimistic_net_cents']) }}</td>
                            <td>{{ $money($forecast['preview_cents']) }}</td>
                            <td>{{ $money($forecast['renewal_cents']) }}</td>
                            <td>{{ $money($forecast['trial_potential_cents']) }}</td>
                            <td>{{ $money($forecast['past_due_risk_cents']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="content-grid" style="margin-top:14px;">
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Prévisualisations des prochaines factures</h2>
                    <p class="panel-subtitle">Prévisions Stripe synchronisées pour les prochains paiements.</p>
                </div>
            </div>
            @forelse($upcomingPreviews as $preview)
                <article class="sub-card">
                    <div class="sub-title">
                        <strong>{{ $preview->subscription?->customer?->display_name ?? $preview->stripe_customer_id }}</strong>
                        <span class="status-pill blue">{{ $money($preview->amount_due_cents, $preview->currency) }}</span>
                    </div>
                    <div class="sub-meta">
                        <span>Licence <b>{{ $preview->subscription?->license_display ?? 'Non liée' }}</b></span>
                        <span>Période <b>{{ $preview->period_end?->format('d/m/Y') ?? 'Non définie' }}</b></span>
                        <span>Promo <b>{{ $preview->promotion_code ?: ($preview->coupon_name ?: 'Aucune') }}</b></span>
                        <span>Prévisualisée <b>{{ $preview->previewed_at?->format('d/m/Y H:i') }}</b></span>
                    </div>
                </article>
            @empty
                <div class="empty-state">Aucune prévisualisation de prochaine facture synchronisée.</div>
            @endforelse
        </div>

        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Risques & opportunités</h2>
                    <p class="panel-subtitle">Essais à convertir et churn déjà programmé.</p>
                </div>
            </div>
            @forelse($trials as $subscription)
                <article class="sub-card">
                    <div class="sub-title">
                        <strong>{{ $subscription->customer?->display_name ?? $subscription->stripe_customer_id }}</strong>
                        <span class="status-pill blue">Essai</span>
                    </div>
                    <div class="sub-meta">
                        <span>Fin d’essai <b>{{ $subscription->trial_end?->format('d/m/Y') }}</b></span>
                        <span>Montant potentiel <b>{{ $money($subscription->amount_cents, $subscription->currency) }}</b></span>
                    </div>
                </article>
            @empty
                <div class="empty-state">Aucun trial à convertir sur 45 jours.</div>
            @endforelse

            <div style="height:12px;"></div>

            @forelse($cancellations as $subscription)
                <article class="sub-card">
                    <div class="sub-title">
                        <strong>{{ $subscription->customer?->display_name ?? $subscription->stripe_customer_id }}</strong>
                        <span class="status-pill violet">Résiliation</span>
                    </div>
                    <div class="sub-meta">
                        <span>Fin période <b>{{ $subscription->current_period_end?->format('d/m/Y') }}</b></span>
                        <span>MRR perdu <b>{{ $money($subscription->mrr_cents, $subscription->currency) }}</b></span>
                    </div>
                </article>
            @empty
                <div class="empty-state">Aucune résiliation programmée sur 90 jours.</div>
            @endforelse
        </div>
    </section>
@endsection

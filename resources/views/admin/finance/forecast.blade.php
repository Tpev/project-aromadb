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

    <section class="content-grid" style="margin-top:14px;">
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Hypothèses nouvelles licences</h2>
                    <p class="panel-subtitle">Objectifs mensuels, avec renouvellement des cohortes sur les mois suivants.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.finance.forecast.assumptions.update') }}">
                @csrf
                <div class="table-wrap">
                    <table class="assumption-table">
                        <thead>
                            <tr>
                                <th>Mois</th>
                                <th>Conservateur</th>
                                <th>Attendu</th>
                                <th>Optimiste</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($forecastMonths as $month)
                                @php
                                    $assumption = $forecastAssumptions->get($month['key']);
                                    $conservativeTarget = old("assumptions.{$month['key']}.conservative_new_customers", $assumption['conservative_new_customers']);
                                    $optimisticTarget = old("assumptions.{$month['key']}.optimistic_new_customers", $assumption['optimistic_new_customers']);
                                    $expectedTarget = (((int) $conservativeTarget) + ((int) $optimisticTarget)) / 2;
                                @endphp
                                <tr>
                                    <td><strong>{{ $month['label'] }}</strong></td>
                                    <td>
                                        <input
                                            type="number"
                                            min="0"
                                            max="500"
                                            step="1"
                                            name="assumptions[{{ $month['key'] }}][conservative_new_customers]"
                                            value="{{ $conservativeTarget }}"
                                            placeholder="8"
                                        >
                                    </td>
                                    <td>
                                        <strong>{{ $customerCount($expectedTarget) }}</strong>
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            min="0"
                                            max="500"
                                            step="1"
                                            name="assumptions[{{ $month['key'] }}][optimistic_new_customers]"
                                            value="{{ $optimisticTarget }}"
                                            placeholder="8"
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="button-row" style="margin-top:12px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>Enregistrer</button>
                </div>
            </form>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Mix licences utilisé</h2>
                    <p class="panel-subtitle">Valeur moyenne {{ $money($licenseMix['average_amount_cents'], $licenseMix['currency']) }} sur {{ $licenseMix['total_customers'] }} licences actives.</p>
                </div>
            </div>
            <div class="chart-list">
                @forelse($licenseMix['items']->take(6) as $item)
                    <div class="mini-line">
                        <span>{{ $item['label'] }} · {{ $item['term'] }}</span>
                        <b>{{ $percent($item['share']) }} · {{ $money($item['amount_cents'], $item['currency']) }}</b>
                    </div>
                @empty
                    <div class="empty-state">Aucune licence active pour calculer le mix.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="table-panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Prévision mensuelle</h2>
                <p class="panel-subtitle">Trois scénarios nets après frais moyens Stripe.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="forecast-table">
                <thead>
                    <tr>
                        <th>Mois</th>
                        <th>Conservateur</th>
                        <th>Attendu</th>
                        <th>Optimiste</th>
                        <th>Prévisualisations</th>
                        <th>Renouvellements</th>
                        <th>Cohortes nouvelles licences</th>
                        <th>Essais</th>
                        <th>Impayés à récupérer</th>
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
                            <td>
                                <span class="mini-line">C <b>{{ $customerCount($forecast['new_customers_conservative']) }} · {{ $money($forecast['new_business_conservative_cents']) }}</b></span>
                                <span class="mini-line">A <b>{{ $customerCount($forecast['new_customers_expected']) }} · {{ $money($forecast['new_business_expected_cents']) }}</b></span>
                                <span class="mini-line">O <b>{{ $customerCount($forecast['new_customers_optimistic']) }} · {{ $money($forecast['new_business_optimistic_cents']) }}</b></span>
                            </td>
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
                @php
                    $paymentDate = $preview->next_payment_attempt ?? $preview->due_date ?? $preview->period_start ?? $preview->period_end;
                    $coveredPeriod = $preview->period_start && $preview->period_end
                        ? $preview->period_start->format('d/m/Y') . ' - ' . $preview->period_end->format('d/m/Y')
                        : null;
                @endphp
                <article class="sub-card">
                    <div class="sub-title">
                        <strong>{{ $preview->subscription?->customer?->display_name ?? $preview->stripe_customer_id }}</strong>
                        <span class="status-pill blue">{{ $money($preview->amount_due_cents, $preview->currency) }}</span>
                    </div>
                    <div class="sub-meta">
                        <span>Licence <b>{{ $preview->subscription?->license_display ?? 'Non liée' }}</b></span>
                        <span>Paiement prévu <b>{{ $paymentDate?->format('d/m/Y') ?? 'Non défini' }}</b></span>
                        <span>Période couverte <b>{{ $coveredPeriod ?? 'Non définie' }}</b></span>
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

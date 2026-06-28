@extends('admin.finance.layout')

@section('title', 'Clients & licences')
@section('page-title', 'Clients & licences')
@section('page-subtitle', 'Board opérationnel pour suivre les abonnements, le terme, les promotions, les prochains paiements et les risques.')

@section('content')
    <form method="GET" action="{{ route('admin.finance.customers') }}" class="filters">
        <label>
            Recherche
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom, email, client Stripe, licence...">
        </label>
        <label>
            Terme
            <select name="term">
                <option value="">Tous</option>
                <option value="month" @selected(($filters['term'] ?? '') === 'month')>Mensuel</option>
                <option value="year" @selected(($filters['term'] ?? '') === 'year')>Annuel</option>
            </select>
        </label>
        <div class="button-row">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i>Filtrer</button>
            <a href="{{ route('admin.finance.customers') }}" class="btn"><i class="fas fa-times"></i>Réinitialiser</a>
        </div>
    </form>

    <section class="board-scroll">
        <div class="board">
            @foreach($columns as $column)
                <div class="board-column" style="--accent: {{ $column['accent'] }};">
                    <div class="column-title">
                        <div>
                            <h2>{{ $column['label'] }}</h2>
                            <div class="panel-subtitle">{{ $column['description'] }}</div>
                        </div>
                        <span class="count-pill">{{ $column['count'] }}</span>
                    </div>
                    <div class="mini-line" style="margin-bottom:10px;">
                        <span>MRR colonne</span>
                        <b>{{ $money($column['mrr_cents']) }}</b>
                    </div>

                    @forelse($column['items'] as $subscription)
                        @php
                            $statusClass = match($subscription->board_status) {
                                'payment_failed' => 'red',
                                'past_due' => 'amber',
                                'trialing' => 'blue',
                                'canceling' => 'violet',
                                'canceled' => '',
                                default => 'green',
                            };
                            $customer = $subscription->customer;
                            $latestInvoice = $subscription->latestInvoice;
                        @endphp
                        <article class="sub-card">
                            <div class="sub-title">
                                <strong>{{ $customer?->display_name ?? $subscription->stripe_customer_id }}</strong>
                                <span class="status-pill {{ $statusClass }}">{{ $subscription->status_label }}</span>
                            </div>
                            <div class="sub-meta">
                                <span>Licence <b>{{ $subscription->license_display }}</b></span>
                                <span>Terme <b>{{ $subscription->interval_label }}</b></span>
                                <span>Prochain paiement <b>{{ $subscription->current_period_end?->format('d/m/Y') ?? 'Non défini' }}</b></span>
                                <span>Montant <b>{{ $money($subscription->amount_cents, $subscription->currency) }}</b></span>
                                <span>MRR / ARR <b>{{ $money($subscription->mrr_cents, $subscription->currency) }} / {{ $money($subscription->arr_cents, $subscription->currency) }}</b></span>
                                <span>Promo <b>{{ $subscription->promotion_code ?: ($subscription->coupon_name ?: 'Aucune') }}</b></span>
                                <span>Dernier paiement <b>{{ $latestInvoice?->status_label ?? 'Non synchronisé' }}</b></span>
                                <span>Utilisateur Aromadb <b>{{ $customer?->user?->name ?? 'Non lié' }}</b></span>
                            </div>
                            <div class="button-row" style="margin-top:10px;">
                                @if($customer)
                                    <a class="btn btn-small" href="{{ route('admin.finance.customers.show', $customer) }}"><i class="fas fa-eye"></i>Voir</a>
                                @endif
                                <a class="btn btn-small" href="{{ $subscription->stripe_dashboard_url }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i>Stripe</a>
                                @if($customer?->user)
                                    <a class="btn btn-small" href="{{ route('admin.therapists.show', $customer->user) }}"><i class="fas fa-user"></i>Aromadb</a>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">Aucun abonnement dans cette colonne.</div>
                    @endforelse
                </div>
            @endforeach
        </div>
    </section>
@endsection

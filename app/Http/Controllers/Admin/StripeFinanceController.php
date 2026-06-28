<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StripeFinanceBalanceTransaction;
use App\Models\StripeFinanceCustomer;
use App\Models\StripeFinanceForecastAssumption;
use App\Models\StripeFinanceInvoice;
use App\Models\StripeFinanceNote;
use App\Models\StripeFinancePayout;
use App\Models\StripeFinanceSubscription;
use App\Models\StripeFinanceSyncRun;
use App\Models\StripeFinanceUpcomingInvoice;
use App\Services\StripeFinanceSyncService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StripeFinanceController extends Controller
{
    private const BOARD_COLUMNS = [
        'active' => ['label' => 'Actifs', 'description' => 'Licences en cours sans alerte', 'accent' => '#047857'],
        'trialing' => ['label' => 'Essais', 'description' => 'Périodes de test à convertir', 'accent' => '#2563eb'],
        'past_due' => ['label' => 'En retard', 'description' => 'Abonnements past_due ou unpaid', 'accent' => '#b45309'],
        'payment_failed' => ['label' => 'Paiement échoué', 'description' => 'Factures tentées et non réglées', 'accent' => '#dc2626'],
        'canceling' => ['label' => 'Résiliation prévue', 'description' => 'Cancel at period end', 'accent' => '#7c3aed'],
        'canceled' => ['label' => 'Annulés', 'description' => 'Licences arrêtées', 'accent' => '#6b7280'],
        'annual' => ['label' => 'Annuels', 'description' => 'Contrats actifs facturés à l’année', 'accent' => '#0f766e'],
        'monthly' => ['label' => 'Mensuels', 'description' => 'Contrats actifs facturés au mois', 'accent' => '#0891b2'],
    ];

    public function overview(Request $request)
    {
        $this->authorizeAdmin();

        [$chartStart, $chartEnd] = $this->chartRange($request);

        $metrics = $this->overviewMetrics();
        $monthly = $this->monthlyFinancialSeries($chartStart, $chartEnd);
        $failedInvoices = $this->failedInvoicesQuery()
            ->with(['customer.user', 'subscription'])
            ->latest('stripe_created_at')
            ->limit(8)
            ->get();
        $trialsEnding = StripeFinanceSubscription::query()
            ->with('customer.user')
            ->where('status', 'trialing')
            ->whereBetween('trial_end', [now(), now()->addDays(14)])
            ->orderBy('trial_end')
            ->limit(8)
            ->get();

        return view('admin.finance.overview', $this->sharedViewData([
            'metrics' => $metrics,
            'monthly' => $monthly,
            'failedInvoices' => $failedInvoices,
            'trialsEnding' => $trialsEnding,
            'latestSync' => StripeFinanceSyncRun::latest('started_at')->first(),
            'syncConfigured' => app(StripeFinanceSyncService::class)->isConfigured(),
        ]));
    }

    public function customers(Request $request)
    {
        $this->authorizeAdmin();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'term' => ['nullable', 'in:month,year'],
        ]);

        $baseQuery = $this->filteredSubscriptionQuery($filters)
            ->with(['customer.user', 'latestInvoice'])
            ->withCount([
                'invoices as failed_invoices_count' => fn (Builder $query) => $this->failedInvoiceConditions($query),
            ]);

        $columns = collect(self::BOARD_COLUMNS)->map(function (array $column, string $key) use ($baseQuery) {
            $query = clone $baseQuery;
            $items = $this->applyBoardColumn($query, $key)
                ->orderByRaw('current_period_end IS NULL')
                ->orderBy('current_period_end')
                ->limit(80)
                ->get();

            return [
                'key' => $key,
                'label' => $column['label'],
                'description' => $column['description'],
                'accent' => $column['accent'],
                'count' => $items->count(),
                'mrr_cents' => $items->sum(fn (StripeFinanceSubscription $subscription) => $subscription->mrr_cents),
                'items' => $items,
            ];
        });

        return view('admin.finance.customers', $this->sharedViewData([
            'columns' => $columns,
            'filters' => $filters,
            'boardColumns' => self::BOARD_COLUMNS,
        ]));
    }

    public function showCustomer(StripeFinanceCustomer $customer)
    {
        $this->authorizeAdmin();

        $customer->load([
            'user:id,name,email,license_product,license_status',
            'subscriptions' => fn ($query) => $query->orderByDesc('status')->orderBy('current_period_end'),
            'invoices' => fn ($query) => $query->latest('stripe_created_at')->limit(80),
            'notes' => fn ($query) => $query->with('creator:id,name')->latest(),
        ]);

        $transactions = StripeFinanceBalanceTransaction::query()
            ->where('stripe_customer_id', $customer->stripe_customer_id)
            ->latest('stripe_created_at')
            ->limit(60)
            ->get();

        $paidInvoices = $customer->invoices->where('status', 'paid');
        $failedInvoices = $customer->invoices->filter(fn (StripeFinanceInvoice $invoice) => $invoice->is_failed);
        $grossPaidCents = $paidInvoices->sum('amount_paid_cents');
        $netRevenueCents = $transactions->isNotEmpty() ? $transactions->sum('net_cents') : $grossPaidCents;
        $feesCents = $transactions->sum('fee_cents');
        $refundsCents = abs($transactions
            ->filter(fn (StripeFinanceBalanceTransaction $transaction) => $this->isRefundTransaction($transaction))
            ->sum('amount_cents'));

        return view('admin.finance.show', $this->sharedViewData([
            'customer' => $customer,
            'transactions' => $transactions,
            'metrics' => [
                'gross_paid_cents' => $grossPaidCents,
                'net_revenue_cents' => $netRevenueCents,
                'fees_cents' => $feesCents,
                'refunds_cents' => $refundsCents,
                'failed_count' => $failedInvoices->count(),
            ],
        ]));
    }

    public function storeCustomerNote(Request $request, StripeFinanceCustomer $customer): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'stripe_finance_subscription_id' => ['nullable', 'integer', 'exists:stripe_finance_subscriptions,id'],
            'type' => ['nullable', 'string', 'max:40'],
            'body' => ['required', 'string', 'max:5000'],
            'due_at' => ['nullable', 'date'],
        ]);

        $customer->notes()->create([
            'stripe_finance_subscription_id' => $validated['stripe_finance_subscription_id'] ?? null,
            'created_by_user_id' => auth()->id(),
            'type' => $validated['type'] ?? 'note',
            'body' => $validated['body'],
            'due_at' => $validated['due_at'] ?? null,
        ]);

        return redirect()
            ->route('admin.finance.customers.show', $customer)
            ->with('success', 'Note finance ajoutée.');
    }

    public function destroyCustomerNote(StripeFinanceCustomer $customer, StripeFinanceNote $note): RedirectResponse
    {
        $this->authorizeAdmin();
        abort_unless((int) $note->stripe_finance_customer_id === (int) $customer->id, 404);

        $note->delete();

        return redirect()
            ->route('admin.finance.customers.show', $customer)
            ->with('success', 'Note supprimée.');
    }

    public function failures()
    {
        $this->authorizeAdmin();

        $failedInvoices = $this->failedInvoicesQuery()
            ->with(['customer.user', 'subscription'])
            ->latest('stripe_created_at')
            ->limit(120)
            ->get();

        $pastDueSubscriptions = StripeFinanceSubscription::query()
            ->with('customer.user')
            ->whereIn('status', ['past_due', 'unpaid', 'incomplete'])
            ->orderBy('current_period_end')
            ->limit(120)
            ->get();

        return view('admin.finance.failures', $this->sharedViewData([
            'failedInvoices' => $failedInvoices,
            'pastDueSubscriptions' => $pastDueSubscriptions,
            'metrics' => [
                'failed_count' => $failedInvoices->count(),
                'at_risk_cents' => $failedInvoices->sum('amount_remaining_cents'),
                'past_due_count' => $pastDueSubscriptions->count(),
                'past_due_mrr_cents' => $pastDueSubscriptions->sum(fn (StripeFinanceSubscription $subscription) => $subscription->mrr_cents),
            ],
        ]));
    }

    public function payouts(Request $request)
    {
        $this->authorizeAdmin();

        [$start, $end] = $this->chartRange($request);

        $payouts = StripeFinancePayout::query()
            ->whereBetween('arrival_date', [$start, $end])
            ->latest('arrival_date')
            ->limit(120)
            ->get();

        $monthly = $this->monthlyPayoutSeries($start, $end);
        $transactions = StripeFinanceBalanceTransaction::query()
            ->whereBetween('stripe_created_at', [$start, $end])
            ->latest('stripe_created_at')
            ->limit(80)
            ->get();

        return view('admin.finance.payouts', $this->sharedViewData([
            'payouts' => $payouts,
            'monthly' => $monthly,
            'transactions' => $transactions,
            'filters' => [
                'from' => $start->toDateString(),
                'to' => $end->toDateString(),
            ],
        ]));
    }

    public function forecast()
    {
        $this->authorizeAdmin();

        $feeRate = $this->averageFeeRate();
        $forecastMonths = $this->forecastMonths();
        $forecastAssumptions = $this->forecastAssumptionMap($forecastMonths);
        $licenseMix = $this->paidLicenseMix();
        $currentBookedCents = $this->currentBookedRevenueCents();
        $windows = collect([30, 60, 90])->mapWithKeys(fn (int $days) => [
            $days => $this->forecastForPeriod(now(), now()->addDays($days), $feeRate, $forecastAssumptions, $licenseMix),
        ]);

        $nextMonthStart = now()->startOfMonth()->addMonth();
        $nextMonthEnd = $nextMonthStart->copy()->endOfMonth();
        $nextMonth = $this->forecastForPeriod($nextMonthStart, $nextMonthEnd, $feeRate, $forecastAssumptions, $licenseMix);

        $upcomingPreviews = $this->upcomingPreviewQuery(now(), now()->addDays(45))
            ->with('subscription.customer.user')
            ->orderByRaw('COALESCE(next_payment_attempt, due_date, period_start, period_end) ASC')
            ->limit(30)
            ->get();

        $trials = StripeFinanceSubscription::query()
            ->with('customer.user')
            ->where('status', 'trialing')
            ->whereBetween('trial_end', [now(), now()->addDays(45)])
            ->orderBy('trial_end')
            ->limit(30)
            ->get();

        $cancellations = StripeFinanceSubscription::query()
            ->with('customer.user')
            ->where('cancel_at_period_end', true)
            ->whereBetween('current_period_end', [now(), now()->addDays(90)])
            ->orderBy('current_period_end')
            ->limit(30)
            ->get();

        $monthlyForecast = $forecastMonths->map(function (array $month) use ($feeRate, $forecastAssumptions, $licenseMix) {
            $forecast = $this->forecastForPeriod($month['start'], $month['end'], $feeRate, $forecastAssumptions, $licenseMix);
            $forecast['label'] = $month['label'];
            $forecast['assumption'] = $forecastAssumptions->get($month['key']);

            return $forecast;
        });

        return view('admin.finance.forecast', $this->sharedViewData([
            'feeRate' => $feeRate,
            'windows' => $windows,
            'nextMonth' => $nextMonth,
            'monthlyForecast' => $monthlyForecast,
            'forecastAssumptions' => $forecastAssumptions,
            'forecastMonths' => $forecastMonths,
            'licenseMix' => $licenseMix,
            'currentBookedCents' => $currentBookedCents,
            'upcomingPreviews' => $upcomingPreviews,
            'trials' => $trials,
            'cancellations' => $cancellations,
        ]));
    }

    public function updateForecastAssumptions(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'assumptions' => ['required', 'array'],
            'assumptions.*.conservative_new_customers' => ['required', 'integer', 'min:0', 'max:500'],
            'assumptions.*.optimistic_new_customers' => ['required', 'integer', 'min:0', 'max:500'],
        ]);

        $allowedMonths = $this->forecastMonths()->pluck('key')->all();

        foreach ($allowedMonths as $monthKey) {
            $values = $validated['assumptions'][$monthKey] ?? [
                'conservative_new_customers' => 0,
                'optimistic_new_customers' => 0,
            ];
            $conservative = (int) $values['conservative_new_customers'];
            $optimistic = (int) $values['optimistic_new_customers'];

            if ($optimistic < $conservative) {
                return back()
                    ->withErrors(['assumptions' => 'Le scénario optimiste doit être supérieur ou égal au conservateur.'])
                    ->withInput();
            }

            $monthDate = Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth()->toDateString();
            $assumption = StripeFinanceForecastAssumption::query()
                ->whereDate('month', $monthDate)
                ->firstOrNew(['month' => $monthDate]);

            $assumption->fill([
                'conservative_new_customers' => $conservative,
                'optimistic_new_customers' => $optimistic,
            ])->save();
        }

        return redirect()
            ->route('admin.finance.forecast')
            ->with('success', 'Hypothèses de nouvelles licences mises à jour.');
    }

    public function sync(Request $request, StripeFinanceSyncService $sync): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'days' => ['nullable', 'integer', 'min:7', 'max:1460'],
            'max' => ['nullable', 'integer', 'min:100', 'max:10000'],
        ]);

        try {
            $summary = $sync->syncAll((int) ($validated['days'] ?? 365), (int) ($validated['max'] ?? 1500));
        } catch (\Throwable $e) {
            return back()->withErrors(['sync' => 'Synchronisation Stripe impossible : ' . $e->getMessage()]);
        }

        return back()->with('success', 'Synchronisation terminée : ' . array_sum($summary) . ' éléments mis à jour.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);
    }

    private function sharedViewData(array $data = []): array
    {
        return array_merge([
            'money' => fn (int|float|null $cents, ?string $currency = 'eur') => $this->money($cents, $currency),
            'percent' => fn (float|int|null $value) => number_format(((float) $value) * 100, 1, ',', ' ') . ' %',
            'bookedPercent' => fn (int|float|null $value, int|float|null $baseline) => $this->bookedPercent($value, $baseline),
            'customerCount' => fn (float|int|null $value) => $this->customerCount($value),
            'boardColumns' => self::BOARD_COLUMNS,
        ], $data);
    }

    private function overviewMetrics(): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $feeRate = $this->averageFeeRate();
        $forecastAssumptions = $this->forecastAssumptionMap();
        $licenseMix = $this->paidLicenseMix();
        $remainingMonthForecast = $this->forecastForPeriod(now(), $monthEnd, $feeRate, $forecastAssumptions, $licenseMix);
        $forecast30 = $this->forecastForPeriod(now(), now()->addDays(30), $feeRate, $forecastAssumptions, $licenseMix);
        $forecast60 = $this->forecastForPeriod(now(), now()->addDays(60), $feeRate, $forecastAssumptions, $licenseMix);
        $forecast90 = $this->forecastForPeriod(now(), now()->addDays(90), $feeRate, $forecastAssumptions, $licenseMix);
        $activeSubscriptions = StripeFinanceSubscription::query()->revenueActive()->get();
        $monthPaidInvoices = StripeFinanceInvoice::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->get();
        $monthTransactions = StripeFinanceBalanceTransaction::query()
            ->whereBetween('stripe_created_at', [$monthStart, $monthEnd])
            ->get();
        $monthPayouts = StripeFinancePayout::query()
            ->where('status', 'paid')
            ->whereBetween('arrival_date', [$monthStart, $monthEnd])
            ->get();
        $bookedInvoices = StripeFinanceInvoice::query()
            ->whereBetween('stripe_created_at', [$monthStart, $monthEnd])
            ->get();

        return [
            'mrr_cents' => $activeSubscriptions->sum(fn (StripeFinanceSubscription $subscription) => $subscription->mrr_cents),
            'arr_cents' => $activeSubscriptions->sum(fn (StripeFinanceSubscription $subscription) => $subscription->arr_cents),
            'booked_revenue_month_cents' => $bookedInvoices->sum('total_cents'),
            'remaining_cash_month_cents' => $remainingMonthForecast['expected_gross_cents'],
            'actual_collected_month_cents' => $monthPaidInvoices->sum('amount_paid_cents'),
            'estimated_month_end_cash_cents' => $monthPaidInvoices->sum('amount_paid_cents') + $remainingMonthForecast['expected_gross_cents'],
            'stripe_fees_month_cents' => $monthTransactions->sum('fee_cents'),
            'net_payout_month_cents' => $monthPayouts->sum('amount_cents'),
            'failed_payments' => $this->failedInvoicesQuery()->count(),
            'trials_ending' => StripeFinanceSubscription::query()
                ->where('status', 'trialing')
                ->whereBetween('trial_end', [now(), now()->addDays(14)])
                ->count(),
            'forecast_30_cents' => $forecast30['expected_net_cents'],
            'forecast_60_cents' => $forecast60['expected_net_cents'],
            'forecast_90_cents' => $forecast90['expected_net_cents'],
        ];
    }

    private function filteredSubscriptionQuery(array $filters): Builder
    {
        $query = StripeFinanceSubscription::query();

        if (!empty($filters['q'])) {
            $needle = trim((string) $filters['q']);
            $query->where(function (Builder $inner) use ($needle) {
                $inner->where('stripe_subscription_id', 'like', "%{$needle}%")
                    ->orWhere('product_name', 'like', "%{$needle}%")
                    ->orWhere('price_nickname', 'like', "%{$needle}%")
                    ->orWhere('license_label', 'like', "%{$needle}%")
                    ->orWhereHas('customer', function (Builder $customer) use ($needle) {
                        $customer->where('name', 'like', "%{$needle}%")
                            ->orWhere('email', 'like', "%{$needle}%")
                            ->orWhere('stripe_customer_id', 'like', "%{$needle}%");
                    });
            });
        }

        if (!empty($filters['term'])) {
            $query->where('interval', $filters['term']);
        }

        return $query;
    }

    private function applyBoardColumn(Builder $query, string $key): Builder
    {
        return match ($key) {
            'active' => $query->where('status', 'active')->where('cancel_at_period_end', false),
            'trialing' => $query->where('status', 'trialing'),
            'past_due' => $query->whereIn('status', ['past_due', 'unpaid']),
            'payment_failed' => $query->where(function (Builder $inner) {
                $inner->whereIn('status', ['past_due', 'unpaid', 'incomplete'])
                    ->orWhereHas('invoices', fn (Builder $invoice) => $this->failedInvoiceConditions($invoice));
            }),
            'canceling' => $query->where('cancel_at_period_end', true),
            'canceled' => $query->whereIn('status', ['canceled', 'incomplete_expired']),
            'annual' => $query->whereIn('status', ['active', 'trialing'])->where('interval', 'year'),
            'monthly' => $query->whereIn('status', ['active', 'trialing'])->where('interval', 'month'),
            default => $query,
        };
    }

    private function failedInvoicesQuery(): Builder
    {
        return $this->failedInvoiceConditions(StripeFinanceInvoice::query());
    }

    private function failedInvoiceConditions(Builder $query): Builder
    {
        return $query->where('attempted', true)
            ->where('amount_remaining_cents', '>', 0)
            ->whereIn('status', ['open', 'uncollectible']);
    }

    private function chartRange(Request $request): array
    {
        $from = $request->date('from') ?: now()->startOfMonth()->subMonths(5);
        $to = $request->date('to') ?: now()->endOfMonth();

        return [$from->copy()->startOfDay(), $to->copy()->endOfDay()];
    }

    private function monthlyFinancialSeries(Carbon $start, Carbon $end): array
    {
        $months = $this->monthSkeleton($start, $end);
        $transactions = StripeFinanceBalanceTransaction::query()
            ->whereBetween('stripe_created_at', [$start, $end])
            ->get();
        $paidInvoices = StripeFinanceInvoice::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->get();

        foreach ($transactions as $transaction) {
            $key = $transaction->stripe_created_at?->format('Y-m');
            if (!$key || !isset($months[$key])) {
                continue;
            }

            if ($this->isDisputeTransaction($transaction)) {
                $months[$key]['disputes_cents'] += abs($transaction->amount_cents);
            } elseif ($this->isRefundTransaction($transaction)) {
                $months[$key]['refunds_cents'] += abs($transaction->amount_cents);
            } elseif ($transaction->amount_cents > 0 && !$this->isPayoutTransaction($transaction)) {
                $months[$key]['gross_cents'] += $transaction->amount_cents;
            }

            if (!$this->isPayoutTransaction($transaction)) {
                $months[$key]['fees_cents'] += $transaction->fee_cents;
                $months[$key]['net_cents'] += $transaction->net_cents;
            }
        }

        foreach ($paidInvoices as $invoice) {
            $key = $invoice->paid_at?->format('Y-m');
            if ($key && isset($months[$key]) && $months[$key]['gross_cents'] === 0) {
                $months[$key]['gross_cents'] += $invoice->amount_paid_cents;
                $months[$key]['net_cents'] += $invoice->amount_paid_cents;
            }
        }

        $max = max(1, collect($months)->max(fn ($row) => $row['gross_cents'] + $row['net_cents']));

        return collect($months)
            ->map(function (array $row) use ($max) {
                $row['bar_percent'] = min(100, (int) round((max($row['gross_cents'], $row['net_cents']) / $max) * 100));
                return $row;
            })
            ->values()
            ->all();
    }

    private function monthlyPayoutSeries(Carbon $start, Carbon $end): array
    {
        $months = $this->monthSkeleton($start, $end);
        $payouts = StripeFinancePayout::query()
            ->whereBetween('arrival_date', [$start, $end])
            ->get();
        $transactions = StripeFinanceBalanceTransaction::query()
            ->whereBetween('stripe_created_at', [$start, $end])
            ->get();

        foreach ($payouts as $payout) {
            $key = $payout->arrival_date?->format('Y-m');
            if ($key && isset($months[$key])) {
                $months[$key]['payout_cents'] += $payout->amount_cents;
                $months[$key]['payout_count']++;
            }
        }

        foreach ($transactions as $transaction) {
            $key = $transaction->stripe_created_at?->format('Y-m');
            if (!$key || !isset($months[$key]) || $this->isPayoutTransaction($transaction)) {
                continue;
            }

            if ($this->isDisputeTransaction($transaction)) {
                $months[$key]['disputes_cents'] += abs($transaction->amount_cents);
            } elseif ($this->isRefundTransaction($transaction)) {
                $months[$key]['refunds_cents'] += abs($transaction->amount_cents);
            } elseif ($transaction->amount_cents > 0) {
                $months[$key]['gross_cents'] += $transaction->amount_cents;
            }

            $months[$key]['fees_cents'] += $transaction->fee_cents;
            $months[$key]['net_cents'] += $transaction->net_cents;
        }

        return array_values($months);
    }

    private function monthSkeleton(Carbon $start, Carbon $end): array
    {
        $cursor = $start->copy()->startOfMonth();
        $last = $end->copy()->startOfMonth();
        $months = [];

        while ($cursor <= $last) {
            $months[$cursor->format('Y-m')] = [
                'key' => $cursor->format('Y-m'),
                'label' => $cursor->translatedFormat('M Y'),
                'gross_cents' => 0,
                'fees_cents' => 0,
                'refunds_cents' => 0,
                'disputes_cents' => 0,
                'net_cents' => 0,
                'payout_cents' => 0,
                'payout_count' => 0,
            ];
            $cursor->addMonth();
        }

        return $months;
    }

    private function forecastForPeriod(
        Carbon $start,
        Carbon $end,
        float $feeRate,
        ?Collection $forecastAssumptions = null,
        ?array $licenseMix = null
    ): array
    {
        $forecastAssumptions ??= $this->forecastAssumptionMap();
        $licenseMix ??= $this->paidLicenseMix();

        $previewQuery = $this->upcomingPreviewQuery($start, $end);
        $previewAmount = (int) (clone $previewQuery)->sum('amount_due_cents');
        $annualPreviewAmount = (int) (clone $previewQuery)
            ->whereHas('subscription', fn (Builder $query) => $query->where('interval', 'year'))
            ->sum('amount_due_cents');
        $previewSubscriptionIds = (clone $previewQuery)
            ->pluck('stripe_finance_subscription_id')
            ->filter()
            ->all();

        $renewalProjection = $this->projectedRenewalBreakdown($start, $end, $previewSubscriptionIds);
        $renewalAmount = $renewalProjection['total_cents'];
        $baseGross = $previewAmount + $renewalAmount;

        $trialPotential = (int) StripeFinanceSubscription::query()
            ->where('status', 'trialing')
            ->whereBetween('trial_end', [$start, $end])
            ->sum('amount_cents');
        $pastDueRisk = (int) $this->failedInvoicesForPeriod($start, $end)
            ->sum('amount_remaining_cents');
        $cancelLoss = (int) StripeFinanceSubscription::query()
            ->where('cancel_at_period_end', true)
            ->whereBetween('current_period_end', [$start, $end])
            ->sum('amount_cents');
        $openInvoiceRecovery = (int) $this->failedInvoicesForPeriod($start, $end)
            ->sum('amount_remaining_cents');
        $newBusiness = $this->newBusinessProjectionForPeriod($start, $end, $forecastAssumptions, $licenseMix);
        $annualExistingCents = $annualPreviewAmount + $renewalProjection['annual_cents'];

        $conservativeGross = max(0, (int) round($baseGross + $newBusiness['conservative_cents'] + ($trialPotential * 0.15) + ($openInvoiceRecovery * 0.2)));
        $expectedGross = max(0, (int) round($baseGross + $newBusiness['expected_cents'] + ($trialPotential * 0.5) + ($openInvoiceRecovery * 0.35)));
        $optimisticGross = max(0, (int) round($baseGross + $newBusiness['optimistic_cents'] + ($trialPotential * 0.85) + ($openInvoiceRecovery * 0.65)));

        return [
            'start' => $start,
            'end' => $end,
            'base_gross_cents' => $baseGross,
            'preview_cents' => $previewAmount,
            'renewal_cents' => $renewalAmount,
            'annual_existing_cents' => $annualExistingCents,
            'annual_conservative_cents' => $annualExistingCents + $newBusiness['annual_conservative_cents'],
            'annual_expected_cents' => $annualExistingCents + $newBusiness['annual_expected_cents'],
            'annual_optimistic_cents' => $annualExistingCents + $newBusiness['annual_optimistic_cents'],
            'trial_potential_cents' => $trialPotential,
            'open_invoice_recovery_cents' => $openInvoiceRecovery,
            'past_due_risk_cents' => $pastDueRisk,
            'cancel_loss_cents' => $cancelLoss,
            'new_business_conservative_cents' => $newBusiness['conservative_cents'],
            'new_business_expected_cents' => $newBusiness['expected_cents'],
            'new_business_optimistic_cents' => $newBusiness['optimistic_cents'],
            'new_customers_conservative' => $newBusiness['conservative_customers'],
            'new_customers_expected' => $newBusiness['expected_customers'],
            'new_customers_optimistic' => $newBusiness['optimistic_customers'],
            'conservative_gross_cents' => $conservativeGross,
            'expected_gross_cents' => $expectedGross,
            'optimistic_gross_cents' => $optimisticGross,
            'conservative_net_cents' => $this->netAfterFees($conservativeGross, $feeRate),
            'expected_net_cents' => $this->netAfterFees($expectedGross, $feeRate),
            'optimistic_net_cents' => $this->netAfterFees($optimisticGross, $feeRate),
        ];
    }

    private function upcomingPreviewQuery(Carbon $start, Carbon $end): Builder
    {
        return StripeFinanceUpcomingInvoice::query()
            ->where(function (Builder $query) use ($start, $end) {
                $query->whereBetween('next_payment_attempt', [$start, $end])
                    ->orWhere(function (Builder $inner) use ($start, $end) {
                        $inner->whereNull('next_payment_attempt')
                            ->whereBetween('due_date', [$start, $end]);
                    })
                    ->orWhere(function (Builder $inner) use ($start, $end) {
                        $inner->whereNull('next_payment_attempt')
                            ->whereNull('due_date')
                            ->whereBetween('period_start', [$start, $end]);
                    })
                    ->orWhere(function (Builder $inner) use ($start, $end) {
                        $inner->whereNull('next_payment_attempt')
                            ->whereNull('due_date')
                            ->whereNull('period_start')
                            ->whereBetween('period_end', [$start, $end]);
                    });
            });
    }

    private function failedInvoicesForPeriod(Carbon $start, Carbon $end): Builder
    {
        return $this->failedInvoicesQuery()
            ->where(function (Builder $query) use ($start, $end) {
                $query->whereBetween('next_payment_attempt', [$start, $end])
                    ->orWhere(function (Builder $inner) use ($start, $end) {
                        $inner->whereNull('next_payment_attempt')
                            ->whereBetween('due_date', [$start, $end]);
                    })
                    ->orWhere(function (Builder $inner) use ($start, $end) {
                        $inner->whereNull('next_payment_attempt')
                            ->whereNull('due_date')
                            ->whereBetween('stripe_created_at', [$start, $end]);
                    });
            });
    }

    private function projectedRenewalBreakdown(Carbon $start, Carbon $end, array $previewSubscriptionIds): array
    {
        $previewSubscriptionIds = array_flip(array_map('intval', $previewSubscriptionIds));
        $subscriptions = StripeFinanceSubscription::query()
            ->where('status', 'active')
            ->where('cancel_at_period_end', false)
            ->where('amount_cents', '>', 0)
            ->whereNotNull('current_period_end')
            ->get();
        $total = 0;
        $annual = 0;

        foreach ($subscriptions as $subscription) {
            $date = $subscription->current_period_end?->copy();
            if (!$date) {
                continue;
            }

            while ($date->lt($start)) {
                $date = $this->advanceBillingDate($date, $subscription->interval, $subscription->interval_count);
            }

            $skipFirstKnownPreview = isset($previewSubscriptionIds[(int) $subscription->id]);

            while ($date->lte($end)) {
                if ($skipFirstKnownPreview) {
                    $skipFirstKnownPreview = false;
                } else {
                    $amountCents = (int) $subscription->amount_cents;
                    $total += $amountCents;

                    if ($subscription->interval === 'year') {
                        $annual += $amountCents;
                    }
                }

                $date = $this->advanceBillingDate($date, $subscription->interval, $subscription->interval_count);
            }
        }

        return [
            'total_cents' => $total,
            'annual_cents' => $annual,
        ];
    }

    private function advanceBillingDate(Carbon $date, ?string $interval, ?int $intervalCount): Carbon
    {
        $count = max(1, (int) $intervalCount);
        $next = $date->copy();

        return match ($interval) {
            'day' => $next->addDays($count),
            'week' => $next->addWeeks($count),
            'year' => $next->addYearsNoOverflow($count),
            default => $next->addMonthsNoOverflow($count),
        };
    }

    private function forecastMonths(int $months = 13): Collection
    {
        return collect(range(0, $months - 1))->map(function (int $offset) {
            $start = now()->startOfMonth()->addMonths($offset);

            return [
                'key' => $start->format('Y-m'),
                'label' => $start->translatedFormat('M Y'),
                'start' => $start->copy(),
                'end' => $start->copy()->endOfMonth(),
            ];
        });
    }

    private function currentBookedRevenueCents(): int
    {
        return (int) StripeFinanceInvoice::query()
            ->whereBetween('stripe_created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_cents');
    }

    private function forecastAssumptionMap(?Collection $months = null): Collection
    {
        $months ??= $this->forecastMonths();
        $monthDates = $months->map(fn (array $month) => $month['start']->toDateString())->all();
        $rows = StripeFinanceForecastAssumption::query()
            ->where(function (Builder $query) use ($monthDates) {
                foreach ($monthDates as $date) {
                    $query->orWhereDate('month', $date);
                }
            })
            ->get()
            ->keyBy(fn (StripeFinanceForecastAssumption $assumption) => $assumption->month?->format('Y-m'));

        return $months->mapWithKeys(function (array $month) use ($rows) {
            $row = $rows->get($month['key']);
            $conservative = (int) ($row?->conservative_new_customers ?? 0);
            $optimistic = (int) ($row?->optimistic_new_customers ?? 0);

            return [
                $month['key'] => [
                    'key' => $month['key'],
                    'label' => $month['label'],
                    'start' => $month['start']->copy(),
                    'end' => $month['end']->copy(),
                    'conservative_new_customers' => $conservative,
                    'expected_new_customers' => ($conservative + $optimistic) / 2,
                    'optimistic_new_customers' => $optimistic,
                ],
            ];
        });
    }

    private function paidLicenseMix(): array
    {
        $subscriptions = StripeFinanceSubscription::query()
            ->where('status', 'active')
            ->where('cancel_at_period_end', false)
            ->where('amount_cents', '>', 0)
            ->get();
        $total = $subscriptions->count();

        if ($total === 0) {
            return [
                'average_amount_cents' => 0,
                'currency' => 'eur',
                'items' => collect(),
                'total_customers' => 0,
            ];
        }

        $items = $subscriptions
            ->groupBy(fn (StripeFinanceSubscription $subscription) => implode('|', [
                $subscription->license_display,
                $subscription->interval ?: 'month',
                (string) $subscription->interval_count,
                (string) $subscription->amount_cents,
                $subscription->currency ?: 'eur',
            ]))
            ->map(function (Collection $group) use ($total) {
                /** @var StripeFinanceSubscription $first */
                $first = $group->first();

                return [
                    'label' => $first->license_display,
                    'term' => $first->interval_label,
                    'interval' => $first->interval ?: 'month',
                    'interval_count' => max(1, (int) $first->interval_count),
                    'amount_cents' => (int) $first->amount_cents,
                    'currency' => $first->currency ?: 'eur',
                    'count' => $group->count(),
                    'share' => $group->count() / $total,
                ];
            })
            ->sortByDesc('count')
            ->values();

        return [
            'average_amount_cents' => (int) round($subscriptions->sum('amount_cents') / $total),
            'currency' => $subscriptions->first()?->currency ?: 'eur',
            'items' => $items,
            'total_customers' => $total,
        ];
    }

    private function newBusinessProjectionForPeriod(Carbon $start, Carbon $end, Collection $forecastAssumptions, array $licenseMix): array
    {
        $items = $licenseMix['items'] ?? collect();
        $conservativeCustomers = 0.0;
        $optimisticCustomers = 0.0;
        $conservativeCents = 0.0;
        $optimisticCents = 0.0;
        $annualConservativeCents = 0.0;
        $annualOptimisticCents = 0.0;

        if (! $items instanceof Collection || $items->isEmpty()) {
            return $this->emptyNewBusinessProjection();
        }

        foreach ($forecastAssumptions as $assumption) {
            foreach ($items as $item) {
                $paymentWeight = $this->projectedCohortPaymentWeight(
                    $assumption['start'],
                    $item['interval'] ?? 'month',
                    (int) ($item['interval_count'] ?? 1),
                    $start,
                    $end
                );

                if ($paymentWeight <= 0) {
                    continue;
                }

                $share = (float) ($item['share'] ?? 0);
                $amountCents = (int) ($item['amount_cents'] ?? 0);
                $conservativeCohortCustomers = ((float) $assumption['conservative_new_customers']) * $share * $paymentWeight;
                $optimisticCohortCustomers = ((float) $assumption['optimistic_new_customers']) * $share * $paymentWeight;

                $conservativeCustomers += $conservativeCohortCustomers;
                $optimisticCustomers += $optimisticCohortCustomers;
                $conservativeCents += $conservativeCohortCustomers * $amountCents;
                $optimisticCents += $optimisticCohortCustomers * $amountCents;

                if (($item['interval'] ?? 'month') === 'year') {
                    $annualConservativeCents += $conservativeCohortCustomers * $amountCents;
                    $annualOptimisticCents += $optimisticCohortCustomers * $amountCents;
                }
            }
        }

        $expectedCustomers = ($conservativeCustomers + $optimisticCustomers) / 2;
        $expectedCents = ($conservativeCents + $optimisticCents) / 2;
        $annualExpectedCents = ($annualConservativeCents + $annualOptimisticCents) / 2;

        return [
            'conservative_customers' => $conservativeCustomers,
            'expected_customers' => $expectedCustomers,
            'optimistic_customers' => $optimisticCustomers,
            'conservative_cents' => (int) round($conservativeCents),
            'expected_cents' => (int) round($expectedCents),
            'optimistic_cents' => (int) round($optimisticCents),
            'annual_conservative_cents' => (int) round($annualConservativeCents),
            'annual_expected_cents' => (int) round($annualExpectedCents),
            'annual_optimistic_cents' => (int) round($annualOptimisticCents),
        ];
    }

    private function projectedCohortPaymentWeight(
        Carbon $cohortStart,
        ?string $interval,
        ?int $intervalCount,
        Carbon $periodStart,
        Carbon $periodEnd
    ): float {
        $paymentDate = $cohortStart->copy();
        $weight = 0.0;
        $iterations = 0;

        while ($paymentDate->lte($periodEnd) && $iterations < 120) {
            if ($iterations === 0) {
                $weight += $this->monthOverlapFactor($cohortStart, $cohortStart->copy()->endOfMonth(), $periodStart, $periodEnd);
            } elseif ($paymentDate->betweenIncluded($periodStart, $periodEnd)) {
                $weight += 1.0;
            }

            $paymentDate = $this->advanceBillingDate($paymentDate, $interval, $intervalCount);
            $iterations++;
        }

        return $weight;
    }

    private function emptyNewBusinessProjection(): array
    {
        return [
            'conservative_customers' => 0.0,
            'expected_customers' => 0.0,
            'optimistic_customers' => 0.0,
            'conservative_cents' => 0,
            'expected_cents' => 0,
            'optimistic_cents' => 0,
            'annual_conservative_cents' => 0,
            'annual_expected_cents' => 0,
            'annual_optimistic_cents' => 0,
        ];
    }

    private function monthOverlapFactor(Carbon $monthStart, Carbon $monthEnd, Carbon $periodStart, Carbon $periodEnd): float
    {
        $overlapStart = $periodStart->gt($monthStart) ? $periodStart->copy() : $monthStart->copy();
        $overlapEnd = $periodEnd->lt($monthEnd) ? $periodEnd->copy() : $monthEnd->copy();

        if ($overlapEnd->lt($overlapStart)) {
            return 0.0;
        }

        $overlapDays = $overlapStart->copy()->startOfDay()->diffInDays($overlapEnd->copy()->endOfDay()) + 1;

        return min(1.0, max(0.0, $overlapDays / max(1, $monthStart->daysInMonth)));
    }

    private function averageFeeRate(): float
    {
        $transactions = StripeFinanceBalanceTransaction::query()
            ->where('amount_cents', '>', 0)
            ->where('fee_cents', '>', 0)
            ->where('stripe_created_at', '>=', now()->subDays(120))
            ->whereNotIn('type', ['payout'])
            ->get(['amount_cents', 'fee_cents']);

        $gross = $transactions->sum('amount_cents');
        if ($gross <= 0) {
            return 0.018;
        }

        return min(0.08, max(0.0, $transactions->sum('fee_cents') / $gross));
    }

    private function netAfterFees(int $grossCents, float $feeRate): int
    {
        return max(0, (int) round($grossCents * (1 - $feeRate)));
    }

    private function isRefundTransaction(StripeFinanceBalanceTransaction $transaction): bool
    {
        return str_contains((string) $transaction->type, 'refund')
            || str_contains((string) $transaction->reporting_category, 'refund')
            || $transaction->amount_cents < 0 && ! $this->isPayoutTransaction($transaction);
    }

    private function isPayoutTransaction(StripeFinanceBalanceTransaction $transaction): bool
    {
        return $transaction->type === 'payout' || $transaction->reporting_category === 'payout';
    }

    private function isDisputeTransaction(StripeFinanceBalanceTransaction $transaction): bool
    {
        return str_contains((string) $transaction->type, 'dispute')
            || str_contains((string) $transaction->reporting_category, 'dispute');
    }

    private function money(int|float|null $cents, ?string $currency = 'eur'): string
    {
        $amount = ((float) ($cents ?? 0)) / 100;
        $suffix = strtoupper((string) ($currency ?: 'eur'));

        if ($suffix === 'EUR') {
            return number_format($amount, 2, ',', ' ') . ' €';
        }

        return number_format($amount, 2, ',', ' ') . ' ' . $suffix;
    }

    private function customerCount(float|int|null $value): string
    {
        $number = (float) ($value ?? 0);
        $decimals = abs($number - round($number)) < 0.05 ? 0 : 1;

        return number_format($number, $decimals, ',', ' ');
    }

    private function bookedPercent(int|float|null $value, int|float|null $baseline): string
    {
        $target = (float) ($value ?? 0);
        if ($target <= 0) {
            return '-';
        }

        return number_format((((float) ($baseline ?? 0)) / $target) * 100, 0, ',', ' ') . ' %';
    }
}

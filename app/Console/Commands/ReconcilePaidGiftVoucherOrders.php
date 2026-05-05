<?php

namespace App\Console\Commands;

use App\Models\GiftVoucherOrder;
use App\Services\GiftVoucherCheckoutService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class ReconcilePaidGiftVoucherOrders extends Command
{
    protected $signature = 'gift-vouchers:reconcile-paid-stripe-orders
        {--since= : Only inspect orders created after this date/time}
        {--dry-run : Show what would be finalized without creating vouchers}';

    protected $description = 'Finalize pending gift voucher orders that are already paid in Stripe.';

    public function handle(GiftVoucherCheckoutService $checkoutService): int
    {
        $stripeSecret = (string) config('services.stripe.secret');
        if ($stripeSecret === '') {
            $this->error('Missing Stripe secret key.');
            return self::FAILURE;
        }

        $since = $this->option('since')
            ? Carbon::parse((string) $this->option('since'))
            : null;
        $dryRun = (bool) $this->option('dry-run');
        $stripe = new StripeClient($stripeSecret);

        $query = GiftVoucherOrder::query()
            ->with('therapist')
            ->where('status', 'pending')
            ->whereNotNull('stripe_session_id');

        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        $stats = [
            'checked' => 0,
            'finalized' => 0,
            'paid_dry_run' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        $query->orderBy('id')->chunkById(50, function ($orders) use ($stripe, $checkoutService, $dryRun, &$stats) {
            foreach ($orders as $order) {
                $stats['checked']++;

                $therapist = $order->therapist;
                if (!$therapist || !$therapist->stripe_account_id) {
                    $stats['skipped']++;
                    $this->warn("Order {$order->id}: skipped, missing therapist Stripe account.");
                    continue;
                }

                try {
                    $session = $stripe->checkout->sessions->retrieve(
                        (string) $order->stripe_session_id,
                        ['expand' => ['payment_intent']],
                        ['stripe_account' => $therapist->stripe_account_id]
                    );

                    if (($session->payment_status ?? null) !== 'paid') {
                        $stats['skipped']++;
                        $this->line("Order {$order->id}: Stripe status is " . ($session->payment_status ?? 'unknown') . '.');
                        continue;
                    }

                    $paymentIntentId = '';
                    if (!empty($session->payment_intent)) {
                        $paymentIntentId = is_object($session->payment_intent)
                            ? (string) ($session->payment_intent->id ?? '')
                            : (string) $session->payment_intent;
                    }

                    if ($dryRun) {
                        $stats['paid_dry_run']++;
                        $this->info("Order {$order->id}: paid in Stripe, would finalize.");
                        continue;
                    }

                    $voucher = $checkoutService->finalizePaidOrder(
                        $order,
                        (string) $session->id,
                        $paymentIntentId !== '' ? $paymentIntentId : null
                    );

                    $stats['finalized']++;
                    $this->info("Order {$order->id}: finalized with voucher {$voucher->id}.");
                } catch (\Throwable $e) {
                    $stats['errors']++;
                    Log::error('Gift voucher reconciliation failed', [
                        'order_id' => $order->id,
                        'stripe_session_id' => $order->stripe_session_id,
                        'error' => $e->getMessage(),
                    ]);

                    $this->error("Order {$order->id}: " . $e->getMessage());
                }
            }
        });

        $this->table(
            ['Checked', 'Finalized', 'Paid dry-run', 'Skipped', 'Errors'],
            [[$stats['checked'], $stats['finalized'], $stats['paid_dry_run'], $stats['skipped'], $stats['errors']]]
        );

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}

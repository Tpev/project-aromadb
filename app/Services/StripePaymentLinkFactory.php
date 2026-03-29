<?php

namespace App\Services;

use App\Models\Invoice;
use Stripe\StripeClient;

class StripePaymentLinkFactory
{
    public function createInvoicePaymentLink(
        Invoice $invoice,
        int $amountCents,
        string $label,
        string $redirectUrl,
        string $stripeAccountId
    ): string {
        $stripe = new StripeClient(config('services.stripe.secret'));

        $paymentLink = $stripe->paymentLinks->create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $amountCents,
                    'product_data' => [
                        'name' => $label,
                        'metadata' => [
                            'invoice_id' => (string) $invoice->id,
                            'user_id' => (string) $invoice->user_id,
                        ],
                    ],
                ],
                'quantity' => 1,
            ]],
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => ['url' => $redirectUrl],
            ],
        ], [
            'stripe_account' => $stripeAccountId,
        ]);

        return $paymentLink->url;
    }
}

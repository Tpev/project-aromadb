<?php

return [
    // Stripe Checkout is open for 30 minutes. The short grace period lets a
    // webhook already in transit complete before the slot is released.
    'pending_payment_expiry_minutes' => (int) env('APPOINTMENT_PENDING_PAYMENT_EXPIRY_MINUTES', 35),

    // These values document the active policy; no automatic financial mutation is performed.
    'cancellation' => [
        'paid_appointment' => 'manual_financial_review',
        'pack_credit' => 'preserve_consumed_credit',
        'consumed_gift_voucher' => 'preserve_consumed_amount',
        'temporary_gift_voucher_reservation' => 'release',
    ],
];

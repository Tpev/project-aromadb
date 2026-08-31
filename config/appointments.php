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

    'earlier_slots' => [
        // Disabled by default so production can migrate first, then enable deliberately.
        'enabled' => filter_var(env('APPOINTMENT_EARLIER_SLOT_ENABLED', false), FILTER_VALIDATE_BOOL),
        'retention_days' => max(30, (int) env('APPOINTMENT_EARLIER_SLOT_RETENTION_DAYS', 90)),
    ],

    'booking_v2' => [
        // Global launch switch. Keep disabled until migrations and availability
        // location backfills have been completed in the target environment.
        'enabled' => filter_var(env('BOOKING_V2_ENABLED', false), FILTER_VALIDATE_BOOL),
    ],
];

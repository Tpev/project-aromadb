<?php

use App\Support\InstallmentPlan;

test('sanitizeAllowed keeps only unique values between 2 and 12', function () {
    $allowed = InstallmentPlan::sanitizeAllowed([1, 2, '2', 3, 12, 13, 0, 'x']);

    expect($allowed)->toBe([2, 3, 12]);
});

test('build splits remainder on first installment', function () {
    $plan = InstallmentPlan::build(1000, 3);

    expect($plan)->not->toBeNull();
    expect($plan['count'])->toBe(3);
    expect($plan['base_cents'])->toBe(333);
    expect($plan['first_cents'])->toBe(334);
    expect($plan['remainder_cents'])->toBe(1);
});

test('build returns null when amount is below Stripe minimum per installment', function () {
    $plan = InstallmentPlan::build(100, 12);

    expect($plan)->toBeNull();
});

test('plansForAllowed only returns viable installment options', function () {
    $plans = InstallmentPlan::plansForAllowed(500, [2, 3, 12, 20]);

    expect(array_keys($plans))->toBe([2, 3]);
    expect($plans[2]['base_cents'])->toBe(250);
    expect($plans[3]['base_cents'])->toBe(166);
});

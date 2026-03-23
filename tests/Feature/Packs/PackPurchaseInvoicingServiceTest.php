<?php

use App\Models\ClientProfile;
use App\Models\DigitalTraining;
use App\Models\PackProduct;
use App\Models\PackPurchase;
use App\Models\Receipt;
use App\Models\User;
use App\Services\PackPurchaseInvoicingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createPackPurchaseForInvoicing(array $purchaseOverrides = []): PackPurchase
{
    $therapist = User::factory()->create([
        'is_therapist' => true,
    ]);

    $client = ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => 'Client',
        'last_name' => 'Pack',
        'email' => 'client-pack-' . uniqid() . '@example.test',
    ]);

    $pack = PackProduct::create([
        'user_id' => $therapist->id,
        'name' => 'Pack suivi',
        'description' => 'Description',
        'price' => 100.00,
        'tax_rate' => 0.00,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    return PackPurchase::create(array_merge([
        'user_id' => $therapist->id,
        'pack_product_id' => $pack->id,
        'client_profile_id' => $client->id,
        'status' => 'active',
        'payment_mode' => 'installments',
        'payment_state' => 'active',
        'installments_total' => 2,
        'installments_paid' => 0,
        'installment_amount_cents' => 5000,
    ], $purchaseOverrides));
}

test('service creates one invoice and updates status with installment receipts', function () {
    $purchase = createPackPurchaseForInvoicing();
    $service = app(PackPurchaseInvoicingService::class);

    $service->registerInstallmentPayment($purchase, 4000, 'in_1', 1, 2, now());
    $purchase->refresh();
    $invoice = $purchase->invoice()->first();

    expect($invoice)->not->toBeNull();
    expect((float) $invoice->total_amount_with_tax)->toBe(100.0);
    expect($invoice->status)->toBe('Partiellement payée');
    expect(Receipt::where('invoice_id', $invoice->id)->count())->toBe(1);

    $service->registerInstallmentPayment($purchase, 6000, 'in_2', 2, 2, now());
    $invoice->refresh();

    expect($invoice->status)->toBe('Payée');
    expect((float) $invoice->solde_restant)->toBe(0.0);
    expect(Receipt::where('invoice_id', $invoice->id)->count())->toBe(2);
});

test('service creates invoice for training purchase snapshot', function () {
    $therapist = User::factory()->create(['is_therapist' => true]);
    $client = ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => 'Client',
        'last_name' => 'Training',
        'email' => 'client-training-' . uniqid() . '@example.test',
    ]);

    $training = DigitalTraining::create([
        'user_id' => $therapist->id,
        'title' => 'Formation avancée',
        'slug' => 'formation-' . uniqid(),
        'description' => 'Desc',
        'is_free' => false,
        'price_cents' => 2990,
        'tax_rate' => 20,
        'access_type' => 'public',
        'status' => 'published',
    ]);

    $dummyPack = PackProduct::create([
        'user_id' => $therapist->id,
        'name' => 'Pack fallback',
        'description' => 'Fallback',
        'price' => 1.00,
        'tax_rate' => 0.00,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    $purchase = PackPurchase::create([
        'user_id' => $therapist->id,
        'pack_product_id' => $dummyPack->id,
        'purchase_type' => 'training',
        'digital_training_id' => $training->id,
        'client_profile_id' => $client->id,
        'status' => 'active',
        'payment_mode' => 'installments',
        'payment_state' => 'active',
        'installments_total' => 3,
        'installments_paid' => 1,
        'installment_amount_cents' => 996,
    ]);

    $service = app(PackPurchaseInvoicingService::class);
    $invoice = $service->ensureInvoiceForPurchase($purchase);

    expect($invoice)->not->toBeNull();
    expect($invoice->pack_purchase_id)->toBe($purchase->id);
    expect((float) $invoice->total_amount_with_tax)->toBe(29.9);
    expect($invoice->items()->where('label', 'like', 'Formation :%')->exists())->toBeTrue();
});

test('service does not duplicate receipt for same stripe invoice id', function () {
    $purchase = createPackPurchaseForInvoicing();
    $service = app(PackPurchaseInvoicingService::class);

    $service->registerInstallmentPayment($purchase, 5000, 'in_same', 1, 2, now());
    $service->registerInstallmentPayment($purchase, 5000, 'in_same', 1, 2, now());

    $invoice = $purchase->fresh()->invoice;
    expect($invoice)->not->toBeNull();
    expect(Receipt::where('invoice_id', $invoice->id)->count())->toBe(1);
});

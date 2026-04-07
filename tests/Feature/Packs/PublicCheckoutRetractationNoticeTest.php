<?php

use App\Models\DigitalTraining;
use App\Models\PackProduct;
use App\Models\PackPurchase;
use App\Models\User;
use App\Services\StripeAccountGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function createTherapistWithRetractationConfig(array $userOverrides = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'slug' => 'retractation-' . uniqid(),
        'digital_sales_retractation_enabled' => true,
        'digital_sales_retractation_label' => 'Je confirme avoir lu les informations sur le droit de rétractation.',
        'digital_sales_retractation_document_path' => 'retractation-notices/test/retractation.pdf',
    ], $userOverrides));
}

function createPublishedTraining(User $therapist, array $overrides = []): DigitalTraining
{
    return DigitalTraining::create(array_merge([
        'user_id' => $therapist->id,
        'title' => 'Formation premium',
        'slug' => 'formation-premium-' . uniqid(),
        'description' => 'Formation test',
        'is_free' => false,
        'price_cents' => 4900,
        'tax_rate' => 0,
        'access_type' => 'public',
        'status' => 'published',
        'use_global_retractation_notice' => true,
    ], $overrides));
}

test('training checkout shows retractation notice when enabled on therapist and training', function () {
    $therapist = createTherapistWithRetractationConfig();
    $training = createPublishedTraining($therapist);

    $response = $this->get(route('public.checkout.show', [
        'slug' => $therapist->slug,
        'item' => 'training:' . $training->id,
    ]));

    $response->assertOk();
    $response->assertSee('Je confirme avoir lu les informations sur le droit de rétractation.');
    $response->assertSee('/storage/retractation-notices/test/retractation.pdf');
});

test('training checkout rejects purchase when retractation notice is required but unchecked', function () {
    $therapist = createTherapistWithRetractationConfig();
    $training = createPublishedTraining($therapist);

    $response = $this->post(route('public.checkout.store', ['slug' => $therapist->slug]), [
        'item' => 'training:' . $training->id,
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'email' => 'alice@example.test',
        'payment_choice' => 'one_time',
    ]);

    $response->assertSessionHasErrors('retractation_notice_acknowledged');
    expect(PackPurchase::count())->toBe(0);
});

test('training checkout stores retractation snapshot when acknowledged', function () {
    if (DB::getDriverName() === 'sqlite') {
        $this->markTestSkipped('Le schéma de test SQLite conserve pack_product_id non nullable pour les achats formation legacy.');
    }

    $therapist = createTherapistWithRetractationConfig();
    $training = createPublishedTraining($therapist);

    $this->mock(StripeAccountGuard::class, function ($mock) {
        $mock->shouldReceive('status')->once()->andReturn(['ready' => false]);
    });

    $response = $this->post(route('public.checkout.store', ['slug' => $therapist->slug]), [
        'item' => 'training:' . $training->id,
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'email' => 'alice@example.test',
        'payment_choice' => 'one_time',
        'retractation_notice_acknowledged' => '1',
    ]);

    $response->assertRedirect(route('therapist.show', $therapist->slug));

    $purchase = PackPurchase::query()->firstOrFail();
    expect($purchase->purchase_type)->toBe('training');
    expect($purchase->retractation_notice_required)->toBeTrue();
    expect($purchase->retractation_notice_accepted_at)->not->toBeNull();
    expect($purchase->retractation_notice_label_snapshot)->toBe('Je confirme avoir lu les informations sur le droit de rétractation.');
    expect($purchase->retractation_notice_document_path_snapshot)->toBe('retractation-notices/test/retractation.pdf');
    expect($purchase->retractation_notice_url_snapshot)->toBeNull();
});

test('pack checkout remains unchanged even if therapist has retractation config', function () {
    $therapist = createTherapistWithRetractationConfig();

    $pack = PackProduct::create([
        'user_id' => $therapist->id,
        'name' => 'Pack test',
        'description' => 'Pack',
        'price' => 120.00,
        'tax_rate' => 0,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    $this->mock(StripeAccountGuard::class, function ($mock) {
        $mock->shouldReceive('status')->once()->andReturn(['ready' => false]);
    });

    $response = $this->post(route('public.checkout.store', ['slug' => $therapist->slug]), [
        'item' => 'pack:' . $pack->id,
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'email' => 'alice@example.test',
        'payment_choice' => 'one_time',
    ]);

    $response->assertRedirect(route('therapist.show', $therapist->slug));

    $purchase = PackPurchase::query()->firstOrFail();
    expect($purchase->purchase_type ?? 'pack')->toBe('pack');
    expect((bool) $purchase->retractation_notice_required)->toBeFalse();
    expect($purchase->retractation_notice_accepted_at)->toBeNull();
});

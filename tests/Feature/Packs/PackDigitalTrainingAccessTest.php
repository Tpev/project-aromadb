<?php

use App\Mail\DigitalTrainingAccessMail;
use App\Models\ClientProfile;
use App\Models\DigitalTraining;
use App\Models\DigitalTrainingEnrollment;
use App\Models\PackProduct;
use App\Models\PackProductItem;
use App\Models\PackPurchase;
use App\Models\Product;
use App\Models\User;
use App\Services\PackDigitalTrainingAccessService;
use App\Services\StripeAccountGuard;
use App\Services\StripePurchaseWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function packDigitalFixture(?string $clientEmail = 'client.formation@example.test'): array
{
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $client = ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => 'Camille',
        'last_name' => 'Martin',
        'email' => $clientEmail,
    ]);
    $product = Product::create([
        'user_id' => $therapist->id,
        'name' => 'Séance pack digitale',
        'description' => 'Prestation incluse',
        'price' => 60,
        'tax_rate' => 0,
        'duration' => 60,
    ]);
    $pack = PackProduct::create([
        'user_id' => $therapist->id,
        'name' => 'Pack avec formation',
        'description' => 'Pack de test',
        'price' => 120,
        'tax_rate' => 0,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
        'installments_enabled' => false,
    ]);
    PackProductItem::create([
        'pack_product_id' => $pack->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'sort_order' => 0,
    ]);
    $training = DigitalTraining::create([
        'user_id' => $therapist->id,
        'title' => 'Formation incluse',
        'slug' => 'formation-incluse-'.str()->lower(str()->random(8)),
        'description' => 'Contenu digital du pack',
        'status' => 'published',
        'access_type' => 'private',
        'is_free' => false,
        'price_cents' => 5000,
        'estimated_duration_minutes' => 45,
    ]);
    $pack->digitalTrainings()->attach($training);

    return compact('therapist', 'client', 'product', 'pack', 'training');
}

test('manual pack assignment creates personal training access and sends its email once', function () {
    Mail::fake();
    $fixture = packDigitalFixture();

    $this->actingAs($fixture['therapist'])
        ->post(route('pack-products.assign', $fixture['pack']), [
            'client_profile_id' => $fixture['client']->id,
            'purchased_at' => now()->toDateString(),
        ])
        ->assertRedirect(route('pack-products.show', $fixture['pack']))
        ->assertSessionHas('success', fn (string $message) => str_contains($message, 'envoyé par email'));

    $purchase = PackPurchase::query()->where('pack_product_id', $fixture['pack']->id)->firstOrFail();
    $enrollment = DigitalTrainingEnrollment::query()->where('pack_purchase_id', $purchase->id)->firstOrFail();

    expect($enrollment->digital_training_id)->toBe($fixture['training']->id)
        ->and($enrollment->client_profile_id)->toBe($fixture['client']->id)
        ->and($enrollment->source)->toBe(DigitalTrainingEnrollment::SOURCE_PACK)
        ->and($enrollment->access_email_sent_at)->not->toBeNull();

    Mail::assertSent(DigitalTrainingAccessMail::class, 1);
    Mail::assertSent(DigitalTrainingAccessMail::class, fn (DigitalTrainingAccessMail $mail) => $mail->hasTo('client.formation@example.test')
        && $mail->enrollment->is($enrollment)
    );

    $this->get(route('digital-trainings.access.show', $enrollment->access_token))
        ->assertOk()
        ->assertSee('45 min');

    app(PackDigitalTrainingAccessService::class)->grant($purchase->fresh());

    expect(DigitalTrainingEnrollment::query()->where('pack_purchase_id', $purchase->id)->count())->toBe(1);
    Mail::assertSent(DigitalTrainingAccessMail::class, 1);
});

test('pack assignment without client email succeeds and can send access after email is added', function () {
    Mail::fake();
    $fixture = packDigitalFixture(null);

    $this->actingAs($fixture['therapist'])
        ->post(route('pack-products.assign', $fixture['pack']), [
            'client_profile_id' => $fixture['client']->id,
        ])
        ->assertRedirect(route('pack-products.show', $fixture['pack']))
        ->assertSessionHas('warning');

    $purchase = PackPurchase::query()->where('pack_product_id', $fixture['pack']->id)->firstOrFail();
    expect($purchase->digitalTrainingEnrollments)->toHaveCount(1)
        ->and($purchase->digitalTrainingEnrollments->first()->participant_email)->toBeNull();
    Mail::assertNothingSent();

    $fixture['client']->update(['email' => 'nouvelle.adresse@example.test']);

    $this->actingAs($fixture['therapist'])
        ->post(route('pack-purchases.digital-access.resend', $purchase))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($purchase->digitalTrainingEnrollments()->first()->participant_email)->toBe('nouvelle.adresse@example.test');
    Mail::assertSent(DigitalTrainingAccessMail::class, 1);
});

test('revoking a pack revokes only training access created by that purchase', function () {
    Mail::fake();
    $fixture = packDigitalFixture();

    $purchase = PackPurchase::create([
        'user_id' => $fixture['therapist']->id,
        'pack_product_id' => $fixture['pack']->id,
        'client_profile_id' => $fixture['client']->id,
        'purchased_at' => now(),
        'status' => 'active',
    ]);
    app(PackDigitalTrainingAccessService::class)->grant($purchase);
    $packEnrollment = $purchase->digitalTrainingEnrollments()->firstOrFail();
    $packEnrollment->update([
        'progress_percent' => 45,
        'first_accessed_at' => now()->subDay(),
    ]);

    $manualEnrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $fixture['training']->id,
        'client_profile_id' => $fixture['client']->id,
        'participant_email' => $fixture['client']->email,
        'access_token' => (string) str()->uuid(),
        'token_expires_at' => now()->addMonth(),
        'source' => DigitalTrainingEnrollment::SOURCE_MANUAL,
    ]);

    $this->actingAs($fixture['therapist'])
        ->delete(route('pack-purchases.revoke', $purchase))
        ->assertRedirect()
        ->assertSessionHas('success', fn (string $message) => str_contains($message, 'formations associées'));

    expect($purchase->fresh()->status)->toBe('cancelled')
        ->and($packEnrollment->fresh())->not->toBeNull()
        ->and($packEnrollment->fresh()->token_expires_at->isPast())->toBeTrue()
        ->and($packEnrollment->fresh()->progress_percent)->toBe(45)
        ->and($manualEnrollment->fresh())->not->toBeNull();

    $this->get(route('digital-trainings.access.show', $packEnrollment->access_token))
        ->assertOk()
        ->assertSee('expiré');
});

test('mobile pack assignment grants the same digital training access', function () {
    Mail::fake();
    $fixture = packDigitalFixture();

    $this->actingAs($fixture['therapist'])
        ->post(route('mobile.packs.assign', $fixture['pack']), [
            'client_profile_id' => $fixture['client']->id,
        ])
        ->assertRedirect(route('mobile.packs.show', $fixture['pack']))
        ->assertSessionHas('success', fn (string $message) => str_contains($message, 'envoyé'));

    expect(DigitalTrainingEnrollment::query()
        ->where('digital_training_id', $fixture['training']->id)
        ->whereNotNull('pack_purchase_id')
        ->exists())->toBeTrue();
    Mail::assertSent(DigitalTrainingAccessMail::class, 1);
});

test('a legacy mobile pack without training stays unchanged when the client has no email', function () {
    Mail::fake();
    $fixture = packDigitalFixture(null);
    $fixture['pack']->digitalTrainings()->detach();

    $this->actingAs($fixture['therapist'])
        ->post(route('mobile.packs.assign', $fixture['pack']), [
            'client_profile_id' => $fixture['client']->id,
        ])
        ->assertRedirect(route('mobile.packs.show', $fixture['pack']))
        ->assertSessionHas('success', 'Pack attribué au client.')
        ->assertSessionMissing('warning');

    $purchase = PackPurchase::query()->where('pack_product_id', $fixture['pack']->id)->firstOrFail();

    expect($purchase->digital_training_ids_snapshot)->toBe([])
        ->and($purchase->digitalTrainingEnrollments()->exists())->toBeFalse();
    Mail::assertNothingSent();
});

test('a therapist cannot attach another practitioners training to a pack', function () {
    $fixture = packDigitalFixture();
    $other = User::factory()->create(['is_therapist' => true]);
    $foreignTraining = DigitalTraining::create([
        'user_id' => $other->id,
        'title' => 'Formation étrangère',
        'slug' => 'formation-etrangere-'.str()->lower(str()->random(8)),
        'status' => 'published',
        'access_type' => 'private',
    ]);

    $this->actingAs($fixture['therapist'])
        ->put(route('pack-products.update', $fixture['pack']), [
            'name' => $fixture['pack']->name,
            'description' => $fixture['pack']->description,
            'price' => $fixture['pack']->price,
            'tax_rate' => 0,
            'is_active' => 1,
            'visible_in_portal' => 1,
            'price_visible_in_portal' => 1,
            'installments_enabled' => 0,
            'items' => [[
                'product_id' => $fixture['product']->id,
                'quantity' => 2,
            ]],
            'digital_training_ids' => [$foreignTraining->id],
        ])
        ->assertSessionHasErrors('digital_training_ids');

    expect($fixture['pack']->fresh()->digitalTrainings->pluck('id')->all())->toBe([$fixture['training']->id]);
});

test('later pack changes do not alter the training snapshot of an existing purchase', function () {
    Mail::fake();
    $fixture = packDigitalFixture();
    $purchase = PackPurchase::create([
        'user_id' => $fixture['therapist']->id,
        'pack_product_id' => $fixture['pack']->id,
        'client_profile_id' => $fixture['client']->id,
        'purchased_at' => now(),
        'status' => 'active',
        'payment_mode' => 'installments',
        'payment_state' => 'active',
    ]);

    app(PackDigitalTrainingAccessService::class)->grant($purchase);

    $laterTraining = DigitalTraining::create([
        'user_id' => $fixture['therapist']->id,
        'title' => 'Formation ajoutée plus tard',
        'slug' => 'formation-ajoutee-'.str()->lower(str()->random(8)),
        'status' => 'published',
        'access_type' => 'private',
    ]);
    $fixture['pack']->digitalTrainings()->attach($laterTraining);

    app(PackDigitalTrainingAccessService::class)->grant($purchase->fresh());

    expect($purchase->fresh()->digital_training_ids_snapshot)->toBe([$fixture['training']->id])
        ->and($purchase->digitalTrainingEnrollments()->pluck('digital_training_id')->all())
        ->toBe([$fixture['training']->id]);
    Mail::assertSent(DigitalTrainingAccessMail::class, 1);
});

test('a purchase assigned without training remains unchanged if the pack receives one later', function () {
    Mail::fake();
    $fixture = packDigitalFixture();
    $fixture['pack']->digitalTrainings()->detach();
    $purchase = PackPurchase::create([
        'user_id' => $fixture['therapist']->id,
        'pack_product_id' => $fixture['pack']->id,
        'client_profile_id' => $fixture['client']->id,
        'purchased_at' => now(),
        'status' => 'active',
    ]);

    app(PackDigitalTrainingAccessService::class)->grant($purchase);
    $fixture['pack']->digitalTrainings()->attach($fixture['training']);
    app(PackDigitalTrainingAccessService::class)->grant($purchase->fresh());

    expect($purchase->fresh()->digital_training_ids_snapshot)->toBe([])
        ->and($purchase->digitalTrainingEnrollments()->exists())->toBeFalse();
    Mail::assertNothingSent();
});

test('an email failure keeps the access retryable without duplicating it', function () {
    $fixture = packDigitalFixture();
    $purchase = PackPurchase::create([
        'user_id' => $fixture['therapist']->id,
        'pack_product_id' => $fixture['pack']->id,
        'client_profile_id' => $fixture['client']->id,
        'purchased_at' => now(),
        'status' => 'active',
    ]);

    Mail::shouldReceive('to')
        ->once()
        ->with($fixture['client']->email)
        ->andReturnSelf();
    Mail::shouldReceive('send')
        ->once()
        ->andThrow(new RuntimeException('SMTP indisponible'));

    $failed = app(PackDigitalTrainingAccessService::class)->grant($purchase);
    $enrollment = $purchase->digitalTrainingEnrollments()->firstOrFail();

    expect($failed['email_failed'])->toBe(1)
        ->and($enrollment->access_email_sent_at)->toBeNull();

    Mail::fake();
    $retried = app(PackDigitalTrainingAccessService::class)->grant($purchase->fresh());

    expect($retried['emailed'])->toBe(1)
        ->and($purchase->digitalTrainingEnrollments()->count())->toBe(1)
        ->and($purchase->digitalTrainingEnrollments()->first()->access_email_sent_at)->not->toBeNull();
    Mail::assertSent(DigitalTrainingAccessMail::class, 1);
});

test('a public pack attribution without online payment grants its training access', function () {
    Mail::fake();
    $fixture = packDigitalFixture();
    $fixture['therapist']->update([
        'slug' => 'pack-formation-public-'.str()->lower(str()->random(8)),
        'stripe_account_id' => null,
    ]);

    $this->mock(StripeAccountGuard::class, function ($mock): void {
        $mock->shouldReceive('status')->once()->andReturn(['ready' => false]);
    });

    $this->post(route('public.checkout.store', ['slug' => $fixture['therapist']->slug]), [
        'item' => 'pack:'.$fixture['pack']->id,
        'first_name' => 'Noémie',
        'last_name' => 'Public',
        'email' => 'noemie.public@example.test',
        'payment_choice' => 'one_time',
    ])->assertRedirect(route('therapist.show', $fixture['therapist']->slug));

    $purchase = PackPurchase::query()->latest('id')->firstOrFail();
    expect($purchase->status)->toBe('active')
        ->and($purchase->digitalTrainingEnrollments()->count())->toBe(1)
        ->and($purchase->digitalTrainingEnrollments()->first()->participant_email)
        ->toBe('noemie.public@example.test');
    Mail::assertSent(DigitalTrainingAccessMail::class, 1);
});

test('Stripe activation is idempotent and subscription deletion revokes training access', function () {
    Mail::fake();
    $fixture = packDigitalFixture();
    $purchase = PackPurchase::create([
        'user_id' => $fixture['therapist']->id,
        'pack_product_id' => $fixture['pack']->id,
        'client_profile_id' => $fixture['client']->id,
        'status' => 'pending',
        'payment_mode' => 'installments',
        'payment_state' => 'pending',
        'installments_total' => 3,
        'installments_paid' => 0,
    ]);
    $session = (object) [
        'id' => 'cs_pack_training',
        'payment_status' => 'paid',
        'subscription' => 'sub_pack_training',
        'customer' => 'cus_pack_training',
        'metadata' => (object) [
            'purchase_kind' => 'pack',
            'payment_mode' => 'installments',
            'pack_purchase_id' => (string) $purchase->id,
        ],
    ];
    $service = app(StripePurchaseWebhookService::class);

    foreach (['evt_pack_training_1', 'evt_pack_training_2'] as $eventId) {
        expect($service->handleEvent((object) [
            'id' => $eventId,
            'type' => 'checkout.session.completed',
            'data' => (object) ['object' => $session],
        ]))->toBeTrue();
    }

    expect($purchase->fresh()->status)->toBe('active')
        ->and($purchase->digitalTrainingEnrollments()->count())->toBe(1);
    Mail::assertSent(DigitalTrainingAccessMail::class, 1);

    expect($service->handleEvent((object) [
        'id' => 'evt_pack_training_deleted',
        'type' => 'customer.subscription.deleted',
        'data' => (object) ['object' => (object) ['id' => 'sub_pack_training']],
    ]))->toBeTrue();

    expect($purchase->fresh()->status)->toBe('cancelled')
        ->and($purchase->digitalTrainingEnrollments()->firstOrFail()->token_expires_at->isPast())->toBeTrue();
});

<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\GiftVoucher;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Services\AppointmentGiftVoucherService;
use App\Services\GiftVoucherRedeemService;
use App\Services\ReceiptRecordingService;

function appointmentVoucherContext(int $balance = 10000): array
{
    $user = User::factory()->create([
        'is_therapist' => true, 'license_status' => 'active', 'license_product' => 'new_pro_mensuelle',
    ]);
    $client = ClientProfile::create([
        'user_id' => $user->id, 'first_name' => 'Alice', 'last_name' => 'Voucher', 'email' => fake()->unique()->safeEmail(),
    ]);
    $product = Product::create([
        'user_id' => $user->id, 'name' => 'Séance bon cadeau', 'price' => 80, 'tax_rate' => 0,
        'duration' => 60, 'dans_le_cabinet' => true,
    ]);
    $appointment = Appointment::create([
        'user_id' => $user->id, 'client_profile_id' => $client->id, 'product_id' => $product->id,
        'appointment_date' => now()->addDay(), 'status' => 'confirmed', 'duration' => 60, 'type' => 'cabinet',
    ]);
    $voucher = GiftVoucher::create([
        'user_id' => $user->id, 'code' => 'AM-TEST-'.strtoupper(fake()->unique()->bothify('????####')),
        'original_amount_cents' => $balance, 'remaining_amount_cents' => $balance, 'currency' => 'EUR',
        'is_active' => true, 'source' => 'manual', 'sale_status' => 'paid',
    ]);

    return compact('user', 'client', 'product', 'appointment', 'voucher');
}

function appointmentVoucherPayload(array $context, int $amount = 8000, int $total = 8000): array
{
    return ['gift_voucher_code' => $context['voucher']->code, 'amount_cents' => $amount, 'total_cents' => $total];
}

function appointmentVoucherInvoice(array $context, array $overrides = []): Invoice
{
    static $number = 33000;

    return Invoice::create(array_merge([
        'user_id' => $context['user']->id, 'client_profile_id' => $context['client']->id,
        'appointment_id' => $context['appointment']->id, 'invoice_date' => today(), 'invoice_number' => ++$number,
        'type' => 'invoice', 'status' => 'En attente', 'total_amount' => 80, 'total_tax_amount' => 0, 'total_amount_with_tax' => 80,
    ], $overrides));
}

test('voucher verification shows the actual deduction without changing any balance', function () {
    $c = appointmentVoucherContext(5000);
    $this->actingAs($c['user'])->from(route('appointments.show', $c['appointment']))
        ->post(route('appointments.gift-voucher.preview', $c['appointment']), ['gift_voucher_code' => ' '.strtolower($c['voucher']->code).' '])
        ->assertRedirect(route('appointments.show', $c['appointment']))
        ->assertSessionHas('appointment_voucher_preview', fn ($quote) => $quote['amount_cents'] === 5000
            && $quote['remaining_cents'] === 3000 && $quote['balance_after_cents'] === 0);
    expect($c['voucher']->fresh()->remaining_amount_cents)->toBe(5000);
    $this->assertDatabaseCount('gift_voucher_redemptions', 0);
});

test('full voucher payment preserves attendance and is idempotent even after exhaustion', function () {
    \Illuminate\Support\Facades\Mail::fake();
    \Illuminate\Support\Facades\Queue::fake();
    $c = appointmentVoucherContext(8000);
    for ($i = 0; $i < 2; $i++) {
        $this->actingAs($c['user'])->post(route('appointments.gift-voucher.store', $c['appointment']), appointmentVoucherPayload($c))
            ->assertSessionHasNoErrors()->assertSessionHas('success');
    }
    expect($c['voucher']->fresh()->remaining_amount_cents)->toBe(0)
        ->and($c['appointment']->fresh()->status)->toBe('confirmed')
        ->and($c['appointment']->fresh()->gift_voucher_payment_label)->toBe('Réglé par bon cadeau');
    $this->assertDatabaseCount('gift_voucher_redemptions', 1);
    $this->assertDatabaseCount('invoices', 0);
    $c['product']->update(['price' => 120]);
    expect($c['appointment']->fresh()->gift_voucher_payment_label)->toBe('Réglé par bon cadeau');
    \Illuminate\Support\Facades\Mail::assertNothingSent();
    \Illuminate\Support\Facades\Queue::assertNothingPushed();
});

test('partial voucher payment stays partial and cannot consume a second voucher', function () {
    $c = appointmentVoucherContext(5000);
    $this->actingAs($c['user'])->post(route('appointments.gift-voucher.store', $c['appointment']), appointmentVoucherPayload($c, 5000))
        ->assertSessionHasNoErrors();
    expect($c['appointment']->fresh()->gift_voucher_payment_label)->toBe('Bon cadeau : 50,00 € · Reste à régler : 30,00 €');
    $second = $c['voucher']->replicate()->fill(['code' => 'AM-SECOND', 'remaining_amount_cents' => 5000, 'is_active' => true]);
    $second->save();
    $this->post(route('appointments.gift-voucher.store', $c['appointment']), [
        'gift_voucher_code' => $second->code, 'amount_cents' => 3000, 'total_cents' => 8000,
    ])->assertSessionHasErrors('gift_voucher_code');
    expect($second->fresh()->remaining_amount_cents)->toBe(5000);
});

test('foreign appointments and foreign voucher codes are rejected', function () {
    $c = appointmentVoucherContext();
    $other = appointmentVoucherContext();
    $this->actingAs($other['user'])->post(route('appointments.gift-voucher.store', $c['appointment']), appointmentVoucherPayload($c))->assertForbidden();
    $this->actingAs($c['user'])->post(route('appointments.gift-voucher.preview', $c['appointment']), ['gift_voucher_code' => $other['voucher']->code])
        ->assertSessionHasErrors('gift_voucher_code');
    $this->assertDatabaseCount('gift_voucher_redemptions', 0);
});

test('unusable voucher cannot be previewed or applied', function (array $changes) {
    $c = appointmentVoucherContext();
    $c['voucher']->update($changes);
    foreach (['preview', 'store'] as $action) {
        $this->actingAs($c['user'])->post(route('appointments.gift-voucher.'.$action, $c['appointment']), appointmentVoucherPayload($c))
            ->assertSessionHasErrors('gift_voucher_code');
    }
    $this->assertDatabaseCount('gift_voucher_redemptions', 0);
})->with([
    'expired' => [['expires_at' => now()->subDay()]],
    'disabled' => [['is_active' => false]],
    'empty' => [['remaining_amount_cents' => 0]],
    'other currency' => [['currency' => 'USD']],
]);

test('ineligible appointments cannot consume a voucher', function (array $changes) {
    $c = appointmentVoucherContext();
    $c['appointment']->forceFill($changes)->saveQuietly();
    $this->actingAs($c['user'])->post(route('appointments.gift-voucher.store', $c['appointment']), appointmentVoucherPayload($c))
        ->assertSessionHasErrors('gift_voucher_code');
    expect($c['voucher']->fresh()->remaining_amount_cents)->toBe(10000);
})->with([
    'cancelled' => [['status' => 'cancelled']],
    'paid legacy status' => [['status' => 'Payée']],
    'pending checkout' => [['status' => 'pending_payment']],
    'external' => [['external' => true]],
    'pack credit' => [['consumed_pack_purchase_id' => 4242]],
    'legacy planned usage' => [['gift_voucher_amount_cents' => 5000]],
]);

test('changed price or voucher balance requires verification again', function (string $change) {
    $c = appointmentVoucherContext();
    if ($change === 'balance') {
        $c['voucher']->update(['remaining_amount_cents' => 3000]);
    } else {
        $c['product']->update(['price' => 90]);
    }
    $this->actingAs($c['user'])->post(route('appointments.gift-voucher.store', $c['appointment']), appointmentVoucherPayload($c))
        ->assertSessionHasErrors('gift_voucher_code');
    $this->assertDatabaseCount('gift_voucher_redemptions', 0);
})->with(['balance', 'price']);

test('existing booking redemptions display without being redeemed again and do not invent old prices', function () {
    $c = appointmentVoucherContext(8000);
    app(GiftVoucherRedeemService::class)->redeem($c['voucher'], 8000, 'Online booking', $c['user']->id, $c['appointment']->id, null, 'booking_online');
    $this->actingAs($c['user'])->post(route('appointments.gift-voucher.store', $c['appointment']), appointmentVoucherPayload($c))->assertSessionHasNoErrors();
    expect($c['appointment']->fresh()->gift_voucher_payment_label)->toBe('Bon cadeau : 80,00 €');
    $c['appointment']->forceFill(['gift_voucher_total_cents' => 8000])->saveQuietly();
    expect($c['appointment']->fresh()->gift_voucher_payment_label)->toBe('Réglé par bon cadeau');
    $this->assertDatabaseCount('gift_voucher_redemptions', 1);
});

test('reserved vouchers are distinct from applied and released vouchers', function () {
    $c = appointmentVoucherContext(5000);
    $service = app(GiftVoucherRedeemService::class);
    $service->reserveForAppointment($c['voucher'], 5000, $c['appointment']->id, $c['user']->id);
    expect($c['appointment']->fresh()->gift_voucher_payment_label)->toBe('Bon cadeau réservé · paiement en attente');
    $this->actingAs($c['user'])->post(route('appointments.gift-voucher.store', $c['appointment']), appointmentVoucherPayload($c, 5000))
        ->assertSessionHasErrors('gift_voucher_code');
    $service->releaseReservedForAppointment($c['appointment']);
    expect($c['appointment']->fresh()->gift_voucher_payment_label)->toBeNull()
        ->and($c['voucher']->fresh()->remaining_amount_cents)->toBe(5000);
});

test('voucher payment on an invoice uses its actual balance and records one receipt', function () {
    $c = appointmentVoucherContext();
    $invoice = appointmentVoucherInvoice($c, ['total_amount' => 90, 'total_amount_with_tax' => 90]);
    app(ReceiptRecordingService::class)->recordInvoicePayment($invoice, 30, today()->toDateString(), 'cash');
    for ($i = 0; $i < 2; $i++) {
        $this->actingAs($c['user'])->post(route('appointments.gift-voucher.store', $c['appointment']), appointmentVoucherPayload($c, 6000, 9000))
            ->assertSessionHasNoErrors();
    }
    expect($c['voucher']->fresh()->remaining_amount_cents)->toBe(4000)
        ->and($invoice->fresh()->status)->toBe('Payée')
        ->and($invoice->fresh()->solde_restant)->toBe(0.0)
        ->and($invoice->receipts()->where('provider', 'gift_voucher')->count())->toBe(1)
        ->and($c['appointment']->fresh()->gift_voucher_payment_label)->toBe('Réglé · dont 60,00 € en bon cadeau');
    $this->assertDatabaseHas('gift_voucher_redemptions', ['appointment_id' => $c['appointment']->id, 'invoice_id' => $invoice->id]);
});

test('partial invoice voucher payment leaves the correct balance', function () {
    $c = appointmentVoucherContext(5000);
    $invoice = appointmentVoucherInvoice($c);
    $this->actingAs($c['user'])->post(route('appointments.gift-voucher.store', $c['appointment']), appointmentVoucherPayload($c, 5000))
        ->assertSessionHasNoErrors();
    expect($invoice->fresh()->status)->toBe('Partiellement payée')
        ->and($invoice->fresh()->solde_restant)->toBe(30.0)
        ->and($c['appointment']->fresh()->gift_voucher_payment_label)->toBe('Bon cadeau : 50,00 € · Reste à régler : 30,00 €');
});

test('paid invoices and ambiguous multiple invoices cannot receive another voucher payment', function (bool $multiple) {
    $c = appointmentVoucherContext();
    appointmentVoucherInvoice($c, ['status' => $multiple ? 'En attente' : 'Payée']);
    if ($multiple) {
        appointmentVoucherInvoice($c);
    }
    $this->actingAs($c['user'])->post(route('appointments.gift-voucher.store', $c['appointment']), appointmentVoucherPayload($c))
        ->assertSessionHasErrors('gift_voucher_code');
    $this->assertDatabaseCount('gift_voucher_redemptions', 0);
})->with([false, true]);

test('creating an invoice after voucher use carries payment once without another deduction', function () {
    $c = appointmentVoucherContext(5000);
    $this->actingAs($c['user'])->post(route('appointments.gift-voucher.store', $c['appointment']), appointmentVoucherPayload($c, 5000))
        ->assertSessionHasNoErrors();
    $payload = [
        'client_profile_id' => $c['client']->id, 'appointment_id' => $c['appointment']->id, 'invoice_date' => today()->toDateString(),
        'items' => [['product_id' => $c['product']->id, 'quantity' => 1, 'unit_price' => 80, 'tax_rate' => 0]],
    ];
    for ($i = 0; $i < 2; $i++) {
        $this->post(route('invoices.store'), $payload)->assertSessionHasNoErrors();
    }
    $invoice = Invoice::sole();
    expect($invoice->solde_restant)->toBe(30.0)
        ->and($invoice->receipts()->count())->toBe(1)
        ->and($c['voucher']->fresh()->remaining_amount_cents)->toBe(0);
    $this->assertDatabaseCount('gift_voucher_redemptions', 1);
});

test('invoice creation below existing voucher usage rolls back the invoice without changing usage', function () {
    $c = appointmentVoucherContext(8000);
    app(AppointmentGiftVoucherService::class)->apply($c['appointment'], $c['voucher']->code, 8000, 8000);
    $this->actingAs($c['user'])->post(route('invoices.store'), [
        'client_profile_id' => $c['client']->id, 'appointment_id' => $c['appointment']->id, 'invoice_date' => today()->toDateString(),
        'items' => [['label' => 'Séance à tarif réduit', 'quantity' => 1, 'unit_price' => 50, 'tax_rate' => 0]],
    ])->assertSessionHasErrors('appointment_id');
    $this->assertDatabaseCount('invoices', 0);
    expect($c['voucher']->fresh()->remaining_amount_cents)->toBe(0);
});

test('desktop mobile and calendar show the voucher payment without changing service colors', function () {
    $c = appointmentVoucherContext(8000);
    $c['product']->update(['calendar_color' => '#123456']);
    app(AppointmentGiftVoucherService::class)->apply($c['appointment'], $c['voucher']->code, 8000, 8000);
    foreach (['appointments.show', 'mobile.appointments.show'] as $route) {
        $this->actingAs($c['user'])->get(route($route, $c['appointment']))->assertOk()->assertSee('Réglé par bon cadeau');
    }
    $this->get(route('appointments.index'))->assertOk()->assertViewHas('events', fn ($events) => collect($events)->contains(
        fn ($event) => $event['title'] === '🎁 Alice Voucher' && $event['color'] === '#123456'
            && $event['extendedProps']['giftVoucherLabel'] === 'Réglé par bon cadeau'
    ));
});

test('public and partner bookings snapshot the total including tax and show applied voucher payment', function (string $channel) {
    \Illuminate\Support\Facades\Mail::fake();
    \Illuminate\Support\Facades\Queue::fake();
    config()->set('appointments.booking_v2.enabled', false);
    $c = appointmentVoucherContext();
    $c['user']->update(['accept_online_appointments' => true, 'minimum_notice_hours' => 0, 'buffer_time_between_appointments' => 0]);
    $c['product']->update(['tax_rate' => 20, 'can_be_booked_online' => true, 'collect_payment' => true, 'visio' => true, 'dans_le_cabinet' => false]);
    $date = now()->addWeek()->startOfDay();
    \App\Models\Availability::create([
        'user_id' => $c['user']->id, 'day_of_week' => $date->dayOfWeekIso - 1,
        'start_time' => '09:00:00', 'end_time' => '18:00:00', 'applies_to_all' => true,
    ]);
    $link = \App\Models\BookingLink::create([
        'user_id' => $c['user']->id, 'token' => 'gift-payment-booking', 'name' => 'Partner',
        'allowed_product_ids' => [$c['product']->id], 'is_enabled' => true,
    ]);
    $url = $channel === 'partner' ? route('bookingLinks.store', $link->token) : route('appointments.storePatient');
    $this->post($url, [
        'therapist_id' => $c['user']->id, 'product_id' => $c['product']->id, 'gift_voucher_code' => $c['voucher']->code,
        'first_name' => 'Alice', 'last_name' => 'Voucher', 'email' => $c['client']->email,
        'appointment_date' => $date->toDateString(), 'appointment_time' => '10:00', 'type' => 'visio',
    ])->assertRedirect()->assertSessionHasNoErrors();
    $booked = Appointment::latest('id')->firstOrFail();
    expect($booked->gift_voucher_total_cents)->toBe(9600)
        ->and($booked->gift_voucher_payment_label)->toBe('Réglé par bon cadeau')
        ->and($c['voucher']->fresh()->remaining_amount_cents)->toBe(400);
})->with(['public', 'partner']);

test('a receipt failure rolls back voucher balance usage and appointment changes together', function () {
    $c = appointmentVoucherContext();
    appointmentVoucherInvoice($c);
    $this->mock(ReceiptRecordingService::class)->shouldReceive('recordInvoicePayment')->once()->andThrow(new RuntimeException('Receipt failure'));
    expect(fn () => app(AppointmentGiftVoucherService::class)->apply($c['appointment'], $c['voucher']->code, 8000, 8000))
        ->toThrow(RuntimeException::class, 'Receipt failure');
    expect($c['voucher']->fresh()->remaining_amount_cents)->toBe(10000)
        ->and($c['appointment']->fresh()->gift_voucher_id)->toBeNull();
    $this->assertDatabaseCount('gift_voucher_redemptions', 0);
    $this->assertDatabaseCount('receipts', 0);
});

test('associating an unpaid invoice carries voucher usage while legacy paid invoices are left intact', function (bool $paid) {
    $c = appointmentVoucherContext(5000);
    app(AppointmentGiftVoucherService::class)->apply($c['appointment'], $c['voucher']->code, 5000, 8000);
    $invoice = appointmentVoucherInvoice($c, ['appointment_id' => null, 'status' => $paid ? 'Payée' : 'En attente']);
    $this->actingAs($c['user'])->post(route('appointments.invoices.associate', $c['appointment']), ['invoice_id' => $invoice->id])
        ->assertSessionHasNoErrors();
    expect($invoice->fresh()->appointment_id)->toBe($c['appointment']->id)
        ->and($invoice->receipts()->count())->toBe($paid ? 0 : 1)
        ->and($c['voucher']->fresh()->remaining_amount_cents)->toBe(0);
})->with([false, true]);

test('a voucher payment invoice cannot be reassigned to another appointment', function () {
    $c = appointmentVoucherContext();
    $invoice = appointmentVoucherInvoice($c);
    app(AppointmentGiftVoucherService::class)->apply($c['appointment'], $c['voucher']->code, 8000, 8000);
    $other = $c['appointment']->replicate()->fill(['gift_voucher_id' => null, 'gift_voucher_amount_cents' => null, 'token' => 'other-appointment-token']);
    $other->save();
    $this->actingAs($c['user'])->post(route('appointments.invoices.associate', $other), ['invoice_id' => $invoice->id, 'confirm_reassign' => 1])
        ->assertSessionHas('error');
    expect($invoice->fresh()->appointment_id)->toBe($c['appointment']->id);
});

test('associating a legacy invoice with manual payments does not add another receipt', function () {
    $c = appointmentVoucherContext(5000);
    app(AppointmentGiftVoucherService::class)->apply($c['appointment'], $c['voucher']->code, 5000, 8000);
    $invoice = appointmentVoucherInvoice($c, ['appointment_id' => null]);
    app(ReceiptRecordingService::class)->recordInvoicePayment($invoice, 50, today()->toDateString(), 'other', 'manual');
    $this->actingAs($c['user'])->post(route('appointments.invoices.associate', $c['appointment']), ['invoice_id' => $invoice->id])
        ->assertSessionHasNoErrors();
    expect($invoice->receipts()->count())->toBe(1)
        ->and($invoice->fresh()->solde_restant)->toBe(30.0)
        ->and($c['appointment']->fresh()->gift_voucher_payment_label)->toBe('Bon cadeau : 50,00 €');
});

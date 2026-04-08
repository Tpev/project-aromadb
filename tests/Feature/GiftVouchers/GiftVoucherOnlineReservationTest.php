<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\GiftVoucher;
use App\Models\GiftVoucherRedemption;
use App\Models\Product;
use App\Models\User;
use App\Services\GiftVoucherRedeemService;
use Carbon\Carbon;

function makeBookingAppointment(array $overrides = []): Appointment
{
    $therapist = $overrides['therapist'] ?? User::factory()->create([
        'is_therapist' => true,
        'slug' => 'therapist-gift-voucher-test-' . fake()->unique()->slug(),
    ]);

    $client = $overrides['client'] ?? ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => 'Delphine',
        'last_name' => 'Schwartz',
        'email' => 'delphine-' . fake()->unique()->safeEmail(),
    ]);

    $product = $overrides['product'] ?? Product::create([
        'user_id' => $therapist->id,
        'name' => 'Consultation test',
        'price' => 60,
        'tax_rate' => 0,
        'duration' => 60,
        'collect_payment' => true,
        'can_be_booked_online' => true,
        'dans_le_cabinet' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    return Appointment::create([
        'client_profile_id' => $client->id,
        'user_id' => $therapist->id,
        'appointment_date' => now()->addDay(),
        'status' => 'pending',
        'type' => 'cabinet',
        'duration' => 60,
        'product_id' => $product->id,
    ]);
}

test('gift voucher online reservation can be finalized after stripe payment', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
    ]);

    $appointment = makeBookingAppointment(['therapist' => $therapist]);

    $voucher = GiftVoucher::create([
        'user_id' => $therapist->id,
        'code' => 'AM-RESERVE-TEST-0001',
        'original_amount_cents' => 5000,
        'remaining_amount_cents' => 5000,
        'currency' => 'EUR',
        'is_active' => true,
        'buyer_name' => 'Acheteur',
        'buyer_email' => 'acheteur@example.test',
        'source' => 'manual',
        'sale_channel' => 'offline_manual',
        'sale_status' => 'paid',
    ]);

    $service = app(GiftVoucherRedeemService::class);

    $reservation = $service->reserveForAppointment(
        $voucher,
        5000,
        $appointment->id,
        $therapist->id,
        'Reservation test'
    );

    expect($reservation->status)->toBe(GiftVoucherRedeemService::STATUS_RESERVED);
    expect($voucher->fresh()->remaining_amount_cents)->toBe(0);

    $applied = $service->finalizeReservedForAppointment($voucher->fresh(), $appointment->id);

    expect($applied)->toBe(5000);
    expect($voucher->fresh()->remaining_amount_cents)->toBe(0);

    $reservation->refresh();
    expect($reservation->status)->toBe(GiftVoucherRedeemService::STATUS_APPLIED);
});

test('gift voucher online reservation is released when stripe payment is cancelled', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'therapist-gift-voucher-cancel',
    ]);

    $appointment = makeBookingAppointment(['therapist' => $therapist]);

    $voucher = GiftVoucher::create([
        'user_id' => $therapist->id,
        'code' => 'AM-RESERVE-TEST-0002',
        'original_amount_cents' => 5000,
        'remaining_amount_cents' => 5000,
        'currency' => 'EUR',
        'is_active' => true,
        'buyer_name' => 'Acheteur',
        'buyer_email' => 'acheteur2@example.test',
        'source' => 'manual',
        'sale_channel' => 'offline_manual',
        'sale_status' => 'paid',
    ]);

    app(GiftVoucherRedeemService::class)->reserveForAppointment(
        $voucher,
        5000,
        $appointment->id,
        $therapist->id,
        'Reservation test'
    );

    $response = $this->get(route('appointments.cancel', ['appointment_id' => $appointment->id]));

    $response->assertRedirect(route('therapist.show', $therapist->slug));
    $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);

    expect($voucher->fresh()->remaining_amount_cents)->toBe(5000);

    $this->assertDatabaseHas('gift_voucher_redemptions', [
        'gift_voucher_id' => $voucher->id,
        'appointment_id' => $appointment->id,
        'status' => GiftVoucherRedeemService::STATUS_RELEASED,
        'source' => GiftVoucherRedeemService::BOOKING_ONLINE_SOURCE,
    ]);
});

test('stale gift voucher online reservations are released automatically', function () {
    Carbon::setTestNow(now());

    $therapist = User::factory()->create([
        'is_therapist' => true,
    ]);

    $appointment = makeBookingAppointment(['therapist' => $therapist]);

    $voucher = GiftVoucher::create([
        'user_id' => $therapist->id,
        'code' => 'AM-RESERVE-TEST-0003',
        'original_amount_cents' => 5000,
        'remaining_amount_cents' => 5000,
        'currency' => 'EUR',
        'is_active' => true,
        'buyer_name' => 'Acheteur',
        'buyer_email' => 'acheteur3@example.test',
        'source' => 'manual',
        'sale_channel' => 'offline_manual',
        'sale_status' => 'paid',
    ]);

    $service = app(GiftVoucherRedeemService::class);

    $reservation = $service->reserveForAppointment(
        $voucher,
        5000,
        $appointment->id,
        $therapist->id,
        'Reservation test'
    );

    GiftVoucherRedemption::query()
        ->whereKey($reservation->id)
        ->update([
            'created_at' => now()->subMinutes(
                GiftVoucherRedeemService::BOOKING_ONLINE_HOLD_MINUTES
                + GiftVoucherRedeemService::BOOKING_ONLINE_RELEASE_GRACE_MINUTES
                + 1
            ),
        ]);

    $released = $service->releaseStaleOnlineReservations();

    expect($released)->toBeGreaterThan(0);
    expect($voucher->fresh()->remaining_amount_cents)->toBe(5000);

    $reservation->refresh();
    expect($reservation->status)->toBe(GiftVoucherRedeemService::STATUS_RELEASED);
    expect($reservation->released_at)->not->toBeNull();
});

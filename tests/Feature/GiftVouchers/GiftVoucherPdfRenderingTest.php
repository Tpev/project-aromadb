<?php

use App\Models\GiftVoucher;
use App\Models\User;
use App\Services\GiftVoucherPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('gift voucher pdf service falls back to the public booking page when therapist slug is missing', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => null,
    ]);

    $voucher = GiftVoucher::create([
        'user_id' => $therapist->id,
        'code' => 'AM-TEST-0001',
        'original_amount_cents' => 10000,
        'remaining_amount_cents' => 10000,
        'currency' => 'EUR',
        'is_active' => true,
        'buyer_name' => 'Acheteur',
        'buyer_email' => 'buyer@example.test',
        'sale_channel' => 'offline_manual',
        'sale_status' => 'paid',
    ]);

    $portalUrl = app(GiftVoucherPdfService::class)->resolvePortalUrl($voucher->fresh('therapist'));

    expect($portalUrl)->toBe(route('appointments.createPatient', ['therapist' => $therapist->id]));
    expect(app(GiftVoucherPdfService::class)->buildQrSvgMarkup($portalUrl))
        ->not->toBeNull()
        ->toContain('<svg');
});

test('gift voucher pdf download works with a custom background image', function () {
    Storage::fake('public');

    $therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'therapist-pdf-test',
    ]);

    $background = UploadedFile::fake()->image('voucher-background.png', 1800, 2400);
    $path = $background->storeAs("gift-vouchers/backgrounds/{$therapist->id}", 'background.jpg', 'public');

    $voucher = GiftVoucher::create([
        'user_id' => $therapist->id,
        'code' => 'AM-TEST-0002',
        'original_amount_cents' => 15000,
        'remaining_amount_cents' => 15000,
        'currency' => 'EUR',
        'is_active' => true,
        'buyer_name' => 'Acheteur',
        'buyer_email' => 'buyer@example.test',
        'sale_channel' => 'offline_manual',
        'sale_status' => 'paid',
        'background_mode_snapshot' => 'custom_upload',
        'background_path_snapshot' => $path,
    ]);

    $pdfBinary = app(GiftVoucherPdfService::class)->renderPdf($voucher->fresh('therapist'));

    expect($pdfBinary)->toBeString();
    expect(strlen($pdfBinary))->toBeGreaterThan(1000);
});

<?php

use App\Models\GiftVoucher;
use App\Models\GiftVoucherRedemption;
use App\Models\User;
use App\Services\GiftVoucherBackgroundService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

function compatibleVoucher(User $user, array $attributes = []): GiftVoucher
{
    return GiftVoucher::create(array_merge([
        'user_id' => $user->id, 'code' => 'COMPAT-'.strtoupper(fake()->unique()->bothify('????####')),
        'original_amount_cents' => 10000, 'remaining_amount_cents' => 10000,
        'is_active' => true, 'currency' => 'EUR', 'source' => 'manual', 'buyer_email' => 'buyer@example.test',
    ], $attributes));
}

function voucherCompatibilityUser(): User
{
    return User::factory()->create(['is_therapist' => true, 'license_status' => 'active', 'license_product' => 'new_pro_mensuelle']);
}

test('legacy voucher labels and database filters agree without changing financial or activation data', function (bool $active, int $balance, bool $expired, string $useStatus, string $expected) {
    $user = voucherCompatibilityUser();
    $voucher = compatibleVoucher($user, [
        'is_active' => $active, 'remaining_amount_cents' => $balance,
        'expires_at' => $expired ? now()->subDay() : null,
    ]);
    if ($useStatus !== '') {
        GiftVoucherRedemption::create(['gift_voucher_id' => $voucher->id, 'user_id' => $user->id,
            'amount_cents' => 5000, 'status' => $useStatus, 'source' => 'booking_online']);
    }
    $before = $voucher->fresh()->getRawOriginal();
    expect($voucher->displayStatus())->toBe($expected)
        ->and(GiftVoucher::withReservationTotals()->findOrFail($voucher->id)->displayStatus())->toBe($expected)
        ->and($voucher->load('redemptions')->displayStatus())->toBe($expected);
    foreach (array_keys(GiftVoucher::STATUS_FILTERS) as $status) {
        expect(GiftVoucher::whereKey($voucher->id)->withDisplayStatus($status)->exists())->toBe($status === 'all' || $status === $expected);
    }
    expect($voucher->fresh()->getRawOriginal())->toBe($before);
})->with([
    'legacy automatically disabled' => [false, 0, false, 'applied', 'exhausted'],
    'legacy zero without history' => [false, 0, true, '', 'exhausted'],
    'active zero' => [true, 0, false, '', 'exhausted'],
    'disabled unused' => [false, 10000, false, '', 'disabled'],
    'disabled expired' => [false, 10000, true, '', 'disabled'],
    'expired unused' => [true, 10000, true, '', 'expired'],
    'fully reserved' => [true, 0, false, 'reserved', 'reserved'],
    'partly reserved' => [true, 5000, false, 'reserved', 'reserved'],
    'disabled reservation' => [false, 5000, false, 'reserved', 'disabled'],
    'expired reservation' => [true, 0, true, 'reserved', 'expired'],
    'released reservation' => [true, 10000, false, 'released', 'active'],
]);

test('desktop and mobile lists details and counts consistently identify exhausted and reserved vouchers', function () {
    $user = voucherCompatibilityUser();
    $exhausted = compatibleVoucher($user, ['is_active' => false, 'remaining_amount_cents' => 0]);
    $reserved = compatibleVoucher($user, ['remaining_amount_cents' => 0]);
    GiftVoucherRedemption::create(['gift_voucher_id' => $reserved->id, 'user_id' => $user->id,
        'amount_cents' => 10000, 'status' => 'reserved', 'source' => 'booking_online']);
    for ($i = 0; $i < 23; $i++) compatibleVoucher($user);
    compatibleVoucher(voucherCompatibilityUser());
    $this->actingAs($user)->get(route('pro.gift-vouchers.index', ['status' => 'exhausted']))->assertOk()
        ->assertViewHas('statusCounts', fn ($counts) => $counts['all'] === 25 && $counts['active'] === 23
            && $counts['exhausted'] === 1 && $counts['disabled'] === 0 && $counts['reserved'] === 1)
        ->assertViewHas('vouchers', fn ($vouchers) => $vouchers->pluck('id')->all() === [$exhausted->id]);
    foreach (['pro', 'mobile'] as $channel) {
        $this->get(route($channel.'.gift-vouchers.index', ['status' => 'reserved']))->assertOk()
            ->assertViewHas('vouchers', fn ($vouchers) => $vouchers->pluck('id')->all() === [$reserved->id]);
        $this->get(route($channel.'.gift-vouchers.show', $exhausted))->assertOk()->assertSee('Épuisé');
        $this->get(route($channel.'.gift-vouchers.show', $reserved))->assertOk()
            ->assertSee('Réservation en cours')->assertSee('temporairement réservés')->assertSee('Montant réservé');
    }
});

test('manual confirmation rejects a changed balance and repeat submission while legacy requests still work', function (string $channel) {
    $user = voucherCompatibilityUser();
    $voucher = compatibleVoucher($user);
    $url = route($channel.'.gift-vouchers.redeem', $voucher);
    $payload = ['amount_eur' => 30, 'expected_balance_cents' => 10000];
    $this->actingAs($user)->post($url, $payload)->assertSessionHasNoErrors();
    $this->post($url, $payload)->assertSessionHasErrors('amount_eur');
    expect($voucher->fresh()->remaining_amount_cents)->toBe(7000)
        ->and($voucher->redemptions()->count())->toBe(1);
    // Old clients do not send the new optional confirmation balance.
    $this->post($url, ['amount_eur' => 20])->assertSessionHasNoErrors();
    expect($voucher->fresh()->remaining_amount_cents)->toBe(5000);
})->with(['pro', 'mobile']);

test('uploading an image automatically selects customization and preserves legacy images and voucher snapshots', function () {
    Storage::fake('public');
    $user = voucherCompatibilityUser();
    $oldPath = 'gift-vouchers/backgrounds/'.$user->id.'/background.jpg';
    $oldImage = UploadedFile::fake()->image('legacy.jpg', 200, 300);
    Storage::disk('public')->put($oldPath, file_get_contents($oldImage->getRealPath()));
    $oldBytes = Storage::disk('public')->get($oldPath);
    $user->forceFill(['gift_voucher_background_mode' => 'custom_upload', 'gift_voucher_background_path' => $oldPath])->save();
    $oldVoucher = compatibleVoucher($user, ['background_mode_snapshot' => 'custom_upload', 'background_path_snapshot' => $oldPath]);
    $before = $oldVoucher->fresh()->getRawOriginal();

    $this->actingAs($user)->post(route('pro.gift-vouchers.settings.update'), [
        'gift_voucher_background_mode' => 'default',
        'gift_voucher_background' => UploadedFile::fake()->image('new.png', 800, 600),
    ])->assertSessionHasNoErrors()->assertSessionHas('success');
    $user->refresh();
    $firstNewPath = $user->gift_voucher_background_path;
    expect($user->gift_voucher_background_mode)->toBe('custom_upload')
        ->and($firstNewPath)->not->toBe($oldPath)
        ->and(Storage::disk('public')->get($oldPath))->toBe($oldBytes)
        ->and($oldVoucher->fresh()->getRawOriginal())->toBe($before);
    $size = getimagesizefromstring(Storage::disk('public')->get($firstNewPath));
    expect([$size[0], $size[1], $size['mime']])->toBe([794, 1123, 'image/jpeg']);
    $newVoucher = compatibleVoucher($user, [
        'background_mode_snapshot' => 'custom_upload', 'background_path_snapshot' => GiftVoucherBackgroundService::snapshotForVoucher($user)['path'],
    ]);
    $firstNewBytes = Storage::disk('public')->get($firstNewPath);
    $this->post(route('pro.gift-vouchers.settings.update'), [
        'gift_voucher_background_mode' => 'custom_upload',
        'gift_voucher_background' => UploadedFile::fake()->image('another.png', 300, 500),
    ])->assertSessionHasNoErrors();
    expect($user->fresh()->gift_voucher_background_path)->not->toBe($firstNewPath)
        ->and(Storage::disk('public')->get($firstNewPath))->toBe($firstNewBytes);

    $this->post(route('pro.gift-vouchers.settings.update'), [
        'gift_voucher_background_mode' => 'default', 'remove_gift_voucher_background' => 1,
    ])->assertSessionHasNoErrors();
    expect(GiftVoucherBackgroundService::snapshotForVoucher($user->fresh()))->toBe(['mode' => 'default', 'path' => null])
        ->and(Storage::disk('public')->get($oldPath))->toBe($oldBytes)
        ->and(Storage::disk('public')->get($firstNewPath))->toBe($firstNewBytes)
        ->and($newVoucher->fresh()->background_path_snapshot)->toBe($firstNewPath);
    $this->get(route('pro.gift-vouchers.pdf', $oldVoucher))->assertOk()->assertHeader('content-type', 'application/pdf');
});

test('invalid or oversized image uploads leave all saved settings and old files intact', function (string $kind) {
    Storage::fake('public');
    $user = voucherCompatibilityUser();
    $path = 'gift-vouchers/backgrounds/'.$user->id.'/background.jpg';
    Storage::disk('public')->put($path, 'existing image');
    $user->forceFill(['gift_voucher_background_mode' => 'custom_upload', 'gift_voucher_background_path' => $path,
        'gift_voucher_online_enabled' => true, 'gift_voucher_validity_months' => 12])->save();
    $before = $user->fresh()->getRawOriginal();
    $file = $kind === 'oversized' ? UploadedFile::fake()->image('large.png')->size(6145) : UploadedFile::fake()->create('document.pdf', 20, 'application/pdf');
    $this->actingAs($user)->post(route('pro.gift-vouchers.settings.update'), [
        'gift_voucher_background_mode' => 'default', 'gift_voucher_validity_months' => 6, 'gift_voucher_background' => $file,
    ])->assertSessionHasErrors('gift_voucher_background');
    expect($user->fresh()->getRawOriginal())->toBe($before)
        ->and(Storage::disk('public')->get($path))->toBe('existing image');
})->with(['oversized', 'unsupported']);

test('a decoding failure does not remove a previously saved image', function () {
    Storage::fake('public');
    $user = voucherCompatibilityUser();
    $path = 'gift-vouchers/backgrounds/'.$user->id.'/background.jpg';
    Storage::disk('public')->put($path, 'existing image');
    $user->forceFill(['gift_voucher_background_mode' => 'custom_upload', 'gift_voucher_background_path' => $path])->save();
    expect(fn () => GiftVoucherBackgroundService::storeGlobalBackground($user, UploadedFile::fake()->createWithContent('broken.png', 'not an image')))
        ->toThrow(ValidationException::class);
    expect(Storage::disk('public')->get($path))->toBe('existing image')
        ->and($user->fresh()->gift_voucher_background_path)->toBe($path);
});

test('a storage failure leaves the old image and saved settings intact', function () {
    Storage::fake('public');
    $oldDisk = Storage::disk('public');
    $user = voucherCompatibilityUser();
    $path = 'gift-vouchers/backgrounds/'.$user->id.'/background.jpg';
    $oldDisk->put($path, 'existing image');
    $user->forceFill(['gift_voucher_background_mode' => 'custom_upload', 'gift_voucher_background_path' => $path,
        'gift_voucher_online_enabled' => true])->save();
    $before = $user->fresh()->getRawOriginal();
    $brokenDisk = Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
    $brokenDisk->shouldReceive('put')->once()->andReturn(false);
    Storage::shouldReceive('disk')->with('public')->andReturn($brokenDisk);
    $this->actingAs($user)->post(route('pro.gift-vouchers.settings.update'), [
        'gift_voucher_background_mode' => 'custom_upload', 'gift_voucher_background' => UploadedFile::fake()->image('new.png'),
    ])->assertSessionHasErrors('gift_voucher_background');
    expect($user->fresh()->getRawOriginal())->toBe($before)
        ->and($oldDisk->get($path))->toBe('existing image');
});

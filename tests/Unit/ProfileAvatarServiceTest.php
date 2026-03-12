<?php

use App\Services\ProfileAvatarService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class);

test('profile avatar service creates expected square webp variants', function () {
    Storage::fake('public');

    $uploaded = UploadedFile::fake()->image('avatar.jpg', 1600, 900);

    $path = ProfileAvatarService::store($uploaded, 501);

    expect($path)->toBe('avatars/501/avatar-320.webp');

    foreach ([320, 640, 1024] as $size) {
        $variantPath = "avatars/501/avatar-{$size}.webp";
        Storage::disk('public')->assertExists($variantPath);

        $imageData = Storage::disk('public')->get($variantPath);
        $dimensions = getimagesizefromstring($imageData);

        expect($dimensions)->not->toBeFalse();
        expect($dimensions[0])->toBe($size);
        expect($dimensions[1])->toBe($size);
    }
});

test('profile avatar service accepts crop payload without breaking variant generation', function () {
    Storage::fake('public');

    $uploaded = UploadedFile::fake()->image('avatar.jpg', 1400, 1200);
    $crop = json_encode([
        'x' => 150,
        'y' => 120,
        'width' => 800,
        'height' => 800,
        'image_width' => 1400,
        'image_height' => 1200,
    ]);

    $path = ProfileAvatarService::store($uploaded, 777, $crop);

    expect($path)->toBe('avatars/777/avatar-320.webp');
    Storage::disk('public')->assertExists('avatars/777/avatar-320.webp');
    Storage::disk('public')->assertExists('avatars/777/avatar-640.webp');
    Storage::disk('public')->assertExists('avatars/777/avatar-1024.webp');
});

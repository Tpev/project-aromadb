<?php

use App\Models\PackProduct;
use App\Models\Product;
use App\Models\User;
use App\Services\PackPhotoService;
use App\Services\StripeAccountGuard;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->therapist = User::factory()->create([
        'is_therapist' => true, 'license_status' => 'active', 'license_product' => 'new_pro_mensuelle', 'slug' => 'pack-photo-demo',
    ]);
    $this->product = Product::create(['user_id' => $this->therapist->id, 'name' => 'Séance', 'price' => 60, 'tax_rate' => 0]);
    $this->payload = [
        'name' => 'Pack photo', 'description' => 'Trois séances', 'price' => 165, 'tax_rate' => 0,
        'is_active' => 1, 'visible_in_portal' => 1, 'price_visible_in_portal' => 1, 'installments_enabled' => 0,
        'items' => [['product_id' => $this->product->id, 'quantity' => 3]],
    ];
    $this->actingAs($this->therapist);
});

test('both editors accept supported photo formats and store only optimized variants', function (string $routes, string $format) {
    $this->get(route($routes.'.create'))->assertOk()->assertSee('multipart/form-data', false)
        ->assertSee('2 Mo maximum')->assertSee('1 200 × 675')->assertSee('4 096 pixels');
    $this->post(route($routes.'.store'), $this->payload + ['photo' => UploadedFile::fake()->image('portrait.'.$format, 600, 900)])
        ->assertSessionHasNoErrors()->assertRedirect();
    $pack = PackProduct::sole();
    expect($pack->photo_path)->toStartWith('pack-photos/'.$this->therapist->id.'/')->toEndWith('.webp');
    $disk = Storage::disk('public');
    expect($disk->allFiles())->toHaveCount(2);
    foreach ([$pack->photo_path => [1200, 675], PackPhotoService::thumbnailPath($pack->photo_path) => [640, 360]] as $path => $dimensions) {
        $info = getimagesizefromstring($disk->get($path));
        expect([$info[0], $info[1]])->toBe($dimensions)->and($info['mime'])->toBe('image/webp');
    }
    expect($pack->items->sole()->quantity)->toBe(3)->and((float) $pack->price)->toBe(165.0);
    foreach (['edit', 'show', 'index'] as $page) {
        $this->get(route($routes.'.'.$page, $page === 'index' ? [] : $pack))->assertOk()->assertSee('/storage/pack-photos/', false);
    }
})->with(['pack-products', 'mobile.packs'])->with(['jpg', 'png', 'webp']);

test('legacy packs work without photos and old forms preserve an existing photo', function (string $routes) {
    $this->post(route($routes.'.store'), $this->payload)->assertSessionHasNoErrors();
    $pack = PackProduct::sole();
    expect($pack->photo_path)->toBeNull()->and($pack->photo_url)->toBeNull();
    $this->get(route($routes.'.show', $pack))->assertOk()->assertDontSee('data-pack-photo', false);
    $this->put(route($routes.'.update', $pack), $this->payload)->assertSessionHasNoErrors();
    expect($pack->fresh()->photo_path)->toBeNull();

    $this->put(route($routes.'.update', $pack), $this->payload + ['photo' => UploadedFile::fake()->image('photo.jpg')])->assertSessionHasNoErrors();
    $path = $pack->fresh()->photo_path;
    // Arbitrary paths from older or forged forms must never overwrite a stored upload.
    $this->put(route($routes.'.update', $pack), $this->payload + ['photo_path' => 'someone-else.webp'])->assertSessionHasNoErrors();
    $this->put(route($routes.'.update', $pack), $this->payload + ['photo' => null, 'remove_photo' => '0'])->assertSessionHasNoErrors();
    expect($pack->fresh()->photo_path)->toBe($path);
    Storage::disk('public')->assertExists($path);
})->with(['pack-products', 'mobile.packs']);

test('replacement removal and pack deletion clean up variants after successful saves', function (string $routes) {
    $this->post(route($routes.'.store'), $this->payload + ['photo' => UploadedFile::fake()->image('first.jpg')])->assertSessionHasNoErrors();
    $pack = PackProduct::sole();
    $old = $pack->photo_path;
    $this->put(route($routes.'.update', $pack), $this->payload + ['photo' => UploadedFile::fake()->image('second.png')])->assertSessionHasNoErrors();
    $replacement = $pack->fresh()->photo_path;
    expect($replacement)->not->toBe($old);
    Storage::disk('public')->assertMissing([$old, PackPhotoService::thumbnailPath($old)]);
    $this->put(route($routes.'.update', $pack), $this->payload + ['remove_photo' => '1'])->assertSessionHasNoErrors();
    expect($pack->fresh()->photo_path)->toBeNull()->and(Storage::disk('public')->allFiles())->toBeEmpty();
    $this->put(route($routes.'.update', $pack), $this->payload + ['photo' => UploadedFile::fake()->image('third.webp')])->assertSessionHasNoErrors();
    $this->delete(route($routes.'.destroy', $pack))->assertRedirect();
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
})->with(['pack-products', 'mobile.packs']);

test('invalid uploads never replace the previous photo or change pack data', function (string $routes, string $invalid) {
    $this->post(route($routes.'.store'), $this->payload + ['photo' => UploadedFile::fake()->image('original.jpg')]);
    $pack = PackProduct::sole();
    $before = $pack->getAttributes();
    $upload = match ($invalid) {
        'large' => UploadedFile::fake()->image('large.jpg')->size(2049),
        'dimensions' => UploadedFile::fake()->image('wide.png', 4097, 10),
        'gif' => UploadedFile::fake()->image('animation.gif'),
        'svg' => UploadedFile::fake()->createWithContent('script.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        'heic' => UploadedFile::fake()->create('phone.heic', 10, 'image/heic'),
        'corrupt' => UploadedFile::fake()->createWithContent('broken.jpg', "\xff\xd8\xff\xe0invalid"),
    };
    $this->put(route($routes.'.update', $pack), array_replace($this->payload, ['photo' => $upload, 'price' => 999]))->assertSessionHasErrors('photo');
    expect($pack->fresh()->getAttributes())->toBe($before)->and(Storage::disk('public')->allFiles())->toHaveCount(2);
    $this->get(route($routes.'.edit', $pack))->assertOk();
})->with(['pack-products', 'mobile.packs'])->with(['large', 'dimensions', 'gif', 'svg', 'heic', 'corrupt']);

test('ownership and other form errors are checked before any file is written', function (string $routes) {
    $this->post(route($routes.'.store'), $this->payload)->assertSessionHasNoErrors();
    $pack = PackProduct::sole();
    $this->put(route($routes.'.update', $pack), array_replace($this->payload, ['name' => '', 'photo' => UploadedFile::fake()->image('valid.jpg')]))
        ->assertSessionHasErrors('name');
    $this->put(route($routes.'.update', $pack), $this->payload + ['photo' => UploadedFile::fake()->image('valid.jpg'), 'remove_photo' => 1])
        ->assertSessionHasErrors('photo');
    $other = User::factory()->create(['is_therapist' => true, 'license_status' => 'active']);
    $this->actingAs($other)->put(route($routes.'.update', $pack), $this->payload + ['photo' => UploadedFile::fake()->image('valid.jpg')])->assertForbidden();
    $this->get(route($routes.'.edit', $pack))->assertForbidden();
    expect(Storage::disk('public')->allFiles())->toBeEmpty()->and($pack->fresh()->photo_path)->toBeNull();
})->with(['pack-products', 'mobile.packs']);

test('failed database saves remove the new upload and preserve the previous photo', function () {
    $this->post(route('pack-products.store'), $this->payload + ['photo' => UploadedFile::fake()->image('old.jpg')]);
    $pack = PackProduct::sole();
    $old = $pack->photo_path;
    expect(fn () => app(PackPhotoService::class)->save($this->therapist->id, $pack, ['photo' => UploadedFile::fake()->image('new.jpg')], function ($attributes) use ($pack) {
        $pack->update($attributes);
        throw new RuntimeException('Simulated failed database save');
    }))->toThrow(RuntimeException::class, 'Simulated failed database save');
    expect($pack->fresh()->photo_path)->toBe($old)->and(Storage::disk('public')->allFiles())->toHaveCount(2);
    Storage::disk('public')->assertExists([$old, PackPhotoService::thumbnailPath($old)]);
});

test('a failed thumbnail write cleans up partial files without creating a pack', function () {
    $realDisk = Storage::disk('public');
    $disk = Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
    $disk->shouldReceive('put')->twice()->andReturnUsing(fn ($path, $contents) => str_ends_with($path, '-small.webp') ? false : $realDisk->put($path, $contents));
    $disk->shouldReceive('delete')->once()->andReturnUsing(fn ($paths) => $realDisk->delete($paths));
    Storage::shouldReceive('disk')->with('public')->andReturn($disk);
    $this->post(route('pack-products.store'), $this->payload + ['photo' => UploadedFile::fake()->image('new.jpg')])->assertSessionHasErrors('photo');
    expect($realDisk->allFiles())->toBeEmpty();
    $this->assertDatabaseCount('pack_products', 0);
});

test('photo URLs and cleanup never use a path belonging to another account or another feature', function () {
    $path = 'pack-photos/999/'.\Illuminate\Support\Str::uuid().'.webp';
    Storage::disk('public')->put($path, 'foreign');
    $pack = PackProduct::create(['user_id' => $this->therapist->id, 'name' => 'Legacy', 'price' => 0, 'tax_rate' => 0, 'photo_path' => $path]);
    expect($pack->photo_url)->toBeNull()->and($pack->photo_thumbnail_url)->toBeNull();
    $pack->delete();
    Storage::disk('public')->assertExists($path);
    app(PackPhotoService::class)->delete('../other.webp', $this->therapist->id);
});

test('public cards and public and private purchase pages display the photo while hidden packs stay hidden', function () {
    $this->mock(StripeAccountGuard::class)->shouldReceive('status')->andReturn(['ready' => false]);
    $this->post(route('pack-products.store'), $this->payload + ['photo' => UploadedFile::fake()->image('cover.jpg')])->assertSessionHasNoErrors();
    $pack = PackProduct::sole();
    $this->get(route('therapist.show', $this->therapist->slug))->assertOk()->assertSee($pack->photo_url, false)->assertSee('loading="lazy"', false);
    $public = route('public.checkout.show', ['slug' => $this->therapist->slug, 'item' => 'pack:'.$pack->id]);
    $this->get($public)->assertOk()->assertSee($pack->photo_url, false);
    $pack->update(['visible_in_portal' => false, 'private_checkout_enabled' => true]);
    $this->get(route('therapist.show', $this->therapist->slug))->assertOk()->assertDontSee($pack->photo_url, false);
    $this->get($public)->assertNotFound();
    $this->get(route('packs.private.show', $pack->private_checkout_token))->assertOk()->assertSee($pack->photo_url, false);
    $this->assertDatabaseCount('pack_purchases', 0);
});

<?php

use App\Models\User;

test('authenticated user can save konva branding settings', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'konva_branding_settings' => null,
    ]);

    $payload = [
        'preset' => 'zen_olive',
        'font_heading' => 'cormorant',
        'font_body' => 'montserrat',
        'color_primary' => '#A7B88A',
        'color_secondary' => '#6B4A3A',
        'color_accent' => '#E9B07A',
        'color_background' => '#F6F2EB',
        'color_text' => '#3F2B22',
    ];

    $this->actingAs($user)
        ->postJson(route('konva.branding.update'), $payload)
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('settings.fonts.heading', 'cormorant')
        ->assertJsonPath('settings.colors.primary', '#A7B88A');

    $user->refresh();

    expect($user->konva_branding_settings)->toBeArray()
        ->and($user->konva_branding_settings['fonts']['body'] ?? null)->toBe('montserrat')
        ->and($user->konva_branding_settings['colors']['accent'] ?? null)->toBe('#E9B07A');
});

test('authenticated user can open konva editor', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
    ]);

    $this->actingAs($user)
        ->get(route('konva.editor'))
        ->assertOk()
        ->assertSee('Studio visuel', false)
        ->assertSee('Etape 3 - Style', false)
        ->assertSee('Branding', false);
});

test('guest cannot save konva branding settings', function () {
    $this->postJson(route('konva.branding.update'), [
        'preset' => 'zen_olive',
        'font_heading' => 'cormorant',
        'font_body' => 'montserrat',
        'color_primary' => '#A7B88A',
        'color_secondary' => '#6B4A3A',
        'color_accent' => '#E9B07A',
        'color_background' => '#F6F2EB',
        'color_text' => '#3F2B22',
    ])->assertStatus(401);
});

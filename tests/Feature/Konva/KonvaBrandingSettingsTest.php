<?php

use App\Models\User;

test('authenticated user can save konva branding settings', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'konva_branding_settings' => null,
    ]);

    $payload = [
        'preset' => 'forest_clarity',
        'font_heading' => 'montserrat',
        'font_body' => 'manrope',
        'color_primary' => '#166534',
        'color_secondary' => '#15803D',
        'color_accent' => '#86EFAC',
        'color_background' => '#F0FDF4',
        'color_text' => '#052E16',
    ];

    $this->actingAs($user)
        ->postJson(route('konva.branding.update'), $payload)
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('settings.fonts.heading', 'montserrat')
        ->assertJsonPath('settings.colors.primary', '#166534');

    $user->refresh();

    expect($user->konva_branding_settings)->toBeArray()
        ->and($user->konva_branding_settings['fonts']['body'] ?? null)->toBe('manrope')
        ->and($user->konva_branding_settings['colors']['accent'] ?? null)->toBe('#86EFAC');
});

test('authenticated user can open konva editor', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
    ]);

    $this->actingAs($user)
        ->get(route('konva.editor'))
        ->assertOk()
        ->assertSee('Studio visuel', false)
        ->assertSee('Branding auto', false)
        ->assertSee('Sources intelligentes', false);
});

test('guest cannot save konva branding settings', function () {
    $this->postJson(route('konva.branding.update'), [
        'preset' => 'zen_olive',
        'font_heading' => 'poppins',
        'font_body' => 'inter',
        'color_primary' => '#647A0B',
        'color_secondary' => '#854F38',
        'color_accent' => '#D4A373',
        'color_background' => '#F8F9F5',
        'color_text' => '#1F2937',
    ])->assertStatus(401);
});

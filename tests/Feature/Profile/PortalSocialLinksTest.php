<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('company information clearly exposes portal social link settings', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
    ]);

    $this->actingAs($therapist)
        ->get(route('profile.editCompanyInfo'))
        ->assertOk()
        ->assertSee('Réseaux sociaux du portail')
        ->assertSee('name="facebook_url"', false)
        ->assertSee('name="instagram_url"', false)
        ->assertSee('name="linkedin_url"', false)
        ->assertSee('Laissez un champ vide pour masquer son bouton.');
});

test('practitioners can configure and clear their portal social links', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
    ]);

    $this->actingAs($therapist)
        ->put(route('profile.updateCompanyInfo'), [
            'facebook_url' => 'https://www.facebook.com/cabinet.test',
            'instagram_url' => 'https://www.instagram.com/cabinet.test',
            'linkedin_url' => 'https://www.linkedin.com/in/cabinet-test',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.editCompanyInfo'));

    expect($therapist->fresh())
        ->facebook_url->toBe('https://www.facebook.com/cabinet.test')
        ->instagram_url->toBe('https://www.instagram.com/cabinet.test')
        ->linkedin_url->toBe('https://www.linkedin.com/in/cabinet-test');

    $this->actingAs($therapist)
        ->put(route('profile.updateCompanyInfo'), [
            'instagram_url' => '',
        ])
        ->assertSessionHasNoErrors();

    expect($therapist->fresh())
        ->facebook_url->toBe('https://www.facebook.com/cabinet.test')
        ->instagram_url->toBeNull()
        ->linkedin_url->toBe('https://www.linkedin.com/in/cabinet-test');
});

test('portal social links only accept http or https urls', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
    ]);

    $this->actingAs($therapist)
        ->from(route('profile.editCompanyInfo'))
        ->put(route('profile.updateCompanyInfo'), [
            'facebook_url' => 'javascript:alert(1)',
        ])
        ->assertRedirect(route('profile.editCompanyInfo'))
        ->assertSessionHasErrors('facebook_url');

    expect($therapist->fresh()->facebook_url)->toBeNull();
});

test('public portal displays buttons only for configured valid social links', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'cabinet-reseaux-sociaux',
        'company_name' => 'Cabinet Réseaux Sociaux',
        'facebook_url' => 'https://www.facebook.com/cabinet.reseaux',
        'instagram_url' => 'https://www.instagram.com/cabinet.reseaux',
        'linkedin_url' => null,
    ]);

    $this->get(route('therapist.show', $therapist->slug))
        ->assertOk()
        ->assertSee('Retrouvez-moi sur')
        ->assertSee('https://www.facebook.com/cabinet.reseaux', false)
        ->assertSee('https://www.instagram.com/cabinet.reseaux', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertDontSee('fab fa-linkedin-in', false);
});

test('public portal hides the social section when no link is configured', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'cabinet-sans-reseaux-sociaux',
        'company_name' => 'Cabinet sans réseaux sociaux',
    ]);

    $this->get(route('therapist.show', $therapist->slug))
        ->assertOk()
        ->assertDontSee('Retrouvez-moi sur');
});

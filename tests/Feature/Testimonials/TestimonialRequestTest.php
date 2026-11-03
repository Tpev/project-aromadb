<?php

use App\Mail\TestimonialRequestMail;
use App\Models\ClientProfile;
use App\Models\Testimonial;
use App\Models\TestimonialRequest;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

function reviewTherapist(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
        'company_email' => 'cabinet@example.test',
        'slug' => 'review-therapist-'.Str::lower(Str::random(10)),
    ], $attributes));
}

function reviewClient(User $therapist, array $attributes = []): ClientProfile
{
    return ClientProfile::create(array_merge([
        'user_id' => $therapist->id,
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'email' => 'alice@example.test',
    ], $attributes));
}

function existingReviewRequest(ClientProfile $client, array $attributes = []): TestimonialRequest
{
    return TestimonialRequest::create(array_merge([
        'therapist_id' => $client->user_id,
        'client_profile_id' => $client->id,
        'token' => Str::random(32),
        'status' => 'pending',
    ], $attributes));
}

test('old callers still send the original Olithea email and clients can submit it', function () {
    Mail::fake();
    $therapist = reviewTherapist();
    $client = reviewClient($therapist);

    $this->actingAs($therapist)->post(route('testimonial.request', $client))
        ->assertSessionHasNoErrors();

    $request = TestimonialRequest::sole();
    expect($request->destination)->toBe('internal')->and($request->google_review_url)->toBeNull();
    Mail::assertSent(TestimonialRequestMail::class, function ($mail) use ($request, $client) {
        $mail->assertSeeInHtml(route('testimonials.submit', $request->token));
        $mail->assertSeeInHtml('Soumettre mon Témoignage');
        $mail->assertDontSeeInHtml('Laisser un avis sur Google');

        return $mail->hasTo($client->email) && $mail->hasReplyTo('cabinet@example.test');
    });

    auth()->logout();
    $this->get(route('testimonials.submit', $request->token))->assertOk();
    $this->post(route('testimonials.submit.post', $request->token), ['testimonial' => 'Merci pour votre accompagnement.'])->assertOk();
    expect($request->fresh()->status)->toBe('completed');
    $this->assertDatabaseHas('testimonials', ['testimonial_request_id' => $request->id]);
});

test('legacy pending tokens with no destination still accept testimonials', function () {
    $client = reviewClient(reviewTherapist());
    $request = existingReviewRequest($client, ['destination' => null]);

    $this->get(route('testimonials.submit', $request->token))->assertOk();
    $this->post(route('testimonials.submit.post', $request->token), ['testimonial' => 'Merci pour votre écoute et votre disponibilité.'])->assertOk();
    $this->post(route('testimonials.submit.post', $request->token), ['testimonial' => 'Une autre réponse ne doit pas être enregistrée.'])->assertNotFound();
    expect(Testimonial::count())->toBe(1)->and($request->fresh()->status)->toBe('completed');
});

test('Google requests coexist with old Olithea requests and keep a fixed URL', function (string $status) {
    Mail::fake();
    $therapist = reviewTherapist();
    $client = reviewClient($therapist);
    $legacy = existingReviewRequest($client, ['destination' => null, 'status' => $status]);
    $url = 'https://g.page/r/EXAMPLE123/review';

    $this->actingAs($therapist)->post(route('testimonial.request', $client), [
        'destination' => 'google', 'google_review_url' => $url, 'remember_google_review_url' => '1',
    ])->assertSessionHasNoErrors()->assertSessionHas('success', 'Demande d’avis Google envoyée avec succès.');

    $google = TestimonialRequest::where('destination', 'google')->sole();
    expect($legacy->fresh()->status)->toBe($status)
        ->and($legacy->fresh()->destination)->toBeNull()
        ->and($google->status)->toBe('pending')
        ->and($therapist->fresh()->google_review_url)->toBe($url)
        ->and(TestimonialRequest::count())->toBe(2);

    $therapist->update(['google_review_url' => 'https://g.page/r/CHANGED/review']);
    Mail::assertSent(TestimonialRequestMail::class, function ($mail) use ($url, $google) {
        $mail->assertSeeInHtml($url);
        $mail->assertSeeInHtml('Laisser un avis sur Google');
        $mail->assertDontSeeInHtml(route('testimonials.submit', $google->token));

        return $mail->hasReplyTo('cabinet@example.test') && $mail->subject === 'Votre avis sur Google';
    });
    expect($google->fresh()->google_review_url)->toBe($url);
    $this->get(route('testimonials.submit', $google->token))->assertNotFound();
    $this->post(route('testimonials.submit.post', $google->token), ['testimonial' => 'Cannot submit a Google request here.'])->assertNotFound();
    $this->assertDatabaseCount('testimonials', 0);
})->with(['pending', 'completed']);

test('Google links can be used without saving and internal requests ignore Google fields', function () {
    Mail::fake();
    $therapist = reviewTherapist(['google_review_url' => 'https://g.page/r/SAVED/review']);
    $client = reviewClient($therapist);

    $this->actingAs($therapist)->post(route('testimonial.request', $client), [
        'destination' => 'google', 'google_review_url' => 'https://g.page/r/ONCE/review', 'remember_google_review_url' => '0',
    ])->assertSessionHasNoErrors();
    $this->post(route('testimonial.request', $client), [
        'destination' => 'internal', 'google_review_url' => 'javascript:alert(1)', 'remember_google_review_url' => '1',
    ])->assertSessionHasNoErrors();

    expect($therapist->fresh()->google_review_url)->toBe('https://g.page/r/SAVED/review')
        ->and(TestimonialRequest::where('destination', 'internal')->sole()->google_review_url)->toBeNull();
    Mail::assertSentCount(2);
});

test('repeated requests do not send another email for the same destination', function (string $destination) {
    Mail::fake();
    $therapist = reviewTherapist();
    $client = reviewClient($therapist);
    $payload = ['destination' => $destination, 'google_review_url' => 'https://g.page/r/DEMO/review'];

    $this->actingAs($therapist)->post(route('testimonial.request', $client), $payload)->assertSessionHasNoErrors();
    $this->post(route('testimonial.request', $client), $payload)->assertSessionHasErrorsIn('reviewRequest', 'destination');
    Mail::assertSentCount(1);
    $this->assertDatabaseCount('testimonial_requests', 1);
})->with(['internal', 'google']);

test('legacy requests also prevent duplicate Olithea sends', function () {
    Mail::fake();
    $therapist = reviewTherapist();
    $client = reviewClient($therapist);
    existingReviewRequest($client, ['destination' => null]);

    $this->actingAs($therapist)->post(route('testimonial.request', $client))
        ->assertSessionHasErrorsIn('reviewRequest', 'destination');
    Mail::assertNothingSent();
});

test('sending enforces ownership and therapist status', function (bool $isTherapist, bool $ownsClient) {
    Mail::fake();
    $user = reviewTherapist(['is_therapist' => $isTherapist]);
    $client = reviewClient($ownsClient ? $user : reviewTherapist());

    $this->actingAs($user)->post(route('testimonial.request', $client))->assertForbidden();
    Mail::assertNothingSent();
    $this->assertDatabaseCount('testimonial_requests', 0);
})->with([[true, false], [false, true]]);

test('review access preserves legacy and eligible licenses', function (?string $license, bool $allowed) {
    Mail::fake();
    $therapist = reviewTherapist(['license_product' => $license]);
    $client = reviewClient($therapist);
    $response = $this->actingAs($therapist)->post(route('testimonial.request', $client));

    if ($allowed) {
        $response->assertSessionHasNoErrors();
        Mail::assertSentCount(1);
    } else {
        $response->assertForbidden();
        Mail::assertNothingSent();
    }
})->with([[null, true], ['old_pro', true], ['new_trial', true], ['new_pro_annuelle', true], ['new_premium_mensuelle', true], ['new_free', false], ['new_starter_mensuelle', false]]);

test('missing or invalid recipient email does not create a request', function (?string $email) {
    Mail::fake();
    $therapist = reviewTherapist();
    $client = reviewClient($therapist, ['email' => $email]);

    $this->actingAs($therapist)->post(route('testimonial.request', $client))->assertSessionHasErrorsIn('reviewRequest', 'email');
    Mail::assertNothingSent();
    $this->assertDatabaseCount('testimonial_requests', 0);
})->with([null, 'invalid-address']);

test('invalid Google destinations are rejected without storing or sending anything', function ($url) {
    Mail::fake();
    $therapist = reviewTherapist();
    $client = reviewClient($therapist);

    $this->actingAs($therapist)->post(route('testimonial.request', $client), [
        'destination' => 'google', 'google_review_url' => $url, 'remember_google_review_url' => 1,
    ])->assertSessionHasErrorsIn('reviewRequest', 'google_review_url');

    Mail::assertNothingSent();
    $this->assertDatabaseCount('testimonial_requests', 0);
    expect($therapist->fresh()->google_review_url)->toBeNull();
})->with([null, 'javascript:alert(1)', 'http://g.page/r/DEMO/review', 'https://example.com/review', 'https://g.page.evil.test/r/DEMO/review', 'https://g.page@evil.test/review', 'https://evil@g.page/r/DEMO/review', 'https://www.google.com/url?q=https://evil.test', 'https://www.google.com/maps', 'https://g.page/r/DEMO', 'https://search.google.com/local/writereview', 'https://search.google.com/local/writereview?placeid[]=x', 'https://g.page:8443/r/DEMO/review', ['invalid']]);

test('supported Google review URLs are accepted', function (string $url) {
    Mail::fake();
    $therapist = reviewTherapist();
    $client = reviewClient($therapist);

    $this->actingAs($therapist)->post(route('testimonial.request', $client), [
        'destination' => 'google', 'google_review_url' => $url,
    ])->assertSessionHasNoErrors();
    Mail::assertSentCount(1);
})->with(['https://g.page/r/EXAMPLE-123/review', 'https://g.page/cabinet-demo/review?rc', 'https://search.google.com/local/writereview?placeid=ChIJ_example', 'https://www.google.com/local/writereview?placeid=ChIJ_example', 'https://maps.app.goo.gl/Example123', 'https://goo.gl/maps/Example123']);

test('mail failure rolls back the request and remembered link and allows retry', function () {
    $therapist = reviewTherapist(['google_review_url' => 'https://g.page/r/SAVED/review']);
    $client = reviewClient($therapist);
    Mail::shouldReceive('to')->once()->with($client->email)->andReturnSelf();
    Mail::shouldReceive('send')->once()->andThrow(new RuntimeException('Transport unavailable'));
    $payload = ['destination' => 'google', 'google_review_url' => 'https://g.page/r/NEW/review', 'remember_google_review_url' => 1];

    $this->actingAs($therapist)->post(route('testimonial.request', $client), $payload)
        ->assertSessionHasErrorsIn('reviewRequest', 'send')
        ->assertSessionMissing('success');
    $this->assertDatabaseCount('testimonial_requests', 0);
    expect($therapist->fresh()->google_review_url)->toBe('https://g.page/r/SAVED/review');

    Mail::fake();
    $this->post(route('testimonial.request', $client), $payload)->assertSessionHasNoErrors();
    Mail::assertSentCount(1);
});

test('desktop and mobile show both destinations without losing legacy history', function (string $route) {
    $therapist = reviewTherapist();
    $client = reviewClient($therapist);
    $legacy = existingReviewRequest($client, ['destination' => null, 'status' => 'completed']);

    $this->actingAs($therapist)->get(route($route, $client))->assertOk()
        ->assertSee('Témoignage reçu le')
        ->assertSee('Demander un témoignage')
        ->assertSee('Où trouver ce lien ?')
        ->assertSee('Avis Google');

    existingReviewRequest($client, ['destination' => 'google', 'google_review_url' => 'https://g.page/r/DEMO/review']);
    $this->get(route($route, $client))->assertOk()
        ->assertSee('Témoignage reçu le')
        ->assertSee('Demande d’avis envoyée le')
        ->assertSee('Olithea ne peut pas confirmer')
        ->assertDontSee('Envoyer la demande');
    expect($legacy->fresh()->status)->toBe('completed');
})->with(['client_profiles.show', 'mobile.clients.show']);

test('invalid destination does not fall back to an Olithea request', function () {
    Mail::fake();
    $therapist = reviewTherapist();
    $client = reviewClient($therapist);
    $this->actingAs($therapist)->post(route('testimonial.request', $client), ['destination' => 'unknown'])
        ->assertSessionHasErrorsIn('reviewRequest', 'destination');
    Mail::assertNothingSent();
});

test('additive migration preserves existing users requests testimonials and tokens', function () {
    $therapist = reviewTherapist();
    $client = reviewClient($therapist);
    $pending = existingReviewRequest($client);
    $completed = existingReviewRequest($client, ['status' => 'completed']);
    $testimonial = Testimonial::create([
        'testimonial_request_id' => $completed->id,
        'therapist_id' => $therapist->id,
        'client_profile_id' => $client->id,
        'testimonial' => 'Un témoignage conservé après la migration.',
    ]);
    $imported = Testimonial::create([
        'therapist_id' => $therapist->id,
        'source' => 'google',
        'external_review_id' => 'existing-google-review',
        'testimonial' => 'Un avis Google déjà importé.',
        'rating' => 5,
    ]);

    // Restore the previous schema inside this isolated SQLite test transaction.
    $migration = require database_path('migrations/2026_11_03_100000_add_destination_to_testimonial_requests.php');
    $migration->down();
    $previousUser = $therapist->fresh()->getAttributes();
    $previousRequest = $pending->fresh()->getAttributes();
    $previousTestimonial = $testimonial->fresh()->getAttributes();
    $previousImported = $imported->fresh()->getAttributes();
    $migration->up();

    expect($therapist->fresh()->getAttributes())->toMatchArray($previousUser)
        ->and($pending->fresh()->getAttributes())->toMatchArray($previousRequest)
        ->and($pending->fresh()->destination)->toBe('internal')
        ->and($pending->fresh()->google_review_url)->toBeNull()
        ->and($completed->fresh()->status)->toBe('completed')
        ->and($testimonial->fresh()->getAttributes())->toBe($previousTestimonial)
        ->and($imported->fresh()->getAttributes())->toBe($previousImported);

    $this->get(route('testimonials.submit', $pending->token))->assertOk();
    $this->post(route('testimonials.submit.post', $pending->token), ['testimonial' => 'Ce lien envoyé avant la migration fonctionne toujours.'])->assertOk();
    $this->assertDatabaseCount('testimonials', 3);
});

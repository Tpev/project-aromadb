<?php

use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function makeTherapist(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'company_name' => 'Therapeute Test',
        'slug' => 'therapeute-' . Str::lower(Str::random(8)),
    ], $overrides));
}

function makeEvent(User $therapist, array $overrides = []): Event
{
    return Event::create(array_merge([
        'user_id' => $therapist->id,
        'name' => 'Atelier Test',
        'description' => 'Description test',
        'start_date_time' => now()->addDays(7),
        'duration' => 60,
        'booking_required' => true,
        'limited_spot' => false,
        'number_of_spot' => null,
        'associated_product' => null,
        'image' => null,
        'showOnPortail' => true,
        'location' => 'Paris',
    ], $overrides));
}

test('public therapist share link points to public info page when booking is not required', function () {
    $therapist = makeTherapist();
    $event = makeEvent($therapist, [
        'booking_required' => false,
    ]);

    $response = $this->get(route('therapist.show', ['slug' => $therapist->slug]));
    $response->assertOk();

    $content = $response->getContent();
    $infoUrl = route('events.public.show', $event);

    expect($content)->toContain('https://www.facebook.com/sharer/sharer.php?u=' . urlencode($infoUrl));
});

test('public therapist share link points to reservation page when booking is required', function () {
    $therapist = makeTherapist();
    $event = makeEvent($therapist, [
        'booking_required' => true,
    ]);

    $response = $this->get(route('therapist.show', ['slug' => $therapist->slug]));
    $response->assertOk();

    $content = $response->getContent();
    $reserveUrl = url("/events/{$event->id}/reserve");

    expect($content)->toContain('https://www.facebook.com/sharer/sharer.php?u=' . urlencode($reserveUrl));
});

test('event backoffice share link points to reservation page when booking is not required', function () {
    $therapist = makeTherapist();
    $event = makeEvent($therapist, [
        'booking_required' => false,
    ]);

    $response = $this->actingAs($therapist)->get(route('events.show', $event->id));
    $response->assertOk();

    $content = $response->getContent();
    $reserveUrl = url("/events/{$event->id}/reserve");

    expect($content)->toContain($reserveUrl);
    expect($content)->toContain('https://www.facebook.com/sharer/sharer.php?u=' . urlencode($reserveUrl));
});

test('reservation page redirects to informational mode when booking is not required', function () {
    $therapist = makeTherapist();
    $event = makeEvent($therapist, [
        'booking_required' => false,
    ]);

    $response = $this->get(route('events.reserve.create', $event->id));
    $response->assertRedirect(route('events.public.show', $event));

    $this->get(route('events.public.show', $event))
        ->assertOk()
        ->assertSee('Sans')
        ->assertSee('en ligne');
});

test('reservation page renders full-event informational mode when event is full', function () {
    $therapist = makeTherapist();
    $event = makeEvent($therapist, [
        'booking_required' => true,
        'limited_spot' => true,
        'number_of_spot' => 1,
    ]);

    Reservation::create([
        'event_id' => $event->id,
        'full_name' => 'Client Test',
        'email' => 'client@example.com',
        'phone' => '0600000000',
        'status' => 'confirmed',
    ]);

    $response = $this->get(route('events.reserve.create', $event->id));

    $response->assertOk();
    $response->assertSee('Informations de participation');
    $response->assertSee('complet');
    $response->assertDontSee('action="' . route('events.reserve.store', $event->id) . '"', false);
});

test('reservation page exposes the event image as its complete social preview', function () {
    Storage::fake('public');

    $therapist = makeTherapist();
    $image = UploadedFile::fake()->image('atelier.jpg', 1200, 630)->store('events', 'public');
    $event = makeEvent($therapist, ['image' => $image]);

    $response = $this->get(route('events.reserve.create', $event));

    $response->assertOk();
    $response->assertSee('property="og:image" content="'.asset('storage/'.$image).'?v=', false);
    $response->assertSee('property="og:image:secure_url"', false);
    $response->assertSee('property="og:image:type" content="image/jpeg"', false);
    $response->assertSee('property="og:image:width" content="1200"', false);
    $response->assertSee('property="og:image:height" content="630"', false);
});

test('informational event page keeps event social metadata after the reserve redirect', function () {
    Storage::fake('public');

    $therapist = makeTherapist();
    $image = UploadedFile::fake()->image('conference.png', 1200, 630)->store('events', 'public');
    $event = makeEvent($therapist, [
        'booking_required' => false,
        'image' => $image,
    ]);

    $this->get(route('events.public.show', $event))
        ->assertOk()
        ->assertSee('property="og:title" content="Atelier Test"', false)
        ->assertSee('property="og:image" content="'.asset('storage/'.$image).'?v=', false)
        ->assertSee('property="og:image:type" content="image/png"', false);
});

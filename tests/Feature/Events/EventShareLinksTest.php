<?php

use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
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

test('public therapist share link points to public event anchor when booking is not required', function () {
    $therapist = makeTherapist();
    $event = makeEvent($therapist, [
        'booking_required' => false,
    ]);

    $response = $this->get(route('therapist.show', ['slug' => $therapist->slug]));
    $response->assertOk();

    $content = $response->getContent();
    $publicAnchorUrl = route('therapist.show', ['slug' => $therapist->slug]) . "#event-{$event->id}";

    expect($content)->toContain('https://www.facebook.com/sharer/sharer.php?u=' . urlencode($publicAnchorUrl));
    expect($content)->not->toContain(url("/events/{$event->id}/reserve"));
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

test('event backoffice share link points to public event anchor when booking is not required', function () {
    $therapist = makeTherapist();
    $event = makeEvent($therapist, [
        'booking_required' => false,
    ]);

    $response = $this->actingAs($therapist)->get(route('events.show', $event->id));
    $response->assertOk();

    $content = $response->getContent();
    $publicAnchorUrl = route('therapist.show', ['slug' => $therapist->slug]) . "#event-{$event->id}";

    expect($content)->toContain($publicAnchorUrl);
    expect($content)->toContain('https://www.facebook.com/sharer/sharer.php?u=' . urlencode($publicAnchorUrl));
});

test('reservation page redirects to public event anchor when booking is not required', function () {
    $therapist = makeTherapist();
    $event = makeEvent($therapist, [
        'booking_required' => false,
    ]);

    $response = $this->get(route('events.reserve.create', $event->id));

    $response->assertRedirect(route('therapist.show', ['slug' => $therapist->slug]) . "#event-{$event->id}");
});

test('reservation page redirects to public event anchor when event is full', function () {
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

    $response->assertRedirect(route('therapist.show', ['slug' => $therapist->slug]) . "#event-{$event->id}");
});

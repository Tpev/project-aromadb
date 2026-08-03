<?php

use App\Mail\EventReminderClientMail;
use App\Mail\ReservationConfirmation;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use App\Support\EventVisioJoinLink;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set([
        'services.jitsi.base_url' => 'https://visio.olithea.test',
        'services.jitsi.domain' => 'visio.olithea.test',
        'services.jitsi.app_id' => 'visio-test',
        'services.jitsi.secret' => 'test-secret-that-is-long-enough',
        'services.jitsi.participant_name_gate.enabled' => true,
        'services.jitsi.participant_name_gate.user_ids' => [],
    ]);
});

function makeVisioGateEvent(User $practitioner, array $attributes = []): Event
{
    return Event::create(array_merge([
        'user_id' => $practitioner->id,
        'name' => 'Conférence bien-être',
        'description' => 'Une conférence de test.',
        'start_date_time' => now()->addDay(),
        'duration' => 90,
        'booking_required' => true,
        'limited_spot' => false,
        'showOnPortail' => true,
        'location' => 'En ligne (Visio)',
        'event_type' => 'visio',
        'visio_provider' => 'aromamade',
        'visio_token' => 'conference-room-token',
    ], $attributes));
}

test('enabled Olithea event links open the participant name gate', function () {
    $event = makeVisioGateEvent(User::factory()->create());
    $joinLink = app(EventVisioJoinLink::class);

    expect($joinLink->for($event))->toBe(route('events.visio.join.show', $event));

    $this->get(route('events.visio.join.show', $event))
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('Avant de rejoindre la visio')
        ->assertSee('Prénom et nom')
        ->assertSee($event->name);
});

test('name submission redirects to the unchanged room with a separate display name fragment', function () {
    $event = makeVisioGateEvent(User::factory()->create());

    $response = $this->post(route('events.visio.join', $event), [
        'display_name' => "  <b>Sophie</b>\nSchauber  ",
    ]);

    $destination = $response->headers->get('Location');

    $response->assertRedirect();
    expect($destination)
        ->toStartWith('https://visio.olithea.test/conference-room-token?jwt=')
        ->toEndWith('#userInfo.displayName=%22Sophie%20Schauber%22')
        ->and($event->fresh()->visio_token)->toBe('conference-room-token')
        ->and(session("event_visio_names.{$event->id}"))->toBe('Sophie Schauber');
});

test('the gate validates the participant name in French', function () {
    $event = makeVisioGateEvent(User::factory()->create());

    $this->from(route('events.visio.join.show', $event))
        ->post(route('events.visio.join', $event), ['display_name' => '   '])
        ->assertRedirect(route('events.visio.join.show', $event))
        ->assertSessionHasErrors('display_name');
});

test('kill switch and practitioner allowlist preserve the existing direct participant link', function () {
    $practitioner = User::factory()->create();
    $event = makeVisioGateEvent($practitioner);
    $joinLink = app(EventVisioJoinLink::class);

    config()->set('services.jitsi.participant_name_gate.enabled', false);
    expect($joinLink->for($event))->toStartWith('https://visio.olithea.test/conference-room-token?jwt=');
    $this->get(route('events.visio.join.show', $event))
        ->assertRedirectContains('https://visio.olithea.test/conference-room-token?jwt=');

    config()->set([
        'services.jitsi.participant_name_gate.enabled' => true,
        'services.jitsi.participant_name_gate.user_ids' => [$practitioner->id + 1000],
    ]);
    expect($joinLink->for($event))->toStartWith('https://visio.olithea.test/conference-room-token?jwt=');

    config()->set('services.jitsi.participant_name_gate.user_ids', [$practitioner->id]);
    expect($joinLink->for($event))->toBe(route('events.visio.join.show', $event));
});

test('external visio links and practitioner host links remain unchanged', function () {
    $practitioner = User::factory()->create();
    $olitheaEvent = makeVisioGateEvent($practitioner);
    $externalEvent = makeVisioGateEvent($practitioner, [
        'visio_provider' => 'external',
        'visio_url' => 'https://external.example.test/conference',
        'visio_token' => null,
    ]);

    expect(app(EventVisioJoinLink::class)->for($externalEvent))
        ->toBe('https://external.example.test/conference')
        ->and($olitheaEvent->visio_host_link)
        ->toStartWith('https://visio.olithea.test/conference-room-token?jwt=')
        ->not->toContain(route('events.visio.join.show', $olitheaEvent));

    $this->get(route('events.visio.join.show', $externalEvent))->assertNotFound();
});

test('new confirmations and reminders use the gate without changing their subjects', function () {
    $practitioner = User::factory()->create([
        'company_email' => 'cabinet@example.test',
    ]);
    $event = makeVisioGateEvent($practitioner);
    $reservation = Reservation::create([
        'event_id' => $event->id,
        'full_name' => 'Sophie Schauber',
        'email' => 'sophie@example.test',
        'status' => 'confirmed',
    ]);
    $expected = route('events.visio.join.show', $event);

    $confirmation = (new ReservationConfirmation($reservation))->build();
    $reminder = (new EventReminderClientMail($event, $reservation))->build();

    expect($confirmation->visioUrl)->toBe($expected)
        ->and($confirmation->hasSubject('Confirmation de votre réservation'))->toBeTrue()
        ->and($reminder->viewData['visioJoinLink'])->toBe($expected)
        ->and($reminder->hasSubject('Rappel : votre événement approche'))->toBeTrue();
});

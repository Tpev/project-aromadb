<?php

use App\Mail\EventReminderClientMail;
use App\Mail\NewReservationNotification;
use App\Mail\ReservationConfirmation;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\Unavailability;
use App\Models\User;
use App\Services\EventCalendarBlockService;
use App\Support\EventDuration;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

afterEach(function (): void {
    Carbon::setTestNow();
});

function eventDurationTherapist(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
        'visible_annuarire_admin_set' => true,
        'slug' => 'duree-evenement-'.strtolower(str()->random(8)),
        'company_name' => 'Cabinet Durée',
    ], $overrides));
}

function eventDurationPayload(User $therapist, array $overrides = []): array
{
    return array_merge([
        'user_id' => $therapist->id,
        'name' => 'Atelier durée test',
        'description' => 'Description de test',
        'start_date_time' => '2026-08-10T09:00',
        'booking_required' => 1,
        'limited_spot' => 0,
        'number_of_spot' => null,
        'associated_product' => null,
        'showOnPortail' => 1,
        'location' => 'Cabinet',
        'event_type' => 'in_person',
        'collect_payment' => 0,
        'tax_rate' => 0,
    ], $overrides);
}

test('event duration stays canonical in minutes and exposes friendly formatting and end time', function () {
    $therapist = eventDurationTherapist();
    $start = Carbon::parse('2026-08-10 09:00:00');

    $event = Event::create(eventDurationPayload($therapist, [
        'start_date_time' => $start,
        'duration' => 90,
    ]));

    expect($event->duration)->toBe(90)
        ->and($event->end_date_time->equalTo($start->copy()->addMinutes(90)))->toBeTrue()
        ->and($event->ends_at->equalTo($start->copy()->addMinutes(90)))->toBeTrue()
        ->and($event->formatted_duration)->toBe('1 h 30')
        ->and($event->formatted_period)->toBe('Le 10/08/2026 de 09:00 à 10:30')
        ->and(EventDuration::format(30))->toBe('30 min')
        ->and(EventDuration::format(60))->toBe('1 h')
        ->and(EventDuration::format(1_440))->toBe('1 jour')
        ->and(EventDuration::format(3_000))->toBe('2 jours 2 h');

    DB::table('events')->where('id', $event->id)->update(['end_date_time' => null]);
    $legacyEvent = $event->fresh();

    expect($legacyEvent->ends_at->equalTo($start->copy()->addMinutes(90)))->toBeTrue()
        ->and(Event::query()->notEnded(Carbon::parse('2026-08-10 09:30'))->whereKey($event->id)->exists())->toBeTrue();
});

test('web creation accepts legacy minutes and new hour and day units', function () {
    $therapist = eventDurationTherapist();

    $this->actingAs($therapist)
        ->get(route('events.create'))
        ->assertOk()
        ->assertSee('name="duration_value"', false)
        ->assertSee('name="duration_unit"', false);

    $this->actingAs($therapist)
        ->post(route('events.store'), eventDurationPayload($therapist, [
            'name' => 'Ancien payload minutes',
            'duration' => 90,
        ]))
        ->assertRedirect(route('events.index'));

    $this->actingAs($therapist)
        ->post(route('events.store'), eventDurationPayload($therapist, [
            'name' => 'Deux heures',
            'duration_value' => 2,
            'duration_unit' => 'hours',
        ]))
        ->assertRedirect(route('events.index'));

    $this->actingAs($therapist)
        ->post(route('events.store'), eventDurationPayload($therapist, [
            'name' => 'Deux jours',
            'duration_value' => 2,
            'duration_unit' => 'days',
        ]))
        ->assertRedirect(route('events.index'));

    expect(Event::where('name', 'Ancien payload minutes')->firstOrFail()->duration)->toBe(90)
        ->and(Event::where('name', 'Deux heures')->firstOrFail()->duration)->toBe(120)
        ->and(Event::where('name', 'Deux jours')->firstOrFail()->duration)->toBe(2_880)
        ->and(Event::where('name', 'Deux jours')->firstOrFail()->formatted_period)
        ->toBe('Du 10/08/2026 à 09:00 au 12/08/2026 à 09:00');
});

test('editing duration preserves visio reservations and payment data while synchronizing its calendar block', function () {
    $therapist = eventDurationTherapist();
    $otherTherapist = eventDurationTherapist();
    $event = Event::create(eventDurationPayload($therapist, [
        'name' => 'Visio longue',
        'start_date_time' => '2026-08-10 09:00:00',
        'duration' => 60,
        'location' => 'En ligne (Visio)',
        'event_type' => 'visio',
        'visio_provider' => 'aromamade',
        'visio_token' => 'token-visio-stable',
        'collect_payment' => false,
        'price' => null,
    ]));
    $reservation = Reservation::create([
        'event_id' => $event->id,
        'full_name' => 'Cliente préservée',
        'email' => 'cliente@example.test',
        'status' => 'paid',
        'amount_ttc' => 120,
        'currency' => 'eur',
        'stripe_session_id' => 'cs_duration_unchanged',
        'stripe_payment_intent_id' => 'pi_duration_unchanged',
    ]);
    $legacyUnavailability = Unavailability::create([
        'user_id' => $therapist->id,
        'start_date' => '2026-08-10 09:00:00',
        'end_date' => '2026-08-10 10:00:00',
        'reason' => 'Ancien bloc non lié',
    ]);
    $otherBlock = Unavailability::create([
        'user_id' => $otherTherapist->id,
        'start_date' => '2026-08-10 09:00:00',
        'end_date' => '2026-08-10 10:00:00',
        'reason' => 'Bloc autre compte',
    ]);

    $this->actingAs($therapist)
        ->put(route('events.update', $event), eventDurationPayload($therapist, [
            'name' => 'Visio longue',
            'start_date_time' => '2026-08-11T10:00',
            'duration_value' => 2,
            'duration_unit' => 'days',
            'block_calendar' => 1,
            'location' => null,
            'event_type' => 'visio',
            'visio_provider' => 'aromamade',
        ]))
        ->assertRedirect(route('events.index'));

    $event->refresh();
    $linkedBlock = $event->calendarBlock()->firstOrFail();

    expect($event->duration)->toBe(2_880)
        ->and($event->visio_token)->toBe('token-visio-stable')
        ->and($event->collect_payment)->toBeFalse()
        ->and($event->reservations()->whereKey($reservation)->exists())->toBeTrue()
        ->and($reservation->fresh()->status)->toBe('paid')
        ->and($reservation->fresh()->amount_ttc)->toBe(120.0)
        ->and($reservation->fresh()->stripe_session_id)->toBe('cs_duration_unchanged')
        ->and($reservation->fresh()->stripe_payment_intent_id)->toBe('pi_duration_unchanged')
        ->and($linkedBlock->user_id)->toBe($therapist->id)
        ->and($linkedBlock->start_date->equalTo($event->start_date_time))->toBeTrue()
        ->and($linkedBlock->end_date->equalTo($event->ends_at))->toBeTrue()
        ->and($legacyUnavailability->fresh())->not->toBeNull()
        ->and($otherBlock->fresh())->not->toBeNull();

    $this->actingAs($otherTherapist)
        ->put(route('events.update', $event), eventDurationPayload($otherTherapist, [
            'name' => 'Tentative autre compte',
            'duration' => 30,
            'block_calendar' => 0,
        ]))
        ->assertForbidden();

    $this->actingAs($therapist)
        ->put(route('events.update', $event), eventDurationPayload($therapist, [
            'name' => 'Visio longue',
            'start_date_time' => '2026-08-11T10:00',
            'duration' => 120,
            'location' => null,
            'event_type' => 'visio',
            'visio_provider' => 'aromamade',
        ]))
        ->assertRedirect(route('events.index'));

    $event->refresh();

    expect($event->calendarBlock)->not->toBeNull()
        ->and($event->calendarBlock->start_date->equalTo($event->start_date_time))->toBeTrue()
        ->and($event->calendarBlock->end_date->equalTo($event->ends_at))->toBeTrue();

    $this->actingAs($therapist)
        ->put(route('events.update', $event), eventDurationPayload($therapist, [
            'name' => 'Visio longue',
            'start_date_time' => '2026-08-11T10:00',
            'duration_value' => 2,
            'duration_unit' => 'days',
            'block_calendar' => 0,
            'location' => null,
            'event_type' => 'visio',
            'visio_provider' => 'aromamade',
        ]))
        ->assertRedirect(route('events.index'));

    expect($event->fresh()->calendarBlock)->toBeNull()
        ->and($legacyUnavailability->fresh())->not->toBeNull()
        ->and($otherBlock->fresh())->not->toBeNull();
});

test('duplicating an event accepts friendly units and creates an isolated linked calendar block', function () {
    $therapist = eventDurationTherapist();
    $event = Event::create(eventDurationPayload($therapist, [
        'name' => 'Événement original',
        'duration' => 60,
    ]));

    $this->actingAs($therapist)
        ->post(route('events.duplicate.store', $event), eventDurationPayload($therapist, [
            'name' => 'Événement dupliqué',
            'start_date_time' => '2026-09-01T14:00',
            'duration_value' => 3,
            'duration_unit' => 'hours',
            'block_calendar' => 1,
            'duplicate_participants' => 0,
        ]))
        ->assertRedirect();

    $duplicate = Event::where('name', 'Événement dupliqué')->firstOrFail();

    expect($duplicate->duration)->toBe(180)
        ->and($duplicate->calendarBlock)->not->toBeNull()
        ->and($duplicate->calendarBlock->event_id)->toBe($duplicate->id)
        ->and($event->fresh()->calendarBlock)->toBeNull();
});

test('ongoing multi day events remain visible publicly and are identified correctly', function () {
    Carbon::setTestNow('2026-08-11 12:00:00');
    $therapist = eventDurationTherapist(['slug' => 'evenements-en-cours']);
    $ongoing = Event::create(eventDurationPayload($therapist, [
        'name' => 'Retraite en cours',
        'start_date_time' => '2026-08-10 09:00:00',
        'duration' => 2_880,
    ]));
    $ended = Event::create(eventDurationPayload($therapist, [
        'name' => 'Atelier déjà terminé',
        'start_date_time' => '2026-08-08 09:00:00',
        'duration' => 60,
    ]));

    expect($ongoing->isOngoing())->toBeTrue()
        ->and($ongoing->isPast())->toBeFalse()
        ->and($ended->isPast())->toBeTrue()
        ->and(Event::query()->notEnded()->pluck('id'))->toContain($ongoing->id)->not->toContain($ended->id);

    $this->get(route('welcome'))
        ->assertOk()
        ->assertSee('Retraite en cours')
        ->assertDontSee('Atelier déjà terminé');

    $this->get(route('therapist.show', $therapist->slug))
        ->assertOk()
        ->assertSee('Retraite en cours')
        ->assertDontSee('Atelier déjà terminé');

    $this->actingAs($therapist)
        ->get(route('mobile.events.index'))
        ->assertOk()
        ->assertSee('En cours');
});

test('mobile event forms normalize day units and synchronize calendar blocking', function () {
    $therapist = eventDurationTherapist();

    $this->actingAs($therapist)
        ->get(route('mobile.events.create'))
        ->assertOk()
        ->assertSee('name="duration_value"', false)
        ->assertSee('name="duration_unit"', false);

    $this->actingAs($therapist)
        ->post(route('mobile.events.store'), eventDurationPayload($therapist, [
            'name' => 'Stage mobile trois jours',
            'duration_value' => 3,
            'duration_unit' => 'days',
            'block_calendar' => 1,
        ]))
        ->assertRedirect();

    $event = Event::where('name', 'Stage mobile trois jours')->firstOrFail();

    expect($event->duration)->toBe(4_320)
        ->and($event->calendarBlock)->not->toBeNull()
        ->and($event->calendarBlock->end_date->equalTo($event->ends_at))->toBeTrue();

    $this->actingAs($therapist)
        ->put(route('mobile.events.update', $event), eventDurationPayload($therapist, [
            'name' => 'Stage mobile actualisé',
            'duration' => 45,
            'block_calendar' => 0,
        ]))
        ->assertRedirect();

    expect($event->fresh()->duration)->toBe(45)
        ->and($event->fresh()->calendarBlock)->toBeNull();
});

test('event pages and emails use the shared duration and period formatting', function () {
    $therapist = eventDurationTherapist();
    $event = Event::create(eventDurationPayload($therapist, [
        'name' => 'Atelier formaté',
        'duration' => 90,
    ]));
    $reservation = Reservation::create([
        'event_id' => $event->id,
        'full_name' => 'Cliente Email',
        'email' => 'email@example.test',
        'status' => 'confirmed',
    ]);

    $this->get(route('events.reserve.create', $event))
        ->assertOk()
        ->assertSee('1 h 30')
        ->assertSee('Le 10/08/2026 de 09:00 à 10:30');

    $confirmation = (new ReservationConfirmation($reservation))->render();
    $notification = (new NewReservationNotification($reservation))->render();
    $reminder = (new EventReminderClientMail($event, $reservation))->render();

    expect($confirmation)->toContain('1 h 30')
        ->and($notification)->toContain('1 h 30')
        ->and($reminder)->toContain('1 h 30');
});

test('deleting an event cascades only its linked calendar block', function () {
    $therapist = eventDurationTherapist();
    $event = Event::create(eventDurationPayload($therapist, ['duration' => 60]));
    $unrelated = Unavailability::create([
        'user_id' => $therapist->id,
        'start_date' => '2026-08-15 09:00:00',
        'end_date' => '2026-08-15 10:00:00',
        'reason' => 'Indisponibilité manuelle',
    ]);
    $linked = app(EventCalendarBlockService::class)->sync($event, true);

    $this->actingAs($therapist)
        ->delete(route('events.destroy', $event))
        ->assertRedirect(route('events.index'));

    expect(Unavailability::find($linked->id))->toBeNull()
        ->and($unrelated->fresh())->not->toBeNull();
});

test('duration migration backfills legacy event end times and rolls back cleanly', function () {
    $therapist = eventDurationTherapist();
    $event = Event::create(eventDurationPayload($therapist, [
        'start_date_time' => '2026-08-10 09:00:00',
        'duration' => 90,
    ]));
    $migration = require database_path('migrations/2026_07_21_090000_add_event_end_time_and_calendar_block_link.php');

    $migration->down();

    expect(Schema::hasColumn('events', 'end_date_time'))->toBeFalse()
        ->and(Schema::hasColumn('unavailabilities', 'event_id'))->toBeFalse();

    $migration->up();

    $backfilledEnd = DB::table('events')->where('id', $event->id)->value('end_date_time');

    expect(Schema::hasColumn('events', 'end_date_time'))->toBeTrue()
        ->and(Schema::hasColumn('unavailabilities', 'event_id'))->toBeTrue()
        ->and(Carbon::parse($backfilledEnd)->equalTo(Carbon::parse('2026-08-10 10:30:00')))->toBeTrue();
});

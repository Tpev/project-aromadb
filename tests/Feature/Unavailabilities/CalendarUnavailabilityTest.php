<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Event;
use App\Models\Product;
use App\Models\Unavailability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function calendarUnavailabilityFixture(): array
{
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $client = ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => 'Lina',
        'last_name' => 'Dupont',
        'email' => 'lina.dupont@example.test',
    ]);
    $product = Product::create([
        'user_id' => $therapist->id,
        'name' => 'Consultation agenda',
        'description' => 'Test agenda',
        'price' => 75,
        'tax_rate' => 0,
        'duration' => 60,
    ]);

    return compact('therapist', 'client', 'product');
}

test('appointments calendar exposes direct appointment and unavailability actions', function () {
    $fixture = calendarUnavailabilityFixture();
    $block = Unavailability::create([
        'user_id' => $fixture['therapist']->id,
        'start_date' => now()->addWeek()->setTime(10, 0),
        'end_date' => now()->addWeek()->setTime(11, 0),
        'reason' => 'Formation personnelle',
    ]);

    $this->actingAs($fixture['therapist'])
        ->get(route('appointments.index'))
        ->assertOk()
        ->assertSee('Que souhaitez-vous ajouter ?')
        ->assertSee('Créer une indisponibilité')
        ->assertSee('dateClick: function', false)
        ->assertSee(str_replace('/', '\\/', route('unavailabilities.edit', $block)), false);
});

test('calendar unavailability warns before overlapping an appointment and preserves it after confirmation', function () {
    Carbon::setTestNow('2026-09-01 08:00:00');
    $fixture = calendarUnavailabilityFixture();
    $appointment = Appointment::create([
        'user_id' => $fixture['therapist']->id,
        'client_profile_id' => $fixture['client']->id,
        'product_id' => $fixture['product']->id,
        'appointment_date' => Carbon::parse('2026-09-10 10:00:00'),
        'duration' => 60,
        'status' => Appointment::STATUS_SCHEDULED,
        'type' => 'visio',
    ]);
    $payload = [
        'unavailability_source' => 'calendar',
        'start_date' => '2026-09-10',
        'start_time' => '09:30',
        'end_date' => '2026-09-10',
        'end_time' => '11:30',
        'reason' => 'Absence urgente',
    ];

    $response = $this->actingAs($fixture['therapist'])
        ->from(route('appointments.index'))
        ->post(route('unavailabilities.store'), $payload)
        ->assertRedirect(route('appointments.index'))
        ->assertSessionHas('unavailability_conflicts');

    $confirmationToken = $response->getSession()->get('unavailability_conflicts.confirmation_token');

    expect(Unavailability::query()->count())->toBe(0);

    $this->actingAs($fixture['therapist'])
        ->post(route('unavailabilities.store'), array_merge($payload, [
            'start_time' => '09:45',
            'confirm_conflicts' => $confirmationToken,
        ]))
        ->assertRedirect()
        ->assertSessionHas('unavailability_conflicts');

    expect(Unavailability::query()->count())->toBe(0);

    $this->actingAs($fixture['therapist'])
        ->post(route('unavailabilities.store'), $payload + ['confirm_conflicts' => $confirmationToken])
        ->assertRedirect(route('appointments.index'))
        ->assertSessionHas('success');

    expect(Unavailability::query()->where('reason', 'Absence urgente')->exists())->toBeTrue()
        ->and($appointment->fresh())->not->toBeNull();

    Carbon::setTestNow();
});

test('event managed unavailability can only be changed from its event', function () {
    $fixture = calendarUnavailabilityFixture();
    $event = Event::create([
        'user_id' => $fixture['therapist']->id,
        'name' => 'Atelier lié',
        'description' => 'Événement de test',
        'start_date_time' => now()->addWeeks(2)->setTime(14, 0),
        'duration' => 120,
        'booking_required' => true,
        'limited_spot' => false,
        'showOnPortail' => true,
        'location' => 'Cabinet',
        'event_type' => 'in_person',
        'collect_payment' => false,
        'tax_rate' => 0,
    ]);
    $block = Unavailability::create([
        'user_id' => $fixture['therapist']->id,
        'event_id' => $event->id,
        'start_date' => $event->start_date_time,
        'end_date' => $event->ends_at,
        'reason' => 'Événement : '.$event->name,
    ]);

    $this->actingAs($fixture['therapist'])
        ->get(route('unavailabilities.edit', $block))
        ->assertRedirect(route('events.show', $event));

    $this->actingAs($fixture['therapist'])
        ->delete(route('unavailabilities.destroy', $block))
        ->assertRedirect(route('events.show', $event));

    expect($block->fresh())->not->toBeNull();

    $this->actingAs($fixture['therapist'])
        ->get(route('unavailabilities.index'))
        ->assertOk()
        ->assertSee('Voir l’événement')
        ->assertDontSee(route('unavailabilities.edit', $block), false);
});

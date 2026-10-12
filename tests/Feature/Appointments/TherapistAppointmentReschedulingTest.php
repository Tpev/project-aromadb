<?php

namespace Tests\Feature\Appointments;

use App\Mail\AppointmentRescheduledClientMail;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\ClientProfile;
use App\Models\PracticeLocation;
use App\Models\Product;
use App\Models\Unavailability;
use App\Models\User;
use App\Services\AppointmentAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TherapistAppointmentReschedulingTest extends TestCase
{
    use RefreshDatabase;

    private User $therapist;
    private Product $product;
    private ClientProfile $client;
    private PracticeLocation $location;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
        $this->travelTo(Carbon::parse('2026-10-10 17:27:00'));
        config()->set('appointments.booking_v2.enabled', true);

        $this->therapist = User::factory()->create([
            'is_therapist' => true,
            'license_status' => 'active',
            'accept_online_appointments' => true,
            'minimum_notice_hours' => 24,
            'cancellation_notice_hours' => 24,
            'buffer_time_between_appointments' => 0,
            'booking_schedule_mode' => 'fixed',
            'booking_slot_interval_minutes' => 60,
        ]);
        $this->product = Product::create([
            'user_id' => $this->therapist->id,
            'name' => 'Consultation de 60 minutes',
            'price' => 70,
            'tax_rate' => 0,
            'duration' => 60,
            'can_be_booked_online' => true,
            'collect_payment' => false,
            'dans_le_cabinet' => true,
            'visio' => false,
            'adomicile' => false,
            'en_entreprise' => false,
        ]);
        $this->client = ClientProfile::create([
            'user_id' => $this->therapist->id,
            'first_name' => 'Camille',
            'last_name' => 'Test',
            'email' => 'rescheduling-client@example.test',
        ]);
        $this->location = PracticeLocation::create([
            'user_id' => $this->therapist->id,
            'label' => 'Cabinet test',
            'address_line1' => '1 rue du Test',
            'postal_code' => '67000',
            'city' => 'Strasbourg',
            'country' => 'FR',
        ]);
        Availability::create([
            'user_id' => $this->therapist->id,
            'day_of_week' => 4,
            'start_time' => '09:00:00',
            'end_time' => '16:00:00',
            'applies_to_all' => true,
            'practice_location_id' => $this->location->id,
        ]);
    }

    private function appointment(array $attributes = []): Appointment
    {
        return Appointment::create(array_replace([
            'user_id' => $this->therapist->id,
            'client_profile_id' => $this->client->id,
            'product_id' => $this->product->id,
            'appointment_date' => '2026-10-09 14:00:00',
            'duration' => 60,
            'type' => 'cabinet',
            'practice_location_id' => $this->location->id,
            'status' => Appointment::STATUS_SCHEDULED,
        ], $attributes));
    }

    private function payload(Carbon $start): array
    {
        return [
            'client_profile_id' => $this->client->id,
            'product_id' => $this->product->id,
            'appointment_date' => $start->toDateString(),
            'appointment_time' => $start->format('H:i'),
            'type' => 'cabinet',
            'practice_location_id' => $this->location->id,
            'status' => Appointment::STATUS_SCHEDULED,
        ];
    }

    #[DataProvider('channelsAndOverrides')]
    public function test_therapist_can_move_the_reported_past_appointment_to_a_green_preview_slot(string $channel, ?int $override): void
    {
        $appointment = $this->appointment([
            'stripe_session_id' => 'cs_preserved_after_reschedule',
            'reminder_24h_sent_at' => now(),
            'reminder_1h_sent_at' => now(),
        ]);
        $newStart = Carbon::parse('2026-10-16 09:30:00');
        $this->actingAs($this->therapist);

        $this->get(route($channel.'appointments.edit', $appointment))->assertOk();
        $preview = $this->postJson(route('appointments.available-slots-therapist'), [
            'date' => $newStart->toDateString(),
            'product_id' => $this->product->id,
            'mode' => 'cabinet',
            'location_id' => $this->location->id,
            'include_conflicts' => 1,
            'exclude_appointment_id' => $appointment->id,
        ])->assertOk();
        $this->assertFalse(collect($preview->json('slots'))->firstWhere('start', '09:30')['has_conflict']);
        $this->assertFalse(app(AppointmentAvailabilityService::class)->isAvailable($appointment, $newStart));

        $payload = $this->payload($newStart);
        if ($override !== null) {
            $payload['force_availability_override'] = $override;
        }
        $this->put(route($channel.'appointments.update', $appointment), $payload)
            ->assertRedirect()->assertSessionHasNoErrors();

        $fresh = $appointment->fresh();
        $this->assertTrue($fresh->appointment_date->equalTo($newStart));
        $this->assertSame($appointment->token, $fresh->token);
        $this->assertSame('cs_preserved_after_reschedule', $fresh->stripe_session_id);
        $this->assertNull($fresh->reminder_24h_sent_at);
        $this->assertNull($fresh->reminder_1h_sent_at);
        $activity = $fresh->activities()->where('action', 'rescheduled')->sole();
        $this->assertSame('practitioner', $activity->actor_type);
        $this->assertTrue($activity->metadata['availability_override']);
        $this->assertDatabaseCount('appointments', 1);
        Mail::assertQueued(AppointmentRescheduledClientMail::class, 1);
    }

    #[DataProvider('channelsAndRestrictions')]
    public function test_therapist_rescheduling_bypasses_public_scheduling_restrictions(string $channel, string $restriction): void
    {
        $newStart = $this->applyRestriction($restriction);
        $appointment = $this->appointment();
        $this->assertFalse(app(AppointmentAvailabilityService::class)->isAvailable($appointment->fresh(), $newStart));

        $this->actingAs($this->therapist->fresh())
            ->put(route($channel.'appointments.update', $appointment), $this->payload($newStart) + ['force_availability_override' => 0])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertTrue($appointment->fresh()->appointment_date->equalTo($newStart));
        $this->assertSame(1, $appointment->activities()->where('action', 'rescheduled')->count());
    }

    private function applyRestriction(string $restriction): Carbon
    {
        $start = Carbon::parse('2026-10-16 10:00:00');
        switch ($restriction) {
            case 'outside_hours':
                $start->setTime(18, 30);
                break;
            case 'appointment_overlap':
                $this->appointment(['appointment_date' => $start]);
                break;
            case 'unavailability':
                Unavailability::create([
                    'user_id' => $this->therapist->id,
                    'start_date' => $start,
                    'end_date' => $start->copy()->addHour(),
                    'reason' => 'Indisponible',
                ]);
                break;
            case 'minimum_notice':
                $this->therapist->update(['minimum_notice_hours' => 200]);
                break;
            case 'fixed_grid':
                $start->setTime(9, 30);
                break;
            case 'optimized_grid':
                $this->therapist->update(['booking_schedule_mode' => 'optimized']);
                $start->setTime(9, 30);
                break;
            case 'product_daily_limit':
                $this->product->update(['max_per_day' => 1]);
                $this->appointment(['appointment_date' => $start->copy()->setTime(12, 0)]);
                break;
            case 'global_daily_limit':
                $this->therapist->update(['global_daily_booking_limit' => 1]);
                $this->appointment(['appointment_date' => $start->copy()->setTime(12, 0)]);
                break;
            case 'preparation':
                $this->product->update(['preparation_time_minutes' => 30]);
                $this->appointment(['appointment_date' => $start->copy()->setTime(8, 45)]);
                break;
            case 'buffer_after':
                $this->product->update(['buffer_time_after_minutes' => 30]);
                $this->appointment(['appointment_date' => $start->copy()->setTime(11, 15)]);
                break;
        }

        return $start;
    }

    #[DataProvider('channels')]
    public function test_therapist_can_reschedule_legacy_appointments_with_booking_v2_disabled(string $channel): void
    {
        config()->set('appointments.booking_v2.enabled', false);
        $appointment = $this->appointment();
        $this->assertNull($appointment->preparation_time_minutes);
        $this->assertNull($appointment->buffer_time_after_minutes);
        $start = Carbon::parse('2026-10-16 18:30:00');

        $this->actingAs($this->therapist)->put(route($channel.'appointments.update', $appointment), $this->payload($start))
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertTrue($appointment->fresh()->appointment_date->equalTo($start));
        $this->assertSame(1, $appointment->activities()->where('action', 'rescheduled')->count());
    }

    #[DataProvider('channels')]
    public function test_therapist_can_correct_an_appointment_to_a_past_date(string $channel): void
    {
        $appointment = $this->appointment(['appointment_date' => '2026-10-16 10:00:00']);
        $start = Carbon::parse('2026-10-08 18:30:00');

        $this->actingAs($this->therapist)->put(route($channel.'appointments.update', $appointment), $this->payload($start))
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertTrue($appointment->fresh()->appointment_date->equalTo($start));
        $this->assertSame(1, $appointment->activities()->where('action', 'rescheduled')->count());
    }

    #[DataProvider('channels')]
    public function test_therapist_can_reschedule_a_completed_appointment(string $channel): void
    {
        $appointment = $this->appointment([
            'appointment_date' => '2026-10-16 10:00:00',
            'status' => Appointment::STATUS_COMPLETED,
        ]);
        $start = Carbon::parse('2026-10-23 09:30:00');
        $payload = array_replace($this->payload($start), ['status' => Appointment::STATUS_COMPLETED]);

        $this->actingAs($this->therapist)->put(route($channel.'appointments.update', $appointment), $payload)
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertTrue($appointment->fresh()->appointment_date->equalTo($start));
        $this->assertTrue($appointment->fresh()->isCompleted());
    }

    #[DataProvider('channels')]
    public function test_therapist_still_cannot_modify_another_therapists_appointment(string $channel): void
    {
        $appointment = $this->appointment();
        $otherTherapist = User::factory()->create(['is_therapist' => true, 'license_status' => 'active']);

        $this->actingAs($otherTherapist)->put(
            route($channel.'appointments.update', $appointment),
            $this->payload(Carbon::parse('2026-10-16 09:30:00')) + ['force_availability_override' => 1]
        )->assertForbidden();

        $this->assertSame('2026-10-09 14:00:00', $appointment->fresh()->appointment_date->format('Y-m-d H:i:s'));
        $this->assertSame(0, $appointment->activities()->count());
    }

    #[DataProvider('channels')]
    public function test_rescheduling_does_not_restore_a_cancelled_appointment(string $channel): void
    {
        $appointment = $this->appointment(['status' => Appointment::STATUS_CANCELLED]);
        $this->actingAs($this->therapist)->put(
            route($channel.'appointments.update', $appointment),
            $this->payload(Carbon::parse('2026-10-16 09:30:00'))
        )->assertRedirect()->assertSessionHas('error');

        $this->assertTrue($appointment->fresh()->isCancelled());
        $this->assertSame('2026-10-09 14:00:00', $appointment->fresh()->appointment_date->format('Y-m-d H:i:s'));
        $this->assertSame(0, $appointment->activities()->count());
    }

    #[DataProvider('restrictions')]
    public function test_client_rescheduling_cannot_forge_a_therapist_override(string $restriction): void
    {
        $start = $this->applyRestriction($restriction);
        $appointment = $this->appointment(['appointment_date' => '2026-10-23 10:00:00']);
        $this->post(route('appointment.confirmation.reschedule', $appointment->token), [
            'appointment_date' => $start->toDateString(),
            'appointment_time' => $start->format('H:i'),
            'force_availability_override' => 1,
            'actor_type' => 'practitioner',
        ])->assertRedirect()->assertSessionHasErrors('appointment_time');

        $this->assertSame('2026-10-23 10:00:00', $appointment->fresh()->appointment_date->format('Y-m-d H:i:s'));
        $this->assertSame(0, $appointment->activities()->count());
        Mail::assertNothingQueued();
    }

    #[DataProvider('clientManagementRestrictions')]
    public function test_client_management_restrictions_remain_enforced(string $restriction): void
    {
        $attributes = ['appointment_date' => '2026-10-23 10:00:00'];
        if ($restriction === 'past') {
            $attributes['appointment_date'] = '2026-10-09 14:00:00';
        } elseif ($restriction === 'completed') {
            $attributes['status'] = Appointment::STATUS_COMPLETED;
        } elseif ($restriction === 'deadline') {
            $attributes['appointment_date'] = '2026-10-11 10:00:00';
        }
        $appointment = $this->appointment($attributes);
        $this->post(route('appointment.confirmation.reschedule', $appointment->token), [
            'appointment_date' => '2026-10-16',
            'appointment_time' => '10:00',
            'force_availability_override' => 1,
        ])->assertRedirect()->assertSessionHasErrors('appointment');

        $this->assertTrue($appointment->fresh()->appointment_date->equalTo($appointment->appointment_date));
        $this->assertSame(0, $appointment->activities()->count());
        Mail::assertNothingQueued();
    }

    #[DataProvider('channels')]
    public function test_public_booking_remains_unable_to_override_an_unavailable_time(string $channel): void
    {
        $this->post(route($channel === 'mobile.' ? 'mobile.appointments.store' : 'appointments.storePatient'), [
            'therapist_id' => $this->therapist->id,
            'product_id' => $this->product->id,
            'first_name' => 'Public',
            'last_name' => 'Client',
            'email' => 'public-reschedule-check@example.test',
            'phone' => '0612345678',
            'appointment_date' => '2026-10-16',
            'appointment_time' => '18:30',
            'type' => 'cabinet',
            'practice_location_id' => $this->location->id,
            'force_availability_override' => 1,
        ])->assertSessionHasErrors('appointment_date');

        $this->assertDatabaseCount('appointments', 0);
        Mail::assertNothingQueued();
    }

    public static function channels(): array
    {
        return ['desktop' => [''], 'mobile' => ['mobile.']];
    }

    public static function channelsAndOverrides(): array
    {
        $cases = [];
        foreach (self::channels() as $channel => [$prefix]) {
            foreach (['missing' => null, 'zero' => 0, 'one' => 1] as $name => $override) {
                $cases[$channel.' '.$name] = [$prefix, $override];
            }
        }

        return $cases;
    }

    public static function restrictions(): array
    {
        return array_map(fn ($restriction) => [$restriction], [
            'outside_hours', 'appointment_overlap', 'unavailability', 'minimum_notice',
            'fixed_grid', 'optimized_grid', 'product_daily_limit', 'global_daily_limit',
            'preparation', 'buffer_after',
        ]);
    }

    public static function channelsAndRestrictions(): array
    {
        $cases = [];
        foreach (self::channels() as $channel => [$prefix]) {
            foreach (self::restrictions() as [$restriction]) {
                $cases[$channel.' '.$restriction] = [$prefix, $restriction];
            }
        }

        return $cases;
    }

    public static function clientManagementRestrictions(): array
    {
        return [['past'], ['completed'], ['deadline']];
    }
}

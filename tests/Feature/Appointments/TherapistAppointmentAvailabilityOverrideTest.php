<?php

namespace Tests\Feature\Appointments;

use App\Jobs\SendAppointmentConfirmationJob;
use App\Mail\AppointmentCreatedTherapistMail;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\ClientProfile;
use App\Models\Product;
use App\Models\Unavailability;
use App\Models\User;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TherapistAppointmentAvailabilityOverrideTest extends TestCase
{
    use RefreshDatabase;

    private User $therapist;

    private ClientProfile $client;

    private Product $product;

    private Carbon $slot;

    private array $payload;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
        $this->travelTo(now()->startOfDay()->setTime(16, 0));

        $this->therapist = User::factory()->create([
            'is_therapist' => true,
            'license_status' => 'active',
            'accept_online_appointments' => true,
            'minimum_notice_hours' => 48,
            'buffer_time_between_appointments' => 0,
            'booking_schedule_mode' => 'fixed',
            'booking_slot_interval_minutes' => 30,
        ]);
        $this->client = ClientProfile::create([
            'user_id' => $this->therapist->id,
            'first_name' => 'Camille',
            'last_name' => 'Test',
            'email' => 'manual-booking@example.test',
        ]);
        $this->product = Product::create([
            'user_id' => $this->therapist->id,
            'name' => 'Consultation test',
            'price' => 90,
            'tax_rate' => 0,
            'duration' => 60,
            'can_be_booked_online' => true,
            'collect_payment' => false,
            'visio' => true,
            'adomicile' => false,
            'dans_le_cabinet' => false,
            'en_entreprise' => false,
        ]);
        $this->slot = now()->addDay()->setTime(14, 0);
        $this->payload = [
            'client_profile_id' => $this->client->id,
            'product_id' => $this->product->id,
            'appointment_date' => $this->slot->toDateString(),
            'appointment_time' => $this->slot->format('H:i'),
            'type' => 'visio',
            'status' => 'confirmed',
        ];
    }

    private function creationFormOverride(TestResponse $response): string
    {
        $document = new DOMDocument;
        @$document->loadHTML($response->getContent());
        $input = (new DOMXPath($document))->query('//input[@name="force_availability_override"]')->item(0);

        $this->assertNotNull($input);

        return $input->getAttribute('value');
    }

    public function test_a_therapist_can_create_a_green_preview_slot_despite_the_public_minimum_notice_rule(): void
    {
        config()->set('appointments.booking_v2.enabled', true);
        Availability::create([
            'user_id' => $this->therapist->id,
            'day_of_week' => $this->slot->dayOfWeekIso - 1,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'applies_to_all' => true,
        ]);

        $form = $this->actingAs($this->therapist)->get(route('appointments.create'))->assertOk();
        $preview = $this->postJson(route('appointments.available-slots-therapist'), [
            'date' => $this->slot->toDateString(),
            'product_id' => $this->product->id,
            'mode' => 'visio',
            'include_conflicts' => 1,
        ])->assertOk();

        $this->assertFalse(collect($preview->json('slots'))->firstWhere('start', '14:00')['has_conflict']);

        // Reproduce the server rejection when the preview turns the override off.
        $this->post(route('appointments.store'), $this->payload + ['force_availability_override' => 0])
            ->assertSessionHasErrors('appointment_time');
        $this->assertDatabaseCount('appointments', 0);

        $this->post(route('appointments.store'), $this->payload + [
            'force_availability_override' => $this->creationFormOverride($form),
        ])->assertSessionHasNoErrors()->assertRedirect(route('appointments.index'));

        $appointment = Appointment::sole();
        $this->assertTrue($appointment->appointment_date->equalTo($this->slot));
        $this->assertSame('visio', $appointment->type);
        Queue::assertPushed(SendAppointmentConfirmationJob::class);
        Mail::assertQueued(AppointmentCreatedTherapistMail::class);
    }

    #[DataProvider('availabilityConflicts')]
    public function test_the_therapist_creation_form_permits_manual_bookings_despite_availability_conflicts(bool $bookingV2, string $conflict): void
    {
        config()->set('appointments.booking_v2.enabled', $bookingV2);
        $this->therapist->update(['minimum_notice_hours' => 0]);

        if ($conflict !== 'outside_hours') {
            Availability::create([
                'user_id' => $this->therapist->id,
                'day_of_week' => $this->slot->dayOfWeekIso - 1,
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'applies_to_all' => true,
            ]);
        }

        if ($conflict === 'appointment') {
            Appointment::create([
                'user_id' => $this->therapist->id,
                'client_profile_id' => $this->client->id,
                'product_id' => $this->product->id,
                'appointment_date' => $this->slot,
                'duration' => 60,
                'type' => 'visio',
                'status' => 'confirmed',
            ]);
        } elseif ($conflict === 'unavailability') {
            Unavailability::create([
                'user_id' => $this->therapist->id,
                'start_date' => $this->slot,
                'end_date' => $this->slot->copy()->addHour(),
                'reason' => 'Indisponible',
            ]);
        }

        $count = Appointment::count();
        $form = $this->actingAs($this->therapist)->get(route('appointments.create'))->assertOk();
        $this->post(route('appointments.store'), $this->payload + [
            'force_availability_override' => $this->creationFormOverride($form),
        ])->assertSessionHasNoErrors()->assertRedirect(route('appointments.index'));

        $this->assertDatabaseCount('appointments', $count + 1);
        Queue::assertPushed(SendAppointmentConfirmationJob::class);
        Mail::assertQueued(AppointmentCreatedTherapistMail::class);
    }

    #[DataProvider('bookingVersions')]
    public function test_public_booking_cannot_use_the_therapist_override_to_bypass_availability(bool $bookingV2): void
    {
        config()->set('appointments.booking_v2.enabled', $bookingV2);

        $this->post(route('appointments.storePatient'), [
            'therapist_id' => $this->therapist->id,
            'product_id' => $this->product->id,
            'first_name' => 'Public',
            'last_name' => 'Client',
            'email' => 'public-booking@example.test',
            'phone' => '0612345678',
            'appointment_date' => $this->slot->toDateString(),
            'appointment_time' => $this->slot->format('H:i'),
            'type' => 'visio',
            'force_availability_override' => 1,
        ])->assertSessionHasErrors('appointment_date');

        $this->assertDatabaseCount('appointments', 0);
        Mail::assertNothingQueued();
        Queue::assertNothingPushed();
    }

    public static function bookingVersions(): array
    {
        return ['legacy' => [false], 'v2' => [true]];
    }

    public static function availabilityConflicts(): array
    {
        $cases = [];
        foreach (self::bookingVersions() as $version => [$enabled]) {
            foreach (['outside_hours', 'appointment', 'unavailability'] as $conflict) {
                $cases[$version.' '.$conflict] = [$enabled, $conflict];
            }
        }

        return $cases;
    }
}

<?php

use App\Jobs\DiscoverEarlierSlotOffersJob;
use App\Mail\AppointmentEarlierSlotAvailableMail;
use App\Mail\AppointmentEarlierSlotClaimedClientMail;
use App\Mail\AppointmentEarlierSlotClaimedTherapistMail;
use App\Models\Appointment;
use App\Models\AppointmentEarlierSlotOffer;
use App\Models\AppointmentEarlierSlotOpportunity;
use App\Models\Availability;
use App\Models\BookingLink;
use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Models\Meeting;
use App\Models\PracticeLocation;
use App\Models\Product;
use App\Models\User;
use App\Services\AppointmentEarlierSlotService;
use App\Services\AppointmentLifecycleService;
use App\Services\AppointmentMailDeliveryGuard;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Mime\Email;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow(Carbon::parse('2026-08-17 08:00:00'));
    config()->set('appointments.earlier_slots.enabled', true);
    Mail::fake();
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function earlierSlotPractitioner(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'accept_online_appointments' => true,
        'slug' => 'praticien-creneau-test',
        'email' => 'praticien-creneau@example.test',
        'company_email' => 'cabinet-creneau@example.test',
        'company_name' => 'Cabinet Sérénité',
        'minimum_notice_hours' => 0,
        'cancellation_notice_hours' => 0,
        'buffer_time_between_appointments' => 0,
    ], $attributes));
}

function earlierSlotProduct(User $practitioner, array $attributes = []): Product
{
    return Product::create(array_merge([
        'user_id' => $practitioner->id,
        'name' => 'Séance individuelle',
        'description' => 'Séance de test',
        'price' => 75,
        'tax_rate' => 0,
        'duration' => 60,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => true,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => false,
    ], $attributes));
}

function earlierSlotClient(User $practitioner, string $email, array $attributes = []): ClientProfile
{
    return ClientProfile::create(array_merge([
        'user_id' => $practitioner->id,
        'first_name' => 'Camille',
        'last_name' => 'Martin',
        'email' => $email,
        'phone' => '0612345678',
    ], $attributes));
}

function earlierSlotAppointment(
    User $practitioner,
    Product $product,
    ClientProfile $client,
    Carbon $start,
    array $attributes = []
): Appointment {
    return Appointment::create(array_merge([
        'user_id' => $practitioner->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => $start,
        'duration' => 60,
        'type' => 'visio',
        'status' => Appointment::STATUS_CONFIRMED,
        'wants_earlier_slot' => true,
        'earlier_slot_opted_in_at' => now(),
    ], $attributes));
}

function earlierSlotAvailability(User $practitioner, Carbon $date, ?int $locationId = null): Availability
{
    return Availability::create([
        'user_id' => $practitioner->id,
        'day_of_week' => $date->dayOfWeekIso - 1,
        'start_time' => '09:00:00',
        'end_time' => '14:00:00',
        'applies_to_all' => true,
        'practice_location_id' => $locationId,
    ]);
}

test('normal and private booking forms store the earlier slot preference without changing legacy defaults', function () {
    $practitioner = earlierSlotPractitioner();
    $product = earlierSlotProduct($practitioner);
    $bookingLink = BookingLink::create([
        'user_id' => $practitioner->id,
        'token' => 'creneau-plus-tot-prive',
        'name' => 'Lien privé',
        'allowed_product_ids' => [$product->id],
        'is_enabled' => true,
        'uses_count' => 0,
    ]);
    $date = Carbon::parse('2026-08-24 10:00:00');
    earlierSlotAvailability($practitioner, $date);

    $this->get(route('appointments.createPatient', $practitioner))
        ->assertOk()
        ->assertSee('Me prévenir si un rendez-vous plus tôt se libère')
        ->assertSee('name="wants_earlier_slot"', false);

    $this->get(route('bookingLinks.create', $bookingLink->token))
        ->assertOk()
        ->assertSee('Me prévenir si un rendez-vous plus tôt se libère')
        ->assertSee('name="wants_earlier_slot"', false);

    $this->get(route('mobile.appointments.create_from_therapist', $practitioner->slug))
        ->assertOk()
        ->assertSee('Me prévenir si un rendez-vous plus tôt se libère')
        ->assertSee('name="wants_earlier_slot"', false);

    $common = [
        'therapist_id' => $practitioner->id,
        'last_name' => 'Martin',
        'phone' => '0612345678',
        'appointment_date' => $date->toDateString(),
        'product_id' => $product->id,
        'type' => 'visio',
        'wants_earlier_slot' => 1,
    ];

    $this->post(route('appointments.storePatient'), array_merge($common, [
        'first_name' => 'Camille',
        'email' => 'camille-normal@example.test',
        'appointment_time' => '10:00',
    ]))->assertSessionHasNoErrors();

    $this->post(route('bookingLinks.store', $bookingLink->token), array_merge($common, [
        'first_name' => 'Alex',
        'email' => 'alex-prive@example.test',
        'appointment_time' => '12:00',
    ]))->assertSessionHasNoErrors();

    $this->post(route('mobile.appointments.store'), array_merge($common, [
        'first_name' => 'Morgan',
        'email' => 'morgan-mobile@example.test',
        'appointment_time' => '11:00',
    ]))->assertSessionHasNoErrors();

    expect(Appointment::query()->whereHas('clientProfile', fn ($query) => $query->where('email', 'camille-normal@example.test'))->firstOrFail()->wants_earlier_slot)->toBeTrue()
        ->and(Appointment::query()->whereHas('clientProfile', fn ($query) => $query->where('email', 'alex-prive@example.test'))->firstOrFail()->wants_earlier_slot)->toBeTrue()
        ->and(Appointment::query()->whereHas('clientProfile', fn ($query) => $query->where('email', 'morgan-mobile@example.test'))->firstOrFail()->wants_earlier_slot)->toBeTrue();

    config()->set('appointments.earlier_slots.enabled', false);

    $this->post(route('appointments.storePatient'), array_merge($common, [
        'first_name' => 'Lou',
        'email' => 'lou-legacy@example.test',
        'appointment_time' => '13:00',
    ]))->assertSessionHasNoErrors();

    expect(Appointment::query()->whereHas('clientProfile', fn ($query) => $query->where('email', 'lou-legacy@example.test'))->firstOrFail()->wants_earlier_slot)->toBeFalse();
});

test('only opted in appointments with the exact practitioner prestation duration mode and location receive an offer', function () {
    $practitioner = earlierSlotPractitioner();
    $product = earlierSlotProduct($practitioner, [
        'visio' => false,
        'dans_le_cabinet' => true,
    ]);
    $otherProduct = earlierSlotProduct($practitioner, [
        'name' => 'Autre séance',
        'visio' => false,
        'dans_le_cabinet' => true,
    ]);
    $location = PracticeLocation::create([
        'user_id' => $practitioner->id,
        'label' => 'Cabinet Centre',
        'address_line1' => '1 rue Centrale',
        'postal_code' => '75001',
        'city' => 'Paris',
        'country' => 'FR',
    ]);
    $otherLocation = PracticeLocation::create([
        'user_id' => $practitioner->id,
        'label' => 'Cabinet Nord',
        'address_line1' => '2 rue du Nord',
        'postal_code' => '75018',
        'city' => 'Paris',
        'country' => 'FR',
    ]);
    $releasedStart = Carbon::parse('2026-08-24 10:00:00');
    earlierSlotAvailability($practitioner, $releasedStart, $location->id);
    $released = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'released@example.test'),
        $releasedStart,
        ['status' => Appointment::STATUS_CANCELLED, 'type' => 'cabinet', 'practice_location_id' => $location->id, 'wants_earlier_slot' => false]
    );

    $eligible = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'eligible@example.test'),
        Carbon::parse('2026-09-07 10:00:00'),
        ['type' => 'cabinet', 'practice_location_id' => $location->id]
    );
    earlierSlotAppointment($practitioner, $product, earlierSlotClient($practitioner, 'wrong-duration@example.test'), Carbon::parse('2026-09-08 10:00:00'), [
        'duration' => 90, 'type' => 'cabinet', 'practice_location_id' => $location->id,
    ]);
    earlierSlotAppointment($practitioner, $otherProduct, earlierSlotClient($practitioner, 'wrong-product@example.test'), Carbon::parse('2026-09-09 10:00:00'), [
        'type' => 'cabinet', 'practice_location_id' => $location->id,
    ]);
    earlierSlotAppointment($practitioner, $product, earlierSlotClient($practitioner, 'wrong-mode@example.test'), Carbon::parse('2026-09-10 10:00:00'), [
        'type' => 'visio', 'practice_location_id' => null,
    ]);
    earlierSlotAppointment($practitioner, $product, earlierSlotClient($practitioner, 'wrong-location@example.test'), Carbon::parse('2026-09-11 10:00:00'), [
        'type' => 'cabinet', 'practice_location_id' => $otherLocation->id,
    ]);
    earlierSlotAppointment($practitioner, $product, earlierSlotClient($practitioner, 'not-opted-in@example.test'), Carbon::parse('2026-09-12 10:00:00'), [
        'type' => 'cabinet', 'practice_location_id' => $location->id, 'wants_earlier_slot' => false, 'earlier_slot_opted_in_at' => null,
    ]);
    earlierSlotAppointment($practitioner, $product, earlierSlotClient($practitioner, 'pending-payment@example.test'), Carbon::parse('2026-09-13 10:00:00'), [
        'type' => 'cabinet', 'practice_location_id' => $location->id, 'status' => Appointment::STATUS_PENDING_PAYMENT,
    ]);

    $opportunity = app(AppointmentEarlierSlotService::class)->discover(
        $released->id,
        $practitioner->id,
        $product->id,
        $releasedStart->toIso8601String(),
        60,
        'cabinet',
        $location->id,
    );

    expect($opportunity)->not->toBeNull()
        ->and($opportunity->offers()->count())->toBe(1)
        ->and($opportunity->offers()->firstOrFail()->appointment_id)->toBe($eligible->id);

    Mail::assertQueued(AppointmentEarlierSlotAvailableMail::class, 1);
    Mail::assertQueued(AppointmentEarlierSlotAvailableMail::class, function ($mail) use ($eligible) {
        $mail->build();

        return $mail->offer->appointment_id === $eligible->id
            && collect($mail->replyTo)->contains(fn ($address) => $address['address'] === 'cabinet-creneau@example.test');
    });
});

test('booking v2 earlier slot offers respect optimized grids and appointment buffer snapshots', function () {
    Queue::fake([DiscoverEarlierSlotOffersJob::class]);

    $practitioner = earlierSlotPractitioner([
        'booking_schedule_mode' => 'optimized',
        'booking_slot_interval_minutes' => 30,
    ]);
    config()->set('appointments.booking_v2.enabled', true);
    config()->set('appointments.booking_v2.allowed_user_ids', [$practitioner->id]);

    $product = earlierSlotProduct($practitioner, [
        'preparation_time_minutes' => 15,
        'buffer_time_after_minutes' => 30,
    ]);
    $releasedDate = Carbon::parse('2026-08-24 10:00:00');
    earlierSlotAvailability($practitioner, $releasedDate);

    earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'v2-blocker@example.test'),
        Carbon::parse('2026-08-24 09:00:00'),
        ['wants_earlier_slot' => false]
    );
    $invalidRelease = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'v2-invalid-release@example.test'),
        $releasedDate,
        ['status' => Appointment::STATUS_CANCELLED, 'wants_earlier_slot' => false]
    );
    $validRelease = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'v2-valid-release@example.test'),
        $releasedDate->copy()->setTime(10, 30),
        ['status' => Appointment::STATUS_CANCELLED, 'wants_earlier_slot' => false]
    );
    $waiting = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'v2-waiting@example.test'),
        Carbon::parse('2026-08-31 10:30:00')
    );

    $invalid = app(AppointmentEarlierSlotService::class)->discover(
        $invalidRelease->id,
        $practitioner->id,
        $product->id,
        $releasedDate->toIso8601String(),
        60,
        'visio',
        null,
    );
    expect($invalid)->toBeNull();

    $opportunity = app(AppointmentEarlierSlotService::class)->discover(
        $validRelease->id,
        $practitioner->id,
        $product->id,
        $releasedDate->copy()->setTime(10, 30)->toIso8601String(),
        60,
        'visio',
        null,
    );

    expect($opportunity)->not->toBeNull()
        ->and($opportunity->offers()->count())->toBe(1)
        ->and($opportunity->offers()->firstOrFail()->appointment_id)->toBe($waiting->id);

    $offer = $opportunity->offers()->firstOrFail();
    $result = app(AppointmentEarlierSlotService::class)->claim($offer->token);
    $moved = $waiting->fresh();

    expect($result['state'])->toBe(AppointmentEarlierSlotService::STATE_CLAIMED)
        ->and($moved->appointment_date->equalTo($releasedDate->copy()->setTime(10, 30)))->toBeTrue()
        ->and($moved->preparation_time_minutes)->toBe(15)
        ->and($moved->buffer_time_after_minutes)->toBe(30);
});

test('home and workplace offers require the same normalized address', function () {
    $practitioner = earlierSlotPractitioner();
    $product = earlierSlotProduct($practitioner, [
        'visio' => false,
        'adomicile' => true,
    ]);
    $releasedStart = Carbon::parse('2026-08-24 10:00:00');
    earlierSlotAvailability($practitioner, $releasedStart);
    $released = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'released-home@example.test', ['address' => '12, rue de la Paix, 75002 Paris']),
        $releasedStart,
        ['status' => Appointment::STATUS_CANCELLED, 'type' => 'domicile', 'wants_earlier_slot' => false]
    );
    $matching = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'matching-home@example.test', ['address' => '12 RUE DE LA PAIX 75002 PARIS']),
        Carbon::parse('2026-08-31 10:00:00'),
        ['type' => 'domicile']
    );
    earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'other-home@example.test', ['address' => '8 rue Victor Hugo, 75016 Paris']),
        Carbon::parse('2026-09-01 10:00:00'),
        ['type' => 'domicile']
    );

    $opportunity = app(AppointmentEarlierSlotService::class)->discover(
        $released->id,
        $practitioner->id,
        $product->id,
        $releasedStart->toIso8601String(),
        60,
        'domicile',
        null,
    );

    expect($opportunity)->not->toBeNull()
        ->and($opportunity->location_fingerprint)->toHaveLength(64)
        ->and($opportunity->offers()->count())->toBe(1)
        ->and($opportunity->offers()->firstOrFail()->appointment_id)->toBe($matching->id);
});

test('the first client claim moves the same paid appointment and invalidates every competing offer', function () {
    Queue::fake([DiscoverEarlierSlotOffersJob::class]);

    $practitioner = earlierSlotPractitioner();
    $product = earlierSlotProduct($practitioner);
    $releasedStart = Carbon::parse('2026-08-24 10:00:00');
    earlierSlotAvailability($practitioner, $releasedStart);
    $released = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'released-claim@example.test'),
        $releasedStart,
        ['status' => Appointment::STATUS_CANCELLED, 'wants_earlier_slot' => false]
    );
    $bookingLink = BookingLink::create([
        'user_id' => $practitioner->id,
        'token' => 'claim-preserved-link',
        'name' => 'Lien conservé',
        'allowed_product_ids' => [$product->id],
        'is_enabled' => true,
        'uses_count' => 0,
    ]);
    $winner = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'winner@example.test'),
        Carbon::parse('2026-08-31 10:00:00'),
        [
            'status' => Appointment::STATUS_PAID,
            'stripe_session_id' => 'cs_paid_preserved',
            'google_event_id' => 'google-event-preserved',
            'booking_link_id' => $bookingLink->id,
            'gift_voucher_amount_cents' => 1500,
            'reminder_24h_sent_at' => now(),
            'reminder_1h_sent_at' => now(),
        ]
    );
    $loser = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'loser@example.test'),
        Carbon::parse('2026-09-01 10:00:00')
    );
    $originalToken = $winner->token;
    $originalLoserStart = $loser->appointment_date->copy();
    $meeting = Meeting::create([
        'appointment_id' => $winner->id,
        'client_profile_id' => $winner->client_profile_id,
        'name' => 'Visio inchangée',
        'start_time' => $winner->appointment_date,
        'duration' => 60,
        'participant_email' => $winner->clientProfile->email,
        'room_token' => str_repeat('w', 32),
    ]);
    $invoice = Invoice::create([
        'user_id' => $practitioner->id,
        'client_profile_id' => $winner->client_profile_id,
        'appointment_id' => $winner->id,
        'invoice_date' => now()->toDateString(),
        'invoice_number' => 'F-EARLY-001',
        'status' => 'Payée',
        'type' => 'invoice',
        'total_amount' => 75,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 75,
    ]);

    $opportunity = app(AppointmentEarlierSlotService::class)->discover(
        $released->id,
        $practitioner->id,
        $product->id,
        $releasedStart->toIso8601String(),
        60,
        'visio',
        null,
    );
    $winnerOffer = $opportunity->offers()->where('appointment_id', $winner->id)->firstOrFail();
    $loserOffer = $opportunity->offers()->where('appointment_id', $loser->id)->firstOrFail();

    $offerPage = $this->get(route('appointments.earlier-slot.show', $winnerOffer->token));
    $offerPage
        ->assertOk()
        ->assertSee('Un créneau plus tôt est disponible')
        ->assertSee('Votre rendez-vous actuel reste réservé');
    expect($offerPage->headers->get('Cache-Control'))
        ->toContain('private')
        ->toContain('no-store');

    $this->post(route('appointments.earlier-slot.claim', $winnerOffer->token))
        ->assertSessionHasErrors('confirmation');
    expect($winner->fresh()->appointment_date->equalTo(Carbon::parse('2026-08-31 10:00:00')))->toBeTrue();

    $this->post(route('appointments.earlier-slot.claim', $winnerOffer->token), [
        'confirmation' => 1,
    ])->assertRedirect(route('appointments.earlier-slot.show', $winnerOffer->token));

    $this->get(route('appointments.earlier-slot.show', $winnerOffer->token))
        ->assertOk()
        ->assertSee('Votre rendez-vous a bien été avancé')
        ->assertSee('Le nouveau créneau est confirmé');

    $moved = $winner->fresh();
    expect($moved->appointment_date->equalTo($releasedStart))->toBeTrue()
        ->and($moved->token)->toBe($originalToken)
        ->and($moved->stripe_session_id)->toBe('cs_paid_preserved')
        ->and($moved->google_event_id)->toBe('google-event-preserved')
        ->and($moved->booking_link_id)->toBe($bookingLink->id)
        ->and($moved->gift_voucher_amount_cents)->toBe(1500)
        ->and($moved->canonicalStatus())->toBe(Appointment::STATUS_PAID)
        ->and($moved->wants_earlier_slot)->toBeFalse()
        ->and($moved->reminder_24h_sent_at)->toBeNull()
        ->and($moved->reminder_1h_sent_at)->toBeNull()
        ->and($invoice->fresh()->appointment_id)->toBe($winner->id)
        ->and($meeting->fresh()->room_token)->toBe(str_repeat('w', 32))
        ->and(Carbon::parse($meeting->fresh()->start_time)->equalTo(Carbon::parse('2026-08-31 10:00:00')))->toBeTrue()
        ->and($opportunity->fresh()->status)->toBe(AppointmentEarlierSlotOpportunity::STATUS_CLAIMED)
        ->and($winnerOffer->fresh()->status)->toBe(AppointmentEarlierSlotOffer::STATUS_CLAIMED)
        ->and($loserOffer->fresh()->status)->toBe(AppointmentEarlierSlotOffer::STATUS_INVALIDATED);

    $loserClaim = $this->post(route('appointments.earlier-slot.claim', $loserOffer->token), [
        'confirmation' => 1,
    ]);
    $loserClaim
        ->assertSessionHas('error', 'Une autre personne a confirmé ce créneau avant vous. Aucun changement n’a été apporté à votre rendez-vous.')
        ->assertRedirect(route('appointments.earlier-slot.show', $loserOffer->token));

    $this->get(route('appointments.earlier-slot.show', $loserOffer->token))
        ->assertOk()
        ->assertSee('Ce créneau a déjà été réservé')
        ->assertSee('Une autre personne a confirmé ce créneau')
        ->assertSee('Cette proposition n’a apporté aucun changement à votre rendez-vous.');

    expect($loser->fresh()->appointment_date->equalTo($originalLoserStart))->toBeTrue();

    Mail::assertQueued(AppointmentEarlierSlotClaimedClientMail::class, fn ($mail) => $mail->appointment->id === $winner->id);
    Mail::assertQueued(AppointmentEarlierSlotClaimedTherapistMail::class, fn ($mail) => $mail->appointment->id === $winner->id);
    Queue::assertPushed(DiscoverEarlierSlotOffersJob::class, fn ($job) => $job->releasedAppointmentId === $winner->id);
});

test('a slot occupied after the email was prepared cannot be claimed', function () {
    Queue::fake([DiscoverEarlierSlotOffersJob::class]);

    $practitioner = earlierSlotPractitioner();
    $product = earlierSlotProduct($practitioner);
    $releasedStart = Carbon::parse('2026-08-24 10:00:00');
    earlierSlotAvailability($practitioner, $releasedStart);
    $released = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'released-race@example.test'),
        $releasedStart,
        ['status' => Appointment::STATUS_CANCELLED, 'wants_earlier_slot' => false]
    );
    $candidate = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'candidate-race@example.test'),
        Carbon::parse('2026-08-31 10:00:00')
    );
    $oldStart = $candidate->appointment_date->copy();
    $opportunity = app(AppointmentEarlierSlotService::class)->discover(
        $released->id,
        $practitioner->id,
        $product->id,
        $releasedStart->toIso8601String(),
        60,
        'visio',
        null,
    );
    $offer = $opportunity->offers()->firstOrFail();

    earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'occupied-race@example.test'),
        $releasedStart,
        ['wants_earlier_slot' => false]
    );

    $queuedEmail = (new Email)->text('Proposition de test');
    $queuedEmail->getHeaders()->addTextHeader(
        AppointmentMailDeliveryGuard::APPOINTMENT_HEADER,
        (string) $candidate->id
    );
    $queuedEmail->getHeaders()->addTextHeader(
        AppointmentMailDeliveryGuard::MESSAGE_HEADER,
        'earlier-slot-offer:'.$offer->id
    );

    expect(app(AppointmentMailDeliveryGuard::class)->handle(new MessageSending($queuedEmail)))->toBeFalse();

    $this->post(route('appointments.earlier-slot.claim', $offer->token), [
        'confirmation' => 1,
    ])->assertSessionHas(
        'error',
        'Ce créneau n’est malheureusement plus disponible. Aucun changement n’a été apporté à votre rendez-vous.'
    );

    $this->get(route('appointments.earlier-slot.show', $offer->token))
        ->assertOk()
        ->assertSee('Cette proposition n’est plus disponible')
        ->assertSee('Le créneau a été réservé, a expiré ou n’est plus compatible avec votre rendez-vous.');

    expect($candidate->fresh()->appointment_date->equalTo($oldStart))->toBeTrue()
        ->and($opportunity->fresh()->status)->toBe(AppointmentEarlierSlotOpportunity::STATUS_CLOSED)
        ->and($offer->fresh()->status)->toBe(AppointmentEarlierSlotOffer::STATUS_INVALIDATED);

    Mail::assertNotQueued(AppointmentEarlierSlotClaimedClientMail::class);
    Mail::assertNotQueued(AppointmentEarlierSlotClaimedTherapistMail::class);
});

test('the earlier-slot confirmation email contains the existing visio room link', function () {
    $practitioner = earlierSlotPractitioner();
    $product = earlierSlotProduct($practitioner);
    $client = earlierSlotClient($practitioner, 'visio-confirmation@example.test');
    $appointment = earlierSlotAppointment(
        $practitioner,
        $product,
        $client,
        Carbon::parse('2026-08-31 10:00:00')
    );
    $room = str_repeat('e', 32);

    Meeting::create([
        'appointment_id' => $appointment->id,
        'client_profile_id' => $client->id,
        'name' => 'Visio créneau avancé',
        'start_time' => $appointment->appointment_date,
        'duration' => 60,
        'participant_email' => $client->email,
        'room_token' => $room,
    ]);

    $html = (new AppointmentEarlierSlotClaimedClientMail(
        $appointment,
        $appointment->appointment_date->copy()->addWeek()
    ))->render();

    expect($html)
        ->toContain('Rejoindre la visio')
        ->toContain($room);
});

test('cancelling and rescheduling appointments queue discovery for the exact released slot', function () {
    Queue::fake([DiscoverEarlierSlotOffersJob::class]);

    $practitioner = earlierSlotPractitioner();
    $product = earlierSlotProduct($practitioner);
    $client = earlierSlotClient($practitioner, 'lifecycle-release@example.test');
    $cancelledStart = Carbon::parse('2026-08-24 10:00:00');
    $rescheduledStart = Carbon::parse('2026-08-31 10:00:00');
    $newStart = Carbon::parse('2026-09-07 11:00:00');
    earlierSlotAvailability($practitioner, $newStart);

    $cancelled = earlierSlotAppointment($practitioner, $product, $client, $cancelledStart, [
        'wants_earlier_slot' => false,
    ]);
    $rescheduled = earlierSlotAppointment($practitioner, $product, $client, $rescheduledStart, [
        'wants_earlier_slot' => false,
    ]);

    $lifecycle = app(AppointmentLifecycleService::class);
    $lifecycle->cancel($cancelled, 'practitioner', $practitioner->id);
    $lifecycle->reschedule($rescheduled, $newStart, 'practitioner', $practitioner->id);

    Queue::assertPushed(DiscoverEarlierSlotOffersJob::class, function ($job) use ($cancelled, $cancelledStart) {
        return $job->releasedAppointmentId === $cancelled->id
            && Carbon::parse($job->slotStart)->equalTo($cancelledStart)
            && $job->mode === 'visio';
    });
    Queue::assertPushed(DiscoverEarlierSlotOffersJob::class, function ($job) use ($rescheduled, $rescheduledStart) {
        return $job->releasedAppointmentId === $rescheduled->id
            && Carbon::parse($job->slotStart)->equalTo($rescheduledStart)
            && $job->mode === 'visio';
    });
});

test('an appointment is never offered the slot that it just released itself', function () {
    $practitioner = earlierSlotPractitioner();
    $product = earlierSlotProduct($practitioner);
    $releasedStart = Carbon::parse('2026-08-24 10:00:00');
    earlierSlotAvailability($practitioner, $releasedStart);
    $appointment = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'self-release@example.test'),
        Carbon::parse('2026-08-31 10:00:00')
    );

    $opportunity = app(AppointmentEarlierSlotService::class)->discover(
        $appointment->id,
        $practitioner->id,
        $product->id,
        $releasedStart->toIso8601String(),
        60,
        'visio',
        null,
    );

    expect($opportunity)->toBeNull();
    Mail::assertNotQueued(AppointmentEarlierSlotAvailableMail::class);
});

test('discovery still works safely if the appointment that released the slot was deleted', function () {
    $practitioner = earlierSlotPractitioner();
    $product = earlierSlotProduct($practitioner);
    $releasedStart = Carbon::parse('2026-08-24 10:00:00');
    earlierSlotAvailability($practitioner, $releasedStart);
    $released = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'deleted-release@example.test'),
        $releasedStart,
        ['status' => Appointment::STATUS_CANCELLED, 'wants_earlier_slot' => false]
    );
    $candidate = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'after-delete@example.test'),
        Carbon::parse('2026-08-31 10:00:00')
    );
    $releasedId = $released->id;
    $released->delete();

    $opportunity = app(AppointmentEarlierSlotService::class)->discover(
        $releasedId,
        $practitioner->id,
        $product->id,
        $releasedStart->toIso8601String(),
        60,
        'visio',
        null,
    );

    expect($opportunity)->not->toBeNull()
        ->and($opportunity->released_appointment_id)->toBeNull()
        ->and($opportunity->offers()->firstOrFail()->appointment_id)->toBe($candidate->id);
});

test('clients can enable and disable the preference from their secure management link', function () {
    $practitioner = earlierSlotPractitioner();
    $product = earlierSlotProduct($practitioner);
    $appointment = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'preference@example.test'),
        Carbon::parse('2026-08-31 10:00:00'),
        ['wants_earlier_slot' => false, 'earlier_slot_opted_in_at' => null]
    );

    $this->get(route('appointments.showPatient', $appointment->token))
        ->assertOk()
        ->assertSee('Vous préférez un rendez-vous plus tôt ?');

    $this->post(route('appointment.confirmation.earlier-slot-preference', $appointment->token), [
        'enabled' => 1,
    ])->assertSessionHas('success');

    expect($appointment->fresh()->wants_earlier_slot)->toBeTrue()
        ->and($appointment->fresh()->earlier_slot_opted_in_at)->not->toBeNull();

    $this->post(route('appointment.confirmation.earlier-slot-preference', $appointment->token), [
        'enabled' => 0,
    ])->assertSessionHas('success');

    expect($appointment->fresh()->wants_earlier_slot)->toBeFalse()
        ->and($appointment->fresh()->earlier_slot_opted_in_at)->toBeNull();

    $this->post(route('appointment.confirmation.earlier-slot-preference', str_repeat('x', 64)), [
        'enabled' => 1,
    ])->assertNotFound();
    $this->get(route('appointments.earlier-slot.show', str_repeat('x', 64)))->assertNotFound();
});

test('legacy cabinet appointments without an identified location are not offered automatically', function () {
    $practitioner = earlierSlotPractitioner();
    $product = earlierSlotProduct($practitioner, [
        'visio' => false,
        'dans_le_cabinet' => true,
    ]);
    $releasedStart = Carbon::parse('2026-08-24 10:00:00');
    earlierSlotAvailability($practitioner, $releasedStart);
    $released = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'legacy-location@example.test'),
        $releasedStart,
        ['status' => Appointment::STATUS_CANCELLED, 'type' => 'cabinet', 'practice_location_id' => null, 'wants_earlier_slot' => false]
    );
    earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'legacy-location-candidate@example.test'),
        Carbon::parse('2026-08-31 10:00:00'),
        ['type' => 'cabinet', 'practice_location_id' => null]
    );

    $opportunity = app(AppointmentEarlierSlotService::class)->discover(
        $released->id,
        $practitioner->id,
        $product->id,
        $releasedStart->toIso8601String(),
        60,
        'cabinet',
        null,
    );

    expect($opportunity)->toBeNull();
    Mail::assertNotQueued(AppointmentEarlierSlotAvailableMail::class);
});

test('expired offers are invalidated and old closed history is purged safely', function () {
    $practitioner = earlierSlotPractitioner();
    $product = earlierSlotProduct($practitioner);
    $appointment = earlierSlotAppointment(
        $practitioner,
        $product,
        earlierSlotClient($practitioner, 'cleanup@example.test'),
        Carbon::parse('2026-08-31 10:00:00')
    );
    $opportunity = AppointmentEarlierSlotOpportunity::create([
        'user_id' => $practitioner->id,
        'released_appointment_id' => null,
        'product_id' => $product->id,
        'slot_start' => now()->subMinute(),
        'duration' => 60,
        'mode' => 'visio',
        'status' => AppointmentEarlierSlotOpportunity::STATUS_OPEN,
        'expires_at' => now()->subMinute(),
    ]);
    $offer = AppointmentEarlierSlotOffer::create([
        'opportunity_id' => $opportunity->id,
        'appointment_id' => $appointment->id,
        'token' => str_repeat('t', 64),
        'token_hash' => hash('sha256', str_repeat('t', 64)),
        'status' => AppointmentEarlierSlotOffer::STATUS_PENDING,
    ]);

    $this->artisan('appointments:expire-earlier-slot-offers')->assertSuccessful();

    expect($opportunity->fresh()->status)->toBe(AppointmentEarlierSlotOpportunity::STATUS_EXPIRED)
        ->and($offer->fresh()->status)->toBe(AppointmentEarlierSlotOffer::STATUS_INVALIDATED);

    $opportunity->forceFill(['created_at' => now()->subDays(100)])->save();
    $this->artisan('appointments:expire-earlier-slot-offers')->assertSuccessful();

    $this->assertDatabaseMissing('appointment_earlier_slot_opportunities', ['id' => $opportunity->id]);
    $this->assertDatabaseMissing('appointment_earlier_slot_offers', ['id' => $offer->id]);
});

test('the disabled feature does not require its schema during a normal booking', function () {
    config()->set('appointments.earlier_slots.enabled', false);
    Schema::dropIfExists('appointment_earlier_slot_offers');
    Schema::dropIfExists('appointment_earlier_slot_opportunities');
    Schema::table('appointments', function ($table) {
        $table->dropIndex('appt_earlier_slot_optin_idx');
        $table->dropColumn(['wants_earlier_slot', 'earlier_slot_opted_in_at']);
    });

    $practitioner = earlierSlotPractitioner();
    $product = earlierSlotProduct($practitioner);
    $date = Carbon::parse('2026-08-24 10:00:00');
    earlierSlotAvailability($practitioner, $date);

    $this->post(route('appointments.storePatient'), [
        'therapist_id' => $practitioner->id,
        'first_name' => 'Camille',
        'last_name' => 'Legacy',
        'email' => 'schema-free@example.test',
        'phone' => '0612345678',
        'appointment_date' => $date->toDateString(),
        'appointment_time' => $date->format('H:i'),
        'product_id' => $product->id,
        'type' => 'visio',
        'wants_earlier_slot' => 1,
    ])->assertSessionHasNoErrors();

    expect(Appointment::query()->whereHas(
        'clientProfile',
        fn ($query) => $query->where('email', 'schema-free@example.test')
    )->exists())->toBeTrue();

    $this->artisan('appointments:expire-earlier-slot-offers')->assertSuccessful();
});

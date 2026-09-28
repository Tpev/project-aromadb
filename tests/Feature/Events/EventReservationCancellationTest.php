<?php

namespace Tests\Feature\Events;

use App\Jobs\ExpireEventReservationCheckout;
use App\Jobs\SendPaidEventConfirmationJob;
use App\Mail\EventReminderClientMail;
use App\Mail\NewReservationNotification;
use App\Mail\ReservationConfirmation;
use App\Models\ClientProfile;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use App\Services\EventPaymentService;
use App\Services\EventReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Tests\TestCase;

class EventReservationCancellationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
        $this->owner = User::factory()->create([
            'is_therapist' => true, 'license_status' => 'active',
            'license_product' => 'new_pro_mensuelle', 'stripe_account_id' => 'acct_cancel_test',
            'slug' => 'event-cancellation-test',
        ]);
        $this->event = Event::create([
            'user_id' => $this->owner->id, 'name' => 'Atelier annulation',
            'start_date_time' => now()->addDay(), 'duration' => 60,
            'booking_required' => true, 'limited_spot' => true, 'number_of_spot' => 1,
            'showOnPortail' => true, 'location' => 'Cabinet', 'event_type' => 'in_person',
            'collect_payment' => false, 'tax_rate' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        ApiRequestor::setHttpClient(new \Stripe\HttpClient\CurlClient);
        parent::tearDown();
    }

    private function reservation(string $status = 'confirmed'): Reservation
    {
        return $this->event->reservations()->create([
            'full_name' => 'Camille Test', 'email' => 'camille@example.test', 'status' => $status,
            'amount_ttc' => 50, 'currency' => 'eur',
            'stripe_session_id' => $status === 'confirmed' ? null : 'cs_cancel_test',
            'stripe_payment_intent_id' => $status === 'paid' ? 'pi_cancel_test' : null,
        ]);
    }

    public function test_owner_can_cancel_once_from_both_screens_and_keep_paid_history(): void
    {
        $reservation = $this->reservation('paid');
        $this->actingAs($this->owner);
        foreach (['events.show', 'mobile.events.show'] as $route) {
            $this->get(route($route, $this->event))->assertOk()
                ->assertSee('Annuler la réservation')->assertSee(route('reservations.destroy', $reservation), false);
        }
        $this->from(route('mobile.events.show', $this->event))
            ->delete(route('reservations.destroy', $reservation))
            ->assertRedirect(route('mobile.events.show', $this->event))->assertSessionHas('success');
        $cancelledAt = $reservation->fresh()->cancelled_at->toISOString();
        $this->delete(route('reservations.destroy', $reservation))->assertSessionHas('success');
        $reservation->refresh();
        $this->assertSame($cancelledAt, $reservation->cancelled_at->toISOString());
        $this->assertEquals($this->owner->id, $reservation->cancelled_by);
        $this->assertSame('paid', $reservation->status);
        $this->assertSame('pi_cancel_test', $reservation->stripe_payment_intent_id);
        $this->assertSame(50.0, $reservation->amount_ttc);
        $this->assertSame(0, $this->event->reservations()->active()->count());
        foreach (['events.show', 'mobile.events.show'] as $route) {
            $this->get(route($route, $this->event))->assertOk()
                ->assertDontSee(route('reservations.destroy', $reservation), false)
                ->assertSee('Camille Test')->assertSee('Paiement reçu')->assertSee('Annulée le');
        }
        Queue::assertNotPushed(ExpireEventReservationCheckout::class);
        Mail::assertNothingSent();
    }

    public function test_guest_and_other_therapist_cannot_cancel(): void
    {
        $reservation = $this->reservation();
        $this->delete(route('reservations.destroy', $reservation))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())
            ->delete(route('reservations.destroy', $reservation))->assertForbidden();
        $this->assertTrue($reservation->fresh()->isActive());
        $this->assertNull($reservation->fresh()->cancelled_at);
    }

    public function test_cancelled_place_is_available_on_public_pages_and_for_new_bookings(): void
    {
        $reservation = $this->reservation();
        $this->get(route('events.reserve.create', $this->event))->assertSee('Cet événement est complet.');
        app(EventReservationService::class)->cancel($reservation, $this->owner);
        $this->get(route('events.reserve.create', $this->event))->assertDontSee('Cet événement est complet.')
            ->assertSee('<p class="info-text">1</p>', false);
        $this->get(route('events.public.show', $this->event))->assertOk()->assertSee('Places restantes :');
        $this->get(route('therapist.show', ['slug' => $this->owner->slug]))->assertOk()->assertSee('Réserver');
        Validator::extend('captcha', fn () => true);
        $this->post(route('events.reserve.store', $this->event), [
            'full_name' => 'Nouvelle participante', 'email' => 'new@example.test', 'g-recaptcha-response' => 'test',
        ])->assertRedirect(route('reservations.success', $this->event));
        $this->assertSame(1, $this->event->reservations()->active()->count());
        $this->assertSame(2, $this->event->reservations()->count());
    }

    public function test_manual_reregistration_works_on_desktop_and_mobile(): void
    {
        $reservation = $this->reservation();
        $client = ClientProfile::create([
            'user_id' => $this->owner->id, 'first_name' => 'Camille', 'last_name' => 'Test', 'email' => $reservation->email,
        ]);
        $this->actingAs($this->owner);
        foreach (['events.reservations.addFromClient', 'mobile.events.participants.add-client'] as $route) {
            app(EventReservationService::class)->cancel($reservation, $this->owner);
            $this->post(route($route, $this->event), ['client_profile_id' => $client->id])->assertSessionHas('success');
            $this->assertSame(1, $this->event->reservations()->active()->count());
            $reservation = $this->event->reservations()->active()->firstOrFail();
        }
        $this->assertSame(3, $this->event->reservations()->count());
    }

    public function test_reservation_service_enforces_capacity_for_every_creation_path(): void
    {
        $this->reservation();
        $this->expectException(ValidationException::class);
        app(EventReservationService::class)->reserve($this->event, ['full_name' => 'Too late', 'email' => 'late@example.test']);
    }

    public function test_late_or_replayed_payment_preserves_cancellation_and_does_not_send_confirmations(): void
    {
        $reservation = $this->reservation('pending_payment');
        app(EventReservationService::class)->cancel($reservation, $this->owner);
        Queue::assertPushed(ExpireEventReservationCheckout::class);
        for ($i = 0; $i < 2; $i++) {
            $this->assertTrue(app(EventPaymentService::class)->confirm(
                $reservation->id, 'acct_cancel_test', 5000, 'eur', 'pi_cancel_test', 'cs_cancel_test'
            ));
        }
        $reservation->refresh();
        $this->assertSame('paid', $reservation->status);
        $this->assertNotNull($reservation->cancelled_at);
        $this->assertFalse($reservation->isActive());
        $this->assertFalse($reservation->isEmailEligible());
        Queue::assertNotPushed(SendPaidEventConfirmationJob::class);
        (new SendPaidEventConfirmationJob($reservation->id))->handle();
        Mail::assertNothingSent();
    }

    public function test_scheduled_and_already_queued_emails_are_suppressed(): void
    {
        $reservation = $this->reservation();
        $queued = [new ReservationConfirmation($reservation), new NewReservationNotification($reservation),
            new EventReminderClientMail($this->event, $reservation, '24h')];
        app(EventReservationService::class)->cancel($reservation, $this->owner);
        $this->artisan('events:send-reminders')->assertSuccessful();
        Mail::assertNothingQueued();
        config(['mail.default' => 'array']);
        Mail::swap(new \Illuminate\Mail\MailManager(app()));
        foreach ($queued as $mail) {
            $mail->to($reservation->email)->send(Mail::mailer());
        }
        $this->assertCount(0, Mail::mailer()->getSymfonyTransport()->messages());
    }

    public function test_unsigned_checkout_cancel_return_cannot_change_a_reservation(): void
    {
        $reservation = $this->reservation('pending_payment');
        $this->get(route('reservations.payment_cancel', ['reservation_id' => $reservation->id]))->assertRedirect();
        $this->assertSame('pending_payment', $reservation->fresh()->status);
        $this->get(URL::signedRoute('reservations.payment_cancel', ['reservation_id' => $reservation->id]))->assertRedirect();
        $this->assertSame('canceled', $reservation->fresh()->status);
        $this->assertTrue(app(EventPaymentService::class)->confirm(
            $reservation->id, 'acct_cancel_test', 5000, 'eur', 'pi_cancel_test', 'cs_cancel_test'
        ));
        $this->assertTrue($reservation->fresh()->isActive());
    }

    public function test_expiry_webhook_releases_only_the_matching_unpaid_reservation(): void
    {
        $reservation = $this->reservation('pending_payment');
        $webhook = (object) ['type' => 'checkout.session.expired', 'data' => (object) ['object' => (object) [
            'id' => 'cs_cancel_test', 'metadata' => (object) ['reservation_id' => $reservation->id],
        ]]];
        $payments = app(EventPaymentService::class);
        $payments->handleWebhook($webhook, 'acct_other');
        $this->assertTrue($reservation->fresh()->isActive());
        $webhook->data->object->id = 'cs_other';
        $payments->handleWebhook($webhook, 'acct_cancel_test');
        $this->assertTrue($reservation->fresh()->isActive());
        $webhook->data->object->id = 'cs_cancel_test';
        $payments->handleWebhook($webhook, 'acct_cancel_test');
        $this->assertSame('expired', $reservation->fresh()->status);
        $this->assertFalse($reservation->fresh()->isActive());
        $reservation->update(['status' => 'paid']);
        $payments->handleWebhook($webhook, 'acct_cancel_test');
        $this->assertSame('paid', $reservation->fresh()->status);
    }

    public function test_cancelled_participants_are_not_copied_when_duplicating_an_event(): void
    {
        $reservation = $this->reservation();
        app(EventReservationService::class)->cancel($reservation, $this->owner);
        $active = app(EventReservationService::class)->reserve($this->event, ['full_name' => 'Active', 'email' => 'active@example.test']);
        $payload = $this->event->only(['name', 'duration', 'booking_required', 'limited_spot', 'number_of_spot', 'showOnPortail', 'location', 'event_type']);
        $payload['start_date_time'] = now()->addWeek()->format('Y-m-d H:i:s');
        $payload['duplicate_participants'] = true;
        $payload['send_confirmation_to_copied_participants'] = true;
        $this->actingAs($this->owner)->post(route('events.duplicate.store', $this->event), $payload)->assertSessionHasNoErrors();
        $copy = Event::latest('id')->firstOrFail();
        $this->assertNotSame($this->event->id, $copy->id);
        $this->assertSame([$active->email], $copy->reservations()->pluck('email')->all());
        Mail::assertQueued(ReservationConfirmation::class, 1);
    }

    public function test_checkout_job_expires_the_connected_account_session(): void
    {
        $reservation = $this->reservation('pending_payment');
        app(EventReservationService::class)->cancel($reservation, $this->owner);
        config(['services.stripe.secret' => 'sk_test_local']);
        $http = \Mockery::mock(ClientInterface::class);
        ApiRequestor::setHttpClient($http);
        $http->shouldReceive('request')->once()->withArgs(function ($method, $url, $headers) {
            return $method === 'get' && str_ends_with($url, '/checkout/sessions/cs_cancel_test')
                && in_array('Stripe-Account: acct_cancel_test', $headers, true);
        })->andReturn([json_encode(['id' => 'cs_cancel_test', 'object' => 'checkout.session', 'status' => 'open', 'payment_status' => 'unpaid']), 200, []]);
        $http->shouldReceive('request')->once()->withArgs(fn ($method, $url) => $method === 'post' && str_ends_with($url, '/cs_cancel_test/expire'))
            ->andReturn([json_encode(['id' => 'cs_cancel_test', 'object' => 'checkout.session', 'status' => 'expired', 'payment_status' => 'unpaid']), 200, []]);
        (new ExpireEventReservationCheckout($reservation->id))->handle();
        $this->assertNotNull($reservation->fresh()->cancelled_at);
    }

    public function test_payment_completing_during_checkout_expiration_is_recorded_without_restoring_attendance(): void
    {
        $reservation = $this->reservation('pending_payment');
        app(EventReservationService::class)->cancel($reservation, $this->owner);
        config(['services.stripe.secret' => 'sk_test_local']);
        $http = \Mockery::mock(ClientInterface::class);
        ApiRequestor::setHttpClient($http);
        $http->shouldReceive('request')->once()->ordered()->andReturn([
            json_encode(['id' => 'cs_cancel_test', 'object' => 'checkout.session', 'status' => 'open', 'payment_status' => 'unpaid']), 200, [],
        ]);
        $http->shouldReceive('request')->once()->ordered()->andReturn([
            json_encode(['error' => ['type' => 'invalid_request_error', 'message' => 'Session is already complete.']]), 400, [],
        ]);
        $http->shouldReceive('request')->once()->ordered()->andReturn([
            json_encode(['id' => 'cs_cancel_test', 'object' => 'checkout.session', 'status' => 'complete',
                'payment_status' => 'paid', 'payment_intent' => 'pi_cancel_test', 'amount_total' => 5000, 'currency' => 'eur']), 200, [],
        ]);
        (new ExpireEventReservationCheckout($reservation->id))->handle();
        $this->assertSame('paid', $reservation->fresh()->status);
        $this->assertFalse($reservation->fresh()->isActive());
        Queue::assertNotPushed(SendPaidEventConfirmationJob::class);
    }

    public function test_conversion_tracking_retains_a_paid_reservations_cancellation(): void
    {
        config(['offer_journeys.enabled' => true]);
        $reservation = $this->reservation('paid');
        $journey = \App\Domain\OfferJourneys\Models\OfferJourney::create([
            'user_id' => $this->owner->id, 'name' => 'Atelier', 'slug' => 'atelier-cancel',
            'objective' => 'event', 'status' => 'published', 'source_type' => 'event', 'source_id' => $this->event->id,
        ]);
        $contact = \App\Domain\OfferJourneys\Models\OfferJourneyContact::create([
            'user_id' => $this->owner->id, 'email' => $reservation->email,
            'email_normalized' => $reservation->email, 'status' => 'new', 'last_activity_at' => now(),
        ]);
        \App\Domain\OfferJourneys\Models\OfferJourneyEntry::create([
            'offer_journey_id' => $journey->id, 'offer_journey_contact_id' => $contact->id,
            'status' => 'active', 'entered_at' => now(), 'last_activity_at' => now(),
        ]);
        $reservation->touch();
        app(EventReservationService::class)->cancel($reservation, $this->owner);
        $this->assertSame('cancelled', \App\Domain\OfferJourneys\Models\OfferJourneyConversion::firstOrFail()->status);
        app(EventPaymentService::class)->confirm($reservation->id, 'acct_cancel_test', 5000, 'eur', 'pi_cancel_test', 'cs_cancel_test');
        $this->assertSame('cancelled', \App\Domain\OfferJourneys\Models\OfferJourneyConversion::firstOrFail()->status);
    }

    public function test_cancellation_migration_round_trip_preserves_reservations_and_payments(): void
    {
        $reservation = $this->reservation('paid');
        $migration = require database_path('migrations/2026_09_28_120000_add_cancellation_to_event_reservations.php');
        $migration->down();
        $migration->up();
        $reservation->refresh();
        $this->assertSame('paid', $reservation->status);
        $this->assertSame('pi_cancel_test', $reservation->stripe_payment_intent_id);
        $this->assertSame(50.0, $reservation->amount_ttc);
        $this->assertNull($reservation->cancelled_at);
        $this->assertTrue($reservation->isActive());
    }

    public function test_cancellation_during_checkout_creation_closes_the_session_when_its_id_arrives(): void
    {
        $this->event->update(['collect_payment' => true, 'price' => 50]);
        config(['services.stripe.secret' => 'sk_test_local']);
        Validator::extend('captcha', fn () => true);
        $http = \Mockery::mock(ClientInterface::class);
        ApiRequestor::setHttpClient($http);
        $http->shouldReceive('request')->once()->andReturnUsing(function () {
            $reservation = $this->event->reservations()->firstOrFail();
            $this->assertNull($reservation->stripe_session_id);
            app(EventReservationService::class)->cancel($reservation, $this->owner);

            return [json_encode(['id' => 'cs_creation_race', 'object' => 'checkout.session', 'url' => 'https://checkout.stripe.test/race']), 200, []];
        });
        $this->post(route('events.reserve.store', $this->event), [
            'full_name' => 'Camille Test', 'email' => 'camille@example.test', 'g-recaptcha-response' => 'test',
        ])->assertRedirect(route('events.reserve.create', $this->event))->assertSessionHas('error', 'Cette réservation a été annulée.');
        $reservation = $this->event->reservations()->firstOrFail();
        $this->assertSame('cs_creation_race', $reservation->stripe_session_id);
        $this->assertFalse($reservation->isActive());
        Queue::assertPushed(ExpireEventReservationCheckout::class, 1);
    }
}

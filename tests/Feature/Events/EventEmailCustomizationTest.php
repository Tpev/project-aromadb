<?php

use App\Jobs\SendPaidEventConfirmationJob;
use App\Mail\EventReminderClientMail;
use App\Mail\NewReservationNotification;
use App\Mail\ReservationConfirmation;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use App\Services\EventMailDeliveryGuard;
use App\Services\EventPaymentService;
use App\Services\StripeFinanceSyncService;
use App\Support\EmailNote;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\Mime\Email;

beforeEach(function () {
    Mail::fake();
    Queue::fake();
    $this->therapist = User::factory()->create([
        'is_therapist' => true, 'license_status' => 'active', 'license_product' => 'new_pro_mensuelle',
        'stripe_account_id' => 'acct_event_notes', 'slug' => 'event-email-notes',
    ]);
    $this->eventPayload = [
        'name' => 'Atelier personnalisé', 'description' => 'Description',
        'start_date_time' => now()->addDay()->format('Y-m-d H:i:s'), 'duration' => 60,
        'booking_required' => 1, 'limited_spot' => 0, 'showOnPortail' => 1,
        'location' => 'Cabinet', 'event_type' => 'in_person', 'collect_payment' => 0,
        'confirmation_email_note' => "Bienvenue !\nhttps://example.test/infos?a=1&b=2",
        'reminder_email_note' => 'Pensez à apporter votre tapis.',
    ];
    $this->event = Event::create($this->eventPayload + ['user_id' => $this->therapist->id]);
    $this->reservation = Reservation::create([
        'event_id' => $this->event->id, 'full_name' => 'Camille Test', 'email' => 'camille@example.test',
        'status' => 'confirmed', 'amount_ttc' => 50, 'currency' => 'eur', 'stripe_session_id' => 'cs_event_notes',
    ]);
});

test('event notes can be created edited cleared and previewed on desktop and mobile', function (string $prefix) {
    $this->actingAs($this->therapist)->get(route($prefix.'events.create'))->assertOk()
        ->assertSee('name="confirmation_email_note"', false)->assertSee('data-note-preview="reminder_email_note"', false);
    $this->post(route($prefix.'events.store'), $this->eventPayload)->assertRedirect()->assertSessionHasNoErrors();
    $created = Event::latest('id')->firstOrFail();
    expect($created->confirmation_email_note)->toBe($this->eventPayload['confirmation_email_note']);
    $this->get(route($prefix.'events.edit', $created))->assertOk()->assertSee('Pensez à apporter votre tapis.');
    $this->put(route($prefix.'events.update', $created), array_replace($this->eventPayload, [
        'confirmation_email_note' => '', 'reminder_email_note' => 'Nouveau rappel',
    ]))->assertRedirect()->assertSessionHasNoErrors();
    expect($created->fresh()->confirmation_email_note)->toBeNull()
        ->and($created->fresh()->reminder_email_note)->toBe('Nouveau rappel');
    $this->put(route($prefix.'events.update', $created), array_replace($this->eventPayload, [
        'confirmation_email_note' => str_repeat('a', 2001),
    ]))->assertSessionHasErrors('confirmation_email_note');
    Mail::assertNothingOutgoing();
})->with(['', 'mobile.']);

test('duplicating an event carries both editable message sections', function () {
    $this->actingAs($this->therapist)->get(route('events.duplicate', $this->event))
        ->assertOk()->assertSee('Pensez à apporter votre tapis.');
    $this->post(route('events.duplicate.store', $this->event), $this->eventPayload)
        ->assertRedirect()->assertSessionHasNoErrors();
    expect(Event::latest('id')->first()->reminder_email_note)->toBe($this->event->reminder_email_note);
});

test('notes retain line breaks and safe links without allowing injected markup', function () {
    $note = "Apportez un tapis.\nhttps://example.test/guide?a=1&b=2\n<script>alert(1)</script> javascript:alert(2)";
    $html = EmailNote::html($note);
    expect($html)->toContain('<br', 'href="https://example.test/guide?a=1&amp;b=2"', '&lt;script&gt;')
        ->not->toContain('<script>', 'href="javascript:');
    $this->event->update(['confirmation_email_note' => $note, 'reminder_email_note' => $note]);
    foreach ([new ReservationConfirmation($this->reservation), new EventReminderClientMail($this->event, $this->reservation, '24h'), new EventReminderClientMail($this->event, $this->reservation, '1h')] as $mail) {
        $rendered = $mail->render();
        expect($rendered)->toContain('Apportez un tapis.', 'Atelier personnalisé', 'Cabinet', 'https://example.test/guide')
            ->not->toContain('<script>');
    }
});

test('already queued emails use the latest saved event message', function () {
    $confirmation = new ReservationConfirmation($this->reservation);
    $reminder = new EventReminderClientMail($this->event, $this->reservation);
    $this->event->update(['confirmation_email_note' => 'Confirmation actualisée', 'reminder_email_note' => 'Rappel actualisé']);
    expect($confirmation->render())->toContain('Confirmation actualisée');
    expect($reminder->render())->toContain('Rappel actualisé')->not->toContain('Pensez à apporter');
});

test('reminders exclude unpaid cancelled and failed registrations', function (string $timing) {
    $this->event->update(['start_date_time' => $timing === '1h' ? now()->addHour() : now()->addDay()]);
    foreach (['paid', 'pending_payment', 'cancelled', 'failed'] as $status) {
        Reservation::create(['event_id' => $this->event->id, 'full_name' => $status, 'email' => $status.'@example.test', 'status' => $status]);
    }
    $this->artisan('events:send-reminders')->assertSuccessful();
    $this->artisan('events:send-reminders')->assertSuccessful();
    Mail::assertQueued(EventReminderClientMail::class, 2);
    Mail::assertQueued(EventReminderClientMail::class, fn ($mail) => $mail->timingLabel === $timing && $mail->reservation->isEmailEligible());
    expect(Reservation::where('status', 'pending_payment')->first()->getAttribute('reminder_'.$timing.'_sent_at'))->toBeNull();
})->with(['24h', '1h']);

test('delivery guard drops stale reminders after cancellation or event rescheduling', function () {
    $message = (new Email)->from('test@example.test')->to('client@example.test')->text('Rappel');
    $message->getHeaders()->addTextHeader(EventMailDeliveryGuard::RESERVATION_HEADER, (string) $this->reservation->id);
    $message->getHeaders()->addTextHeader(EventMailDeliveryGuard::MESSAGE_HEADER, 'reminder:24h');
    $guard = app(EventMailDeliveryGuard::class);
    expect($guard->handle(new MessageSending($message)))->toBeNull();
    $this->reservation->update(['status' => 'cancelled']);
    expect($guard->handle(new MessageSending($message)))->toBeFalse();
    $this->reservation->update(['status' => 'paid']);
    $this->event->update(['start_date_time' => now()->addWeek()]);
    expect($guard->handle(new MessageSending($message)))->toBeFalse();
});

test('paid event webhook confirms without a browser return and delivery is replay safe', function () {
    $this->reservation->update(['status' => 'pending_payment']);
    config(['services.stripe.secret' => 'sk_test_local', 'services.stripe.webhook' => 'whsec_local']);
    $this->mock(StripeFinanceSyncService::class)->shouldReceive('ingestWebhookEvent')->andReturnNull();
    $payload = json_encode([
        'id' => 'evt_confirmation', 'object' => 'event', 'type' => 'checkout.session.completed', 'account' => 'acct_event_notes',
        'data' => ['object' => ['id' => 'cs_event_notes', 'object' => 'checkout.session', 'payment_status' => 'paid',
            'amount_total' => 5000, 'currency' => 'eur', 'payment_intent' => 'pi_event_notes',
            'metadata' => ['reservation_id' => (string) $this->reservation->id]]],
    ]);
    $signature = 't='.time().',v1='.hash_hmac('sha256', time().'.'.$payload, 'whsec_local');
    for ($i = 0; $i < 2; $i++) {
        $this->call('POST', '/stripe/webhook', [], [], [], ['HTTP_STRIPE_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'], $payload)->assertOk();
        (new SendPaidEventConfirmationJob($this->reservation->id))->handle();
    }
    expect($this->reservation->fresh()->status)->toBe('paid')->and($this->reservation->fresh()->confirmation_sent_at)->not->toBeNull();
    Mail::assertSent(ReservationConfirmation::class, 1);
    Mail::assertSent(NewReservationNotification::class, 1);
    Queue::assertPushed(SendPaidEventConfirmationJob::class, 1);
});

test('event fulfillment rejects mismatched payment details', function (array $overrides) {
    $this->reservation->update(['status' => 'pending_payment']);
    $args = array_replace(['reservationId' => $this->reservation->id, 'accountId' => 'acct_event_notes', 'amountCents' => 5000,
        'currency' => 'eur', 'paymentIntentId' => 'pi_event_notes', 'sessionId' => 'cs_event_notes'], $overrides);
    expect(app(EventPaymentService::class)->confirm(...$args))->toBeFalse()
        ->and($this->reservation->fresh()->status)->toBe('pending_payment');
    Queue::assertNotPushed(SendPaidEventConfirmationJob::class);
})->with([[['accountId' => 'acct_other']], [['amountCents' => 1]], [['currency' => 'usd']], [['sessionId' => 'cs_other']]]);

test('legacy paid reservations do not resend confirmations on a callback after deployment', function () {
    $this->reservation->update(['status' => 'paid', 'stripe_payment_intent_id' => 'pi_event_notes']);
    expect(app(EventPaymentService::class)->confirm($this->reservation->id, 'acct_event_notes', 5000, 'eur', 'pi_event_notes', 'cs_event_notes'))->toBeTrue();
    Queue::assertNotPushed(SendPaidEventConfirmationJob::class);
});

test('a verified event payment can still complete after returning through the legacy cancel url', function () {
    $this->reservation->update(['status' => 'canceled']);
    expect(app(EventPaymentService::class)->confirm($this->reservation->id, 'acct_event_notes', 5000, 'eur', 'pi_event_notes', 'cs_event_notes'))->toBeTrue()
        ->and($this->reservation->fresh()->status)->toBe('paid');
    Queue::assertPushed(SendPaidEventConfirmationJob::class, 1);
});

test('real mail transport applies the reservation guard to confirmations and queued reminders', function () {
    config(['mail.default' => 'array']);
    Mail::swap(new \Illuminate\Mail\MailManager(app()));
    $transport = Mail::mailer()->getSymfonyTransport();
    foreach (['pending_payment', 'paid', 'canceled'] as $status) {
        $this->reservation->update(['status' => $status]);
        (new ReservationConfirmation($this->reservation))->to($this->reservation->email)->send(Mail::mailer());
        // Invoke the same mailable method used by a queued SendQueuedMailable job.
        (new EventReminderClientMail($this->event, $this->reservation, '24h'))->to($this->reservation->email)->send(Mail::mailer());
    }
    expect($transport->messages())->toHaveCount(2);
});

test('a partial event email failure retries only the unsent recipient', function () {
    $this->reservation->update(['status' => 'pending_payment']);
    app(EventPaymentService::class)->confirm($this->reservation->id, 'acct_event_notes', 5000, 'eur', 'pi_event_notes', 'cs_event_notes');
    $clientMail = Mockery::mock(\Illuminate\Mail\PendingMail::class);
    $practitionerMail = Mockery::mock(\Illuminate\Mail\PendingMail::class);
    Mail::shouldReceive('to')->with($this->reservation->email)->once()->andReturn($clientMail);
    Mail::shouldReceive('to')->with($this->therapist->email)->twice()->andReturn($practitionerMail);
    $clientMail->shouldReceive('send')->once();
    $practitionerMail->shouldReceive('send')->once()->andThrow(new RuntimeException('Temporary mail failure'))->ordered();
    $practitionerMail->shouldReceive('send')->once()->andReturnNull()->ordered();
    $job = new SendPaidEventConfirmationJob($this->reservation->id);
    expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'Temporary mail failure');
    expect($this->reservation->fresh()->confirmation_sent_at)->not->toBeNull()
        ->and($this->reservation->fresh()->therapist_notification_sent_at)->toBeNull();
    $job->handle();
    $job->handle();
    expect($this->reservation->fresh()->therapist_notification_sent_at)->not->toBeNull();
});

test('the additive migration upgrades existing data and can be rolled back without losing reservations', function () {
    $migration = require database_path('migrations/2026_09_17_180000_add_booking_and_event_communication_settings.php');
    $this->reservation->update(['status' => 'paid', 'stripe_payment_intent_id' => 'pi_existing']);
    $migration->down();
    try {
        expect(\Illuminate\Support\Facades\Schema::hasColumn('users', 'booking_phone_required'))->toBeFalse();
        $migration->up();
        expect($this->therapist->fresh()->booking_phone_required)->toBeFalse()
            ->and($this->event->fresh()->confirmation_email_note)->toBeNull()
            ->and($this->reservation->fresh()->status)->toBe('paid')
            ->and($this->reservation->fresh()->stripe_payment_intent_id)->toBe('pi_existing')
            ->and($this->reservation->fresh()->payment_confirmation_requested_at)->toBeNull();
    } finally {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('users', 'booking_phone_required')) {
            $migration->up();
        }
    }
});

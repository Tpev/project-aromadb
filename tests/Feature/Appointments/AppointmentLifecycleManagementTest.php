<?php

use App\Jobs\SendAppointmentConfirmationJob;
use App\Jobs\SendAppointmentReminderJob;
use App\Mail\AppointmentCancellationConfirmedClientMail;
use App\Mail\AppointmentPaymentAfterCancellationMail;
use App\Mail\AppointmentRescheduledClientMail;
use App\Mail\AppointmentRescheduledTherapistMail;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\ClientProfile;
use App\Models\Meeting;
use App\Models\Product;
use App\Models\User;
use App\Services\AppointmentLifecycleService;
use App\Services\AppointmentMailDeliveryGuard;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;

uses(RefreshDatabase::class);

function lifecycleUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'email' => 'praticien-lifecycle@example.test',
        'company_email' => 'cabinet-lifecycle@example.test',
        'company_name' => 'Cabinet Test',
        'cancellation_notice_hours' => 0,
        'minimum_notice_hours' => 0,
        'buffer_time_between_appointments' => 0,
    ], $attributes));
}

function lifecycleProduct(User $user, array $attributes = []): Product
{
    return Product::create(array_merge([
        'user_id' => $user->id,
        'name' => 'Séance test',
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

function lifecycleClient(User $user, array $attributes = []): ClientProfile
{
    return ClientProfile::create(array_merge([
        'user_id' => $user->id,
        'first_name' => 'Camille',
        'last_name' => 'Client',
        'email' => 'camille-lifecycle@example.test',
        'phone' => '0612345678',
    ], $attributes));
}

function lifecycleAppointment(User $user, Product $product, ClientProfile $client, array $attributes = []): Appointment
{
    return Appointment::create(array_merge([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => now()->addDays(7)->setTime(10, 0),
        'duration' => 60,
        'type' => 'visio',
        'status' => 'Confirmé',
    ], $attributes));
}

test('all known status aliases remain compatible', function () {
    $aliases = [
        'scheduled' => Appointment::STATUS_SCHEDULED,
        'Programmé' => Appointment::STATUS_SCHEDULED,
        'Programme' => Appointment::STATUS_SCHEDULED,
        'pending' => Appointment::STATUS_PENDING_PAYMENT,
        'Payée' => Appointment::STATUS_PAID,
        'Confirmé' => Appointment::STATUS_CONFIRMED,
        'Confirme' => Appointment::STATUS_CONFIRMED,
        'Complété' => Appointment::STATUS_COMPLETED,
        'Complete' => Appointment::STATUS_COMPLETED,
        'cancelled' => Appointment::STATUS_CANCELLED,
        'canceled' => Appointment::STATUS_CANCELLED,
        'Annulé' => Appointment::STATUS_CANCELLED,
        'Annule' => Appointment::STATUS_CANCELLED,
        'Annulée' => Appointment::STATUS_CANCELLED,
        'Annulee' => Appointment::STATUS_CANCELLED,
    ];

    foreach ($aliases as $legacy => $canonical) {
        expect(Appointment::normalizeStatus($legacy))->toBe($canonical);
    }
});

test('every cancellation alias frees the slot and skips a queued reminder', function (string $cancelledStatus) {
    Mail::fake();
    $user = lifecycleUser(['email' => uniqid('alias-practitioner-').'@example.test']);
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user, ['email' => uniqid('alias-client-').'@example.test']);
    $slotStart = now()->addDays(5)->setTime(10, 0)->startOfMinute();

    Availability::create([
        'user_id' => $user->id,
        'day_of_week' => $slotStart->dayOfWeekIso - 1,
        'start_time' => '09:00:00',
        'end_time' => '13:00:00',
        'applies_to_all' => true,
    ]);

    $cancelled = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => $slotStart,
        'status' => $cancelledStatus,
    ]);
    $candidate = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => now()->addDays(20),
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    expect(Appointment::query()->notCancelled()->whereKey($cancelled->id)->doesntExist())->toBeTrue()
        ->and(app(\App\Services\AppointmentAvailabilityService::class)->isAvailable($candidate, $slotStart))->toBeTrue();

    $reminderAppointment = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => now()->addHour()->startOfMinute(),
        'status' => $cancelledStatus,
    ]);

    (new SendAppointmentReminderJob(
        $reminderAppointment->id,
        '1h',
        $reminderAppointment->appointment_date->toIso8601String(),
        now()->toIso8601String()
    ))->handle();

    Mail::assertNothingSent();
})->with(Appointment::CANCELLED_STATUSES);

test('legacy practitioner appointments have a working management page', function (string $legacyStatus) {
    $user = lifecycleUser(['email' => uniqid('practitioner-').'@example.test']);
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user, ['email' => uniqid('client-').'@example.test']);
    $appointment = lifecycleAppointment($user, $product, $client, ['status' => $legacyStatus]);

    $this->get(route('appointments.showPatient', $appointment->token))
        ->assertOk()
        ->assertSee('Gérer mon rendez-vous')
        ->assertSee('Modifier le créneau')
        ->assertSee('Annuler le rendez-vous');
})->with(['Confirmé', 'Programme', 'Confirme', 'scheduled', 'Payée']);

test('client cancellation is non destructive, idempotent and audited', function () {
    Mail::fake();
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, [
        'reminder_24h_sent_at' => now(),
        'reminder_1h_sent_at' => now(),
    ]);
    $meeting = Meeting::create([
        'appointment_id' => $appointment->id,
        'client_profile_id' => $client->id,
        'name' => 'Visio test',
        'start_time' => $appointment->appointment_date,
        'duration' => 60,
        'participant_email' => $client->email,
        'room_token' => str_repeat('r', 32),
    ]);

    $response = $this->post(route('appointment.confirmation.cancel', $appointment->token), [
        'cancellation_reason' => 'Empêchement personnel',
    ]);

    $response->assertRedirect(route('appointments.showPatient', $appointment->token));
    $fresh = $appointment->fresh();
    expect($fresh)->not->toBeNull()
        ->and($fresh->status)->toBe(Appointment::STATUS_CANCELLED)
        ->and($fresh->cancelled_at)->not->toBeNull()
        ->and($fresh->cancellation_reason)->toBe('Empêchement personnel')
        ->and($meeting->fresh())->not->toBeNull()
        ->and($fresh->activities()->where('action', 'cancelled')->count())->toBe(1);

    $this->post(route('appointment.confirmation.cancel', $appointment->token))->assertRedirect();
    expect($fresh->activities()->where('action', 'cancelled')->count())->toBe(1);

    Mail::assertQueued(AppointmentCancellationConfirmedClientMail::class);
});

test('rescheduling preserves commercial data and leaves the visio meeting untouched', function () {
    Mail::fake();
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $oldStart = now()->addDays(7)->setTime(10, 0)->startOfMinute();
    $newStart = $oldStart->copy()->addWeek()->setTime(11, 0);

    Availability::create([
        'user_id' => $user->id,
        'day_of_week' => $newStart->dayOfWeekIso - 1,
        'start_time' => '09:00:00',
        'end_time' => '13:00:00',
        'applies_to_all' => true,
    ]);

    $appointment = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => $oldStart,
        'stripe_session_id' => 'cs_preserved',
        'status' => 'Payée',
        'reminder_24h_sent_at' => now(),
        'reminder_1h_sent_at' => now(),
    ]);
    $originalToken = $appointment->token;
    $meeting = Meeting::create([
        'appointment_id' => $appointment->id,
        'client_profile_id' => $client->id,
        'name' => 'Visio test',
        'start_time' => $oldStart,
        'duration' => 60,
        'participant_email' => $client->email,
        'room_token' => str_repeat('v', 32),
    ]);

    $this->post(route('appointment.confirmation.reschedule', $appointment->token), [
        'appointment_date' => $newStart->toDateString(),
        'appointment_time' => $newStart->format('H:i'),
    ])->assertRedirect(route('appointments.showPatient', $originalToken));

    $fresh = $appointment->fresh();
    expect($fresh->appointment_date->equalTo($newStart))->toBeTrue()
        ->and($fresh->token)->toBe($originalToken)
        ->and($fresh->product_id)->toBe($product->id)
        ->and($fresh->client_profile_id)->toBe($client->id)
        ->and($fresh->duration)->toBe(60)
        ->and($fresh->stripe_session_id)->toBe('cs_preserved')
        ->and($fresh->canonicalStatus())->toBe(Appointment::STATUS_PAID)
        ->and($fresh->reminder_24h_sent_at)->toBeNull()
        ->and($fresh->reminder_1h_sent_at)->toBeNull()
        ->and($meeting->fresh()->room_token)->toBe(str_repeat('v', 32))
        ->and(Carbon::parse($meeting->fresh()->start_time)->equalTo($oldStart))->toBeTrue()
        ->and((int) $meeting->fresh()->duration)->toBe(60)
        ->and($fresh->activities()->where('action', 'rescheduled')->count())->toBe(1);

    Mail::assertQueued(AppointmentRescheduledClientMail::class);
    Mail::assertQueued(AppointmentRescheduledTherapistMail::class);
});

test('an occupied replacement slot is rejected without changing the appointment', function () {
    Mail::fake();
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $otherClient = lifecycleClient($user, ['email' => 'occupied-slot@example.test']);
    $oldStart = now()->addDays(7)->setTime(10, 0)->startOfMinute();
    $newStart = $oldStart->copy()->addDay()->setTime(11, 0);

    Availability::create([
        'user_id' => $user->id,
        'day_of_week' => $newStart->dayOfWeekIso - 1,
        'start_time' => '09:00:00',
        'end_time' => '13:00:00',
        'applies_to_all' => true,
    ]);

    $appointment = lifecycleAppointment($user, $product, $client, ['appointment_date' => $oldStart]);
    lifecycleAppointment($user, $product, $otherClient, [
        'appointment_date' => $newStart,
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    $this->from(route('appointment.confirmation.reschedule.form', $appointment->token))
        ->post(route('appointment.confirmation.reschedule', $appointment->token), [
            'appointment_date' => $newStart->toDateString(),
            'appointment_time' => $newStart->format('H:i'),
        ])
        ->assertRedirect(route('appointment.confirmation.reschedule.form', $appointment->token))
        ->assertSessionHasErrors('appointment_time');

    expect($appointment->fresh()->appointment_date->equalTo($oldStart))->toBeTrue()
        ->and($appointment->fresh()->activities()->count())->toBe(0);
    Mail::assertNothingQueued();
});

test('a queued reminder and confirmation are skipped after cancellation', function () {
    Mail::fake();
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => now()->addHour(),
        'status' => Appointment::STATUS_CANCELLED,
    ]);

    (new SendAppointmentReminderJob(
        $appointment->id,
        '1h',
        $appointment->appointment_date->toIso8601String(),
        now()->toIso8601String()
    ))->handle();
    (new SendAppointmentConfirmationJob($appointment->id))->handle();

    Mail::assertNothingSent();
    Mail::assertNothingQueued();
});

test('the final mail transport guard stops stale appointment emails', function () {
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => now()->addDays(5),
        'status' => Appointment::STATUS_CANCELLED,
    ]);

    $guardedEmail = (new Email())->text('Test');
    $guardedEmail->getHeaders()->addTextHeader(
        AppointmentMailDeliveryGuard::APPOINTMENT_HEADER,
        (string) $appointment->id
    );
    $guardedEmail->getHeaders()->addTextHeader(
        AppointmentMailDeliveryGuard::MESSAGE_HEADER,
        'confirmation'
    );

    $guard = app(AppointmentMailDeliveryGuard::class);

    expect($guard->handle(new MessageSending($guardedEmail)))->toBeFalse()
        ->and($guard->handle(new MessageSending((new Email())->text('Sans garde'))))->toBeNull();
});

test('the final mail transport guard validates reminder timing', function () {
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => now()->addDays(5),
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    $email = (new Email())->text('Test');
    $email->getHeaders()->addTextHeader(
        AppointmentMailDeliveryGuard::APPOINTMENT_HEADER,
        (string) $appointment->id
    );
    $email->getHeaders()->addTextHeader(
        AppointmentMailDeliveryGuard::MESSAGE_HEADER,
        'reminder'
    );

    $guard = app(AppointmentMailDeliveryGuard::class);
    expect($guard->handle(new MessageSending($email)))->toBeFalse();

    $appointment->update(['appointment_date' => now()->addHour()]);
    expect($guard->handle(new MessageSending($email)))->toBeNull();
});

test('client confirmation delivery is idempotent and marked only after transport', function () {
    Mail::fake();
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, ['status' => 'confirmed']);

    (new SendAppointmentConfirmationJob($appointment->id))->handle();
    (new SendAppointmentConfirmationJob($appointment->id))->handle();

    Mail::assertSent(\App\Mail\AppointmentCreatedPatientMail::class, 1);
    expect($appointment->fresh()->client_confirmation_sent_at)->not->toBeNull();
});

test('a reminder queued for an old date is skipped after rescheduling', function () {
    Mail::fake();
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $oldStart = now()->addHour()->startOfMinute();
    $appointment = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => $oldStart->copy()->addDay(),
        'status' => 'confirmed',
    ]);

    (new SendAppointmentReminderJob(
        $appointment->id,
        '1h',
        $oldStart->toIso8601String(),
        now()->toIso8601String()
    ))->handle();

    Mail::assertNotSent(\App\Mail\AppointmentReminderClientMail::class);
});

test('the practitioner deadline applies to cancellation and rescheduling', function () {
    Mail::fake();
    $user = lifecycleUser(['cancellation_notice_hours' => 24]);
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => now()->addHours(12),
    ]);

    $this->get(route('appointments.showPatient', $appointment->token))
        ->assertOk()
        ->assertDontSee('Modifier le créneau');

    $this->post(route('appointment.confirmation.cancel', $appointment->token))
        ->assertSessionHas('error');

    expect($appointment->fresh()->isCancelled())->toBeFalse();
});

test('the legacy numeric cancellation URL never mutates an appointment', function () {
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, ['status' => 'pending']);

    $this->get(route('appointments.cancel', ['appointment_id' => $appointment->id]))
        ->assertRedirect(route('welcome'));

    expect($appointment->fresh())->not->toBeNull()
        ->and($appointment->fresh()->isPendingPayment())->toBeTrue();
});

test('client portals only expose their own active appointments', function () {
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $other = lifecycleClient($user, ['email' => 'other-lifecycle@example.test']);
    $own = lifecycleAppointment($user, $product, $client);
    $cancelled = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => now()->addDays(8),
        'status' => 'Annulée',
    ]);
    $foreign = lifecycleAppointment($user, $product, $other, [
        'appointment_date' => now()->addDays(9),
    ]);

    $this->actingAs($client, 'client')->get(route('client.home'))
        ->assertOk()
        ->assertSee(route('client.appointments.show', $own), false)
        ->assertDontSee($cancelled->token)
        ->assertDontSee($foreign->token);

    $this->actingAs($client, 'client')
        ->get(route('client.appointments.show', $foreign))
        ->assertNotFound();

    $this->actingAs($client, 'client')
        ->post(route('client.appointments.cancel', $foreign))
        ->assertNotFound();

    expect($foreign->fresh()->isCancelled())->toBeFalse();
});

test('invalid management tokens are rejected without leaking appointments', function () {
    $this->get(route('appointments.showPatient', str_repeat('x', 63)))->assertNotFound();
    $this->get(route('appointments.showPatient', str_repeat('x', 64)))->assertNotFound();
});

test('paid pack and voucher references remain intact after cancellation', function () {
    Mail::fake();
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, [
        'status' => Appointment::STATUS_PAID,
        'stripe_session_id' => 'cs_paid_preserved',
        'consumed_pack_purchase_id' => 4242,
        'gift_voucher_amount_cents' => 2500,
    ]);

    $this->get(route('appointments.showPatient', $appointment->token))
        ->assertOk()
        ->assertSee('n’entraîne pas automatiquement un remboursement');

    $this->post(route('appointment.confirmation.cancel', $appointment->token))->assertRedirect();

    $fresh = $appointment->fresh();
    expect($fresh->isCancelled())->toBeTrue()
        ->and($fresh->stripe_session_id)->toBe('cs_paid_preserved')
        ->and($fresh->consumed_pack_purchase_id)->toBe(4242)
        ->and($fresh->gift_voucher_amount_cents)->toBe(2500)
        ->and($fresh->financial_follow_up_required)->toBeTrue();
});

test('normal practitioners cannot hard delete appointment history', function () {
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client);

    $this->actingAs($user)->delete(route('appointments.destroy', $appointment))->assertForbidden();
    expect($appointment->fresh())->not->toBeNull();
});

test('a late payment never reactivates a cancelled appointment', function () {
    Mail::fake();
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, ['status' => 'cancelled']);

    $controller = app(\App\Http\Controllers\AppointmentController::class);
    $result = $controller->finalizeStripeAppointmentPayment(
        $appointment,
        [],
        7500,
        'pi_late_test'
    );
    $controller->finalizeStripeAppointmentPayment($appointment, [], 7500, 'pi_late_test');

    expect($result['cancelled_payment_received'])->toBeTrue()
        ->and($appointment->fresh()->isCancelled())->toBeTrue()
        ->and($appointment->fresh()->financial_follow_up_required)->toBeTrue()
        ->and($appointment->fresh()->activities()->where('action', 'payment_received_after_cancellation')->count())->toBe(1);

    Mail::assertQueued(AppointmentPaymentAfterCancellationMail::class, 1);
});

test('a cancelled appointment cannot be edited or completed by a practitioner', function () {
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, [
        'status' => Appointment::STATUS_CANCELLED,
    ]);

    $this->actingAs($user)
        ->get(route('appointments.edit', $appointment))
        ->assertRedirect(route('appointments.show', $appointment));

    $this->actingAs($user)
        ->put(route('appointments.update', $appointment), ['status' => Appointment::STATUS_SCHEDULED])
        ->assertRedirect(route('appointments.show', $appointment));

    $this->actingAs($user)
        ->put(route('appointments.complete', $appointment))
        ->assertRedirect();

    expect($appointment->fresh()->isCancelled())->toBeTrue();
});

test('a pending payment page clearly lets the client resume checkout', function () {
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, [
        'status' => 'pending',
        'stripe_session_id' => 'cs_resume_test',
    ]);

    $this->get(route('appointments.showPatient', $appointment->token))
        ->assertOk()
        ->assertSee('Reprendre le paiement')
        ->assertSee(route('appointment.confirmation.payment.resume', $appointment->token), false);
});

test('expired pending payments are cancelled while fresh holds remain active', function () {
    Mail::fake();
    config(['appointments.pending_payment_expiry_minutes' => 35]);
    $user = lifecycleUser(['stripe_account_id' => null]);
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $expired = lifecycleAppointment($user, $product, $client, [
        'status' => 'pending',
        'stripe_session_id' => 'cs_expired_test',
    ]);
    $expired->forceFill([
        'created_at' => now()->subMinutes(40),
        'updated_at' => now()->subMinutes(40),
    ])->saveQuietly();
    $fresh = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => now()->addDays(8),
        'status' => Appointment::STATUS_PENDING_PAYMENT,
        'stripe_session_id' => 'cs_fresh_test',
    ]);

    $this->artisan('appointments:expire-pending-payments')->assertSuccessful();
    $this->artisan('appointments:expire-pending-payments')->assertSuccessful();

    expect($expired->fresh()->isCancelled())->toBeTrue()
        ->and($expired->fresh()->cancelled_by_type)->toBe('system')
        ->and($expired->fresh()->activities()->where('action', 'payment_expired')->count())->toBe(1)
        ->and(Appointment::query()->notCancelled()->whereKey($expired->id)->doesntExist())->toBeTrue()
        ->and($fresh->fresh()->isPendingPayment())->toBeTrue();

    Mail::assertNothingQueued();
});

test('an invalid rescheduling date is rejected without a server error', function () {
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client);

    $this->from(route('appointments.showPatient', $appointment->token))
        ->get(route('appointment.confirmation.reschedule.form', [
            'token' => $appointment->token,
            'date' => 'not-a-date',
        ]))
        ->assertRedirect(route('appointments.showPatient', $appointment->token))
        ->assertSessionHasErrors('date');
});

test('a cancelled appointment ICS response is explicitly marked as a cancellation', function () {
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, [
        'status' => Appointment::STATUS_CANCELLED,
    ]);

    $this->get(route('appointments.downloadICS', $appointment->token))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/calendar; charset=UTF-8; method=CANCEL')
        ->assertSee('METHOD:CANCEL', false)
        ->assertSee('STATUS:CANCELLED', false);

    $mail = new AppointmentCancellationConfirmedClientMail($appointment);
    expect($mail->render())->toContain(route('appointments.downloadICS', $appointment->token));
});

test('cancelled appointments are excluded from practitioner dashboard indicators', function () {
    $user = lifecycleUser(['license_status' => 'active']);
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $active = lifecycleAppointment($user, $product, $client);
    $cancelled = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => now()->addDays(8),
        'status' => 'Annulée',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard-pro'))
        ->assertOk()
        ->assertViewHas('upcomingAppointments', 1)
        ->assertViewHas('recentAppointments', function ($appointments) use ($active, $cancelled) {
            return $appointments->contains('id', $active->id)
                && !$appointments->contains('id', $cancelled->id);
        });
});

test('a practitioner can mark an uncompleted past appointment as cancelled', function () {
    Mail::fake();
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => now()->subDay(),
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    $this->actingAs($user)
        ->post(route('appointments.lifecycle.cancel', $appointment))
        ->assertRedirect(route('appointments.show', $appointment))
        ->assertSessionHas('success', 'Le rendez-vous passé a été marqué comme annulé. Aucun email n’a été envoyé au client.');

    $fresh = $appointment->fresh();
    $activity = $fresh->activities()->where('action', 'cancelled')->firstOrFail();

    expect($fresh->isCancelled())->toBeTrue()
        ->and($fresh->status_label)->toBe('Annulé')
        ->and($fresh->cancelled_by_type)->toBe('practitioner')
        ->and($activity->metadata['historical_correction'])->toBeTrue();

    Mail::assertNothingQueued();
});

test('clients still cannot cancel past appointments', function () {
    Mail::fake();
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => now()->subDay(),
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    $this->post(route('appointment.confirmation.cancel', $appointment->token))
        ->assertRedirect(route('appointments.showPatient', $appointment->token))
        ->assertSessionHas('error');

    expect($appointment->fresh()->isCancelled())->toBeFalse();
    Mail::assertNothingQueued();
});

test('completed appointments remain protected from historical cancellation', function () {
    Mail::fake();
    $user = lifecycleUser();
    $product = lifecycleProduct($user);
    $client = lifecycleClient($user);
    $appointment = lifecycleAppointment($user, $product, $client, [
        'appointment_date' => now()->subDay(),
        'status' => Appointment::STATUS_COMPLETED,
    ]);

    $this->actingAs($user)
        ->post(route('appointments.lifecycle.cancel', $appointment))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($appointment->fresh()->isCompleted())->toBeTrue()
        ->and($appointment->fresh()->isCancelled())->toBeFalse();
    Mail::assertNothingQueued();
});

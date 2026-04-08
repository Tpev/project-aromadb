<?php

use App\Mail\AppointmentCancelledByClient;
use App\Mail\AppointmentReminderClientMail;
use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function cancellationTherapist(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'email' => 'therapist@example.test',
        'company_email' => 'cabinet@example.test',
        'cancellation_notice_hours' => 0,
    ], $attributes));
}

function cancellationProduct(User $therapist, array $attributes = []): Product
{
    return Product::create(array_merge([
        'user_id' => $therapist->id,
        'name' => 'Séance test',
        'price' => 80,
        'tax_rate' => 0,
        'duration' => 60,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => false,
        'en_visio' => false,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => true,
    ], $attributes));
}

function cancellationClient(User $therapist, string $email = 'client@example.test'): ClientProfile
{
    return ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => 'Delphine',
        'last_name' => 'Test',
        'email' => $email,
    ]);
}

function cancellationAppointment(User $therapist, ClientProfile $client, Product $product, array $attributes = []): Appointment
{
    return Appointment::create(array_merge([
        'client_profile_id' => $client->id,
        'user_id' => $therapist->id,
        'product_id' => $product->id,
        'appointment_date' => now()->addDay(),
        'status' => 'confirmed',
        'duration' => 60,
        'type' => 'cabinet',
    ], $attributes));
}

test('client can cancel a future appointment from the patient link', function () {
    Mail::fake();

    $therapist = cancellationTherapist();
    $product = cancellationProduct($therapist);
    $client = cancellationClient($therapist);
    $appointment = cancellationAppointment($therapist, $client, $product, [
        'appointment_date' => now()->addHours(30),
    ]);

    $response = $this->post(route('appointment.confirmation.cancel', $appointment->token));

    $response->assertRedirect(route('appointments.showPatient', $appointment->token));

    expect($appointment->fresh()->status)->toBe('cancelled');

    Mail::assertQueued(AppointmentCancelledByClient::class, function ($mail) use ($therapist, $appointment) {
        return $mail->hasTo($therapist->company_email)
            && $mail->appointment->is($appointment->fresh());
    });
});

test('client cancellation is blocked when the cutoff window is exceeded', function () {
    Mail::fake();

    $therapist = cancellationTherapist([
        'cancellation_notice_hours' => 24,
    ]);
    $product = cancellationProduct($therapist);
    $client = cancellationClient($therapist, 'cutoff@example.test');
    $appointment = cancellationAppointment($therapist, $client, $product, [
        'appointment_date' => now()->addHours(2),
    ]);

    $response = $this->post(route('appointment.confirmation.cancel', $appointment->token));

    $response->assertRedirect(route('appointments.showPatient', $appointment->token));
    $response->assertSessionHas('error');

    expect($appointment->fresh()->status)->toBe('confirmed');

    Mail::assertNothingSent();
});

test('24h reminder command skips cancelled appointments', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-08 10:00:00'));
    Mail::fake();

    $therapist = cancellationTherapist(['email' => 'therapist-24h@example.test']);
    $product = cancellationProduct($therapist);
    $activeClient = cancellationClient($therapist, 'active-24h@example.test');
    $cancelledClient = cancellationClient($therapist, 'cancelled-24h@example.test');

    $activeAppointment = cancellationAppointment($therapist, $activeClient, $product, [
        'appointment_date' => now()->addHours(24),
        'status' => 'confirmed',
    ]);

    $cancelledAppointment = cancellationAppointment($therapist, $cancelledClient, $product, [
        'appointment_date' => now()->addHours(24),
        'status' => 'cancelled',
    ]);

    Artisan::call('email:send-appointment-reminders');

    Mail::assertQueued(AppointmentReminderClientMail::class, function ($mail) use ($activeAppointment) {
        return $mail->appointment->is($activeAppointment->fresh());
    });

    Mail::assertNotQueued(AppointmentReminderClientMail::class, function ($mail) use ($cancelledAppointment) {
        return $mail->appointment->is($cancelledAppointment->fresh());
    });

    Carbon::setTestNow();
});

test('1h reminder command skips cancelled appointments', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-08 10:00:00'));
    Mail::fake();

    $therapist = cancellationTherapist(['email' => 'therapist-1h@example.test']);
    $product = cancellationProduct($therapist);
    $activeClient = cancellationClient($therapist, 'active-1h@example.test');
    $cancelledClient = cancellationClient($therapist, 'cancelled-1h@example.test');

    $activeAppointment = cancellationAppointment($therapist, $activeClient, $product, [
        'appointment_date' => now()->addHour(),
        'status' => 'confirmed',
    ]);

    $cancelledAppointment = cancellationAppointment($therapist, $cancelledClient, $product, [
        'appointment_date' => now()->addHour(),
        'status' => 'cancelled',
    ]);

    Artisan::call('email:send-one-hour-reminder');

    Mail::assertQueued(AppointmentReminderClientMail::class, function ($mail) use ($activeAppointment) {
        return $mail->appointment->is($activeAppointment->fresh());
    });

    Mail::assertNotQueued(AppointmentReminderClientMail::class, function ($mail) use ($cancelledAppointment) {
        return $mail->appointment->is($cancelledAppointment->fresh());
    });

    Carbon::setTestNow();
});

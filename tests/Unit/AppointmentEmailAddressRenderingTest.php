<?php

use App\Mail\AppointmentCreatedPatientMail;
use App\Mail\AppointmentCreatedTherapistMail;
use App\Mail\AppointmentReminderClientMail;
use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Meeting;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

uses(TestCase::class);

function buildAppointmentForEmails(array $overrides = []): Appointment
{
    $user = new User(array_merge([
        'id' => 101,
        'name' => 'Therapeute Test',
        'email' => 'therapeute@example.test',
        'company_name' => 'Cabinet Test',
        'company_address' => '99 Rue du Cabinet, 75008 Paris',
        'cancellation_notice_hours' => 24,
    ], $overrides['user'] ?? []));

    $client = new ClientProfile(array_merge([
        'id' => 202,
        'user_id' => 101,
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'email' => 'alice@example.test',
        'address' => '12 Rue des Lilas, 75001 Paris',
    ], $overrides['client'] ?? []));

    $product = new Product(array_merge([
        'id' => 303,
        'user_id' => 101,
        'name' => 'Massage Test',
        'duration' => 60,
        'visio' => false,
        'adomicile' => true,
        'en_entreprise' => false,
        'dans_le_cabinet' => false,
    ], $overrides['product'] ?? []));

    $appointment = new Appointment(array_merge([
        'id' => 404,
        'token' => 'tok_appointment_test_1234567890',
        'client_profile_id' => 202,
        'user_id' => 101,
        'product_id' => 303,
        'appointment_date' => Carbon::parse('2026-04-20 10:00:00'),
        'status' => 'confirmed',
        'notes' => 'Note test',
        'type' => 'domicile',
        'duration' => 60,
        'address' => null,
    ], $overrides['appointment'] ?? []));

    $appointment->setRelation('user', $user);
    $appointment->setRelation('clientProfile', $client);
    $appointment->setRelation('product', $product);
    $appointment->setRelation('practiceLocation', null);
    $appointment->setRelation('meeting', null);

    return $appointment;
}

test('patient creation email includes domicile address', function () {
    $appointment = buildAppointmentForEmails([
        'client' => [
            'address' => '44 Avenue Victor Hugo, 75016 Paris',
        ],
        'appointment' => [
            'type' => 'domicile',
        ],
    ]);

    $html = (new AppointmentCreatedPatientMail($appointment))->render();

    expect($html)->toContain('Adresse du domicile');
    expect($html)->toContain('44 Avenue Victor Hugo, 75016 Paris');
    expect($html)->not->toContain('Adresse du cabinet');
});

test('patient reminder email includes domicile address', function () {
    $appointment = buildAppointmentForEmails([
        'client' => [
            'address' => '22 Rue de la Paix, 75002 Paris',
        ],
        'appointment' => [
            'type' => 'domicile',
        ],
    ]);

    $html = (new AppointmentReminderClientMail($appointment))->render();

    expect($html)->toContain('Adresse du domicile');
    expect($html)->toContain('22 Rue de la Paix, 75002 Paris');
});

test('patient reminder email includes visio link when appointment is visio', function () {
    config([
        'services.jitsi.app_id' => 'test-app-id',
        'services.jitsi.secret' => 'test-secret',
        'services.jitsi.base_url' => 'https://visio.aromamade.com',
        'services.jitsi.domain' => 'visio.aromamade.com',
    ]);

    $appointment = buildAppointmentForEmails([
        'product' => [
            'visio' => true,
            'adomicile' => false,
        ],
        'appointment' => [
            'type' => 'visio',
        ],
    ]);

    $meeting = new Meeting([
        'room_token' => 'room-test-123',
    ]);
    $appointment->setRelation('meeting', $meeting);

    $html = (new AppointmentReminderClientMail($appointment))->render();

    expect($html)->toContain('Lien de visioconférence');
    expect($html)->toContain('Rejoindre la visio');
    expect($html)->toContain('visio.aromamade.com/room-test-123?jwt=');
});

test('therapist creation email includes domicile address', function () {
    $appointment = buildAppointmentForEmails([
        'client' => [
            'address' => '8 Boulevard Saint-Germain, 75005 Paris',
        ],
        'appointment' => [
            'type' => 'domicile',
        ],
    ]);

    $html = (new AppointmentCreatedTherapistMail($appointment))->render();

    expect($html)->toContain('Adresse du domicile');
    expect($html)->toContain('8 Boulevard Saint-Germain, 75005 Paris');
});

test('patient creation email shows fallback message for entreprise mode without address', function () {
    $appointment = buildAppointmentForEmails([
        'client' => [
            'address' => null,
        ],
        'product' => [
            'adomicile' => false,
            'en_entreprise' => true,
        ],
        'appointment' => [
            'type' => 'entreprise',
            'address' => null,
        ],
    ]);

    $html = (new AppointmentCreatedPatientMail($appointment))->render();

    expect($html)->toContain('Adresse de l’entreprise');
    expect($html)->toContain('Adresse non renseignée');
});

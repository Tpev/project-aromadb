<?php

use App\Mail\AppointmentCreatedPatientMail;
use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Meeting;
use App\Models\PracticeLocation;
use App\Models\Product;
use App\Models\User;
use App\Services\AppointmentIcsService;
use Carbon\Carbon;
use Tests\TestCase;

uses(TestCase::class);

function buildAppointmentForIcsTests(array $overrides = []): Appointment
{
    $user = new User(array_merge([
        'name' => 'John Satch',
        'email' => 'john@example.test',
        'company_name' => 'Cabinet Aroma',
        'company_address' => '10 Rue des Fleurs, 75001 Paris',
    ], $overrides['user'] ?? []));
    $user->id = 501;

    $client = new ClientProfile(array_merge([
        'user_id' => 501,
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'email' => 'alice@example.test',
        'address' => '44 Avenue Victor Hugo, 75016 Paris',
    ], $overrides['client'] ?? []));
    $client->id = 502;

    $product = new Product(array_merge([
        'user_id' => 501,
        'name' => 'Consultation Aroma',
        'duration' => 60,
        'visio' => false,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => true,
    ], $overrides['product'] ?? []));
    $product->id = 503;

    $appointment = new Appointment(array_merge([
        'token' => 'tok_ics_test_1234567890',
        'client_profile_id' => 502,
        'user_id' => 501,
        'product_id' => 503,
        'appointment_date' => Carbon::parse('2026-05-10 14:30:00'),
        'status' => 'confirmed',
        'notes' => "Pense-bête\navec virgule, et point-virgule;",
        'type' => 'cabinet',
        'duration' => 60,
        'practice_location_id' => 601,
        'address' => null,
    ], $overrides['appointment'] ?? []));
    $appointment->id = 504;

    $practiceLocation = new PracticeLocation(array_merge([
        'user_id' => 501,
        'label' => 'Cabinet principal',
        'address_line1' => '10 Rue des Fleurs',
        'postal_code' => '75001',
        'city' => 'Paris',
        'country' => 'France',
    ], $overrides['practiceLocation'] ?? []));
    $practiceLocation->id = 601;

    $appointment->setRelation('user', $user);
    $appointment->setRelation('clientProfile', $client);
    $appointment->setRelation('product', $product);
    $appointment->setRelation('practiceLocation', $practiceLocation);
    $appointment->setRelation('meeting', null);

    return $appointment;
}

test('ics export includes cabinet details and calendar metadata', function () {
    $appointment = buildAppointmentForIcsTests();

    $ics = app(AppointmentIcsService::class)->build($appointment);

    expect($ics)->toContain('BEGIN:VCALENDAR');
    expect($ics)->toContain('METHOD:PUBLISH');
    expect($ics)->toContain('SUMMARY:Consultation Aroma avec Cabinet Aroma');
    expect($ics)->toContain('LOCATION:Cabinet principal - 10 Rue des Fleurs\\, 75001 Paris\\, France');
    expect($ics)->toContain('Mode : Dans le Cabinet');
    expect($ics)->toContain('Adresse du cabinet : 10 Rue');
    expect($ics)->toContain('75001 Paris\\, France');
    expect($ics)->not->toContain('LOCATION:En ligne ou au cabinet');
});

test('ics export includes domicile address when appointment is at home', function () {
    $appointment = buildAppointmentForIcsTests([
        'product' => [
            'visio' => false,
            'adomicile' => true,
            'dans_le_cabinet' => false,
        ],
        'appointment' => [
            'type' => 'domicile',
            'practice_location_id' => null,
            'address' => '22 Rue de la Paix, 75002 Paris',
        ],
    ]);
    $appointment->setRelation('practiceLocation', null);

    $ics = app(AppointmentIcsService::class)->build($appointment);

    expect($ics)->toContain('LOCATION:À domicile - 22 Rue de la Paix\\, 75002 Paris');
    expect($ics)->toContain('Adresse du domicile : 22 Rue de');
    expect($ics)->toContain('la Paix\\, 75002 Paris');
});

test('ics export includes visio url and email contains add to calendar button', function () {
    config([
        'services.jitsi.app_id' => 'test-app-id',
        'services.jitsi.secret' => 'test-secret',
        'services.jitsi.base_url' => 'https://visio.aromamade.com',
        'services.jitsi.domain' => 'visio.aromamade.com',
    ]);

    $appointment = buildAppointmentForIcsTests([
        'product' => [
            'visio' => true,
            'adomicile' => false,
            'dans_le_cabinet' => false,
        ],
        'appointment' => [
            'type' => 'visio',
            'practice_location_id' => null,
        ],
    ]);

    $appointment->setRelation('practiceLocation', null);
    $appointment->setRelation('meeting', new Meeting([
        'room_token' => 'room-test-123',
    ]));

    $ics = app(AppointmentIcsService::class)->build($appointment);
    $html = (new AppointmentCreatedPatientMail($appointment))->render();

    expect($ics)->toContain('LOCATION:En visio - lien dans la description');
    expect($ics)->toContain('Lien de visioconférence : https://');
    expect($ics)->toContain('visio.aromamade.com/room-test-123?jwt=');
    expect($ics)->toContain('URL:https://visio.aromamade.com/room-test-123?jwt=');
    expect($html)->toContain('Ajouter à mon calendrier');
    expect($html)->toContain(route('appointments.downloadICS', ['token' => $appointment->token]));
});


test('google calendar url reuses appointment details with mode-specific data', function () {
    $appointment = buildAppointmentForIcsTests([
        'product' => [
            'visio' => false,
            'adomicile' => true,
            'dans_le_cabinet' => false,
        ],
        'appointment' => [
            'type' => 'domicile',
            'practice_location_id' => null,
            'address' => '22 Rue de la Paix, 75002 Paris',
        ],
    ]);
    $appointment->setRelation('practiceLocation', null);

    $url = app(AppointmentIcsService::class)->googleCalendarUrl($appointment);
    $decodedUrl = urldecode($url);

    expect($url)->toStartWith('https://calendar.google.com/calendar/render?action=TEMPLATE');
    expect($decodedUrl)->toContain('text=Consultation Aroma avec Cabinet Aroma');
    expect($decodedUrl)->toContain('details=Rendez-vous AromaMade');
    expect($decodedUrl)->toContain('Domicile');
    expect($decodedUrl)->toContain('Adresse du domicile : 22 Rue de la Paix, 75002 Paris');
    expect($decodedUrl)->toContain('location=');
    expect($decodedUrl)->toContain(route('appointments.showPatient', ['token' => $appointment->token]));
});

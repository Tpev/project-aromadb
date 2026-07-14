<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\SessionNote;
use App\Models\SessionNoteTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function sessionNoteTherapist(string $email): User
{
    return User::factory()->create([
        'email' => $email,
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
}

function sessionNoteClient(User $user, string $firstName = 'Serge'): ClientProfile
{
    return ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => $firstName,
        'last_name' => 'Patient',
        'email' => strtolower($firstName).'-'.uniqid().'@example.test',
    ]);
}

function sessionNoteAppointment(User $user, ClientProfile $client): Appointment
{
    return Appointment::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'appointment_date' => now()->subHour(),
        'status' => 'Complété',
        'duration' => 60,
        'type' => 'cabinet',
    ]);
}

test('legacy client notes without an appointment remain visible', function () {
    $therapist = sessionNoteTherapist('notes-legacy@example.test');
    $client = sessionNoteClient($therapist);
    $note = SessionNote::create([
        'user_id' => $therapist->id,
        'client_profile_id' => $client->id,
        'appointment_id' => null,
        'note' => 'Note historique sans rendez-vous.',
    ]);

    $this->actingAs($therapist)
        ->get(route('session_notes.index', $client))
        ->assertOk()
        ->assertSee('Note historique sans rendez-vous.');

    expect($note->fresh()->appointment_id)->toBeNull();
});

test('a note can be created from an owned appointment', function () {
    $therapist = sessionNoteTherapist('notes-appointment@example.test');
    $client = sessionNoteClient($therapist);
    $appointment = sessionNoteAppointment($therapist, $client);

    $this->actingAs($therapist)
        ->post(route('session_notes.store', $client), [
            'appointment_id' => $appointment->id,
            'note' => '<p>Compte rendu de séance.</p>',
        ])
        ->assertRedirect(route('appointments.show', $appointment));

    $note = SessionNote::firstOrFail();
    expect($note->appointment_id)->toBe($appointment->id)
        ->and($note->client_profile_id)->toBe($client->id)
        ->and($appointment->fresh()->note_tracking_label)->toBe('Note créée');
});

test('a general client note can still be created without an appointment', function () {
    $therapist = sessionNoteTherapist('notes-general@example.test');
    $client = sessionNoteClient($therapist);

    $this->actingAs($therapist)
        ->post(route('session_notes.store', $client), ['note' => 'Suivi général.'])
        ->assertRedirect(route('session_notes.index', $client));

    expect(SessionNote::firstOrFail()->appointment_id)->toBeNull();
});

test('another practitioner appointment or a different client appointment is rejected', function () {
    $therapist = sessionNoteTherapist('notes-owner@example.test');
    $client = sessionNoteClient($therapist, 'ClientA');
    $otherClient = sessionNoteClient($therapist, 'ClientB');
    $wrongClientAppointment = sessionNoteAppointment($therapist, $otherClient);

    $otherTherapist = sessionNoteTherapist('notes-other@example.test');
    $otherPractitionerClient = sessionNoteClient($otherTherapist, 'ClientC');
    $otherPractitionerAppointment = sessionNoteAppointment($otherTherapist, $otherPractitionerClient);

    $this->actingAs($therapist)
        ->post(route('session_notes.store', $client), [
            'appointment_id' => $wrongClientAppointment->id,
            'note' => 'Ne doit pas être créée.',
        ])
        ->assertNotFound();

    $this->post(route('session_notes.store', $client), [
        'appointment_id' => $otherPractitionerAppointment->id,
        'note' => 'Ne doit pas être créée non plus.',
    ])->assertNotFound();

    expect(SessionNote::count())->toBe(0);
});

test('template management preserves only validated client and appointment context', function () {
    $therapist = sessionNoteTherapist('notes-template@example.test');
    $client = sessionNoteClient($therapist);
    $appointment = sessionNoteAppointment($therapist, $client);

    $otherTherapist = sessionNoteTherapist('notes-template-other@example.test');
    $otherClient = sessionNoteClient($otherTherapist, 'Autre');
    $otherAppointment = sessionNoteAppointment($otherTherapist, $otherClient);

    $this->actingAs($therapist)
        ->get(route('session-note-templates.index', [
            'client_profile_id' => $client->id,
            'appointment_id' => $appointment->id,
            'return_url' => 'https://example.org/unsafe',
        ]))
        ->assertOk()
        ->assertSee('Retour à la note de')
        ->assertSee(route('session_notes.create', [
            'clientProfile' => $client->id,
            'appointment_id' => $appointment->id,
        ]), false)
        ->assertDontSee('example.org/unsafe');

    $this->get(route('session-note-templates.index', [
        'client_profile_id' => $otherClient->id,
        'appointment_id' => $otherAppointment->id,
    ]))
        ->assertOk()
        ->assertDontSee($otherClient->first_name);

    $this->post(route('session-note-templates.store'), [
        'title' => 'Bilan de séance',
        'content' => '<p>Observations</p>',
        'client_profile_id' => $client->id,
        'appointment_id' => $appointment->id,
    ])->assertRedirect(route('session-note-templates.index', [
        'client_profile_id' => $client->id,
        'appointment_id' => $appointment->id,
    ]));

    expect(SessionNoteTemplate::firstOrFail()->user_id)->toBe($therapist->id);
});

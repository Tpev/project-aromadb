<?php

use App\Models\Appointment;
use App\Models\User;

test('cancelled appointments remove their google event instead of syncing it', function () {
    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->status = 'cancelled';
    $appointment->external = false;

    $appointment->shouldReceive('removeFromGoogle')->once();

    $appointment->syncToGoogle();
});

test('active appointments without google connection do not try to delete google events', function () {
    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->status = 'confirmed';
    $appointment->external = false;
    $appointment->setRelation('user', new User(['google_access_token' => null]));

    $appointment->shouldReceive('removeFromGoogle')->never();

    $appointment->syncToGoogle();
});

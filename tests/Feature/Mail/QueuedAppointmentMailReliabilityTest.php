<?php

use App\Mail\AppointmentCreatedPatientMail;
use App\Mail\AppointmentReminderClientMail;
use Illuminate\Queue\Events\Looping;
use Illuminate\Support\Facades\Mail;

function queuedMailWithoutConstructor(string $class): object
{
    return (new ReflectionClass($class))->newInstanceWithoutConstructor();
}

test('appointment confirmation and reminder mails retry transient transport failures', function () {
    $confirmation = queuedMailWithoutConstructor(AppointmentCreatedPatientMail::class);
    $reminder = queuedMailWithoutConstructor(AppointmentReminderClientMail::class);

    expect($confirmation->tries)->toBe(5)
        ->and($confirmation->backoff())->toBe([60, 300, 900, 1800])
        ->and($reminder->tries)->toBe(5)
        ->and($reminder->backoff())->toBe([60, 300, 900, 1800]);
});

test('the queue loop purges cached mail transports between jobs', function () {
    Mail::shouldReceive('purge')->once();

    event(new Looping('database', 'default'));
});

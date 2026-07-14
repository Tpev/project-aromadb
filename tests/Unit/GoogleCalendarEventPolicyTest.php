<?php

use App\Services\GoogleCalendarEventPolicy;

test('transparent Google events do not block availability', function () {
    $googleEvent = new \Google_Service_Calendar_Event;
    $googleEvent->setTransparency('transparent');
    $event = (object) ['googleEvent' => $googleEvent];

    expect((new GoogleCalendarEventPolicy)->isBlocking($event))->toBeFalse();
});

test('opaque and unspecified Google events block availability', function (?string $transparency) {
    $googleEvent = new \Google_Service_Calendar_Event;
    $googleEvent->setTransparency($transparency);
    $event = (object) ['googleEvent' => $googleEvent];

    expect((new GoogleCalendarEventPolicy)->isBlocking($event))->toBeTrue();
})->with(['opaque', null]);

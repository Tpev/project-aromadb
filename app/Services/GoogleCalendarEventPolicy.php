<?php

namespace App\Services;

class GoogleCalendarEventPolicy
{
    public function isBlocking(object $event): bool
    {
        $transparency = null;

        if (isset($event->googleEvent) && method_exists($event->googleEvent, 'getTransparency')) {
            $transparency = $event->googleEvent->getTransparency();
        }

        if ($transparency === null) {
            $transparency = $event->transparency ?? null;
        }

        return strtolower((string) $transparency) !== 'transparent';
    }
}

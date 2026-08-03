<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Unavailability;

final class EventCalendarBlockService
{
    public function sync(Event $event, bool $enabled): ?Unavailability
    {
        $ownedBlock = Unavailability::query()
            ->where('event_id', $event->id)
            ->where('user_id', $event->user_id);

        if (! $enabled) {
            $ownedBlock->delete();

            return null;
        }

        return Unavailability::query()->updateOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $event->user_id,
            ],
            [
                'start_date' => $event->start_date_time,
                'end_date' => $event->ends_at,
                'reason' => 'Événement : '.$event->name,
            ],
        );
    }
}

<?php

namespace App\Support;

use App\Models\Event;

final class EventVisioJoinLink
{
    public function for(Event $event): ?string
    {
        if (! $event->isVisio()) {
            return null;
        }

        if ($this->usesNameGate($event)) {
            return route('events.visio.join.show', $event);
        }

        return $this->directFor($event);
    }

    public function directFor(Event $event): ?string
    {
        return $event->visio_public_link;
    }

    public function usesNameGate(Event $event): bool
    {
        if (! $event->isAromaMadeVisio()) {
            return false;
        }

        if (! config('services.jitsi.participant_name_gate.enabled', false)) {
            return false;
        }

        $allowedUserIds = config('services.jitsi.participant_name_gate.user_ids', []);

        return $allowedUserIds === []
            || in_array((int) $event->user_id, $allowedUserIds, true);
    }

    public function directForDisplayName(Event $event, string $displayName): ?string
    {
        $directLink = $this->directFor($event);

        if (! $directLink) {
            return null;
        }

        $encodedName = rawurlencode(json_encode(
            $displayName,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        ));

        return $directLink.'#userInfo.displayName='.$encodedName;
    }
}

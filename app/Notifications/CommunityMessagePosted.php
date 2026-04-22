<?php

namespace App\Notifications;

use App\Models\CommunityMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CommunityMessagePosted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected CommunityMessage $message)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $group = $this->message->group;
        $channel = $this->message->channel;
        $client = $this->message->clientProfile;
        $clientName = trim((string) (($client?->first_name ?? '') . ' ' . ($client?->last_name ?? ''))) ?: 'Un membre';

        return [
            'community_group_id' => $group?->id,
            'community_channel_id' => $channel?->id,
            'message' => $clientName . ' a repondu dans la communaute "' . ($group?->name ?? 'Communaute') . '".',
            'url' => route('communities.show', ['community' => $group?->id, 'channel' => $channel?->id]),
        ];
    }
}

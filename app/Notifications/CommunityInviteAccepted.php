<?php

namespace App\Notifications;

use App\Models\CommunityGroup;
use App\Models\CommunityMember;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CommunityInviteAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected CommunityGroup $group, protected CommunityMember $member)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $client = $this->member->clientProfile;
        $clientName = trim((string) (($client?->first_name ?? '') . ' ' . ($client?->last_name ?? ''))) ?: 'Un client';

        return [
            'community_group_id' => $this->group->id,
            'message' => $clientName . ' a rejoint la communauté "' . $this->group->name . '".',
            'url' => route('communities.show', $this->group),
        ];
    }
}

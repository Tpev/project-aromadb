<?php

namespace App\Services;

use App\Models\ClientProfile;
use App\Models\CommunityChannel;
use App\Models\CommunityGroup;
use App\Models\CommunityMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommunityMessageService
{
    /**
     * @param  UploadedFile[]  $attachments
     */
    public function createPractitionerMessage(
        CommunityGroup $community,
        CommunityChannel $channel,
        User $user,
        string $content,
        array $attachments = [],
    ): CommunityMessage {
        $message = CommunityMessage::create([
            'community_group_id' => $community->id,
            'community_channel_id' => $channel->id,
            'user_id' => $user->id,
            'sender_type' => CommunityMessage::SENDER_PRACTITIONER,
            'content' => trim($content),
        ]);

        $this->storeAttachments($message, $attachments);

        return $message->load('attachments');
    }

    /**
     * @param  UploadedFile[]  $attachments
     */
    public function createClientMessage(
        CommunityGroup $community,
        CommunityChannel $channel,
        ClientProfile $client,
        string $content,
        array $attachments = [],
    ): CommunityMessage {
        $message = CommunityMessage::create([
            'community_group_id' => $community->id,
            'community_channel_id' => $channel->id,
            'client_profile_id' => $client->id,
            'sender_type' => CommunityMessage::SENDER_CLIENT,
            'content' => trim($content),
        ]);

        $this->storeAttachments($message, $attachments);

        return $message->load('attachments');
    }

    /**
     * @param  UploadedFile[]  $attachments
     */
    protected function storeAttachments(CommunityMessage $message, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            if (!$attachment instanceof UploadedFile) {
                continue;
            }

            $extension = strtolower($attachment->getClientOriginalExtension() ?: $attachment->extension() ?: 'bin');
            $fileName = Str::uuid()->toString() . '.' . $extension;
            $path = $attachment->storeAs(
                'community-attachments/' . $message->group->user_id . '/' . $message->group->id,
                $fileName,
                'public'
            );

            $message->attachments()->create([
                'file_path' => $path,
                'original_name' => $attachment->getClientOriginalName(),
                'mime_type' => $attachment->getClientMimeType(),
                'size' => $attachment->getSize(),
            ]);
        }
    }

    public function deleteAttachmentFile(string $path): void
    {
        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

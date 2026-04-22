<?php

namespace App\Http\Controllers;

use App\Models\CommunityChannel;
use App\Models\CommunityGroup;
use App\Models\CommunityMessage;
use App\Services\CommunityMessageService;
use App\Support\UploadLimit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityMessageController extends Controller
{
    public function __construct(protected CommunityMessageService $messageService)
    {
    }

    public function store(Request $request, CommunityGroup $community): RedirectResponse
    {
        abort_unless((int) $community->user_id === (int) Auth::id(), 403);

        if ($community->is_archived) {
            return back()->with('error', 'Cette communauté est archivée.');
        }

        $data = $request->validate([
            'community_channel_id' => 'required|integer|exists:community_channels,id',
            'content' => 'required|string|max:5000',
            'attachments' => 'nullable|array|max:4',
            'attachments.*' => 'file|max:' . UploadLimit::communityAttachmentValidationMaxKilobytes() . '|mimes:pdf,jpg,jpeg,png,webp,gif,doc,docx,xls,xlsx,ppt,pptx,txt,csv,mp3,m4a,wav,ogg',
        ]);

        $channel = CommunityChannel::query()
            ->where('community_group_id', $community->id)
            ->findOrFail((int) $data['community_channel_id']);

        $this->messageService->createPractitionerMessage(
            community: $community,
            channel: $channel,
            user: $request->user(),
            content: $data['content'],
            attachments: $request->file('attachments', []),
        );

        return redirect()
            ->route('communities.show', ['community' => $community->id, 'channel' => $channel->id])
            ->with('success', 'Message envoyé.');
    }

    public function pin(CommunityGroup $community, CommunityMessage $message): RedirectResponse
    {
        abort_unless((int) $community->user_id === (int) Auth::id(), 403);
        abort_unless((int) $message->community_group_id === (int) $community->id, 404);

        $message->channel()->update([
            'pinned_community_message_id' => $message->id,
        ]);

        return redirect()
            ->route('communities.show', ['community' => $community->id, 'channel' => $message->community_channel_id])
            ->with('success', 'Message épinglé dans ce salon.');
    }

    public function unpin(CommunityGroup $community, CommunityChannel $channel): RedirectResponse
    {
        abort_unless((int) $community->user_id === (int) Auth::id(), 403);
        abort_unless((int) $channel->community_group_id === (int) $community->id, 404);

        $channel->update([
            'pinned_community_message_id' => null,
        ]);

        return redirect()
            ->route('communities.show', ['community' => $community->id, 'channel' => $channel->id])
            ->with('success', 'Message épinglé retiré.');
    }
}

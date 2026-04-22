<?php

namespace App\Http\Controllers;

use App\Models\CommunityChannel;
use App\Models\CommunityGroup;
use App\Models\CommunityMember;
use App\Notifications\CommunityInviteAccepted;
use App\Notifications\CommunityMessagePosted;
use App\Services\CommunityMessageService;
use App\Support\UploadLimit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientCommunityController extends Controller
{
    public function __construct(protected CommunityMessageService $messageService)
    {
    }

    public function index(): View
    {
        $client = auth('client')->user();

        $memberships = CommunityMember::query()
            ->where('client_profile_id', $client->id)
            ->whereIn('status', [CommunityMember::STATUS_INVITED, CommunityMember::STATUS_ACTIVE])
            ->with(['group.user', 'group.channels.pinnedMessage'])
            ->latest()
            ->get();

        $pendingInvites = $memberships->where('status', CommunityMember::STATUS_INVITED)->values();
        $communities = $memberships->where('status', CommunityMember::STATUS_ACTIVE)->values();

        return view('client.communities.index', compact('pendingInvites', 'communities'));
    }

    public function accept(CommunityGroup $community): RedirectResponse
    {
        $client = auth('client')->user();

        $member = CommunityMember::query()
            ->where('community_group_id', $community->id)
            ->where('client_profile_id', $client->id)
            ->firstOrFail();

        abort_unless($member->status === CommunityMember::STATUS_INVITED, 403);

        $member->update([
            'status' => CommunityMember::STATUS_ACTIVE,
            'joined_at' => now(),
            'removed_at' => null,
        ]);

        if ($community->user) {
            $community->user->notify(new CommunityInviteAccepted($community, $member->fresh('clientProfile')));
        }

        return redirect()
            ->route('client.communities.show', $community)
            ->with('success', 'Vous avez rejoint la communauté.');
    }

    public function show(Request $request, CommunityGroup $community): View
    {
        $client = auth('client')->user();

        $member = CommunityMember::query()
            ->where('community_group_id', $community->id)
            ->where('client_profile_id', $client->id)
            ->where('status', CommunityMember::STATUS_ACTIVE)
            ->firstOrFail();

        $community->load([
            'user',
            'channels.pinnedMessage.user',
            'channels.pinnedMessage.clientProfile',
            'channels.pinnedMessage.attachments',
        ]);

        $selectedChannel = $community->channels
            ->firstWhere('id', (int) $request->integer('channel'))
            ?? $community->channels->first();

        $messages = collect();
        if ($selectedChannel) {
            $messages = $selectedChannel->messages()
                ->with(['user', 'clientProfile', 'attachments', 'channel'])
                ->latest()
                ->take(100)
                ->get()
                ->reverse()
                ->values();
        }

        return view('client.communities.show', [
            'community' => $community,
            'membership' => $member,
            'selectedChannel' => $selectedChannel,
            'messages' => $messages,
            'attachmentLimitLabel' => UploadLimit::communityAttachmentLimitLabel(),
        ]);
    }

    public function storeMessage(Request $request, CommunityGroup $community): RedirectResponse
    {
        $client = auth('client')->user();

        CommunityMember::query()
            ->where('community_group_id', $community->id)
            ->where('client_profile_id', $client->id)
            ->where('status', CommunityMember::STATUS_ACTIVE)
            ->firstOrFail();

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

        if ($channel->isAnnouncements()) {
            return back()->with('error', 'Seul votre praticien peut publier dans Annonces.');
        }

        $message = $this->messageService->createClientMessage(
            community: $community,
            channel: $channel,
            client: $client,
            content: $data['content'],
            attachments: $request->file('attachments', []),
        );

        if ($community->user) {
            $community->user->notify(new CommunityMessagePosted($message->load(['group', 'channel', 'clientProfile'])));
        }

        return redirect()
            ->route('client.communities.show', ['community' => $community->id, 'channel' => $channel->id])
            ->with('success', 'Message envoyé.');
    }
}

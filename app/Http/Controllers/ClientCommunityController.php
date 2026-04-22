<?php

namespace App\Http\Controllers;

use App\Models\CommunityChannel;
use App\Models\CommunityGroup;
use App\Models\CommunityMember;
use App\Models\CommunityMessage;
use App\Notifications\CommunityInviteAccepted;
use App\Notifications\CommunityMessagePosted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientCommunityController extends Controller
{
    public function index(): View
    {
        $client = auth('client')->user();

        $memberships = CommunityMember::query()
            ->where('client_profile_id', $client->id)
            ->whereIn('status', [CommunityMember::STATUS_INVITED, CommunityMember::STATUS_ACTIVE])
            ->with(['group.user', 'group.channels'])
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

        $community->load(['user', 'channels']);

        $selectedChannel = $community->channels
            ->firstWhere('id', (int) $request->integer('channel'))
            ?? $community->channels->first();

        $messages = collect();
        if ($selectedChannel) {
            $messages = $selectedChannel->messages()
                ->with(['user', 'clientProfile'])
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
        ]);
    }

    public function storeMessage(Request $request, CommunityGroup $community): RedirectResponse
    {
        $client = auth('client')->user();

        $member = CommunityMember::query()
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
        ]);

        $channel = CommunityChannel::query()
            ->where('community_group_id', $community->id)
            ->findOrFail((int) $data['community_channel_id']);

        if ($channel->isAnnouncements()) {
            return back()->with('error', 'Seul votre praticien peut publier dans Annonces.');
        }

        $message = CommunityMessage::create([
            'community_group_id' => $community->id,
            'community_channel_id' => $channel->id,
            'client_profile_id' => $client->id,
            'sender_type' => CommunityMessage::SENDER_CLIENT,
            'content' => trim($data['content']),
        ]);

        if ($community->user) {
            $community->user->notify(new CommunityMessagePosted($message->load(['group', 'channel', 'clientProfile'])));
        }

        return redirect()
            ->route('client.communities.show', ['community' => $community->id, 'channel' => $channel->id])
            ->with('success', 'Message envoyé.');
    }
}

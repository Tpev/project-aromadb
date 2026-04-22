<?php

namespace App\Http\Controllers;

use App\Models\CommunityChannel;
use App\Models\CommunityGroup;
use App\Models\CommunityMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityMessageController extends Controller
{
    public function store(Request $request, CommunityGroup $community): RedirectResponse
    {
        abort_unless((int) $community->user_id === (int) Auth::id(), 403);

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

        CommunityMessage::create([
            'community_group_id' => $community->id,
            'community_channel_id' => $channel->id,
            'user_id' => Auth::id(),
            'sender_type' => CommunityMessage::SENDER_PRACTITIONER,
            'content' => trim($data['content']),
        ]);

        return redirect()
            ->route('communities.show', ['community' => $community->id, 'channel' => $channel->id])
            ->with('success', 'Message envoyé.');
    }
}

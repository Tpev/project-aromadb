<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\CommunityGroup;
use App\Models\CommunityMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityMemberController extends Controller
{
    public function store(Request $request, CommunityGroup $community): RedirectResponse
    {
        abort_unless((int) $community->user_id === (int) Auth::id(), 403);

        $data = $request->validate([
            'client_profile_id' => 'required|integer|exists:client_profiles,id',
        ]);

        $client = ClientProfile::query()
            ->where('user_id', Auth::id())
            ->findOrFail((int) $data['client_profile_id']);

        $member = CommunityMember::firstOrNew([
            'community_group_id' => $community->id,
            'client_profile_id' => $client->id,
        ]);

        if ($member->exists && $member->status === CommunityMember::STATUS_ACTIVE) {
            return back()->with('success', 'Ce client fait deja partie de la communaute.');
        }

        $member->status = CommunityMember::STATUS_INVITED;
        $member->invited_at = now();
        $member->joined_at = null;
        $member->save();

        return redirect()
            ->route('communities.show', $community)
            ->with('success', 'Invitation envoyee dans la communaute.');
    }

    public function destroy(CommunityGroup $community, CommunityMember $member): RedirectResponse
    {
        abort_unless((int) $community->user_id === (int) Auth::id(), 403);
        abort_unless((int) $member->community_group_id === (int) $community->id, 404);

        $member->update([
            'status' => CommunityMember::STATUS_REMOVED,
        ]);

        return redirect()
            ->route('communities.show', $community)
            ->with('success', 'Membre retire de la communaute.');
    }
}

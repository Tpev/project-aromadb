<?php

namespace App\Http\Controllers;

use App\Mail\CommunityInviteMail;
use App\Models\ClientProfile;
use App\Models\CommunityGroup;
use App\Models\CommunityMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        if (!$client->email) {
            return redirect()
                ->route('communities.show', $community)
                ->with('success', 'Invitation enregistree. Ce client n a pas d adresse email, il verra la communaute depuis son espace client si son acces est actif.');
        }

        try {
            Mail::to($client->email)->queue(new CommunityInviteMail(
                community: $community->fresh('user'),
                client: $client->fresh('user'),
                joinUrl: $this->buildJoinUrlFor($client),
                requiresAccountSetup: !$client->hasEspaceClient(),
            ));
        } catch (\Throwable $e) {
            return redirect()
                ->route('communities.show', $community)
                ->with('error', 'Invitation enregistree, mais l email n a pas pu etre envoye. Le client verra tout de meme l invitation dans son espace client.');
        }

        return redirect()
            ->route('communities.show', $community)
            ->with('success', 'Invitation envoyee dans la communaute et email transmis au client.');
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

    protected function buildJoinUrlFor(ClientProfile $client): string
    {
        if ($client->hasEspaceClient()) {
            return route('client.communities.index');
        }

        $plainToken = Str::uuid()->toString();

        $client->forceFill([
            'password_setup_token_hash' => hash('sha256', $plainToken),
            'password_setup_expires_at' => now()->addDays(3),
        ])->save();

        return route('client.setup.show', [
            'token' => $plainToken,
            'redirect' => route('client.communities.index'),
        ]);
    }
}

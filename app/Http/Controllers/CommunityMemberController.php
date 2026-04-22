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
        $this->authorizeCommunity($community);

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
            return back()->with('success', 'Ce client fait déjà partie de la communauté.');
        }

        $member->status = CommunityMember::STATUS_INVITED;
        $member->invited_at = now();
        $member->invitation_email_sent_at = $client->email ? now() : null;
        $member->joined_at = null;
        $member->removed_at = null;
        $member->save();

        if (!$client->email) {
            return redirect()
                ->route('communities.manage', $community)
                ->with('success', 'Invitation enregistrée. Ce client n’a pas d’adresse email ; il verra la communauté dans son espace client si son accès est actif.');
        }

        return $this->sendInvitationEmail($community, $client)
            ? redirect()
                ->route('communities.manage', $community)
                ->with('success', 'Invitation envoyée dans la communauté et email transmis au client.')
            : redirect()
                ->route('communities.manage', $community)
                ->with('error', 'Invitation enregistrée, mais l’email n’a pas pu être envoyé. Le client verra tout de même l’invitation dans son espace client.');
    }

    public function resendInvitation(CommunityGroup $community, CommunityMember $member): RedirectResponse
    {
        $this->authorizeCommunity($community);
        abort_unless((int) $member->community_group_id === (int) $community->id, 404);
        abort_unless($member->status === CommunityMember::STATUS_INVITED, 422);

        $client = $member->clientProfile;
        abort_if(!$client, 404);

        $member->update([
            'invited_at' => now(),
            'invitation_email_sent_at' => $client->email ? now() : null,
        ]);

        if (!$client->email) {
            return redirect()
                ->route('communities.manage', $community)
                ->with('success', 'L’invitation a bien été relancée dans l’espace client.');
        }

        return $this->sendInvitationEmail($community, $client)
            ? redirect()
                ->route('communities.manage', $community)
                ->with('success', 'Invitation relancée par email.')
            : redirect()
                ->route('communities.manage', $community)
                ->with('error', 'La relance a été enregistrée, mais l’email n’a pas pu être envoyé.');
    }

    public function destroy(CommunityGroup $community, CommunityMember $member): RedirectResponse
    {
        $this->authorizeCommunity($community);
        abort_unless((int) $member->community_group_id === (int) $community->id, 404);

        $member->update([
            'status' => CommunityMember::STATUS_REMOVED,
            'removed_at' => now(),
        ]);

        return redirect()
            ->route('communities.manage', $community)
            ->with('success', 'Membre retiré de la communauté.');
    }

    protected function sendInvitationEmail(CommunityGroup $community, ClientProfile $client): bool
    {
        try {
            Mail::to($client->email)->queue(new CommunityInviteMail(
                community: $community->fresh('user'),
                client: $client->fresh('user'),
                joinUrl: $this->buildJoinUrlFor($client),
                requiresAccountSetup: !$client->hasEspaceClient(),
            ));

            return true;
        } catch (\Throwable $e) {
            return false;
        }
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

    protected function authorizeCommunity(CommunityGroup $community): void
    {
        abort_unless((int) $community->user_id === (int) Auth::id(), 403);
    }
}

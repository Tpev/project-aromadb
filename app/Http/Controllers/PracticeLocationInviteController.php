<?php

namespace App\Http\Controllers;

use App\Mail\PracticeLocationInviteMail;
use App\Models\PracticeLocation;
use App\Models\PracticeLocationInvite;
use App\Models\PracticeLocationMember;
use App\Models\User;
use App\Services\CabinetAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PracticeLocationInviteController extends Controller
{
    public function __construct(
        private readonly CabinetAccessService $cabinetAccessService,
    ) {
    }

    public function store(Request $request, PracticeLocation $practice_location)
    {
        $this->abortIfDisabled();
        $this->authorizeOwner($practice_location);

        $data = $request->validate([
            'email' => ['required', 'email:rfc'],
        ]);

        if (!$practice_location->is_shared) {
            return back()->withErrors([
                'email' => 'Activez d’abord le mode cabinet partagé pour envoyer une invitation.',
            ]);
        }

        $invitedUser = User::query()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($data['email']))])
            ->where('is_therapist', true)
            ->first();

        if (!$invitedUser) {
            return back()->withErrors([
                'email' => 'Aucun compte thérapeute existant ne correspond à cette adresse email.',
            ])->withInput();
        }

        if ((int) $invitedUser->id === (int) $practice_location->user_id) {
            return back()->withErrors([
                'email' => 'Le propriétaire du cabinet ne peut pas être invité à son propre cabinet.',
            ])->withInput();
        }

        $alreadyMember = PracticeLocationMember::query()
            ->where('practice_location_id', $practice_location->id)
            ->where('user_id', $invitedUser->id)
            ->whereNotNull('accepted_at')
            ->exists();

        if ($alreadyMember) {
            return back()->withErrors([
                'email' => 'Ce thérapeute a déjà accès à ce cabinet partagé.',
            ])->withInput();
        }

        $this->cabinetAccessService->ensureOwnerMembership($practice_location);

        $invite = PracticeLocationInvite::query()->updateOrCreate(
            [
                'practice_location_id' => $practice_location->id,
                'invited_user_id' => $invitedUser->id,
            ],
            [
                'invited_email' => $invitedUser->email,
                'invited_by_user_id' => Auth::id(),
                'token' => Str::random(64),
                'status' => PracticeLocationInvite::STATUS_PENDING,
                'expires_at' => now()->addDays(7),
                'accepted_at' => null,
                'declined_at' => null,
            ]
        );

        $inviteUrl = route('practice-locations.invites.show', $invite->token);

        Mail::to($invite->invited_email)->queue(
            new PracticeLocationInviteMail($invite->load('practiceLocation'), Auth::user(), $inviteUrl)
        );

        return redirect()
            ->route('practice-locations.edit', $practice_location)
            ->with('success', 'Invitation envoyée avec succès.');
    }

    public function show(string $token)
    {
        $this->abortIfDisabled();

        $invite = PracticeLocationInvite::query()
            ->with(['practiceLocation.owner', 'invitedUser', 'invitedBy'])
            ->where('token', $token)
            ->firstOrFail();

        if ($invite->isPending() && $invite->isExpired()) {
            $invite->update(['status' => PracticeLocationInvite::STATUS_EXPIRED]);
            $invite->refresh();
        }

        return view('practice_locations.invites.show', [
            'invite' => $invite,
            'currentUser' => Auth::user(),
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $this->abortIfDisabled();

        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('warning', 'Connectez-vous pour accepter cette invitation.');
        }

        $invite = PracticeLocationInvite::query()
            ->with('practiceLocation')
            ->where('token', $token)
            ->firstOrFail();

        if ($invite->isExpired()) {
            $invite->update(['status' => PracticeLocationInvite::STATUS_EXPIRED]);

            return redirect()
                ->route('practice-locations.invites.show', $token)
                ->with('error', 'Cette invitation a expiré.');
        }

        if (!$invite->isPending()) {
            return redirect()
                ->route('practice-locations.invites.show', $token)
                ->with('warning', 'Cette invitation n’est plus en attente.');
        }

        if (mb_strtolower(Auth::user()->email) !== mb_strtolower($invite->invited_email)) {
            return redirect()
                ->route('practice-locations.invites.show', $token)
                ->with('error', 'Connectez-vous avec le compte correspondant à l’adresse invitée pour accepter ce cabinet.');
        }

        DB::transaction(function () use ($invite) {
            PracticeLocationMember::query()->updateOrCreate(
                [
                    'practice_location_id' => $invite->practice_location_id,
                    'user_id' => $invite->invited_user_id,
                ],
                [
                    'role' => 'member',
                    'accepted_at' => now(),
                    'added_by_user_id' => $invite->invited_by_user_id,
                ]
            );

            $invite->update([
                'status' => PracticeLocationInvite::STATUS_ACCEPTED,
                'accepted_at' => now(),
            ]);
        });

        return redirect()
            ->route('practice-locations.index')
            ->with('success', 'Le cabinet partagé a bien été ajouté à votre compte.');
    }

    public function decline(Request $request, string $token)
    {
        $this->abortIfDisabled();

        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('warning', 'Connectez-vous pour refuser cette invitation.');
        }

        $invite = PracticeLocationInvite::query()
            ->where('token', $token)
            ->firstOrFail();

        if (mb_strtolower(Auth::user()->email) !== mb_strtolower($invite->invited_email)) {
            return redirect()
                ->route('practice-locations.invites.show', $token)
                ->with('error', 'Connectez-vous avec le compte correspondant à l’adresse invitée pour gérer cette invitation.');
        }

        if ($invite->isPending()) {
            $invite->update([
                'status' => PracticeLocationInvite::STATUS_DECLINED,
                'declined_at' => now(),
            ]);
        }

        return redirect()
            ->route('practice-locations.index')
            ->with('success', 'Invitation refusée.');
    }

    public function cancel(Request $request, PracticeLocationInvite $invite)
    {
        $this->abortIfDisabled();
        $this->authorizeOwner($invite->practiceLocation);

        if ($invite->status === PracticeLocationInvite::STATUS_PENDING) {
            $invite->update(['status' => PracticeLocationInvite::STATUS_CANCELLED]);
        }

        return redirect()
            ->route('practice-locations.edit', $invite->practiceLocation)
            ->with('success', 'Invitation annulée.');
    }

    private function authorizeOwner(PracticeLocation $location): void
    {
        abort_unless($this->cabinetAccessService->canManageLocation(Auth::user(), $location), 403);
    }

    private function abortIfDisabled(): void
    {
        abort_unless($this->cabinetAccessService->enabled(), 404);
    }
}

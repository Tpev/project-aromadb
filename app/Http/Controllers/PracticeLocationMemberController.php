<?php

namespace App\Http\Controllers;

use App\Models\PracticeLocation;
use App\Models\PracticeLocationMember;
use App\Services\CabinetAccessService;
use Illuminate\Support\Facades\Auth;

class PracticeLocationMemberController extends Controller
{
    public function __construct(
        private readonly CabinetAccessService $cabinetAccessService,
    ) {
    }

    public function destroy(PracticeLocation $practice_location, PracticeLocationMember $member)
    {
        $this->abortIfDisabled();
        abort_unless($this->cabinetAccessService->canManageLocation(Auth::user(), $practice_location), 403);

        abort_unless((int) $member->practice_location_id === (int) $practice_location->id, 404);

        if ($member->role === 'owner' || (int) $member->user_id === (int) $practice_location->user_id) {
            return back()->withErrors(['member' => 'Le propriétaire principal du cabinet ne peut pas être retiré.']);
        }

        $member->delete();

        return redirect()
            ->route('practice-locations.edit', $practice_location)
            ->with('success', 'Membre retiré du cabinet partagé.');
    }

    public function leave(PracticeLocation $practice_location)
    {
        $this->abortIfDisabled();

        $membership = PracticeLocationMember::query()
            ->where('practice_location_id', $practice_location->id)
            ->where('user_id', Auth::id())
            ->whereNotNull('accepted_at')
            ->first();

        if (!$membership) {
            abort(404);
        }

        if ($membership->role === 'owner' || (int) $practice_location->user_id === (int) Auth::id()) {
            return back()->withErrors(['member' => 'Le propriétaire ne peut pas quitter son propre cabinet.']);
        }

        $membership->delete();

        return redirect()
            ->route('practice-locations.index')
            ->with('success', 'Vous n’avez plus accès à ce cabinet partagé.');
    }

    private function abortIfDisabled(): void
    {
        abort_unless($this->cabinetAccessService->enabled(), 404);
    }
}

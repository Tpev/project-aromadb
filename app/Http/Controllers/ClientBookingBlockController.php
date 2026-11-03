<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Services\ClientBookingBlockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientBookingBlockController extends Controller
{
    public function update(Request $request, ClientProfile $clientProfile, ClientBookingBlockService $blocks): RedirectResponse
    {
        abort_unless(
            $request->user()->is_therapist && (int) $clientProfile->user_id === (int) $request->user()->id,
            403,
        );

        $validated = $request->validate(['blocked' => ['required', 'boolean']]);
        $blocked = (bool) $validated['blocked'];
        $blocks->setBlocked((int) $clientProfile->user_id, $clientProfile->email, $blocked);

        return redirect()->route(
            $request->routeIs('mobile.*') ? 'mobile.clients.show' : 'client_profiles.show',
            $clientProfile,
        )->with('booking_block_status', $blocked
            ? 'Ce client ne peut plus réserver en ligne avec cette adresse email.'
            : 'Ce client peut à nouveau réserver en ligne avec cette adresse email.');
    }
}

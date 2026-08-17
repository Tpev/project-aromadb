<?php

namespace App\Http\Controllers;

use App\Services\AppointmentEarlierSlotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AppointmentEarlierSlotController extends Controller
{
    public function show(string $token, AppointmentEarlierSlotService $service): Response
    {
        $offer = $service->offerForToken($token);
        abort_unless($offer, 404);

        return response()
            ->view('appointments.earlier_slot_offer', [
                'offer' => $offer,
                'state' => $service->state($offer),
            ])
            ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate');
    }

    public function claim(
        Request $request,
        string $token,
        AppointmentEarlierSlotService $service
    ): RedirectResponse {
        $request->validate(['confirmation' => ['required', 'accepted']]);
        abort_unless($service->offerForToken($token), 404);

        $result = $service->claim($token);

        if ($result['state'] === AppointmentEarlierSlotService::STATE_CLAIMED) {
            return redirect()->route('appointments.earlier-slot.show', $token)
                ->with('success', 'Votre rendez-vous a bien été avancé.');
        }

        if ($result['state'] === AppointmentEarlierSlotService::STATE_BUSY) {
            return redirect()->route('appointments.earlier-slot.show', $token)
                ->with('error', 'Une confirmation est en cours. Réessayez dans quelques secondes.');
        }

        if ($result['state'] === AppointmentEarlierSlotService::STATE_TAKEN) {
            return redirect()->route('appointments.earlier-slot.show', $token)
                ->with('error', 'Une autre personne a confirmé ce créneau avant vous. Aucun changement n’a été apporté à votre rendez-vous.');
        }

        return redirect()->route('appointments.earlier-slot.show', $token)
            ->with('error', 'Ce créneau n’est malheureusement plus disponible. Aucun changement n’a été apporté à votre rendez-vous.');
    }
}

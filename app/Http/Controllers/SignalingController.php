<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\SignalingEvent;

class SignalingController extends Controller
{
    /**
     * Gérer les messages de signaling WebRTC.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signaling(Request $request)
    {
        $type = $request->input('type');
        $payload = $request->input('payload');
        $room = $request->input('room');

        // Valider les entrées
        $request->validate([
            'type' => 'required|string|in:offer,answer,ice-candidate',
            'payload' => 'required|array',
            'room' => 'required|string',
        ]);

        // Diffuser l'événement de signaling
        broadcast(new SignalingEvent($type, $payload, $room))->toOthers();

        return response()->json(['status' => 'Message envoyé']);
    }
}

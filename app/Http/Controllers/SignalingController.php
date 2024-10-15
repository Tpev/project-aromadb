<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\SignalingEvent;
use Illuminate\Support\Facades\Log;

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

        // Log des données reçues
        Log::info("SignalingController: Diffusion de l'événement - Type: {$type}, Salle: {$room}");

        // Diffuser l'événement de signaling
        try {
            broadcast(new SignalingEvent($type, $payload, $room))->toOthers();
            Log::info("SignalingController: Événement {$type} diffusé sur room.{$room}");
        } catch (\Exception $e) {
            Log::error("SignalingController: Erreur lors de la diffusion de l'événement - {$e->getMessage()}");
        }

        return response()->json(['status' => 'Message envoyé']);
    }
}

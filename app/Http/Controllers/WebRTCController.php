<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebRTCController extends Controller
{
    /**
     * Display the WebRTC room view.
     *
     * @param string $room
     * @return \Illuminate\View\View
     */
    public function room($room)
    {
        // Optional: Add validation for the room name
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $room)) {
            abort(404, 'Room not found.');
        }

        Log::info("User joined room: {$room}");

        return view('webrtc.webrtc', ['room' => $room]);
    }

    /**
     * Handle signaling data (offer/answer).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signaling(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'room' => 'required|string',
            'type' => 'required|string',
            'data' => 'required|string',
            'senderId' => 'required|string',
        ]);

        $roomKey = 'webrtc-' . $request->room;
        $senderId = $request->senderId;
        $type = $request->type;

        if ($type === 'offer') {
            // Store the offer and mark the sender as initiator
            cache()->put("{$roomKey}-offer", $request->data, 600); // TTL 10 minutes
            cache()->put("{$roomKey}-initiator", $senderId, 600);
            Log::info("Stored offer for room: {$request->room} by senderId: {$senderId}");
        } elseif ($type === 'answer') {
            // Store the answer
            cache()->put("{$roomKey}-answer", $request->data, 600); // TTL 10 minutes
            Log::info("Stored answer for room: {$request->room} by senderId: {$senderId}");
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Retrieve the offer for a given room.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOffer(Request $request)
    {
        $roomKey = 'webrtc-' . $request->room;
        $offer = cache()->get("{$roomKey}-offer");

        if ($offer) {
            Log::info("Retrieved offer for room: {$request->room}");
            // Do not forget the offer here to allow reconnections
        } else {
            Log::warning("No offer found for room: {$request->room}");
        }

        return response()->json(['offer' => $offer ?? null]);
    }

    /**
     * Retrieve the answer for a given room.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnswer(Request $request)
    {
        $roomKey = 'webrtc-' . $request->room;
        $answer = cache()->get("{$roomKey}-answer");

        if ($answer) {
            Log::info("Retrieved answer for room: {$request->room}");
            cache()->forget("{$roomKey}-answer"); // Clear the answer after retrieval
        } else {
            Log::warning("No answer found for room: {$request->room}");
        }

        return response()->json(['answer' => $answer ?? null]);
    }

    /**
     * Clear signaling data when a participant disconnects.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearSignaling(Request $request)
    {
        $request->validate([
            'room' => 'required|string',
            'senderId' => 'required|string',
        ]);

        $roomKey = 'webrtc-' . $request->room;
        $senderId = $request->senderId;

        $initiatorId = cache()->get("{$roomKey}-initiator");

        if ($senderId === $initiatorId) {
            // If initiator disconnects, clear offer and answer
            cache()->forget("{$roomKey}-offer");
            cache()->forget("{$roomKey}-answer");
            cache()->forget("{$roomKey}-initiator");
            Log::info("Cleared offer and answer for room: {$request->room} as initiator disconnected.");
        } else {
            // If non-initiator disconnects, only clear answer
            cache()->forget("{$roomKey}-answer");
            Log::info("Cleared answer for room: {$request->room} as non-initiator disconnected.");
        }

        return response()->json(['status' => 'signaling cleared']);
    }
}

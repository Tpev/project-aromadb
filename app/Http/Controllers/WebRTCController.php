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
        Log::info("Received signaling data for room: {$request->room}, type: {$request->type}, senderId: {$request->senderId}");

        // Store offer or answer depending on type
        if ($request->type === 'offer') {
            cache()->put("{$roomKey}-offer", $request->data, 300); // Store offer
            Log::info("Stored offer for room: {$request->room}");
        } elseif ($request->type === 'answer') {
            cache()->put("{$roomKey}-answer", $request->data, 300); // Store answer
            Log::info("Stored answer for room: {$request->room}");
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
        } else {
            Log::warning("No answer found for room: {$request->room}");
        }

        return response()->json(['answer' => $answer ?? null]);
    }
}

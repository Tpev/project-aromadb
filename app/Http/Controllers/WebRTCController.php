<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebRTCController extends Controller
{
public function signaling(Request $request)
{
    // Validate the incoming request
    $request->validate([
        'room' => 'required|string',
        'type' => 'required|string',
        'data' => 'required|string', // Ensure 'data' is treated as a string
        'senderId' => 'required|string',
    ]);

    // Store the offer or answer
    $roomKey = 'webrtc-' . $request->room;

    // Store offer or answer depending on type
    if ($request->type === 'offer') {
        cache()->put($roomKey . '-offer', $request->data, 300); // Store offer
    } elseif ($request->type === 'answer') {
        cache()->put($roomKey . '-answer', $request->data, 300); // Store answer
    }

    return response()->json(['status' => 'success']);
}



public function getOffer(Request $request)
{
    $roomKey = 'webrtc-' . $request->room;

    // Retrieve the offer from cache
    $offer = cache()->get($roomKey . '-offer');

    return response()->json(['offer' => $offer ? $offer : null]);
}


    public function getAnswer(Request $request)
    {
        $roomKey = 'webrtc-' . $request->room;

        // Retrieve the answer from cache
        $answer = cache()->get($roomKey . '-answer');
        return response()->json(['answer' => $answer ? $answer : null]);
    }
}

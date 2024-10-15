<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pusher\Pusher;

class WebRTCController extends Controller
{
    protected $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ]
        );
    }

    public function signaling(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'room' => 'required|string',
            'type' => 'required|string',
            'data' => 'required|array', // Signaling data, like offer, answer, or ice-candidate
            'senderId' => 'required|string',
        ]);

        // Store 'offer' in cache so that late joiners can access it
        if ($request->type === 'offer') {
            cache()->forever('offer-' . $request->room, $request->data);
        }

        // Broadcast the signaling data (offer, answer, or ice-candidate) to all users in the room via Pusher
        $this->pusher->trigger('video-room.' . $request->room, 'client-signaling', [
            'type' => $request->type,  // either 'offer', 'answer', or 'ice-candidate'
            'data' => $request->data,
            'senderId' => $request->senderId,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function joinRoom(Request $request)
    {
        // Simulate "first user" logic based on caching to determine the initiator
        $isFirstUser = cache()->has('room-' . $request->room) ? false : true;

        // Cache the room to simulate that it's occupied for subsequent users
        if ($isFirstUser) {
            cache()->forever('room-' . $request->room, true);
        }

        // Broadcast that a new user has joined the room
        $this->pusher->trigger('video-room.' . $request->room, 'client-joined', [
            'user_id' => $request->user_id,
        ]);

        // Check if there's already an offer cached (i.e., if someone already initiated the room)
        $offer = cache()->get('offer-' . $request->room);

        return response()->json([
            'status' => 'joined',
            'isInitiator' => $isFirstUser, // Let the client know if they are the initiator
            'offer' => $offer, // Send the cached offer if it exists
        ]);
    }
}

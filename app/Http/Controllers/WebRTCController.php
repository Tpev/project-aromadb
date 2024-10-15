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
            'data' => 'required|array',
            'senderId' => 'required|string',
        ]);

        // Broadcast the signaling data to the room using Pusher
        $this->pusher->trigger('video-room.' . $request->room, 'client-signaling', [
            'type' => $request->type,
            'data' => $request->data,
            'senderId' => $request->senderId,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function joinRoom(Request $request)
    {
        // Simulate a "first user" condition based on session or time (since no user count logic without auth)
        $isFirstUser = cache()->has('room-' . $request->room) ? false : true;

        // Cache the room to simulate "first user" logic for subsequent users
        cache()->forever('room-' . $request->room, true);

        // Broadcast that a user has joined the room
        $this->pusher->trigger('video-room.' . $request->room, 'client-joined', [
            'user_id' => $request->user_id,
        ]);

        return response()->json(['status' => 'joined', 'isInitiator' => $isFirstUser]);
    }
}

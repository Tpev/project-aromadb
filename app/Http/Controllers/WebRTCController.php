<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Services\JitsiJwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebRTCController extends Controller
{
    public function room(string $room)
    {
        // 1. Meeting must exist
        $meeting = Meeting::where('room_token', $room)->firstOrFail();

        // 2. Who is joining?
        $user = Auth::user();

        $isTherapist = $user && $meeting->appointment
            && $meeting->appointment->user_id === $user->id;

        // 3. Build JWT payload
        $jwt = JitsiJwtService::generate([
            'sub' => config('services.jitsi.domain'),
            'room' => $room,
            'context' => [
                'user' => [
                    'name' => $isTherapist
                        ? $user->name
                        : ($meeting->clientProfile->first_name ?? 'Client'),
                    'moderator' => $isTherapist,
                ],
            ],
        ]);

        // 4. Redirect to Jitsi
        $url = sprintf(
            'https://%s/%s?jwt=%s',
            config('services.jitsi.domain'),
            $room,
            $jwt
        );

        return redirect()->away($url);
    }
}

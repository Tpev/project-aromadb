<?php
namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class MeetingController extends Controller
{
    public function create()
    {
		$clientProfiles = ClientProfile::where('user_id', Auth::id())->get();
        return view('meetings.create', compact('clientProfiles')0); // Create a view for the meeting form
    }

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'start_time' => 'required|date',
        'duration' => 'required|integer',
        'participant_email' => 'nullable|email',
        'client_profile_id' => 'nullable|exists:client_profiles,id',
    ]);

    // Generate a secure token for the room
    $token = Str::random(32);

    // Create the meeting
    $meeting = Meeting::create([
        'name' => $request->name,
        'start_time' => $request->start_time,
        'duration' => $request->duration,
        'participant_email' => $request->participant_email,
        'client_profile_id' => $request->client_profile_id,
        'room_token' => $token,
    ]);

    // Create the connection link using the meeting name
    $connectionLink = route('webrtc.room', ['room' => $token]); // Using the room token for secure access

    // Send email with connection link
    Mail::to($request->participant_email)->send(new \App\Mail\MeetingInvitation($connectionLink));

    // Redirect to confirmation page
    return redirect()->route('meetings.confirmation', ['link' => $connectionLink]);
}
public function confirmation(Request $request)
{
    $link = $request->query('link'); // Get the link from the query parameter
    return view('meetings.confirmation', compact('link'));
}


}

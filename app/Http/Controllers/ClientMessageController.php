<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\ClientProfile;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClientMessageReceivedTherapistMail;
use App\Mail\TherapistMessageSentToClientMail;

class ClientMessageController extends Controller
{
	
	public function fetchLatest()
{
    $client = auth('client')->user();

    $messages = Message::where('client_profile_id', $client->id)
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($msg) {
            return [
                'content' => $msg->content,
                'sender_type' => $msg->sender_type,
                'timestamp' => $msg->created_at->format('d/m/Y H:i'),
            ];
        });

    return response()->json($messages);
}

public function fetchLatestTherapist(ClientProfile $clientProfile)
{
    // Optional: Add authorization if needed
    if ($clientProfile->user_id !== auth()->id()) {
        abort(403);
    }

    $messages = $clientProfile->messages()
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($msg) {
            return [
                'content' => $msg->content,
                'sender_type' => $msg->sender_type,
                'timestamp' => $msg->created_at->format('d/m/Y H:i'),
            ];
        });

    return response()->json($messages);
}

public function index()
{
    $client = auth('client')->user();

    $messages = Message::where('client_profile_id', $client->id)
        ->orderBy('created_at', 'asc')
        ->get();

    return view('client.messages.index', compact('messages'));
}

public function store(Request $request)
{
    $client = auth('client')->user();

    $request->validate([
        'content' => 'required|string|max:2000',
    ]);

    $message = Message::create([
        'client_profile_id' => $client->id,
        'user_id'           => $client->user_id, // therapist owner
        'sender_type'       => 'client',
        'content'           => $request->content,
    ]);

    // Notify therapist by email
    $therapist = $client->user; // assumes ClientProfile belongsTo(User::class,'user_id')
    if ($therapist && $therapist->email) {
        Mail::to($therapist->email)->queue(
            new ClientMessageReceivedTherapistMail($client, $message)
        );
    }

    return response()->json(['success' => true]);
}



public function storeTherapist(Request $request, ClientProfile $clientProfile)
{
    if ($clientProfile->user_id !== auth()->id()) {
        abort(403, 'Accès non autorisé à ce profil client.');
    }

    $request->validate([
        'content' => 'required|string|max:2000',
    ]);

    $message = Message::create([
        'client_profile_id' => $clientProfile->id,
        'user_id'           => Auth::id(),
        'sender_type'       => 'therapist',
        'content'           => $request->content,
    ]);

    // Email patient/client
    $clientEmail = $clientProfile->email ?? $clientProfile->client_email ?? null;
    if ($clientEmail) {
        Mail::to($clientEmail)->queue(new TherapistMessageSentToClientMail($clientProfile, $message));
    }

    return response()->json(['success' => true]);
}

}

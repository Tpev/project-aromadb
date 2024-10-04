<?php

namespace App\Http\Controllers;

use App\Models\SessionNote;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mews\Purifier\Facades\Purifier; // Importer le façade Purifier

class SessionNoteController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    /**
     * Display a listing of session notes for a specific client profile.
     */
    public function index(ClientProfile $clientProfile)
    {
        $this->authorize('view', $clientProfile);

        $sessionNotes = SessionNote::where('client_profile_id', $clientProfile->id)
            ->where('user_id', Auth::id())
            ->get();

        return view('session_notes.index', compact('sessionNotes', 'clientProfile'));
    }

    /**
     * Show the form for creating a new session note for a specific client.
     */
    public function create($client_profile_id)
    {
        $clientProfile = ClientProfile::findOrFail($client_profile_id);
        $this->authorize('create', SessionNote::class);

        return view('session_notes.create', compact('clientProfile'));
    }

    /**
     * Store a newly created session note in storage.
     */
    public function store(Request $request, $client_profile_id)
    {
        $request->validate([
            'note' => 'required|string',
        ]);
			
        // Purifier le contenu HTML
        $clean_note = Purifier::clean($request->note);
		
		
        SessionNote::create([
            'client_profile_id' => $client_profile_id,
            'user_id' => Auth::id(),
            'note' => $clean_note,
        ]);
		
        return redirect()->route('session_notes.index', $client_profile_id)
            ->with('success', 'Session note created successfully.');
    }

    /**
     * Show a specific session note.
     */
    public function show(SessionNote $sessionNote)
    {
        $this->authorize('view', $sessionNote);

        return view('session_notes.show', compact('sessionNote'));
    }

    /**
     * Show the form for editing a session note.
     */
    public function edit(SessionNote $sessionNote)
    {
        $this->authorize('update', $sessionNote);

        return view('session_notes.edit', compact('sessionNote'));
    }

    /**
     * Update a session note in storage.
     */
    public function update(Request $request, SessionNote $sessionNote)
    {
        $this->authorize('update', $sessionNote);

        $request->validate([
            'note' => 'required|string',
        ]);

        // Purifier le contenu HTML
        $clean_note = Purifier::clean($request->note);

        $sessionNote->update([
            'note' => $clean_note,
        ]);

        return redirect()->route('session_notes.index', $sessionNote->client_profile_id)
            ->with('success', 'Session note updated successfully.');
    }

    /**
     * Remove the specified session note from storage.
     */
    public function destroy(SessionNote $sessionNote)
    {
        $this->authorize('delete', $sessionNote);

        $sessionNote->delete();

        return redirect()->route('session_notes.index', $sessionNote->client_profile_id)
            ->with('success', 'Session note deleted successfully.');
    }
}

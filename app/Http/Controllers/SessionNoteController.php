<?php

namespace App\Http\Controllers;

use App\Models\SessionNote;
use App\Models\ClientProfile;
use App\Models\SessionNoteTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mews\Purifier\Facades\Purifier;

class SessionNoteController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function index(ClientProfile $clientProfile)
    {
        $this->authorize('view', $clientProfile);

        $sessionNotes = SessionNote::where('client_profile_id', $clientProfile->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('session_notes.index', compact('sessionNotes', 'clientProfile'));
    }

    public function create($client_profile_id)
    {
        $clientProfile = ClientProfile::findOrFail($client_profile_id);
        $this->authorize('create', SessionNote::class);

        $templates = SessionNoteTemplate::where('user_id', Auth::id())
            ->orderBy('title')
            ->get();

        return view('session_notes.create', compact('clientProfile', 'templates'));
    }

    public function store(Request $request, $client_profile_id)
    {
        $data = $request->validate([
            'note' => 'required|string',
            'session_note_template_id' => 'nullable|integer',
        ]);

        $templateId = $data['session_note_template_id'] ?? null;

        if ($templateId) {
            // sécurité: le template doit appartenir au user
            $exists = SessionNoteTemplate::where('id', $templateId)
                ->where('user_id', Auth::id())
                ->exists();

            if (!$exists) {
                abort(403, 'Accès refusé (template).');
            }
        }

        $clean_note = Purifier::clean($data['note']);

        SessionNote::create([
            'client_profile_id' => $client_profile_id,
            'user_id' => Auth::id(),
            'session_note_template_id' => $templateId,
            'note' => $clean_note,
        ]);

        return redirect()->route('session_notes.index', $client_profile_id)
            ->with('success', 'Note de séance créée avec succès.');
    }

    public function show(SessionNote $sessionNote)
    {
        $this->authorize('view', $sessionNote);

        return view('session_notes.show', compact('sessionNote'));
    }

    public function edit(SessionNote $sessionNote)
    {
        $this->authorize('update', $sessionNote);

        return view('session_notes.edit', compact('sessionNote'));
    }

    public function update(Request $request, SessionNote $sessionNote)
    {
        $this->authorize('update', $sessionNote);

        $data = $request->validate([
            'note' => 'required|string',
        ]);

        $clean_note = Purifier::clean($data['note']);

        $sessionNote->update([
            'note' => $clean_note,
        ]);

        return redirect()->route('session_notes.index', $sessionNote->client_profile_id)
            ->with('success', 'Note de séance mise à jour avec succès.');
    }

    public function destroy(SessionNote $sessionNote)
    {
        $this->authorize('delete', $sessionNote);

        $sessionNote->delete();

        return redirect()->route('session_notes.index', $sessionNote->client_profile_id)
            ->with('success', 'Note de séance supprimée avec succès.');
    }
}

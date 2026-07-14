<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\SessionNote;
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

        $sessionNotes = SessionNote::query()
            ->with(['template', 'appointment'])
            ->where('client_profile_id', $clientProfile->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('session_notes.index', compact('sessionNotes', 'clientProfile'));
    }

    public function create(Request $request, ClientProfile $clientProfile)
    {
        $this->authorize('view', $clientProfile);
        $this->authorize('create', SessionNote::class);

        $templates = SessionNoteTemplate::query()
            ->where('user_id', Auth::id())
            ->orderBy('title')
            ->get();
        $appointment = $this->ownedAppointment($request, $clientProfile);

        return view('session_notes.create', compact('clientProfile', 'templates', 'appointment'));
    }

    public function store(Request $request, ClientProfile $clientProfile)
    {
        $this->authorize('view', $clientProfile);
        $this->authorize('create', SessionNote::class);

        $data = $request->validate([
            'note' => ['required', 'string'],
            'session_note_template_id' => ['nullable', 'integer'],
            'appointment_id' => ['nullable', 'integer'],
        ]);

        $templateId = $data['session_note_template_id'] ?? null;
        if ($templateId && ! SessionNoteTemplate::query()
            ->where('id', $templateId)
            ->where('user_id', Auth::id())
            ->exists()) {
            abort(403, 'Accès refusé au modèle de note.');
        }

        $appointment = $this->ownedAppointment($request, $clientProfile);
        SessionNote::create([
            'client_profile_id' => $clientProfile->id,
            'user_id' => Auth::id(),
            'session_note_template_id' => $templateId,
            'appointment_id' => $appointment?->id,
            'note' => Purifier::clean($data['note']),
        ]);

        $redirect = $appointment
            ? redirect()->route('appointments.show', $appointment)
            : redirect()->route('session_notes.index', $clientProfile);

        return $redirect->with('success', 'Note de séance créée avec succès.');
    }

    public function show(SessionNote $sessionNote)
    {
        $this->authorize('view', $sessionNote);
        $sessionNote->load(['clientProfile', 'template', 'appointment']);

        return view('session_notes.show', compact('sessionNote'));
    }

    public function edit(SessionNote $sessionNote)
    {
        $this->authorize('update', $sessionNote);
        $sessionNote->load(['clientProfile', 'appointment']);

        return view('session_notes.edit', compact('sessionNote'));
    }

    public function update(Request $request, SessionNote $sessionNote)
    {
        $this->authorize('update', $sessionNote);
        $data = $request->validate(['note' => ['required', 'string']]);
        $sessionNote->update(['note' => Purifier::clean($data['note'])]);

        return redirect()->route('session_notes.index', $sessionNote->client_profile_id)
            ->with('success', 'Note de séance mise à jour avec succès.');
    }

    public function destroy(SessionNote $sessionNote)
    {
        $this->authorize('delete', $sessionNote);
        $clientProfileId = $sessionNote->client_profile_id;
        $sessionNote->delete();

        return redirect()->route('session_notes.index', $clientProfileId)
            ->with('success', 'Note de séance supprimée avec succès.');
    }

    private function ownedAppointment(Request $request, ClientProfile $clientProfile): ?Appointment
    {
        if (! $request->filled('appointment_id')) {
            return null;
        }

        return Appointment::query()
            ->where('user_id', Auth::id())
            ->where('client_profile_id', $clientProfile->id)
            ->where(function ($query) {
                $query->where('external', false)->orWhereNull('external');
            })
            ->findOrFail($request->integer('appointment_id'));
    }
}

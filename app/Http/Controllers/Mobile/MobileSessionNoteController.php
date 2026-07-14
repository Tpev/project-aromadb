<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\SessionNote;
use App\Models\SessionNoteTemplate;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mews\Purifier\Facades\Purifier;

class MobileSessionNoteController extends Controller
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

        return view('mobile.session-notes.index', compact('clientProfile', 'sessionNotes'));
    }

    public function create(Request $request, ClientProfile $clientProfile)
    {
        $this->authorize('view', $clientProfile);
        $this->authorize('create', SessionNote::class);

        $appointment = $this->ownedAppointment($request, $clientProfile);

        return view('mobile.session-notes.form', [
            'clientProfile' => $clientProfile,
            'sessionNote' => new SessionNote(),
            'templates' => $this->templates(),
            'action' => route('mobile.session-notes.store', $clientProfile),
            'method' => 'POST',
            'title' => 'Nouvelle note',
            'submitLabel' => 'Creer la note',
            'appointment' => $appointment,
        ]);
    }

    public function store(Request $request, ClientProfile $clientProfile)
    {
        $this->authorize('view', $clientProfile);
        $this->authorize('create', SessionNote::class);

        $validated = $this->validatedNote($request, includeTemplate: true);
        $templateId = $this->ownedTemplateId($validated['session_note_template_id'] ?? null);
        $appointment = $this->ownedAppointment($request, $clientProfile);

        SessionNote::create([
            'client_profile_id' => $clientProfile->id,
            'user_id' => Auth::id(),
            'session_note_template_id' => $templateId,
            'appointment_id' => $appointment?->id,
            'note' => Purifier::clean($validated['note']),
        ]);

        $redirect = $appointment
            ? redirect()->route('mobile.appointments.show', $appointment)
            : redirect()->route('mobile.session-notes.index', $clientProfile);

        return $redirect->with('success', 'Note de séance créée.');
    }

    public function show(SessionNote $sessionNote)
    {
        $this->authorize('view', $sessionNote);

        $sessionNote->load(['clientProfile', 'template', 'appointment']);

        return view('mobile.session-notes.show', compact('sessionNote'));
    }

    public function edit(SessionNote $sessionNote)
    {
        $this->authorize('update', $sessionNote);

        $sessionNote->load('clientProfile');

        return view('mobile.session-notes.form', [
            'clientProfile' => $sessionNote->clientProfile,
            'sessionNote' => $sessionNote,
            'templates' => collect(),
            'action' => route('mobile.session-notes.update', $sessionNote),
            'method' => 'PUT',
            'title' => 'Modifier la note',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function update(Request $request, SessionNote $sessionNote)
    {
        $this->authorize('update', $sessionNote);

        $validated = $this->validatedNote($request);

        $sessionNote->update([
            'note' => Purifier::clean($validated['note']),
        ]);

        return redirect()
            ->route('mobile.session-notes.show', $sessionNote)
            ->with('success', 'Note de seance mise a jour.');
    }

    public function destroy(SessionNote $sessionNote)
    {
        $this->authorize('delete', $sessionNote);

        $clientProfile = $sessionNote->clientProfile;
        $sessionNote->delete();

        return redirect()
            ->route('mobile.session-notes.index', $clientProfile)
            ->with('success', 'Note de seance supprimee.');
    }

    protected function validatedNote(Request $request, bool $includeTemplate = false): array
    {
        $rules = [
            'note' => ['required', 'string'],
        ];

        if ($includeTemplate) {
            $rules['session_note_template_id'] = ['nullable', 'integer'];
            $rules['appointment_id'] = ['nullable', 'integer'];
        }

        return $request->validate($rules);
    }

    protected function ownedTemplateId(null|int|string $templateId): ?int
    {
        if (! $templateId) {
            return null;
        }

        $template = SessionNoteTemplate::query()
            ->where('user_id', Auth::id())
            ->findOrFail($templateId);

        return (int) $template->id;
    }

    protected function templates()
    {
        return SessionNoteTemplate::query()
            ->where('user_id', Auth::id())
            ->orderBy('title')
            ->get();
    }

    protected function ownedAppointment(Request $request, ClientProfile $clientProfile): ?Appointment
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

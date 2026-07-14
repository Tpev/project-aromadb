<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\SessionNoteTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mews\Purifier\Facades\Purifier;

class SessionNoteTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = SessionNoteTemplate::query()
            ->where('user_id', Auth::id())
            ->orderBy('title')
            ->get();

        return view('session_note_templates.index', array_merge(
            compact('templates'),
            $this->templateContext($request)
        ));
    }

    public function create(Request $request)
    {
        return view('session_note_templates.create', $this->templateContext($request));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        SessionNoteTemplate::create([
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'content' => Purifier::clean($data['content'] ?? ''),
        ]);

        return redirect()->route('session-note-templates.index', $this->contextQuery($request))
            ->with('success', 'Modèle créé avec succès.');
    }

    public function show(Request $request, SessionNoteTemplate $session_note_template)
    {
        $this->own($session_note_template);

        return view('session_note_templates.show', array_merge([
            'template' => $session_note_template,
        ], $this->templateContext($request)));
    }

    public function edit(Request $request, SessionNoteTemplate $session_note_template)
    {
        $this->own($session_note_template);

        return view('session_note_templates.edit', array_merge([
            'template' => $session_note_template,
        ], $this->templateContext($request)));
    }

    public function update(Request $request, SessionNoteTemplate $session_note_template)
    {
        $this->own($session_note_template);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $session_note_template->update([
            'title' => $data['title'],
            'content' => Purifier::clean($data['content'] ?? ''),
        ]);

        return redirect()->route('session-note-templates.index', $this->contextQuery($request))
            ->with('success', 'Modèle mis à jour avec succès.');
    }

    public function destroy(Request $request, SessionNoteTemplate $session_note_template)
    {
        $this->own($session_note_template);
        $session_note_template->delete();

        return redirect()->route('session-note-templates.index', $this->contextQuery($request))
            ->with('success', 'Modèle supprimé.');
    }

    private function own(SessionNoteTemplate $template): void
    {
        if ((int) $template->user_id !== (int) Auth::id()) {
            abort(403, 'Accès refusé.');
        }
    }

    private function templateContext(Request $request): array
    {
        $query = $this->contextQuery($request);
        $appointment = null;
        $client = null;

        if (! empty($query['appointment_id'])) {
            $appointment = Appointment::query()
                ->where('user_id', Auth::id())
                ->with('clientProfile')
                ->find($query['appointment_id']);
            $client = $appointment?->clientProfile;

            if ($client && (int) $client->user_id !== (int) Auth::id()) {
                $appointment = null;
                $client = null;
            }
        }

        if (! $client && ! empty($query['client_profile_id'])) {
            $client = ClientProfile::query()
                ->where('user_id', Auth::id())
                ->find($query['client_profile_id']);
        }

        $safeQuery = array_filter([
            'client_profile_id' => $client?->id,
            'appointment_id' => $appointment?->id,
        ]);

        return [
            'templateContextQuery' => $safeQuery,
            'templateReturnUrl' => $client
                ? route('session_notes.create', array_merge(['clientProfile' => $client->id], array_filter([
                    'appointment_id' => $appointment?->id,
                ])))
                : route('client_profiles.index'),
            'templateReturnLabel' => $client
                ? 'Retour à la note de '.trim($client->first_name.' '.$client->last_name)
                : 'Retour aux clients',
        ];
    }

    private function contextQuery(Request $request): array
    {
        return array_filter([
            'client_profile_id' => $request->integer('client_profile_id') ?: null,
            'appointment_id' => $request->integer('appointment_id') ?: null,
        ]);
    }
}

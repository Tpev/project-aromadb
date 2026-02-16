<?php

namespace App\Http\Controllers;

use App\Models\SessionNoteTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mews\Purifier\Facades\Purifier;

class SessionNoteTemplateController extends Controller
{
    public function index()
    {
        $templates = SessionNoteTemplate::where('user_id', Auth::id())
            ->orderBy('title')
            ->get();

        return view('session_note_templates.index', compact('templates'));
    }

    public function create()
    {
        return view('session_note_templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $clean = Purifier::clean($data['content'] ?? '');

        SessionNoteTemplate::create([
            'user_id'  => Auth::id(),
            'title'    => $data['title'],
            'content'  => $clean,
        ]);

        return redirect()->route('session-note-templates.index')
            ->with('success', 'Template créé avec succès.');
    }

    public function show(SessionNoteTemplate $session_note_template)
    {
        $this->own($session_note_template);

        return view('session_note_templates.show', [
            'template' => $session_note_template,
        ]);
    }

    public function edit(SessionNoteTemplate $session_note_template)
    {
        $this->own($session_note_template);

        return view('session_note_templates.edit', [
            'template' => $session_note_template,
        ]);
    }

    public function update(Request $request, SessionNoteTemplate $session_note_template)
    {
        $this->own($session_note_template);

        $data = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $clean = Purifier::clean($data['content'] ?? '');

        $session_note_template->update([
            'title'   => $data['title'],
            'content' => $clean,
        ]);

        return redirect()->route('session-note-templates.index')
            ->with('success', 'Template mis à jour avec succès.');
    }

    public function destroy(SessionNoteTemplate $session_note_template)
    {
        $this->own($session_note_template);

        $session_note_template->delete();

        return redirect()->route('session-note-templates.index')
            ->with('success', 'Template supprimé.');
    }

    private function own(SessionNoteTemplate $template): void
    {
        if ($template->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }
    }
}

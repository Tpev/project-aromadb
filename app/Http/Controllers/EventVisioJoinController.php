<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\EventVisioJoinLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class EventVisioJoinController extends Controller
{
    public function show(Event $event, EventVisioJoinLink $joinLink): Response|RedirectResponse
    {
        abort_unless($event->isAromaMadeVisio(), 404);

        if (! $joinLink->usesNameGate($event)) {
            return $this->redirectToDirectLink($event, $joinLink);
        }

        return response()
            ->view('events.visio-join', [
                'event' => $event,
                'suggestedName' => session("event_visio_names.{$event->id}"),
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function join(Request $request, Event $event, EventVisioJoinLink $joinLink): RedirectResponse
    {
        abort_unless($event->isAromaMadeVisio(), 404);

        if (! $joinLink->usesNameGate($event)) {
            return $this->redirectToDirectLink($event, $joinLink);
        }

        $displayName = strip_tags((string) $request->input('display_name', ''));
        $displayName = preg_replace('/[\x{0000}-\x{001F}\x{007F}]/u', ' ', $displayName) ?? '';
        $displayName = preg_replace('/\s+/u', ' ', trim($displayName)) ?? '';
        $request->merge(['display_name' => $displayName]);

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:80'],
        ], [
            'display_name.required' => 'Indiquez le prénom et le nom à afficher pendant la visio.',
            'display_name.max' => 'Le nom affiché ne peut pas dépasser 80 caractères.',
        ]);

        session()->put("event_visio_names.{$event->id}", $validated['display_name']);

        $destination = $joinLink->directForDisplayName($event, $validated['display_name']);
        abort_unless($destination, 404);

        return redirect()->away($destination);
    }

    private function redirectToDirectLink(Event $event, EventVisioJoinLink $joinLink): RedirectResponse
    {
        $destination = $joinLink->directFor($event);
        abort_unless($destination, 404);

        return redirect()->away($destination);
    }
}

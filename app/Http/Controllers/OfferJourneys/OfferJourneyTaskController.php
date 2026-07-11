<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyTask;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfferJourneyTaskController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, OfferJourneyContact $contact): RedirectResponse
    {
        $this->authorize('update', $contact);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'note' => ['nullable', 'string', 'max:2000'],
            'due_at' => ['nullable', 'date'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high'])],
        ]);

        $contact->tasks()->create([
            ...$validated,
            'user_id' => $request->user()->id,
            'status' => 'open',
        ]);

        return back()->with('success', 'La tâche a été ajoutée.');
    }

    public function update(
        Request $request,
        OfferJourneyContact $contact,
        OfferJourneyTask $task
    ): RedirectResponse {
        $this->authorize('update', $contact);
        abort_unless((int) $task->offer_journey_contact_id === (int) $contact->id, 404);

        $validated = $request->validate(['status' => ['required', Rule::in(['open', 'completed', 'cancelled'])]]);
        $task->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ]);

        return back()->with('success', 'La tâche a été mise à jour.');
    }
}

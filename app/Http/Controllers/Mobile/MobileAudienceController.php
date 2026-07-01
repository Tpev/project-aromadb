<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MobileAudienceController extends Controller
{
    public function index()
    {
        $audiences = Audience::query()
            ->where('user_id', Auth::id())
            ->withCount('clients')
            ->orderBy('name')
            ->get();

        return view('mobile.audiences.index', compact('audiences'));
    }

    public function create()
    {
        return view('mobile.audiences.form', [
            'title' => 'Nouvelle audience',
            'audience' => new Audience(),
            'clients' => $this->ownedClients(),
            'selectedClientIds' => [],
            'action' => route('mobile.audiences.store'),
            'method' => 'POST',
            'submitLabel' => 'Creer',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPayload($request);

        $audience = Audience::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $audience->clients()->sync($validated['client_ids']);

        return redirect()
            ->route('mobile.audiences.show', $audience)
            ->with('success', 'Audience creee.');
    }

    public function show(Audience $audience)
    {
        $this->ensureOwnsAudience($audience);

        $audience->load([
            'clients' => fn ($query) => $query->orderBy('last_name')->orderBy('first_name'),
        ]);
        $audience->loadCount('clients');

        return view('mobile.audiences.show', compact('audience'));
    }

    public function edit(Audience $audience)
    {
        $this->ensureOwnsAudience($audience);

        return view('mobile.audiences.form', [
            'title' => 'Modifier l audience',
            'audience' => $audience,
            'clients' => $this->ownedClients(),
            'selectedClientIds' => $audience->clients()->pluck('client_profiles.id')->map(fn ($id) => (int) $id)->all(),
            'action' => route('mobile.audiences.update', $audience),
            'method' => 'PUT',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function update(Request $request, Audience $audience)
    {
        $this->ensureOwnsAudience($audience);

        $validated = $this->validatedPayload($request);

        $audience->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $audience->clients()->sync($validated['client_ids']);

        return redirect()
            ->route('mobile.audiences.show', $audience)
            ->with('success', 'Audience mise a jour.');
    }

    public function destroy(Audience $audience)
    {
        $this->ensureOwnsAudience($audience);

        $audience->clients()->detach();
        $audience->delete();

        return redirect()
            ->route('mobile.audiences.index')
            ->with('success', 'Audience supprimee.');
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_ids' => ['nullable', 'array'],
            'client_ids.*' => ['integer', 'exists:client_profiles,id'],
        ]);

        $clientIds = collect($validated['client_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($clientIds->isNotEmpty()) {
            $ownedCount = ClientProfile::query()
                ->where('user_id', Auth::id())
                ->whereIn('id', $clientIds)
                ->count();

            if ($ownedCount !== $clientIds->count()) {
                throw ValidationException::withMessages([
                    'client_ids' => 'Un ou plusieurs clients ne vous appartiennent pas.',
                ]);
            }
        }

        $validated['client_ids'] = $clientIds->all();

        return $validated;
    }

    private function ownedClients()
    {
        return ClientProfile::query()
            ->where('user_id', Auth::id())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    private function ensureOwnsAudience(Audience $audience): void
    {
        if ((int) $audience->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}

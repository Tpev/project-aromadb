<?php

namespace App\Http\Controllers;

use App\Models\Audience;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AudienceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $audiences = Audience::where('user_id', $user->id)
            ->withCount('clients')
            ->orderBy('name')
            ->get();

        return view('audiences.index', compact('audiences'));
    }

    public function create()
    {
        $user = Auth::user();

        $clients = ClientProfile::where('user_id', $user->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('audiences.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_ids' => ['nullable', 'array'],
            'client_ids.*' => ['integer', 'exists:client_profiles,id'],
        ]);

        $audience = Audience::create([
            'user_id'     => $user->id,
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['client_ids'])) {
            $audience->clients()->sync($validated['client_ids']);
        }

        return redirect()
            ->route('audiences.index')
            ->with('success', 'Liste créée avec succès.');
    }

    public function edit(Audience $audience)
    {
        $this->authorizeAudience($audience);

        $user = Auth::user();

        $clients = ClientProfile::where('user_id', $user->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $selectedClientIds = $audience->clients()->pluck('client_profiles.id')->toArray();

        return view('audiences.edit', compact('audience', 'clients', 'selectedClientIds'));
    }

    public function update(Request $request, Audience $audience)
    {
        $this->authorizeAudience($audience);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_ids' => ['nullable', 'array'],
            'client_ids.*' => ['integer', 'exists:client_profiles,id'],
        ]);

        $audience->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $audience->clients()->sync($validated['client_ids'] ?? []);

        return redirect()
            ->route('audiences.index')
            ->with('success', 'Liste mise à jour avec succès.');
    }

    public function destroy(Audience $audience)
    {
        $this->authorizeAudience($audience);

        $audience->delete();

        return redirect()
            ->route('audiences.index')
            ->with('success', 'Liste supprimée.');
    }

    protected function authorizeAudience(Audience $audience): void
    {
        if ($audience->user_id !== Auth::id()) {
            abort(403);
        }
    }
}

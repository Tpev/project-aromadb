<?php

namespace App\Http\Controllers;

use App\Models\Conseil;
use App\Http\Requests\ConseilRequest;
use Illuminate\Support\Facades\Auth;

class ConseilController extends Controller
{
    /**
     * Display a listing of the conseils.
     */
    public function index()
    {
        // Get only the conseils for the currently authenticated therapist (if needed)
        $conseils = Conseil::where('user_id', Auth::id())->get();

        return view('conseils.index', compact('conseils'));
    }

    /**
     * Show the form for creating a new conseil.
     */
    public function create()
    {
        return view('conseils.create');
    }

    /**
     * Store a newly created conseil in storage.
     */
    public function store(ConseilRequest $request)
    {
        $data = $request->validated();

        // Handle file uploads if necessary
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('conseils/images', 'public');
        }

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('conseils/attachments', 'public');
        }

        $data['user_id'] = Auth::id();

        Conseil::create($data);

        return redirect()->route('conseils.index')->with('success', 'Conseil créé avec succès.');
    }

    /**
     * Display the specified conseil.
     */
public function show(Conseil $conseil)
{
    // Check that the conseil belongs to the currently authenticated user
    if ($conseil->user_id !== Auth::id()) {
        abort(403, 'Accès refusé. Ce conseil ne vous appartient pas.');
    }

    return view('conseils.show', compact('conseil'));
}

/**
 * Show the form for editing the specified conseil.
 */
public function edit(Conseil $conseil)
{
    // Check that the conseil belongs to the currently authenticated user
    if ($conseil->user_id !== Auth::id()) {
        abort(403, 'Accès refusé. Ce conseil ne vous appartient pas.');
    }

    return view('conseils.edit', compact('conseil'));
}


    /**
     * Update the specified conseil in storage.
     */
    public function update(ConseilRequest $request, Conseil $conseil)
    {
      //  $this->authorize('update', $conseil);
    // Check that the conseil belongs to the currently authenticated user
    if ($conseil->user_id !== Auth::id()) {
        abort(403, 'Accès refusé. Ce conseil ne vous appartient pas.');
    }
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('conseils/images', 'public');
        }

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('conseils/attachments', 'public');
        }

        $conseil->update($data);

        return redirect()->route('conseils.index')->with('success', 'Conseil mis à jour avec succès.');
    }

    /**
     * Remove the specified conseil from storage.
     */
    public function destroy(Conseil $conseil)
    {
      //  $this->authorize('delete', $conseil);
    // Check that the conseil belongs to the currently authenticated user
    if ($conseil->user_id !== Auth::id()) {
        abort(403, 'Accès refusé. Ce conseil ne vous appartient pas.');
    }
        $conseil->delete();

        return redirect()->route('conseils.index')->with('success', 'Conseil supprimé avec succès.');
    }
}

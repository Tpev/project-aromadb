<?php

namespace App\Http\Controllers;

use App\Models\Tisane;
use Illuminate\Http\Request;

class TisaneController extends Controller
{
    public function index()
    {
        $tisanes = Tisane::all();
        return view('tisanes.index', compact('tisanes'));
    }

    public function create()
    {
        return view('tisanes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'REF' => 'required|string|max:255',
            'NomTisane' => 'required|string|max:255',
            'NomLatin' => 'required|string|max:255',
            'Provenance' => 'nullable|string|max:255',
            'OrganeProducteur' => 'nullable|string|max:255',
            'Sb' => 'nullable|string|max:255',
            'Properties' => 'nullable|string',
            'Indications' => 'nullable|string',
            'ContreIndications' => 'nullable|string',
            'Note' => 'nullable|string',
            'Description' => 'nullable|string',
        ]);

        Tisane::create($data);

        return redirect()->route('tisanes.index')->with('success', 'Tisane created successfully');
    }

    public function show($slug)
    {
        $tisane = Tisane::where('slug', $slug)->firstOrFail();
        return view('tisanes.show', compact('tisane'));
    }

    public function edit(Tisane $tisane)
    {
        return view('tisanes.edit', compact('tisane'));
    }

    public function update(Request $request, Tisane $tisane)
    {
        $data = $request->validate([
            'REF' => 'required|string|max:255',
            'NomTisane' => 'required|string|max:255',
            'NomLatin' => 'required|string|max:255',
            'Provenance' => 'nullable|string|max:255',
            'OrganeProducteur' => 'nullable|string|max:255',
            'Sb' => 'nullable|string|max:255',
            'Properties' => 'nullable|string',
            'Indications' => 'nullable|string',
            'ContreIndications' => 'nullable|string',
            'Note' => 'nullable|string',
            'Description' => 'nullable|string',
        ]);

        $data['slug'] = \Str::slug($data['NomTisane']);
        $tisane->update($data);

        return redirect()->route('tisanes.index')->with('success', 'Tisane updated successfully');
    }

    public function destroy(Tisane $tisane)
    {
        $tisane->delete();
        return redirect()->route('tisanes.index')->with('success', 'Tisane deleted successfully');
    }
}

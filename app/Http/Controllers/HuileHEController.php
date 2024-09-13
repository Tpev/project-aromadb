<?php

namespace App\Http\Controllers;

use App\Models\HuileHE;
use Illuminate\Http\Request;

class HuileHEController extends Controller
{
	public function index()
	{
		$huileHEs = HuileHE::all();
		return view('huilehe.index', compact('huileHEs'));
	}


    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'REF' => 'required|string|max:255',
            'NomHE' => 'required|string|max:255',
            'NomLatin' => 'required|string|max:255',
            'Provenance' => 'required|string|max:255',
            'OrganeProducteur' => 'required|string|max:255',
            'Sb' => 'required|string|max:255',
            'Properties' => 'required|string',
            'Indications' => 'required|string',
            'ContreIndications' => 'nullable|string',
            'Note' => 'nullable|string',
            'Description' => 'nullable|string',
        ]);

        $huileHE = HuileHE::create($request->all());
        return response()->json($huileHE, 201);
    }

    public function show($slug)
    {
        // Retrieve the record by slug instead of id
        $huileHE = HuileHE::where('slug', $slug)->first();

        // Ensure that the record exists
        if (!$huileHE) {
            abort(404, 'HuileHV not found');
        }

        // Pass the record to the view
        return view('huilehe.show', compact('huileHE'));
    }


    public function edit(HuileHE $huileHE)
    {
        //
    }

    public function update(Request $request, HuileHE $huileHE)
    {
        $request->validate([
            'REF' => 'required|string|max:255',
            'NomHE' => 'required|string|max:255',
            'NomLatin' => 'required|string|max:255',
            'Provenance' => 'required|string|max:255',
            'OrganeProducteur' => 'required|string|max:255',
            'Sb' => 'required|string|max:255',
            'Properties' => 'required|string',
            'Indications' => 'required|string',
            'ContreIndications' => 'nullable|string',
            'Note' => 'nullable|string',
            'Description' => 'nullable|string',
        ]);

        $huileHE->update($request->all());
        return response()->json($huileHE);
    }

    public function destroy(HuileHE $huileHE)
    {
        $huileHE->delete();
        return response()->json(null, 204);
    }
}

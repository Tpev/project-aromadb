<?php

namespace App\Http\Controllers;

use App\Models\HuileHE;
use Illuminate\Http\Request;

class HuileHEController extends Controller
{
   public function index(Request $request)
    {
        // Start a query on the HuileHE model
        $query = HuileHE::query();

        // Handle Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('NomHE', 'like', '%' . $search . '%');
        }

        // Handle Filter by Indication
        if ($request->filled('indication')) {
            $indication = $request->input('indication');
            $query->where('Indications', 'like', '%' . $indication . '%');
        }

        // Paginate the results with 12 items per page
        $huileHEs = $query->orderBy('NomHE', 'asc')->paginate(12)->appends($request->only(['search', 'indication']));

        // Gather all unique indications for the filter dropdown
        $indications = HuileHE::pluck('Indications')
            ->map(function($item) {
                return explode(';', $item);
            })
            ->flatten()
            ->unique()
            ->filter()
            ->sort();

        return view('huilehe.index', compact('huileHEs', 'indications'));
    
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

public function showHuileHEPropriete()
{
    // Retrieve all HuileHE data
    $huileHEs = HuileHE::all();

    // Group the huiles by their 'Propriétés', splitting by semicolon and sorting alphabetically
    $groupedByProperty = collect();

    foreach ($huileHEs as $huileHE) {
        // Split the Properties field by semicolon and trim each value
        $properties = explode(';', $huileHE->Properties);

        foreach ($properties as $property) {
            $property = trim($property); // Trim spaces around each property
            if ($property !== '') {
                // If the property doesn't exist yet in the collection, initialize it as an empty array
                if (!$groupedByProperty->has($property)) {
                    $groupedByProperty[$property] = collect();
                }
                // Add the huileHE to the collection of that property
                $groupedByProperty[$property]->push($huileHE);
            }
        }
    }

    // Sort the grouped properties alphabetically by their key (property name)
    $groupedByProperty = $groupedByProperty->sortKeys();

    // Pass the grouped and sorted data to the view
    return view('huilehe.showhuilehepropriete', compact('groupedByProperty'));
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

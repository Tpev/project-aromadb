<?php
namespace App\Http\Controllers;

use App\Models\HuileHV;
use Illuminate\Http\Request;

class HuileHVController extends Controller
{
   public function index(Request $request)
    {
        // Initialiser la requête
        $query = HuileHV::query();

        // Gestion de la recherche par nom
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('NomHV', 'like', '%' . $search . '%');
        }

        // Gestion du filtrage par indication
        if ($request->filled('indication')) {
            $indication = $request->input('indication');
            $query->where('Indications', 'like', '%' . $indication . '%');
        }

        // Pagination avec 12 éléments par page
        $huileHVs = $query->orderBy('NomHV', 'asc')->paginate(12)->appends($request->only(['search', 'indication']));

        // Récupérer toutes les indications uniques pour le filtre
        $indications = HuileHV::pluck('Indications')
            ->map(function($item) {
                return explode(';', $item);
            })
            ->flatten()
            ->unique()
            ->filter()
            ->sort();

        // Ajouter une propriété 'image_url' à chaque huile végétale
        $huileHVs->getCollection()->transform(function($huileHV) {
            $imagePath = 'images/' . $huileHV->slug . '.webp';
            $defaultImage = 'images/default.webp';
            $finalImage = file_exists(public_path($imagePath)) ? $imagePath : $defaultImage;
            $huileHV->image_url = asset($finalImage);
            return $huileHV;
        });

        return view('huilehvs.index', compact('huileHVs', 'indications'));
    }

    public function create()
    {
        return view('huilehvs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'REF' => 'required|string|max:255',
            'NomHV' => 'required|string|max:255',
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

        // Generate slug before saving
        $data['slug'] = \Str::slug($data['NomHV']);

        HuileHV::create($data);

        return redirect()->route('huilehvs.index')->with('success', 'HuileHV created successfully');
    }

    // Retrieve by slug
    public function show($slug)
    {
        // Retrieve the record by slug instead of id
        $huileHV = HuileHV::where('slug', $slug)->first();

        // Ensure that the record exists
        if (!$huileHV) {
            abort(404, 'HuileHV not found');
        }

        // Pass the record to the view
        return view('huilehvs.show', compact('huileHV'));
    }

    public function edit(HuileHV $huileHV)
    {
        return view('huilehvs.edit', compact('huileHV'));
    }

    public function update(Request $request, HuileHV $huileHV)
    {
        $data = $request->validate([
            'REF' => 'required|string|max:255',
            'NomHV' => 'required|string|max:255',
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

        // Update slug if the name changes
        $data['slug'] = \Str::slug($data['NomHV']);

        $huileHV->update($data);

        return redirect()->route('huilehvs.index')->with('success', 'HuileHV updated successfully');
    }

    public function destroy(HuileHV $huileHV)
    {
        $huileHV->delete();
        return redirect()->route('huilehvs.index')->with('success', 'HuileHV deleted successfully');
    }
}

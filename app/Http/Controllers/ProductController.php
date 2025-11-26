<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // Apply authentication and authorization policies
    public function __construct()
    {
        // You can add middleware or policies here, if needed
    }

    /**
     * Display a listing of the products for the authenticated user.
     */
    public function index()
    {
        if (Auth::user()->license_status === 'inactive') {
            return redirect('/license-tiers/pricing');
        }

        // Fetch products for the logged-in user
        $products = Product::where('user_id', Auth::id())->get();

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created product in the database.
     */
    public function store(Request $request)
    {
        // Valider la requête
        $validatedData = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'price'                => 'required|numeric|min:0',
            'tax_rate'             => 'required|numeric|min:0|max:100',
            'duration'             => 'nullable|integer|min:1',
            'mode'                 => 'required|string|in:visio,adomicile,dans_le_cabinet',
            'max_per_day'          => 'nullable|integer|min:1',
            'can_be_booked_online' => 'required|boolean',
            'collect_payment'      => 'required|boolean', // Ajouté
            'image'                => 'nullable|image|max:8048',
            'brochure'             => 'nullable|mimes:pdf|max:10120',
            'display_order'        => 'nullable|integer|min:0',
            'requires_emargement'  => 'required|boolean',
            'visible_in_portal'    => 'required|boolean', // NEW
			'price_visible_in_portal' => 'required|boolean',
        ]);

        // Définir l'ordre d'affichage par défaut si non fourni
        if (!isset($validatedData['display_order'])) {
            $maxOrder = Product::where('user_id', Auth::id())->max('display_order');
            $validatedData['display_order'] = ($maxOrder ?? 0) + 1;
        }

        // Gérer les uploads de fichiers
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products/images', 'public');
            $validatedData['image'] = $imagePath;
        }

        if ($request->hasFile('brochure')) {
            $brochurePath = $request->file('brochure')->store('products/brochures', 'public');
            $validatedData['brochure'] = $brochurePath;
        }

        // Définir les modes de consultation
        $visio           = $validatedData['mode'] === 'visio';
        $adomicile       = $validatedData['mode'] === 'adomicile';
        $dans_le_cabinet = $validatedData['mode'] === 'dans_le_cabinet';

        // Créer le produit
        $product = Product::create([
            'user_id'              => Auth::id(),
            'name'                 => $validatedData['name'],
            'description'          => $validatedData['description'] ?? null,
            'price'                => $validatedData['price'],
            'tax_rate'             => $validatedData['tax_rate'],
            'duration'             => $validatedData['duration'] ?? null,
            'can_be_booked_online' => $validatedData['can_be_booked_online'],
            'collect_payment'      => $validatedData['collect_payment'], // Ajouté
            'visio'                => $visio,
            'adomicile'            => $adomicile,
            'dans_le_cabinet'      => $dans_le_cabinet,
            'max_per_day'          => $validatedData['max_per_day'] ?? null,
            'image'                => $validatedData['image'] ?? null,
            'brochure'             => $validatedData['brochure'] ?? null,
            'display_order'        => $validatedData['display_order'],
            'requires_emargement'  => $validatedData['requires_emargement'],
            'visible_in_portal'    => $validatedData['visible_in_portal'], // NEW
			'price_visible_in_portal' => $validatedData['price_visible_in_portal'],
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Prestation créée avec succès.');
    }

    /**
     * Display a specific product along with its associated invoices.
     */
    public function show(Product $product)
    {
        // Fetch the invoices associated with this product
        $invoices = Invoice::whereHas('items', function ($query) use ($product) {
            $query->where('product_id', $product->id);
        })->with('clientProfile')->get();

        return view('products.show', compact('product', 'invoices'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        // Ensure the user is authorized to edit this product
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product in the database.
     */
    public function update(Request $request, Product $product)
    {
        // Valider la requête
        $validatedData = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'price'                => 'required|numeric|min:0',
            'tax_rate'             => 'required|numeric|min:0|max:100',
            'duration'             => 'nullable|integer|min:1',
            'mode'                 => 'required|string|in:visio,adomicile,dans_le_cabinet',
            'max_per_day'          => 'nullable|integer|min:1',
            'can_be_booked_online' => 'required|boolean',
            'collect_payment'      => 'required|boolean', // Ajouté
            'image'                => 'nullable|image|max:5048',
            'brochure'             => 'nullable|mimes:pdf|max:10120',
            'display_order'        => 'nullable|integer|min:0',
            'requires_emargement'  => 'required|boolean',
            'visible_in_portal'    => 'required|boolean', // NEW
			 'price_visible_in_portal' => 'required|boolean',
        ]);

        // Gérer les uploads de fichiers
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products/images', 'public');
            $validatedData['image'] = $imagePath;
        }

        if ($request->hasFile('brochure')) {
            // Supprimer l'ancienne brochure si elle existe
            if ($product->brochure) {
                Storage::disk('public')->delete($product->brochure);
            }
            $brochurePath = $request->file('brochure')->store('products/brochures', 'public');
            $validatedData['brochure'] = $brochurePath;
        }

        // Définir les modes de consultation
        $visio           = $validatedData['mode'] === 'visio';
        $adomicile       = $validatedData['mode'] === 'adomicile';
        $dans_le_cabinet = $validatedData['mode'] === 'dans_le_cabinet';

        // Mettre à jour le produit
        $product->update([
            'name'                 => $validatedData['name'],
            'description'          => $validatedData['description'] ?? null,
            'price'                => $validatedData['price'],
            'tax_rate'             => $validatedData['tax_rate'],
            'duration'             => $validatedData['duration'] ?? null,
            'can_be_booked_online' => $validatedData['can_be_booked_online'],
            'collect_payment'      => $validatedData['collect_payment'], // Ajouté
            'visio'                => $visio,
            'adomicile'            => $adomicile,
            'dans_le_cabinet'      => $dans_le_cabinet,
            'max_per_day'          => $validatedData['max_per_day'] ?? null,
            'image'                => $validatedData['image'] ?? $product->image,
            'brochure'             => $validatedData['brochure'] ?? $product->brochure,
            'display_order'        => $validatedData['display_order'] ?? $product->display_order,
            'requires_emargement'  => $validatedData['requires_emargement'],
            'visible_in_portal'    => $validatedData['visible_in_portal'], // NEW
			'price_visible_in_portal' => $validatedData['price_visible_in_portal'],
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Prestation mise à jour avec succès.');
    }

    /**
     * Remove the specified product from the database.
     */
    public function destroy(Product $product)
    {
        // Check if the authenticated user owns the product
        if ($product->user_id !== auth()->id()) {
            return redirect()->route('products.index')->with('error', 'Vous n\'êtes pas autorisé à supprimer ce produit.');
        }

        // Delete the product
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produit supprimé avec succès.');
    }

    public function duplicate(Product $product)
    {
        // Vérifier si l'utilisateur est autorisé à dupliquer cette prestation
        if ($product->user_id !== Auth::id()) {
            return redirect()->route('products.index')->with('error', 'Vous n\'êtes pas autorisé à dupliquer cette prestation.');
        }

        // Retourner la vue de duplication avec les données de la prestation existante
        return view('products.duplicate', compact('product'));
    }

    public function storeDuplicate(Request $request, Product $product)
    {
        // Valider les données du formulaire
        $validatedData = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'price'                => 'required|numeric|min:0',
            'tax_rate'             => 'required|numeric|min:0|max:100',
            'duration'             => 'nullable|integer|min:1',
            'mode'                 => 'required|string|in:visio,adomicile,dans_le_cabinet',
            'max_per_day'          => 'nullable|integer|min:1',
            'can_be_booked_online' => 'nullable|boolean',
            'collect_payment'      => 'nullable|boolean', // NEW (robuste si le champ n’est pas dans le formulaire)
            'image'                => 'nullable|image|max:4048',
            'brochure'             => 'nullable|mimes:pdf|max:5120',
            'display_order'        => 'nullable|integer|min:0',
            'requires_emargement'  => 'nullable|boolean',
            'visible_in_portal'    => 'nullable|boolean', // NEW
			'price_visible_in_portal' => 'nullable|boolean',
        ]);

        // Gérer les uploads de fichiers
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products/images', 'public');
            $validatedData['image'] = $imagePath;
        } else {
            $validatedData['image'] = $product->image;
        }

        if ($request->hasFile('brochure')) {
            $brochurePath = $request->file('brochure')->store('products/brochures', 'public');
            $validatedData['brochure'] = $brochurePath;
        } else {
            $validatedData['brochure'] = $product->brochure;
        }

        // Déterminer les modes de prestation
        $visio           = $validatedData['mode'] === 'visio';
        $adomicile       = $validatedData['mode'] === 'adomicile';
        $dans_le_cabinet = $validatedData['mode'] === 'dans_le_cabinet';

        // Valeurs par défaut si certains champs ne sont pas envoyés par le formulaire de duplication
        $canBeBookedOnline = array_key_exists('can_be_booked_online', $validatedData)
            ? (bool)$validatedData['can_be_booked_online']
            : (bool)$product->can_be_booked_online;

        $collectPayment = array_key_exists('collect_payment', $validatedData)
            ? (bool)$validatedData['collect_payment']
            : (bool)$product->collect_payment;

        $requiresEmargement = array_key_exists('requires_emargement', $validatedData)
            ? (bool)$validatedData['requires_emargement']
            : (bool)$product->requires_emargement;

        $visibleInPortal = array_key_exists('visible_in_portal', $validatedData)
            ? (bool)$validatedData['visible_in_portal']
            : (bool)$product->visible_in_portal;
		$priceVisibleInPortal = array_key_exists('price_visible_in_portal', $validatedData)
			? (bool)$validatedData['price_visible_in_portal']
			: (bool)($product->price_visible_in_portal ?? true); // fallback
        // Créer la nouvelle prestation
        $newProduct = Product::create([
            'user_id'              => Auth::id(),
            'name'                 => $validatedData['name'],
            'description'          => $validatedData['description'] ?? null,
            'price'                => $validatedData['price'],
            'tax_rate'             => $validatedData['tax_rate'],
            'duration'             => $validatedData['duration'] ?? null,
            'can_be_booked_online' => $canBeBookedOnline,
            'collect_payment'      => $collectPayment,
            'visio'                => $visio,
            'adomicile'            => $adomicile,
            'dans_le_cabinet'      => $dans_le_cabinet,
            'max_per_day'          => $validatedData['max_per_day'] ?? null,
            'image'                => $validatedData['image'],
            'brochure'             => $validatedData['brochure'],
            'requires_emargement'  => $requiresEmargement,
            'visible_in_portal'    => $visibleInPortal, // NEW
			'price_visible_in_portal' => $priceVisibleInPortal,
            'display_order'        => $validatedData['display_order'] ?? (($product->display_order ?? 0) + 1),
        ]);

        return redirect()->route('products.show', $newProduct)->with('success', 'Prestation dupliquée avec succès.');
    }
	
}

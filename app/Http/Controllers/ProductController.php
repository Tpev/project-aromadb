<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        // middleware/policies si besoin
    }

    public function index()
    {
        if (Auth::user()->license_status === 'inactive') {
            return redirect('/license-tiers/pricing');
        }

        $products = Product::where('user_id', Auth::id())->get();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'price'                => 'required|numeric|min:0',
            'tax_rate'             => 'required|numeric|min:0|max:100',
            'duration'             => 'nullable|integer|min:1',
            'mode'                 => 'required|string|in:visio,adomicile,en_entreprise,dans_le_cabinet',
            'max_per_day'          => 'nullable|integer|min:1',
            'can_be_booked_online' => 'required|boolean',
            'collect_payment'      => 'required|boolean',
            'image'                => 'nullable|image|max:8048',
            'brochure'             => 'nullable|mimes:pdf|max:10120',
            'display_order'        => 'nullable|integer|min:0',
            'requires_emargement'  => 'required|boolean',
            'visible_in_portal'    => 'required|boolean',
            'price_visible_in_portal' => 'required|boolean',
        ]);

        if (!isset($validatedData['display_order'])) {
            $maxOrder = Product::where('user_id', Auth::id())->max('display_order');
            $validatedData['display_order'] = ($maxOrder ?? 0) + 1;
        }

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('products/images', 'public');
        }

        if ($request->hasFile('brochure')) {
            $validatedData['brochure'] = $request->file('brochure')->store('products/brochures', 'public');
        }

        $visio           = $validatedData['mode'] === 'visio';
        $adomicile       = $validatedData['mode'] === 'adomicile';
        $enEntreprise    = $validatedData['mode'] === 'en_entreprise';
        $dansLeCabinet   = $validatedData['mode'] === 'dans_le_cabinet';

        $product = Product::create([
            'user_id'              => Auth::id(),
            'name'                 => $validatedData['name'],
            'description'          => $validatedData['description'] ?? null,
            'price'                => $validatedData['price'],
            'tax_rate'             => $validatedData['tax_rate'],
            'duration'             => $validatedData['duration'] ?? null,
            'can_be_booked_online' => $validatedData['can_be_booked_online'],
            'collect_payment'      => $validatedData['collect_payment'],

            'visio'                => $visio,
            'adomicile'            => $adomicile,
            'en_entreprise'        => $enEntreprise,
            'dans_le_cabinet'      => $dansLeCabinet,

            'max_per_day'          => $validatedData['max_per_day'] ?? null,
            'image'                => $validatedData['image'] ?? null,
            'brochure'             => $validatedData['brochure'] ?? null,
            'display_order'        => $validatedData['display_order'],
            'requires_emargement'  => $validatedData['requires_emargement'],
            'visible_in_portal'    => $validatedData['visible_in_portal'],
            'price_visible_in_portal' => $validatedData['price_visible_in_portal'],
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Prestation créée avec succès.');
    }

    public function show(Product $product)
    {
        $invoices = Invoice::whereHas('items', function ($query) use ($product) {
            $query->where('product_id', $product->id);
        })->with('clientProfile')->get();

        return view('products.show', compact('product', 'invoices'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'price'                => 'required|numeric|min:0',
            'tax_rate'             => 'required|numeric|min:0|max:100',
            'duration'             => 'nullable|integer|min:1',
            'mode'                 => 'required|string|in:visio,adomicile,en_entreprise,dans_le_cabinet',
            'max_per_day'          => 'nullable|integer|min:1',
            'can_be_booked_online' => 'required|boolean',
            'collect_payment'      => 'required|boolean',
            'image'                => 'nullable|image|max:5048',
            'brochure'             => 'nullable|mimes:pdf|max:10120',
            'display_order'        => 'nullable|integer|min:0',
            'requires_emargement'  => 'required|boolean',
            'visible_in_portal'    => 'required|boolean',
            'price_visible_in_portal' => 'required|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validatedData['image'] = $request->file('image')->store('products/images', 'public');
        }

        if ($request->hasFile('brochure')) {
            if ($product->brochure) {
                Storage::disk('public')->delete($product->brochure);
            }
            $validatedData['brochure'] = $request->file('brochure')->store('products/brochures', 'public');
        }

        $visio           = $validatedData['mode'] === 'visio';
        $adomicile       = $validatedData['mode'] === 'adomicile';
        $enEntreprise    = $validatedData['mode'] === 'en_entreprise';
        $dansLeCabinet   = $validatedData['mode'] === 'dans_le_cabinet';

        $product->update([
            'name'                 => $validatedData['name'],
            'description'          => $validatedData['description'] ?? null,
            'price'                => $validatedData['price'],
            'tax_rate'             => $validatedData['tax_rate'],
            'duration'             => $validatedData['duration'] ?? null,
            'can_be_booked_online' => $validatedData['can_be_booked_online'],
            'collect_payment'      => $validatedData['collect_payment'],

            'visio'                => $visio,
            'adomicile'            => $adomicile,
            'en_entreprise'        => $enEntreprise,
            'dans_le_cabinet'      => $dansLeCabinet,

            'max_per_day'          => $validatedData['max_per_day'] ?? null,
            'image'                => $validatedData['image'] ?? $product->image,
            'brochure'             => $validatedData['brochure'] ?? $product->brochure,
            'display_order'        => $validatedData['display_order'] ?? $product->display_order,
            'requires_emargement'  => $validatedData['requires_emargement'],
            'visible_in_portal'    => $validatedData['visible_in_portal'],
            'price_visible_in_portal' => $validatedData['price_visible_in_portal'],
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Prestation mise à jour avec succès.');
    }

    public function destroy(Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            return redirect()->route('products.index')->with('error', 'Vous n\'êtes pas autorisé à supprimer ce produit.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produit supprimé avec succès.');
    }

    public function duplicate(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return redirect()->route('products.index')->with('error', 'Vous n\'êtes pas autorisé à dupliquer cette prestation.');
        }

        return view('products.duplicate', compact('product'));
    }

    public function storeDuplicate(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'price'                => 'required|numeric|min:0',
            'tax_rate'             => 'required|numeric|min:0|max:100',
            'duration'             => 'nullable|integer|min:1',
            'mode'                 => 'required|string|in:visio,adomicile,en_entreprise,dans_le_cabinet',
            'max_per_day'          => 'nullable|integer|min:1',
            'can_be_booked_online' => 'nullable|boolean',
            'collect_payment'      => 'nullable|boolean',
            'image'                => 'nullable|image|max:4048',
            'brochure'             => 'nullable|mimes:pdf|max:5120',
            'display_order'        => 'nullable|integer|min:0',
            'requires_emargement'  => 'nullable|boolean',
            'visible_in_portal'    => 'nullable|boolean',
            'price_visible_in_portal' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('products/images', 'public');
        } else {
            $validatedData['image'] = $product->image;
        }

        if ($request->hasFile('brochure')) {
            $validatedData['brochure'] = $request->file('brochure')->store('products/brochures', 'public');
        } else {
            $validatedData['brochure'] = $product->brochure;
        }

        $visio           = $validatedData['mode'] === 'visio';
        $adomicile       = $validatedData['mode'] === 'adomicile';
        $enEntreprise    = $validatedData['mode'] === 'en_entreprise';
        $dansLeCabinet   = $validatedData['mode'] === 'dans_le_cabinet';

        $canBeBookedOnline = array_key_exists('can_be_booked_online', $validatedData)
            ? (bool) $validatedData['can_be_booked_online']
            : (bool) $product->can_be_booked_online;

        $collectPayment = array_key_exists('collect_payment', $validatedData)
            ? (bool) $validatedData['collect_payment']
            : (bool) $product->collect_payment;

        $requiresEmargement = array_key_exists('requires_emargement', $validatedData)
            ? (bool) $validatedData['requires_emargement']
            : (bool) $product->requires_emargement;

        $visibleInPortal = array_key_exists('visible_in_portal', $validatedData)
            ? (bool) $validatedData['visible_in_portal']
            : (bool) $product->visible_in_portal;

        $priceVisibleInPortal = array_key_exists('price_visible_in_portal', $validatedData)
            ? (bool) $validatedData['price_visible_in_portal']
            : (bool) ($product->price_visible_in_portal ?? true);

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
            'en_entreprise'        => $enEntreprise,
            'dans_le_cabinet'      => $dansLeCabinet,

            'max_per_day'          => $validatedData['max_per_day'] ?? null,
            'image'                => $validatedData['image'],
            'brochure'             => $validatedData['brochure'],
            'requires_emargement'  => $requiresEmargement,
            'visible_in_portal'    => $visibleInPortal,
            'price_visible_in_portal' => $priceVisibleInPortal,
            'display_order'        => $validatedData['display_order'] ?? (($product->display_order ?? 0) + 1),
        ]);

        return redirect()->route('products.show', $newProduct)->with('success', 'Prestation dupliquée avec succès.');
    }
}

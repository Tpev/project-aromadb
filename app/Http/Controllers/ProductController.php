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
    // Validate the request
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'tax_rate' => 'required|numeric|min:0|max:100',
        'duration' => 'nullable|integer|min:1',
        'mode' => 'required|string|in:visio,adomicile,dans_le_cabinet',
        'max_per_day' => 'nullable|integer|min:1',
        'can_be_booked_online' => 'required|boolean',
        'image' => 'nullable|image|max:4048',        // Validate image
        'brochure' => 'nullable|mimes:pdf|max:5120', // Validate brochure (PDF)
    ]);

    // Handle file uploads
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('products/images', 'public');
        $validatedData['image'] = $imagePath;
    }

    if ($request->hasFile('brochure')) {
        $brochurePath = $request->file('brochure')->store('products/brochures', 'public');
        $validatedData['brochure'] = $brochurePath;
    }

    // Set consultation modes
    $visio = $validatedData['mode'] === 'visio';
    $adomicile = $validatedData['mode'] === 'adomicile';
    $dans_le_cabinet = $validatedData['mode'] === 'dans_le_cabinet';

    // Create the product
    $product = Product::create([
        'user_id' => Auth::id(),
        'name' => $validatedData['name'],
        'description' => $validatedData['description'],
        'price' => $validatedData['price'],
        'tax_rate' => $validatedData['tax_rate'],
        'duration' => $validatedData['duration'],
        'can_be_booked_online' => $validatedData['can_be_booked_online'],
        'visio' => $visio,
        'adomicile' => $adomicile,
        'dans_le_cabinet' => $dans_le_cabinet,
        'max_per_day' => $validatedData['max_per_day'],
        'image' => $validatedData['image'] ?? null,
        'brochure' => $validatedData['brochure'] ?? null,
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
    // Validate the request
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'tax_rate' => 'required|numeric|min:0|max:100',
        'duration' => 'nullable|integer|min:1',
        'mode' => 'required|string|in:visio,adomicile,dans_le_cabinet',
        'max_per_day' => 'nullable|integer|min:1',
        'can_be_booked_online' => 'required|boolean',
        'image' => 'nullable|image|max:2048',
        'brochure' => 'nullable|mimes:pdf|max:5120',
    ]);

    // Handle file uploads
    if ($request->hasFile('image')) {
        // Delete old image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $imagePath = $request->file('image')->store('products/images', 'public');
        $validatedData['image'] = $imagePath;
    }

    if ($request->hasFile('brochure')) {
        // Delete old brochure if exists
        if ($product->brochure) {
            Storage::disk('public')->delete($product->brochure);
        }
        $brochurePath = $request->file('brochure')->store('products/brochures', 'public');
        $validatedData['brochure'] = $brochurePath;
    }

    // Set consultation modes
    $visio = $validatedData['mode'] === 'visio';
    $adomicile = $validatedData['mode'] === 'adomicile';
    $dans_le_cabinet = $validatedData['mode'] === 'dans_le_cabinet';

    // Update the product
    $product->update([
        'name' => $validatedData['name'],
        'description' => $validatedData['description'],
        'price' => $validatedData['price'],
        'tax_rate' => $validatedData['tax_rate'],
        'duration' => $validatedData['duration'],
        'can_be_booked_online' => $validatedData['can_be_booked_online'],
        'visio' => $visio,
        'adomicile' => $adomicile,
        'dans_le_cabinet' => $dans_le_cabinet,
        'max_per_day' => $validatedData['max_per_day'],
        'image' => $validatedData['image'] ?? $product->image,
        'brochure' => $validatedData['brochure'] ?? $product->brochure,
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

}

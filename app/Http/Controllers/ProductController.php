<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'can_be_booked_online' => 'required|boolean',  // Handle the new can_be_booked_online field
        ]);

        // Set visio, adomicile, and dans_le_cabinet based on the selected mode
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
            'can_be_booked_online' => $validatedData['can_be_booked_online'],  // Store the can_be_booked_online value
            'visio' => $visio,
            'adomicile' => $adomicile,
            'dans_le_cabinet' => $dans_le_cabinet,
            'max_per_day' => $validatedData['max_per_day'],
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
            'mode' => 'required|string|in:visio,adomicile,dans_le_cabinet',  // Updated to validate the mode
            'max_per_day' => 'nullable|integer|min:1',
            'can_be_booked_online' => 'required|boolean',  // Handle the can_be_booked_online field
        ]);

        // Set visio, adomicile, and dans_le_cabinet based on the selected mode
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
            'can_be_booked_online' => $validatedData['can_be_booked_online'],  // Update the can_be_booked_online value
            'visio' => $visio,
            'adomicile' => $adomicile,
            'dans_le_cabinet' => $dans_le_cabinet,
            'max_per_day' => $validatedData['max_per_day'],
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Prestation mise à jour avec succès.');
    }

    /**
     * Remove the specified product from the database.
     */
    public function destroy(Product $product)
    {
        // Ensure the user is authorized to delete this product
        $this->authorize('delete', $product);

        // Delete the product
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produit supprimé avec succès.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // Applique l'authentification et les politiques
    public function __construct()
    {

    }

    /**
     * Affiche la liste des produits de l'utilisateur authentifié.
     */
    public function index()
    {
        $products = Product::where('user_id', Auth::id())->get();

        return view('products.index', compact('products'));
    }

    /**
     * Affiche le formulaire pour créer un nouveau produit.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Enregistre un nouveau produit en base de données.
     */
    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100', // Ajout du champ tax_rate
        ]);

        // Crée le produit
        $product = Product::create([
            'user_id' => Auth::id(),
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'price' => $validatedData['price'],
            'tax_rate' => $validatedData['tax_rate'], // Enregistrement du tax_rate
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Produit créé avec succès.');
    }

    /**
     * Affiche un produit spécifique.
     */
    public function show(Product $product)
    {
        // Vérifie que l'utilisateur est autorisé à voir ce produit
       

        // Récupère les factures associées au produit
        $invoices = Invoice::whereHas('items', function($query) use ($product) {
            $query->where('product_id', $product->id);
        })->with('clientProfile')->get();

        return view('products.show', compact('product', 'invoices'));
    }

    /**
     * Affiche le formulaire pour éditer un produit existant.
     */
    public function edit(Product $product)
    {
        // Vérifie que l'utilisateur est autorisé à éditer ce produit
        

        return view('products.edit', compact('product'));
    }

    /**
     * Met à jour un produit existant en base de données.
     */
    public function update(Request $request, Product $product)
    {
        // Vérifie que l'utilisateur est autorisé à mettre à jour ce produit
       

        // Validation des données
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100', // Ajout du champ tax_rate
        ]);

        // Met à jour le produit
        $product->update($validatedData);

        return redirect()->route('products.show', $product)->with('success', 'Produit mis à jour avec succès.');
    }

    /**
     * Supprime un produit de la base de données.
     */
    public function destroy(Product $product)
    {
        // Vérifie que l'utilisateur est autorisé à supprimer ce produit
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produit supprimé avec succès.');
    }
}

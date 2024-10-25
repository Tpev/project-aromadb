<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryItemController extends Controller
{
    // Ensure only authenticated users can access these methods
    public function __construct()
    {
      
    }

    // Display a listing of the inventory items
    public function index()
    {
        // Retrieve inventory items for the authenticated user
        $inventoryItems = InventoryItem::where('user_id', Auth::id())->get();

        return view('inventory_items.index', compact('inventoryItems'));
    }

    // Show the form for creating a new inventory item
    public function create()
    {
        return view('inventory_items.create');
    }

    // Store a newly created inventory item in storage
public function store(Request $request)
{
    // Valider les données
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'reference' => 'required|string|max:255|unique:inventory_items,reference',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'quantity_in_stock' => 'required|integer|min:0',
        'brand' => 'nullable|string|max:255',
    ]);

    // Créer l'article avec l'assignation de masse sécurisée
    $inventoryItem = InventoryItem::create(array_merge(
        $validatedData,
        ['user_id' => Auth::id()]
    ));

    return redirect()->route('inventory_items.index')->with('success', 'Article ajouté avec succès.');
}


    // Display the specified inventory item
    public function show(InventoryItem $inventoryItem)
    {
        $this->authorize('view', $inventoryItem);

        return view('inventory_items.show', compact('inventoryItem'));
    }

    // Show the form for editing the specified inventory item
    public function edit(InventoryItem $inventoryItem)
    {
        $this->authorize('update', $inventoryItem);

        return view('inventory_items.edit', compact('inventoryItem'));
    }

    // Update the specified inventory item in storage
    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $this->authorize('update', $inventoryItem);

        // Validate the request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'reference' => 'required|string|max:255|unique:inventory_items,reference,' . $inventoryItem->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity_in_stock' => 'required|integer|min:0',
            'brand' => 'nullable|string|max:255',
        ]);

        // Update the inventory item
        $inventoryItem->update($validatedData);

        return redirect()->route('inventory_items.index')->with('success', 'Item updated successfully.');
    }

    // Remove the specified inventory item from storage
    public function destroy(InventoryItem $inventoryItem)
    {
        $this->authorize('delete', $inventoryItem);

        $inventoryItem->delete();

        return redirect()->route('inventory_items.index')->with('success', 'Item deleted successfully.');
    }
}

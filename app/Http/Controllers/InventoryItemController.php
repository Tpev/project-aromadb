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
    $rules = [
        'name' => 'required|string|max:255',
        'reference' => 'required|string|max:255|unique:inventory_items,reference',
        'description' => 'nullable|string',
        'price' => 'nullable|numeric|min:0',
        'selling_price' => 'nullable|numeric|min:0',
        'brand' => 'nullable|string|max:255',
        'unit_type' => 'required|in:unit,ml,drop,gramme',
        'quantity_per_unit' => 'nullable|numeric|min:0',
        'quantity_remaining' => 'nullable|numeric|min:0',
    ];

    // Required only if it's a unit-based item
    if ($request->unit_type === 'unit') {
        $rules['quantity_in_stock'] = 'required|integer|min:0';
    }

    $validatedData = $request->validate($rules);

    // Default quantity_in_stock = 1 if not unit
    if ($request->unit_type !== 'unit') {
        $validatedData['quantity_in_stock'] = 1;
    }

    $validatedData['user_id'] = Auth::id();

    InventoryItem::create($validatedData);

    return redirect()->route('inventory_items.index')->with('success', 'Article ajouté avec succès.');
}



    // Display the specified inventory item
    public function show(InventoryItem $inventoryItem)
    {
		if ($inventoryItem->user_id !== auth()->id()) {
    abort(403, 'Unauthorized action.');
}

        $this->authorize('view', $inventoryItem);

        return view('inventory_items.show', compact('inventoryItem'));
    }

    // Show the form for editing the specified inventory item
    public function edit(InventoryItem $inventoryItem)
    {
 		if ($inventoryItem->user_id !== auth()->id()) {
    abort(403, 'Unauthorized action.');
}

        return view('inventory_items.edit', compact('inventoryItem'));
    }

 public function update(Request $request, InventoryItem $inventoryItem)
{
    if ($inventoryItem->user_id !== auth()->id()) {
        abort(403, 'Unauthorized action.');
    }

    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'reference' => 'required|string|max:255|unique:inventory_items,reference,' . $inventoryItem->id,
        'description' => 'nullable|string',
        'price' => 'nullable|numeric|min:0',
        'selling_price' => 'nullable|numeric|min:0',
        'quantity_in_stock' => 'required|numeric|min:0',
        'brand' => 'nullable|string|max:255',
        'unit_type' => 'required|string|in:unit,ml,drop,gramme',
        'quantity_per_unit' => 'nullable|numeric|min:0',
        'quantity_remaining' => 'nullable|numeric|min:0',
    ]);

    $inventoryItem->update($validatedData);

    return redirect()->route('inventory_items.index')->with('success', 'Item updated successfully.');
}



	
public function consume(Request $request, InventoryItem $inventoryItem)
{
    if ($inventoryItem->user_id !== auth()->id()) {
        abort(403, 'Unauthorized action.');
    }

    $request->validate([
        'amount_ml' => 'nullable|numeric|min:0',
        'amount_drops' => 'nullable|numeric|min:0',
        'amount_gramme' => 'nullable|numeric|min:0',
    ]);

    if (!in_array($inventoryItem->unit_type, ['ml', 'drop', 'gramme'])) {
        return back()->withErrors(['unit_type' => 'Ce produit ne peut pas être consommé en ml, gouttes ou grammes.']);
    }

    $ml = (float) $request->input('amount_ml', 0);
    $drops = (int) $request->input('amount_drops', 0);
    $grammes = (float) $request->input('amount_gramme', 0);

    $totalToConsume = 0;

    if ($inventoryItem->unit_type === 'ml' || $inventoryItem->unit_type === 'drop') {
        $convertedFromDrops = $drops > 0 ? ($drops / ($inventoryItem->drop_to_ml_ratio ?? 20)) : 0;
        $totalToConsume = $ml + $convertedFromDrops;
    } elseif ($inventoryItem->unit_type === 'gramme') {
        $totalToConsume = $grammes;
    }

    if ($totalToConsume <= 0) {
        return back()->withErrors(['amount' => 'Veuillez saisir une quantité à consommer.']);
    }

    if ($inventoryItem->quantity_remaining < $totalToConsume) {
        return back()->withErrors(['amount' => 'Stock insuffisant pour consommer cette quantité.']);
    }

    $inventoryItem->quantity_remaining -= $totalToConsume;
    $inventoryItem->save();

    $unitLabel = $inventoryItem->unit_type === 'gramme' ? 'g' : 'ml';

    return back()->with('success', 'Consommation enregistrée : ' . number_format($totalToConsume, 2) . ' ' . $unitLabel);
}

public function consumeUnit(InventoryItem $inventoryItem)
{
    if ($inventoryItem->user_id !== auth()->id()) {
        abort(403);
    }

    if ($inventoryItem->unit_type !== 'unit') {
        return back()->with('error', 'Cet article n’est pas de type "unité".');
    }

    if ($inventoryItem->quantity_in_stock < 1) {
        return back()->with('error', 'Stock insuffisant pour consommer une unité.');
    }

    $inventoryItem->quantity_in_stock -= 1;
    $inventoryItem->save();

    return redirect()->route('inventory_items.index')->with('success', '1 unité consommée.');
}

	
}

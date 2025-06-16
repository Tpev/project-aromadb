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
    'price' => 'required|numeric|min:0',
    'selling_price' => 'required|numeric|min:0',
    'brand' => 'nullable|string|max:255',
    'unit_type' => 'required|in:unit,ml,drop',
    'quantity_per_unit' => 'nullable|numeric|min:0',
    'quantity_remaining' => 'nullable|numeric|min:0',
];

// Only require quantity_in_stock if unit_type == 'unit'
if ($request->unit_type === 'unit') {
    $rules['quantity_in_stock'] = 'required|integer|min:0';
}

$validatedData = $request->validate($rules);

// If it's ml or drop, default quantity_in_stock to 1
if ($request->unit_type !== 'unit') {
    $validatedData['quantity_in_stock'] = 1;
}

// Add user_id
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

    // Update the specified inventory item in storage
    public function update(Request $request, InventoryItem $inventoryItem)
    {
		if ($inventoryItem->user_id !== auth()->id()) {
    abort(403, 'Unauthorized action.');
}

  

$validatedData = $request->validate([
    'name' => 'required|string|max:255',
    'reference' => 'required|string|max:255|unique:inventory_items,reference,' . ($inventoryItem->id ?? 'NULL'),
    'description' => 'nullable|string',
    'price' => 'required|numeric|min:0',
    'selling_price' => 'required|numeric|min:0',
    'quantity_in_stock' => 'required|numeric|min:0',
    'brand' => 'nullable|string|max:255',
    'unit_type' => 'required|string|in:unit,ml,drop',
    'quantity_per_unit' => 'nullable|numeric|min:0',
    'quantity_remaining' => 'nullable|numeric|min:0',
]);


        // Update the inventory item
        $inventoryItem->update($validatedData);

        return redirect()->route('inventory_items.index')->with('success', 'Item updated successfully.');
    }

    // Remove the specified inventory item from storage
    public function destroy(InventoryItem $inventoryItem)
    {
if ($inventoryItem->user_id !== auth()->id()) {
    abort(403, 'Unauthorized action.');
}


        $inventoryItem->delete();

        return redirect()->route('inventory_items.index')->with('success', 'Item deleted successfully.');
    }
	
public function consume(Request $request, InventoryItem $inventoryItem)
{
 if ($inventoryItem->user_id !== auth()->id()) {
    abort(403, 'Unauthorized action.');
}


    $request->validate([
        'amount_ml' => 'nullable|numeric|min:0',
        'amount_drops' => 'nullable|numeric|min:0',
    ]);

    if (!in_array($inventoryItem->unit_type, ['ml', 'drop'])) {
        return back()->withErrors(['unit_type' => 'Ce produit n\'est pas mesuré en ml ou gouttes.']);
    }

    $ml = $request->input('amount_ml', 0);
    $drops = $request->input('amount_drops', 0);

    // Convert drops to ml if needed
    $convertedFromDrops = $drops > 0 ? ($drops / ($inventoryItem->drop_to_ml_ratio ?? 20)) : 0;

    $totalMlToConsume = $ml + $convertedFromDrops;

    if ($totalMlToConsume <= 0) {
        return back()->withErrors(['amount_ml' => 'Veuillez saisir une quantité à consommer.']);
    }

    if ($inventoryItem->quantity_remaining < $totalMlToConsume) {
        return back()->withErrors(['amount_ml' => 'Stock insuffisant pour consommer cette quantité.']);
    }

    $inventoryItem->quantity_remaining -= $totalMlToConsume;
    $inventoryItem->save();

    return back()->with('success', 'Consommation enregistrée : ' . number_format($totalMlToConsume, 2) . ' ml');
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

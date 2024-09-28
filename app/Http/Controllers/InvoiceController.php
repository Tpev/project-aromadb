<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ClientProfile;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;


class InvoiceController extends Controller
{
    // Assure que l'utilisateur est authentifié et que les politiques sont appliquées
    public function __construct()
    {
    
     
    }

    /**
     * Affiche la liste des factures.
     */
    public function index()
    {

        // Retrieve all invoices for the authenticated user
        $invoices = Invoice::where('user_id', Auth::id())->with('clientProfile')->get();

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Affiche le formulaire pour créer une nouvelle facture.
     */
public function create(Request $request)
{
    // Get clients and products for the authenticated user
    $clients = ClientProfile::where('user_id', auth()->id())->get();
    $products = Product::where('user_id', auth()->id())->get();

    // Preload data if provided via query parameters
    $selectedClient = $request->input('client_id') ? ClientProfile::find($request->input('client_id')) : null;
    $selectedProduct = $request->input('product_id') ? Product::find($request->input('product_id')) : null;

    return view('invoices.create', compact('clients', 'products', 'selectedClient', 'selectedProduct'));
}



    /**
     * Stocke une nouvelle facture en base de données.
     */
public function store(Request $request)
{
    // Validation des données
    $validatedData = $request->validate([
        'client_profile_id' => 'required|exists:client_profiles,id',
        'invoice_date' => 'required|date',
        'due_date' => 'nullable|date|after_or_equal:invoice_date',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'notes' => 'nullable|string',
    ]);

    // Créer la facture
    $invoice = Invoice::create([
        'client_profile_id' => $request->client_profile_id,
        'user_id' => Auth::id(),
        'invoice_date' => $request->invoice_date,
        'due_date' => $request->due_date,
        'total_amount' => 0, // Sera calculé
        'total_tax_amount' => 0, // Sera calculé
        'total_amount_with_tax' => 0, // Sera calculé
        'status' => 'En attente',
        'notes' => $request->notes,
    ]);

    $totalAmount = 0;
    $totalTaxAmount = 0;

    foreach ($request->items as $itemData) {
        // Vérifie que le produit appartient à l'utilisateur
        $product = Product::where('id', $itemData['product_id'])
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        $quantity = $itemData['quantity'];
        $unitPrice = $product->price;
        $taxRate = $product->tax_rate;
        $totalPrice = $unitPrice * $quantity;

        $taxAmount = ($totalPrice * $taxRate) / 100;
        $totalPriceWithTax = $totalPrice + $taxAmount;

        $invoice->items()->create([
            'product_id' => $product->id,
            'description' => $product->name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_price' => $totalPrice,
            'total_price_with_tax' => $totalPriceWithTax,
        ]);

        $totalAmount += $totalPrice;
        $totalTaxAmount += $taxAmount;
    }

    $totalAmountWithTax = $totalAmount + $totalTaxAmount;

    // Mettre à jour le montant total
    $invoice->update([
        'total_amount' => $totalAmount,
        'total_tax_amount' => $totalTaxAmount,
        'total_amount_with_tax' => $totalAmountWithTax,
    ]);

    return redirect()->route('invoices.show', $invoice)
                     ->with('success', 'Facture créée avec succès.');
}


    /**
     * Affiche une facture spécifique.
     */
    public function show(Invoice $invoice)
    {
        // La politique assure que l'utilisateur peut voir la facture
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Affiche le formulaire pour éditer une facture.
     */
    public function edit(Invoice $invoice)
    {
        // La politique assure que l'utilisateur peut éditer la facture

        // Récupère les clients et les produits
        $clients = ClientProfile::where('user_id', Auth::id())->get();
        $products = Product::where('user_id', Auth::id())->get();

        return view('invoices.edit', compact('invoice', 'clients', 'products'));
    }

    /**
     * Met à jour une facture existante.
     */
public function update(Request $request, Invoice $invoice)
{
    // Validation des données
    $validatedData = $request->validate([
        'client_profile_id' => 'required|exists:client_profiles,id',
        'invoice_date' => 'required|date',
        'due_date' => 'nullable|date|after_or_equal:invoice_date',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'notes' => 'nullable|string',
    ]);

    // Mettre à jour la facture
    $invoice->update([
        'client_profile_id' => $request->client_profile_id,
        'invoice_date' => $request->invoice_date,
        'due_date' => $request->due_date,
        'notes' => $request->notes,
    ]);

    // Supprimer les éléments existants
    $invoice->items()->delete();

    $totalAmount = 0;
    $totalTaxAmount = 0;

    foreach ($request->items as $itemData) {
        // Vérifie que le produit appartient à l'utilisateur
        $product = Product::where('id', $itemData['product_id'])
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        $quantity = $itemData['quantity'];
        $unitPrice = $product->price;
        $taxRate = $product->tax_rate;
        $totalPrice = $unitPrice * $quantity;

        $taxAmount = ($totalPrice * $taxRate) / 100;
        $totalPriceWithTax = $totalPrice + $taxAmount;

        $invoice->items()->create([
            'product_id' => $product->id,
            'description' => $product->name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_price' => $totalPrice,
            'total_price_with_tax' => $totalPriceWithTax,
        ]);

        $totalAmount += $totalPrice;
        $totalTaxAmount += $taxAmount;
    }

    $totalAmountWithTax = $totalAmount + $totalTaxAmount;

    // Mettre à jour le montant total
    $invoice->update([
        'total_amount' => $totalAmount,
        'total_tax_amount' => $totalTaxAmount,
        'total_amount_with_tax' => $totalAmountWithTax,
    ]);

    return redirect()->route('invoices.show', $invoice)
                     ->with('success', 'Facture mise à jour avec succès.');
}

    /**
     * Supprime une facture.
     */
    public function destroy(Invoice $invoice)
    {
        // La politique assure que l'utilisateur peut supprimer la facture
        $invoice->delete();

        return redirect()->route('invoices.index')
                         ->with('success', 'Facture supprimée avec succès.');
    }
	
	    public function allInvoices()
    {
       

        // Récupère toutes les factures de l'utilisateur authentifié
        $invoices = Invoice::where('user_id', Auth::id())->with('clientProfile')->get();

        return view('invoices.index', compact('invoices'));
    }
	
	public function clientInvoices(ClientProfile $clientProfile)
{
    // Retrieve invoices for the specific client
    $invoices = $clientProfile->invoices()->with('clientProfile')->get();

    return view('invoices.index', compact('invoices', 'clientProfile'));
}
public function generatePDF(Invoice $invoice)
{
    // Charger les relations nécessaires
    $invoice->load('clientProfile', 'items.product');

    // Partager les données avec la vue
    $data = [
        'invoice' => $invoice,
    ];

    // Générer le PDF
    $pdf = PDF::loadView('invoices.pdf', $data);

    // Télécharger le PDF avec un nom de fichier personnalisé
    return $pdf->download('facture_' . $invoice->id . '.pdf');
}
public function markAsPaid(Invoice $invoice)
{
    $invoice->update(['status' => 'Payée']);
    
    return redirect()->route('invoices.show', $invoice)->with('success', 'La facture a été marquée comme payée.');
}

}

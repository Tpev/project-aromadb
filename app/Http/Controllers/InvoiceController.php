<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ClientProfile;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\DB; // Import DB facade
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;



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
        // Validate the incoming request data
        $validatedData = $request->validate([
            'client_profile_id' => 'required|exists:client_profiles,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        // Use a transaction to ensure data integrity
        $invoice = DB::transaction(function () use ($request) {
            // Lock the invoices table for the current user to prevent race conditions
            $lastInvoice = Invoice::where('user_id', Auth::id())
                                  ->lockForUpdate()
                                  ->orderBy('invoice_number', 'desc')
                                  ->first();

            // Determine the next invoice number
            $nextInvoiceNumber = $lastInvoice ? $lastInvoice->invoice_number + 1 : 1;

            // Create the invoice with the determined invoice_number
            return Invoice::create([
                'client_profile_id' => $request->client_profile_id,
                'user_id' => Auth::id(),
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'total_amount' => 0, // Will be calculated later
                'total_tax_amount' => 0, // Will be calculated later
                'total_amount_with_tax' => 0, // Will be calculated later
                'status' => 'En attente',
                'notes' => $request->notes,
                'invoice_number' => $nextInvoiceNumber,
            ]);
        });

        // Initialize totals
        $totalAmount = 0;
        $totalTaxAmount = 0;

        // Iterate over each item in the invoice
        foreach ($request->items as $itemData) {
            // Ensure the product belongs to the authenticated user
            $product = Product::where('id', $itemData['product_id'])
                              ->where('user_id', Auth::id())
                              ->firstOrFail();

            $quantity = $itemData['quantity'];
            $unitPrice = $product->price;
            $taxRate = $product->tax_rate;
            $totalPrice = $unitPrice * $quantity;

            $taxAmount = ($totalPrice * $taxRate) / 100;
            $totalPriceWithTax = $totalPrice + $taxAmount;

            // Create the invoice item
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

            // Accumulate totals
            $totalAmount += $totalPrice;
            $totalTaxAmount += $taxAmount;
        }

        // Calculate the total amount with tax
        $totalAmountWithTax = $totalAmount + $totalTaxAmount;

        // Update the invoice with the calculated totals
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
    return $pdf->download('facture_' . $invoice->invoice_number . '.pdf');
}
public function markAsPaid(Invoice $invoice)
{
    $invoice->update(['status' => 'Payée']);
    
    return redirect()->route('invoices.show', $invoice)->with('success', 'La facture a été marquée comme payée.');
}

public function sendEmail(Invoice $invoice)
{
    // Ensure the user is authorized to send this invoice


    // Retrieve the client profile
    $client = $invoice->clientProfile;

    // Ensure the client has an email
    if (!$client->email) {
        return redirect()->back()->with('error', 'Le client n\'a pas d\'adresse email.');
    }

    // Get the therapist's name (assuming User has 'name' attribute)
    $therapistName = Auth::user()->name;

    // Send the email
    try {
        Mail::to($client->email)->queue(new InvoiceMail($invoice, $therapistName));

        // Update sent_at
        $invoice->update(['sent_at' => now()]);

        return redirect()->route('invoices.show', $invoice->id)
                         ->with('success', 'Facture envoyée par email avec succès.');
    } catch (\Exception $e) {
        // Log the error or handle it as needed
        \Log::error('Error sending invoice email: ' . $e->getMessage());

        return redirect()->route('invoices.show', $invoice->id)
                         ->with('error', 'Une erreur est survenue lors de l\'envoi de l\'email.');
    }
}



}

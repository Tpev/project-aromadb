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
use Stripe\Stripe;
use Stripe\PaymentLink;
use Illuminate\Support\Facades\Log;
use App\Mail\InvoicePaymentLinkMail;
use Stripe\StripeClient;

class InvoiceController extends Controller
{
	    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    // Ensure the user is authenticated and policies are applied
    public function __construct()
    {

    }

    /**
     * Affiche la liste des factures.
     */
    public function index()
    {
		    if (Auth::user()->license_status === 'inactive') {
        return redirect('/license-tiers/pricing');
    }
        $this->authorize('viewAny', Invoice::class); // Check permission to view any invoice
        
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
        'items.*.unit_price' => 'required|numeric|min:0', // Added validation for unit_price
        'notes' => 'nullable|string',
    ]);

    // Use a transaction to ensure data integrity
    $invoice = DB::transaction(function () use ($request, $validatedData) {
        // Lock the invoices table for the current user to prevent race conditions
        $lastInvoice = Invoice::where('user_id', Auth::id())
                              ->lockForUpdate()
                              ->orderBy('invoice_number', 'desc')
                              ->first();

        // Determine the next invoice number
        $nextInvoiceNumber = $lastInvoice ? $lastInvoice->invoice_number + 1 : 1;

        // Create the invoice with the determined invoice_number
        return Invoice::create([
            'client_profile_id' => $validatedData['client_profile_id'],
            'user_id' => Auth::id(),
            'invoice_date' => $validatedData['invoice_date'],
            'due_date' => $validatedData['due_date'],
            'total_amount' => 0, // Will be calculated later
            'total_tax_amount' => 0, // Will be calculated later
            'total_amount_with_tax' => 0, // Will be calculated later
            'status' => 'En attente',
            'notes' => $validatedData['notes'],
            'invoice_number' => $nextInvoiceNumber,
        ]);
    });

    // Initialize totals
    $totalAmount = 0;
    $totalTaxAmount = 0;

    // Iterate over each item in the invoice
    foreach ($validatedData['items'] as $itemData) {
        // Ensure the product belongs to the authenticated user
        $product = Product::where('id', $itemData['product_id'])
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        $quantity = $itemData['quantity'];
        $unitPrice = $itemData['unit_price']; // Use unit_price from the form
        $taxRate = $product->tax_rate;
        $totalPrice = $unitPrice * $quantity;

        $taxAmount = ($totalPrice * $taxRate) / 100;
        $totalPriceWithTax = $totalPrice + $taxAmount;

        // Create the invoice item
        $invoice->items()->create([
            'product_id' => $product->id,
            'description' => $itemData['description'] ?? $product->name, // Use description from form if provided
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
        // Check permission to view the invoice
        $this->authorize('view', $invoice);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Affiche le formulaire pour éditer une facture.
     */
    public function edit(Invoice $invoice)
    {
        // Check permission to edit the invoice
        $this->authorize('update', $invoice);

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
    // Check permission to update the invoice
    $this->authorize('update', $invoice);

    // Validation des données
    $validatedData = $request->validate([
        'client_profile_id' => 'required|exists:client_profiles,id',
        'invoice_date' => 'required|date',
        'due_date' => 'nullable|date|after_or_equal:invoice_date',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0', // Added validation for unit_price
        'notes' => 'nullable|string',
    ]);

    // Mettre à jour la facture
    $invoice->update([
        'client_profile_id' => $validatedData['client_profile_id'],
        'invoice_date' => $validatedData['invoice_date'],
        'due_date' => $validatedData['due_date'],
        'notes' => $validatedData['notes'],
    ]);

    // Supprimer les éléments existants
    $invoice->items()->delete();

    // Initialize totals
    $totalAmount = 0;
    $totalTaxAmount = 0;

    foreach ($validatedData['items'] as $itemData) {
        // Vérifie que le produit appartient à l'utilisateur
        $product = Product::where('id', $itemData['product_id'])
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        $quantity = $itemData['quantity'];
        $unitPrice = $itemData['unit_price']; // Use unit_price from the form
        $taxRate = $product->tax_rate;
        $totalPrice = $unitPrice * $quantity;

        $taxAmount = ($totalPrice * $taxRate) / 100;
        $totalPriceWithTax = $totalPrice + $taxAmount;

        $invoice->items()->create([
            'product_id' => $product->id,
            'description' => $itemData['description'] ?? $product->name, // Use description from form if provided
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
        // Check permission to delete the invoice
        $this->authorize('delete', $invoice); // Ensure only the owner can delete the invoice

        // La politique assure que l'utilisateur peut supprimer la facture
        $invoice->delete();

        return redirect()->route('invoices.index')
                         ->with('success', 'Facture supprimée avec succès.');
    }
public function clientPdf(Invoice $invoice)
{
    // Ensure the authenticated client owns this invoice
    $client = auth('client')->user();

    if ($invoice->client_profile_id !== $client->id) {
        abort(403, 'Vous n\'êtes pas autorisé à accéder à cette facture.');
    }

    $invoice->load('clientProfile', 'items.product');

    $pdf = PDF::loadView('invoices.pdf', ['invoice' => $invoice]);

    return $pdf->download('facture_' . $invoice->invoice_number . '.pdf');
}

    public function generatePDF(Invoice $invoice)
    {
        // Check permission to view the invoice
        $this->authorize('view', $invoice);

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
        $this->authorize('update', $invoice); // Check permission to update invoice status

        $invoice->update(['status' => 'Payée']);
        
        return redirect()->route('invoices.show', $invoice)->with('success', 'La facture a été marquée comme payée.');
    }

    public function sendEmail(Invoice $invoice)
    {
        // Ensure the user is authorized to send this invoice
        $this->authorize('view', $invoice); // Check permission to view the invoice

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
public function createPaymentLink(Invoice $invoice)
{
    // Authorization: Ensure the user can update the invoice
    $this->authorize('update', $invoice);
	$invoice->load('user');
    // Check if the invoice already has a payment link
    if ($invoice->payment_link) {
        return redirect()->back()->with('error', 'Un lien de paiement a déjà été généré pour cette facture.');
    }

    try {
        // Retrieve the therapist (user) associated with the invoice
        $user = $invoice->user;

        // Ensure the user has a connected Stripe account
        if (!$user->stripe_account_id) {
            throw new \Exception('Le thérapeute n\'a pas de compte Stripe connecté.');
        }

        // Initialize Stripe with the platform's secret key
        $stripe = new StripeClient(config('services.stripe.secret'));

        // Retrieve all invoice items
        $invoiceItems = $invoice->items; // Assuming 'items' relationship is defined

        $lineItems = [];

        foreach ($invoiceItems as $item) {
            $product = $item->product;

            // Ensure the product belongs to the authenticated user
            if ($product->user_id !== Auth::id()) {
                throw new \Exception('Produit non autorisé.');
            }

            // Synchronize product with Stripe
            $stripeProductId = $this->syncProductWithStripe($stripe, $product, $user->stripe_account_id);

            // Synchronize price with Stripe
            $stripePriceId = $this->syncPriceWithStripe($stripe, $product, $user->stripe_account_id);

            // Add to line items
            $lineItems[] = [
                'price' => $stripePriceId,
                'quantity' => $item->quantity,
            ];
        }

        // Create a Stripe Payment Link using the synchronized prices
        $paymentLink = $stripe->paymentLinks->create([
            'line_items' => $lineItems,
            'metadata' => [
                'invoice_id' => $invoice->id, // Embed Invoice ID in metadata
                'user_id' => $invoice->user_id, // Optional: Embed User ID if needed
            ],
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => [
                     'url' => route('therapist.show', ['slug' => $user->slug]),
                ],
            ],
        ], [
            'stripe_account' => $user->stripe_account_id, // Use connected account
        ]);

        // Save the payment link URL to the invoice
        $invoice->payment_link = $paymentLink->url;
        $invoice->save();
		$therapistName = Auth::user()->name;
        // Optionally, send the payment link via email to the patient
        $client = $invoice->clientProfile;
        if ($client && $client->email) {
            Mail::to($client->email)->queue(new InvoicePaymentLinkMail($invoice, $therapistName));
        }

        return redirect()->back()->with('success', 'Lien de paiement Stripe généré avec succès.');
    } catch (\Exception $e) {
        Log::error("Stripe Payment Link Creation Failed for Invoice ID {$invoice->id}: " . $e->getMessage(), [
            'invoice_id' => $invoice->id,
            'user_id' => $invoice->user_id,
            'exception' => $e,
        ]);
        return redirect()->back()->with('error', 'Une erreur est survenue lors de la création du lien de paiement.');
    }
}


    /**
     * Synchronize a product with Stripe.
     *
     * @param  \Stripe\StripeClient  $stripe
     * @param  \App\Models\Product  $product
     * @param  string  $stripeAccountId
     * @return string  Stripe Product ID
     */
protected function syncProductWithStripe(StripeClient $stripe, Product $product, string $stripeAccountId): string
{
    // If the product already has a Stripe Product ID, return it
    if ($product->stripe_product_id) {
        return $product->stripe_product_id;
    }

    // Prepare product data
    $productData = [
        'name' => $product->name,
    ];

    // Conditionally add 'description' if it's not empty
    if (!empty($product->description)) {
        $productData['description'] = $product->description;
    }

    // Create a new product in Stripe within the connected account
    $stripeProduct = $stripe->products->create($productData, [
        'stripe_account' => $stripeAccountId,
    ]);

    // Update the product with the Stripe Product ID
    $product->stripe_product_id = $stripeProduct->id;
    $product->save();

    return $stripeProduct->id;
}


    /**
     * Synchronize a price with Stripe.
     *
     * @param  \Stripe\StripeClient  $stripe
     * @param  \App\Models\Product  $product
     * @param  string  $stripeAccountId
     * @return string  Stripe Price ID
     */
    protected function syncPriceWithStripe(StripeClient $stripe, Product $product, string $stripeAccountId): string
    {
        // If the product already has a Stripe Price ID, retrieve and compare
        if ($product->stripe_price_id) {
            try {
                // Retrieve the current price from Stripe
                $currentStripePrice = $stripe->prices->retrieve(
                    $product->stripe_price_id,
                    [],
                    [
                        'stripe_account' => $stripeAccountId,
                    ]
                );

                // Calculate local price including tax
                $localPriceInclTax = $product->price_incl_tax; // Accessor method

                // Compare prices (Stripe uses cents)
                if (intval($localPriceInclTax * 100) !== $currentStripePrice->unit_amount) {
                    // Prices differ, create a new price in Stripe
                    $newStripePrice = $stripe->prices->create([
                        'unit_amount' => intval($localPriceInclTax * 100), // Convert to cents
                        'currency' => 'eur',
                        'product' => $product->stripe_product_id,
                    ], [
                        'stripe_account' => $stripeAccountId,
                    ]);

                    // Update the product with the new Stripe Price ID
                    $product->stripe_price_id = $newStripePrice->id;
                    $product->save();

                    return $newStripePrice->id;
                }

                // Prices match, return existing Stripe Price ID
                return $product->stripe_price_id;

            } catch (\Stripe\Exception\ApiErrorException $e) {
                // Handle Stripe API errors
                Log::error("Stripe API Error while retrieving/updating price: " . $e->getMessage(), [
                    'product_id' => $product->id,
                    'stripe_price_id' => $product->stripe_price_id,
                    'stripe_account_id' => $stripeAccountId,
                ]);
                throw new \Exception('Erreur lors de la récupération ou de la mise à jour du prix depuis Stripe.');
            }
        }

 

        // Calculate price including tax
        $priceInclTax = $product->price_incl_tax; // Accessor method

        if (empty($priceInclTax)) {
            throw new \Exception('Le prix incluant la taxe n\'est pas défini pour le produit.');
        }

        try {
            // Create a new price in Stripe within the connected account
            $stripePrice = $stripe->prices->create([
                'unit_amount' => intval($priceInclTax * 100), // Convert to cents
                'currency' => 'eur', // Ensure currency is valid
                'product' => $product->stripe_product_id,
            ], [
                'stripe_account' => $stripeAccountId,
            ]);

            // Update the product with the Stripe Price ID
            $product->stripe_price_id = $stripePrice->id;
            $product->save();

            return $stripePrice->id;

        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle Stripe API errors
            Log::error("Stripe API Error while creating price: " . $e->getMessage(), [
                'product_id' => $product->id,
                'stripe_account_id' => $stripeAccountId,
            ]);
            throw new \Exception('Erreur lors de la création du prix dans Stripe.');
        }
    }


}

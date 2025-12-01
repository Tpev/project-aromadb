<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ClientProfile;
use App\Models\Product;
use App\Models\InventoryItem;
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
use App\Mail\QuoteMail;


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
public function index(Request $request)
{
    if (Auth::user()->license_status === 'inactive') {
        return redirect('/license-tiers/pricing');
    }

    $this->authorize('viewAny', Invoice::class);

    // Separate invoices and quotes
    $invoices = Invoice::where('user_id', Auth::id())
        ->where('type', 'invoice')
        ->with('clientProfile')
        ->orderByDesc('invoice_date')
        ->get();

    $quotes = Invoice::where('user_id', Auth::id())
        ->where('type', 'quote')
        ->with('clientProfile')
        ->orderByDesc('invoice_date')
        ->get();

    // Some useful aggregates for mobile UI (also harmless for web)
    $invoiceStats = [
        'count'            => $invoices->count(),
        'paid_count'       => $invoices->where('status', 'Payée')->count(),
        'outstanding_count'=> $invoices->whereIn('status', ['En attente', 'Partiellement payée'])->count(),
        'outstanding_total'=> $invoices->sum('solde_restant ?? 0'),
        'total_ttc'        => $invoices->sum('total_amount_with_tax'),
    ];

    $quoteStats = [
        'count'       => $quotes->count(),
        'accepted'    => $quotes->where('status', 'Devis Accepté')->count(),
        'pending'     => $quotes->where('status', 'Devis')->count(),
        'rejected'    => $quotes->where('status', 'Devis Refusé')->count(),
        'total_ttc'   => $quotes->sum('total_amount_with_tax'),
    ];


 
        
    // If we’re on the mobile route, use the mobile view
    if (request()->routeIs('mobile.*') || request()->is('mobile/*')) {
        return view('mobile.invoices.index', [
            'invoices'     => $invoices,
            'quotes'       => $quotes,
            'invoiceStats' => $invoiceStats,
            'quoteStats'   => $quoteStats,
        ]);
    }

    // Default: web view
    return view('invoices.index', [
        'invoices'     => $invoices,
        'quotes'       => $quotes,
        'invoiceStats' => $invoiceStats,
        'quoteStats'   => $quoteStats,
    ]);
}


/**
 * Affiche le formulaire pour créer une nouvelle facture.
 */
public function create(Request $request)
{
    // Get clients, products and inventory items for the authenticated user
    $clients = ClientProfile::where('user_id', auth()->id())->get();
    $products = Product::where('user_id', auth()->id())->get();
    $inventoryItems = InventoryItem::where('user_id', auth()->id())->get();

    // Preload selected client or product if passed via query parameters
    $selectedClient = $request->input('client_id') ? ClientProfile::find($request->input('client_id')) : null;
    $selectedProduct = $request->input('product_id') ? Product::find($request->input('product_id')) : null;

    return view('invoices.create', compact('clients', 'products', 'inventoryItems', 'selectedClient', 'selectedProduct'));
}


 /**
 * Stocke une nouvelle facture en base de données.
 */
public function store(Request $request)
{
    $validatedData = $request->validate([
        'client_profile_id' => 'required|exists:client_profiles,id',
        'invoice_date' => 'required|date',
        'due_date' => 'nullable|date|after_or_equal:invoice_date',
        'items.*.product_id' => 'nullable|exists:products,id',
        'items.*.inventory_item_id' => 'nullable|exists:inventory_items,id',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.unit_price' => 'required|numeric|min:0', // utilisé uniquement pour lignes personnalisées
        'items.*.description' => 'nullable|string',
        'notes' => 'nullable|string',
    ]);

    $invoice = DB::transaction(function () use ($validatedData) {
        $lastInvoice = Invoice::where('user_id', Auth::id())
            ->lockForUpdate()
            ->orderBy('invoice_number', 'desc')
            ->first();

        $nextInvoiceNumber = $lastInvoice ? $lastInvoice->invoice_number + 1 : 1;

        return Invoice::create([
            'client_profile_id' => $validatedData['client_profile_id'],
            'user_id' => Auth::id(),
            'invoice_date' => $validatedData['invoice_date'],
            'due_date' => $validatedData['due_date'],
            'notes' => $validatedData['notes'],
            'invoice_number' => $nextInvoiceNumber,
            'total_amount' => 0,
            'total_tax_amount' => 0,
            'total_amount_with_tax' => 0,
            'status' => 'En attente',
        ]);
    });

    $totalAmount = 0;
    $totalTaxAmount = 0;

    foreach ($validatedData['items'] as $item) {
        $description = $item['description'] ?? '';
        $quantity = $item['quantity'];
        $type = null;
        $unitPriceHt = 0;
        $taxRate = 0;
        $unitPriceTtc = 0;

        if (isset($item['product_id'])) {
            $type = 'product';
        } elseif (isset($item['inventory_item_id'])) {
            $type = 'inventory';
        }

        if ($type === 'product') {
            $product = Product::where('id', $item['product_id'])->where('user_id', Auth::id())->firstOrFail();
            $description = $description ?: $product->name;
            $taxRate = $product->tax_rate;
            $unitPriceHt = $product->price;
            $unitPriceTtc = $unitPriceHt * (1 + $taxRate / 100);

            $invoice->items()->create([
                'type' => 'product',
                'product_id' => $product->id,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPriceHt,
                'tax_rate' => $taxRate,
                'tax_amount' => $unitPriceHt * $quantity * ($taxRate / 100),
                'total_price' => $unitPriceHt * $quantity,
                'total_price_with_tax' => $unitPriceHt * $quantity * (1 + $taxRate / 100),
            ]);
        } elseif ($type === 'inventory') {
            $inv = InventoryItem::where('id', $item['inventory_item_id'])->where('user_id', Auth::id())->firstOrFail();
            $description = $description ?: $inv->name;
            $taxRate = $inv->vat_rate_sale ?? 0;

            // Choix du prix TTC selon unit_type
            if ($inv->unit_type === 'ml') {
                $unitPriceTtc = $inv->selling_price_per_ml;
            } else { // unit
                $unitPriceTtc = $inv->selling_price;
            }

            // Conversion TTC → HT
            $unitPriceHt = $taxRate > 0 ? $unitPriceTtc / (1 + $taxRate / 100) : $unitPriceTtc;

            $invoice->items()->create([
                'type' => 'inventory',
                'inventory_item_id' => $inv->id,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPriceHt,
                'tax_rate' => $taxRate,
                'tax_amount' => $unitPriceHt * $quantity * ($taxRate / 100),
                'total_price' => $unitPriceHt * $quantity,
                'total_price_with_tax' => $unitPriceTtc * $quantity,
            ]);
        } else {
            // Ligne personnalisée
            $unitPriceHt = $item['unit_price'];
            $taxRate = 0;
            $unitPriceTtc = $unitPriceHt;

            $invoice->items()->create([
                'type' => 'custom',
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPriceHt,
                'tax_rate' => $taxRate,
                'tax_amount' => $unitPriceHt * $quantity * ($taxRate / 100),
                'total_price' => $unitPriceHt * $quantity,
                'total_price_with_tax' => $unitPriceHt * $quantity * (1 + $taxRate / 100),
            ]);
        }

        $total = $unitPriceHt * $quantity;
        $tax = $total * ($taxRate / 100);
        $totalAmount += $total;
        $totalTaxAmount += $tax;
    }

    $invoice->update([
        'total_amount' => $totalAmount,
        'total_tax_amount' => $totalTaxAmount,
        'total_amount_with_tax' => $totalAmount + $totalTaxAmount,
    ]);

    return redirect()->route('invoices.show', $invoice)->with('success', 'Facture créée avec succès.');
}







public function show(Invoice $invoice)
{
    $this->authorize('view', $invoice);

    // Avoid N+1 and get everything the Blade needs
    $invoice->load([
        'clientProfile',
        'items.product',
        'items.inventoryItem',
        'receipts' => fn ($q) => $q->orderBy('encaissement_date')->orderBy('id'),
    ]);

    return view('invoices.show', compact('invoice'));
}

    /**
     * Affiche le formulaire pour éditer une facture.
     */
public function edit(Invoice $invoice)
{
    $this->authorize('update', $invoice);

    $clients        = ClientProfile::where('user_id', Auth::id())->get();
    $products       = Product::where('user_id', Auth::id())->get();
    $inventoryItems = InventoryItem::where('user_id', Auth::id())->get();

    return view('invoices.edit', compact('invoice', 'clients', 'products', 'inventoryItems'));
}
/**
 * Met à jour une facture existante.
 */
/**
 * Met à jour une facture existante.
 */
public function update(Request $request, Invoice $invoice)
{
    $this->authorize('update', $invoice);

    $validatedData = $request->validate([
        'client_profile_id'      => 'required|exists:client_profiles,id',
        'invoice_date'           => 'required|date',
        'due_date'               => 'nullable|date|after_or_equal:invoice_date',
        'items.*.product_id'         => 'nullable|exists:products,id',
        'items.*.inventory_item_id'  => 'nullable|exists:inventory_items,id',
        'items.*.quantity'           => 'required|numeric|min:0.01',
        'items.*.unit_price'         => 'required|numeric|min:0', // only for custom lines
        'items.*.description'        => 'nullable|string',
        'notes'                  => 'nullable|string',
    ]);

    DB::transaction(function() use ($validatedData, $invoice) {
        // 1) Update basic invoice fields
        $invoice->update([
            'client_profile_id'   => $validatedData['client_profile_id'],
            'invoice_date'        => $validatedData['invoice_date'],
            'due_date'            => $validatedData['due_date'],
            'notes'               => $validatedData['notes'],
        ]);

        // 2) Remove existing items
        $invoice->items()->delete();

        // 3) Re-create all items, recompute totals
        $totalAmount   = 0;
        $totalTaxAmount = 0;

        foreach ($validatedData['items'] as $item) {
            $qty         = $item['quantity'];
            $desc        = $item['description'] ?? '';
            $type        = isset($item['product_id'])
                             ? 'product'
                             : (isset($item['inventory_item_id']) ? 'inventory' : 'custom');
            $unitPriceHt = 0;
            $unitPriceTtc = 0;
            $taxRate     = 0;

            if ($type === 'product') {
                $prod     = Product::where('id', $item['product_id'])
                                   ->where('user_id', Auth::id())
                                   ->firstOrFail();
                $desc     = $desc ?: $prod->name;
                $taxRate  = $prod->tax_rate;
                $unitPriceHt  = $prod->price;
                $unitPriceTtc = $unitPriceHt * (1 + $taxRate/100);

                $invoice->items()->create([
                    'type'                 => 'product',
                    'product_id'           => $prod->id,
                    'description'          => $desc,
                    'quantity'             => $qty,
                    'unit_price'           => $unitPriceHt,
                    'tax_rate'             => $taxRate,
                    'tax_amount'           => $unitPriceHt * $qty * ($taxRate/100),
                    'total_price'          => $unitPriceHt * $qty,
                    'total_price_with_tax' => $unitPriceHt * $qty * (1 + $taxRate/100),
                ]);
            }
            elseif ($type === 'inventory') {
                $inv      = InventoryItem::where('id', $item['inventory_item_id'])
                                         ->where('user_id', Auth::id())
                                         ->firstOrFail();
                $desc     = $desc ?: $inv->name;
                $taxRate  = $inv->vat_rate_sale ?? 0;

                // choose TTC price per unit_type
                if ($inv->unit_type === 'ml') {
                    $unitPriceTtc = $inv->selling_price_per_ml;
                } else {
                    $unitPriceTtc = $inv->selling_price;
                }

                // compute HT
                $unitPriceHt = $taxRate > 0
                    ? $unitPriceTtc / (1 + $taxRate/100)
                    : $unitPriceTtc;

                $invoice->items()->create([
                    'type'                 => 'inventory',
                    'inventory_item_id'    => $inv->id,
                    'description'          => $desc,
                    'quantity'             => $qty,
                    'unit_price'           => $unitPriceHt,
                    'tax_rate'             => $taxRate,
                    'tax_amount'           => $unitPriceHt * $qty * ($taxRate/100),
                    'total_price'          => $unitPriceHt * $qty,
                    'total_price_with_tax' => $unitPriceTtc * $qty,
                ]);
            }
            else {
                // custom line
                $unitPriceHt  = $item['unit_price'];
                $taxRate      = 0;
                $unitPriceTtc = $unitPriceHt;

                $invoice->items()->create([
                    'type'                 => 'custom',
                    'description'          => $desc,
                    'quantity'             => $qty,
                    'unit_price'           => $unitPriceHt,
                    'tax_rate'             => $taxRate,
                    'tax_amount'           => $unitPriceHt * $qty * ($taxRate/100),
                    'total_price'          => $unitPriceHt * $qty,
                    'total_price_with_tax' => $unitPriceHt * $qty * (1 + $taxRate/100),
                ]);
            }

            // accumulate
            $totalAmount   += $unitPriceHt * $qty;
            $totalTaxAmount += ($unitPriceHt * $qty) * ($taxRate/100);
        }

        // 4) Update invoice totals
        $invoice->update([
            'total_amount'           => $totalAmount,
            'total_tax_amount'       => $totalTaxAmount,
            'total_amount_with_tax'  => $totalAmount + $totalTaxAmount,
        ]);
    });

    return redirect()
        ->route('invoices.show', $invoice)
        ->with('success', __('Facture mise à jour avec succès.'));
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

    $invoice->load([
  'user','clientProfile',
  'items.product','items.inventoryItem',
]);


    $pdf = PDF::loadView('invoices.pdf', ['invoice' => $invoice]);

    return $pdf->download('facture_' . $invoice->invoice_number . '.pdf');
}

public function generatePDF(Invoice $invoice)
{
    $this->authorize('view', $invoice);

    // eager load everything we need
    $invoice->load([
        'user',
        'clientProfile',
        'items.product',
        'items.inventoryItem',
    ]);

    $pdf = PDF::loadView('invoices.pdf', ['invoice' => $invoice]);

    return $pdf->download('facture_'.$invoice->invoice_number.'.pdf');
}


public function markAsPaid(Request $request, Invoice $invoice)
{
    $this->authorize('update', $invoice);

    $validated = $request->validate([
        'encaissement_date' => ['nullable','date'],
        'payment_method'    => ['nullable','in:transfer,card,check,cash,other'],
        'amount_ttc'        => ['nullable','numeric','min:0.01'], // pour paiement partiel éventuel
        'nature'            => ['nullable','in:service,goods'],
        'note'              => ['nullable','string','max:255'],
    ]);

    $date  = $validated['encaissement_date'] ?? now()->toDateString();
    $pm    = $validated['payment_method']    ?? 'transfer';
    $nature= $validated['nature']            ?? 'service';

    // Montant à enregistrer : par défaut le solde restant TTC
    $montantTtc = isset($validated['amount_ttc']) && $validated['amount_ttc'] > 0
        ? (float) $validated['amount_ttc']
        : (float) $invoice->solde_restant;

    if ($montantTtc <= 0) {
        return back()->with('error', 'Aucun montant à encaisser (solde déjà réglé).');
    }

    // Convertir TTC -> HT à partir de la facture (proportionnel au ratio global)
    $ttcFacture = (float) $invoice->total_amount_with_tax;
    $htFacture  = (float) $invoice->total_amount;

    $ratioHT = $ttcFacture > 0 ? $htFacture / $ttcFacture : 1.0;
    $montantHt = round($montantTtc * $ratioHT, 2);

    // Créer l’écriture
    \App\Models\Receipt::create([
        'user_id'           => $invoice->user_id,
        'invoice_id'        => $invoice->id,
        'invoice_number'    => (string) $invoice->invoice_number,
        'encaissement_date' => $date,
        'client_name'       => $invoice->clientProfile
                                 ? ($invoice->clientProfile->first_name.' '.$invoice->clientProfile->last_name)
                                 : 'Client',
        'nature'            => $nature,
        'amount_ht'         => $montantHt,
        'amount_ttc'        => $montantTtc,
        'payment_method'    => $pm,
        'direction'         => 'credit',
        'source'            => 'payment',
        'note'              => $validated['note'] ?? null,
    ]);

    // Mettre le statut
    $invoice->refresh();
    if ($invoice->solde_restant <= 0.001) {
        $invoice->update(['status' => 'Payée']);
    } else {
        $invoice->update(['status' => 'Partiellement payée']);
    }

    return redirect()->route('invoices.show', $invoice)
        ->with('success', 'Encaissement enregistré dans le livre de recettes.');
}


public function sendEmail(Invoice $invoice)
{
    $this->authorize('view', $invoice);

    // eager-load everything the PDF and email view need
    $invoice->load([
        'user',
        'clientProfile.company',   // ⬅️ important for queued mails
        'items.product',
        'items.inventoryItem',
    ]);

    $client  = $invoice->clientProfile;
    $company = $client?->company;

    // Determine recipient(s)
    $to = null;
    $cc = null;

    if ($company && $company->billing_email) {
        // Entreprise avec email de facturation dédié
        $to = $company->billing_email;
        $cc = $client->email; // copie au bénéficiaire si renseigné
    } elseif ($company && $company->email) {
        // Entreprise sans "billing_email" mais avec email général
        $to = $company->email;
        $cc = $client->email;
    } elseif ($client && $client->email) {
        // Client "normal" (pas d'entreprise)
        $to = $client->email;
    }

    if (!$to) {
        return back()->with('error', "Aucune adresse email de facturation n'est définie (client ou entreprise).");
    }

    $therapistName = Auth::user()->name;

    try {
        Mail::to($to)
            ->when($cc, fn ($message) => $message->cc($cc))
            ->queue(new InvoiceMail($invoice, $therapistName));

        $invoice->update(['sent_at' => now()]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Facture envoyée par email avec succès.');
    } catch (\Exception $e) {
        Log::error("Error sending invoice email: " . $e->getMessage());
        return redirect()
            ->route('invoices.show', $invoice)
            ->with('error', "Une erreur est survenue lors de l'envoi de l'email.");
    }
}



public function createPaymentLink(Invoice $invoice)
{
    $this->authorize('update', $invoice);
    $invoice->load('user', 'items.product', 'items.inventoryItem');

    if ($invoice->payment_link) {
        return back()->with('error', 'Un lien de paiement existe déjà.');
    }

    $stripe = new StripeClient(config('services.stripe.secret'));
    $lineItems = [];

    foreach ($invoice->items as $item) {
        // --- PRODUCT LINE ---
        if ($item->type === 'product' && $item->product) {
            $priceId = $this->syncPriceWithStripe(
                $stripe,
                $item->product,
                $invoice->user->stripe_account_id
            );

            $lineItems[] = [
                'price'    => $priceId,
                'quantity' => $item->quantity,
            ];
        }

        // --- INVENTORY LINE ---
        elseif ($item->type === 'inventory' && $item->inventoryItem) {
            $inv       = $item->inventoryItem;
            $qty       = $item->quantity;
            $unitType  = $inv->unit_type;
            $unitTtc   = $unitType === 'ml'
                           ? $inv->selling_price_per_ml
                           : $inv->selling_price;
            $unitAmount = intval(round($unitTtc * 100));

            try {
                // 1) Create a Stripe Product for this inventory item
                $stripeProduct = $stripe->products->create([
                    'name'        => $inv->name,
                    'description' => $item->description,
                ], ['stripe_account' => $invoice->user->stripe_account_id]);

                // 2) Create a one-off Stripe Price for that product
                $stripePrice = $stripe->prices->create([
                    'product'     => $stripeProduct->id,
                    'unit_amount' => $unitAmount,
                    'currency'    => 'eur',
                ], ['stripe_account' => $invoice->user->stripe_account_id]);

                // 3) Add to the line items
                $lineItems[] = [
                    'price'    => $stripePrice->id,
                    'quantity' => $qty,
                ];

            } catch (ApiErrorException $e) {
                Log::error("Stripe Inventory Price Creation Failed for InvoiceItem {$item->id}: " . $e->getMessage());
                return back()->with('error', 'Impossible de créer le prix Stripe pour un article d’inventaire.');
            }
        }
    }

    // Create the Payment Link with only `price`‐based lines
    $paymentLink = $stripe->paymentLinks->create([
        'line_items' => $lineItems,
        'after_completion' => [
            'type' => 'redirect',
            'redirect' => [
                'url' => route('therapist.show', $invoice->user->slug),
            ],
        ],
    ], ['stripe_account' => $invoice->user->stripe_account_id]);

    $invoice->update(['payment_link' => $paymentLink->url]);

    return back()->with('success', 'Lien de paiement généré avec succès.');
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
// QUOTE RELATED METHODS

public function createQuote(Request $request)
{
    $clients        = ClientProfile::where('user_id', auth()->id())->get();
    $products       = Product::where('user_id', auth()->id())->get();
    $inventoryItems = InventoryItem::where('user_id', auth()->id())->get();

    return view('invoices.create-quote', compact('clients','products','inventoryItems'));
}

public function storeQuote(Request $request)
{
    $validated = $request->validate([
        'client_profile_id'       => 'required|exists:client_profiles,id',
        'quote_date'              => 'required|date',
        'valid_until'             => 'nullable|date|after_or_equal:quote_date',
        'items.*.type'            => 'required|in:product,inventory',
        'items.*.product_id'      => 'nullable|exists:products,id',
        'items.*.inventory_item_id'=>'nullable|exists:inventory_items,id',
        'items.*.quantity'        => 'required|numeric|min:0.01',
        'items.*.unit_price'      => 'required|numeric|min:0', // only custom
        'items.*.description'     => 'nullable|string',
        'notes'                   => 'nullable|string',
    ]);

    $quote = DB::transaction(function() use($validated){
        $last = Invoice::where('type','quote')
                       ->where('user_id',auth()->id())
                       ->orderByDesc('id')->first();
        $num  = 'D-'.str_pad(($last?->id ?? 0)+1,5,'0',STR_PAD_LEFT);

        return Invoice::create([
            'client_profile_id'  => $validated['client_profile_id'],
            'user_id'            => auth()->id(),
            'invoice_date'       => $validated['quote_date'],
            'due_date'           => $validated['valid_until'],
            'notes'              => $validated['notes'],
            'status'             => 'Devis',
            'type'               => 'quote',
            'quote_number'       => $num,
            'total_amount'       => 0,
            'total_tax_amount'   => 0,
            'total_amount_with_tax'=>0,
        ]);
    });

    $totHT  = 0;
    $totTax = 0;

    foreach($validated['items'] as $item) {
        $qty = $item['quantity'];
        $desc= $item['description'] ?? '';
        $type= $item['type'];

        if($type==='product') {
            $prod     = Product::where('id',$item['product_id'])
                               ->where('user_id',auth()->id())
                               ->firstOrFail();
            $desc     = $desc ?: $prod->name;
            $taxRate  = $prod->tax_rate;
            $htUnit   = $prod->price;
            $ttcLine  = $htUnit*$qty*(1+$taxRate/100);
        }
        elseif($type==='inventory') {
            $inv      = InventoryItem::where('id',$item['inventory_item_id'])
                                     ->where('user_id',auth()->id())
                                     ->firstOrFail();
            $desc     = $desc ?: $inv->name;
            $taxRate  = $inv->vat_rate_sale ?? 0;
            $ttcUnit  = ($inv->unit_type==='ml')
                           ? $inv->selling_price_per_ml
                           : $inv->selling_price;
            $htUnit   = $ttcUnit / (1+$taxRate/100);
            $ttcLine  = $ttcUnit * $qty;
        }
        else {
            $htUnit   = $item['unit_price'];
            $taxRate  = 0;
            $ttcLine  = $htUnit*$qty;
        }

        $taxAmt   = $htUnit*$qty*($taxRate/100);
        $totHT   += $htUnit*$qty;
        $totTax  += $taxAmt;

        $quote->items()->create([
            'type'                 => $type,
            'product_id'           => $item['product_id'] ?? null,
            'inventory_item_id'    => $item['inventory_item_id'] ?? null,
            'description'          => $desc,
            'quantity'             => $qty,
            'unit_price'           => $htUnit,
            'tax_rate'             => $taxRate,
            'tax_amount'           => $taxAmt,
            'total_price'          => $htUnit*$qty,
            'total_price_with_tax' => $ttcLine,
        ]);
    }

    $quote->update([
        'total_amount'         => $totHT,
        'total_tax_amount'     => $totTax,
        'total_amount_with_tax'=> $totHT + $totTax,
    ]);

    return redirect()->route('invoices.showQuote',$quote)
                     ->with('success','Devis créé avec succès.');
}


public function editQuote(Invoice $quote)
{
    $this->authorize('update', $quote);

    $clients        = ClientProfile::where('user_id', Auth::id())->get();
    $products       = Product::where('user_id', Auth::id())->get();
    $inventoryItems = InventoryItem::where('user_id', Auth::id())->get();

    return view('invoices.edit-quote', compact('quote', 'clients', 'products', 'inventoryItems'));
}
public function updateQuote(Request $request, Invoice $quote)
{
    $this->authorize('update', $quote);

    $validated = $request->validate([
        'client_profile_id'      => 'required|exists:client_profiles,id',
        'quote_date'             => 'required|date',
        'valid_until'            => 'nullable|date|after_or_equal:quote_date',
        'items.*.product_id'     => 'nullable|exists:products,id',
        'items.*.inventory_item_id' => 'nullable|exists:inventory_items,id',
        'items.*.quantity'       => 'required|numeric|min:0.01',
        'notes'                  => 'nullable|string',
    ]);

    DB::transaction(function() use ($validated, $quote) {
        // Met à jour les infos générales
        $quote->update([
            'client_profile_id' => $validated['client_profile_id'],
            'invoice_date'      => $validated['quote_date'],
            'due_date'          => $validated['valid_until'],
            'notes'             => $validated['notes'],
        ]);

        // Supprime les anciennes lignes
        $quote->items()->delete();

        $totalHT  = 0;
        $totalTax = 0;

        foreach ($validated['items'] as $item) {
            // Détecte le type
            if (!empty($item['product_id'])) {
                $type = 'product';
                $model = Product::where('user_id',Auth::id())
                                ->findOrFail($item['product_id']);
                $name        = $model->name;
                $taxRate     = $model->tax_rate;
                $priceHT     = $model->price;
                $priceTTC    = $priceHT * (1 + $taxRate/100);
            }
            elseif (!empty($item['inventory_item_id'])) {
                $type = 'inventory';
                $model = InventoryItem::where('user_id',Auth::id())
                                      ->findOrFail($item['inventory_item_id']);
                $name    = $model->name;
                $taxRate = $model->vat_rate_sale ?: 0;
                // Choix des tarifs comme pour la facture
                if ($model->unit_type === 'ml') {
                    $priceTTC = $model->selling_price_per_ml;
                } else {
                    $priceTTC = $model->selling_price;
                }
                $priceHT = $taxRate>0
                    ? $priceTTC / (1 + $taxRate/100)
                    : $priceTTC;
            }
            else {
                $type    = 'custom';
                $name    = '';
                $taxRate = 0;
                $priceHT = $item['unit_price'] ?? 0;
                $priceTTC = $priceHT;
            }

            $qty    = $item['quantity'];
            $desc   = $item['description'] ?? $name;
            $totalLigneHT  = $priceHT  * $qty;
            $taxLigne      = $totalLigneHT * ($taxRate/100);
            $totalLigneTTC = $priceTTC * $qty;

            // Crée la ligne
            $quote->items()->create([
                'type'                  => $type,
                'product_id'            => $type==='product'   ? $model->id : null,
                'inventory_item_id'     => $type==='inventory' ? $model->id : null,
                'description'           => $desc,
                'quantity'              => $qty,
                'unit_price'            => $priceHT,
                'tax_rate'              => $taxRate,
                'tax_amount'            => $taxLigne,
                'total_price'           => $totalLigneHT,
                'total_price_with_tax'  => $totalLigneTTC,
            ]);

            $totalHT  += $totalLigneHT;
            $totalTax += $taxLigne;
        }

        // Met à jour les totaux du devis
        $quote->update([
            'total_amount'           => $totalHT,
            'total_tax_amount'       => $totalTax,
            'total_amount_with_tax'  => $totalHT + $totalTax,
        ]);
    });

    return redirect()
        ->route('invoices.showQuote', $quote)
        ->with('success','Devis mis à jour avec succès.');
}

/**
 * Affiche un devis spécifique.
 */
public function showQuote($id)
{
    // On charge clientProfile, items.product et items.inventoryItem
    $quote = Invoice::where('id', $id)
        ->where('type','quote')
        ->with(['clientProfile','items.product','items.inventoryItem'])
        ->firstOrFail();

    $this->authorize('view',$quote);

    return view('invoices.show-quote', compact('quote'));
}

public function updateQuoteStatus(Request $request, Invoice $quote)
{
    $this->authorize('update', $quote); // facultatif selon ta logique

    $validated = $request->validate([
        'status' => 'required|in:Devis Accepté,Devis Refusé',
    ]);

    if ($quote->type !== 'quote') {
        return redirect()->back()->with('error', 'Ce document n\'est pas un devis.');
    }

    $quote->status = $validated['status'];
    $quote->save();

    return redirect()->back()->with('success', 'Statut du devis mis à jour : ' . $quote->status);
}


public function generateQuotePDF(Invoice $invoice)
{
    $this->authorize('view', $invoice);

    // eager-load toutes les relations nécessaires
    $invoice->load([
        'user',
        'clientProfile',
        'items.product',
        'items.inventoryItem', // <— pour les inventaires
    ]);

    $pdf = PDF::loadView('invoices.pdf_quote', ['invoice' => $invoice]);

    return $pdf->download('devis_' . ($invoice->quote_number ?? $invoice->id) . '.pdf');
}


public function sendQuoteEmail(Invoice $quote)
{
    $this->authorize('view', $quote);

    if ($quote->type !== 'quote') {
        return redirect()->back()->with('error', 'Ce document n\'est pas un devis.');
    }

    $client = $quote->clientProfile;

    // 1) Email de facturation prioritaire, sinon email du client
    $toEmail = $client->email_billing ?: $client->email;

    if (!$toEmail) {
        return redirect()->back()->with('error', 'Le client n\'a pas d\'adresse email.');
    }

    // 2) Nom du contact de facturation pour le contenu de l’email
    $billingFirst = $client->first_name_billing ?: $client->first_name;
    $billingLast  = $client->last_name_billing  ?: $client->last_name;
    $contactName  = trim($billingFirst.' '.$billingLast);

    try {
        $therapistName = Auth::user()->name;

        // Tu peux faire évoluer QuoteMail pour accepter $contactName
        \Mail::to($toEmail)->queue(new QuoteMail($quote, $therapistName, $contactName));

        $quote->update(['sent_at' => now()]);

        return redirect()->route('invoices.showQuote', $quote)
                         ->with('success', 'Devis envoyé par email avec succès.');
    } catch (\Exception $e) {
        \Log::error('Erreur envoi devis : ' . $e->getMessage());
        return redirect()->route('invoices.showQuote', $quote)
                         ->with('error', 'Erreur lors de l\'envoi du devis.');
    }
}


}

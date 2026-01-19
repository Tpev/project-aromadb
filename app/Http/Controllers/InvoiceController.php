<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ClientProfile;
use App\Models\CorporateClient;
use App\Models\Product;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\DB; // Import DB facade
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Mail\InvoiceMail;
use Stripe\Stripe;
use Stripe\PaymentLink;
use Illuminate\Support\Facades\Log;
use App\Mail\InvoicePaymentLinkMail;
use Stripe\StripeClient;
use App\Mail\QuoteMail;
use App\Models\PackPurchase;
use App\Models\PackProduct;


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
        ->orderByDesc('id')
        ->get();

    $quotes = Invoice::where('user_id', Auth::id())
        ->where('type', 'quote')
        ->with('clientProfile')
        ->orderByDesc('id')
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


public function create(Request $request)
{
    $clients        = ClientProfile::where('user_id', auth()->id())->get();
    $corporateClients = CorporateClient::where('user_id', auth()->id())->get();
    $products       = Product::where('user_id', auth()->id())->get();
    $inventoryItems = InventoryItem::where('user_id', auth()->id())->get();

    // ✅ Packs sold by this therapist
    $packProducts = PackProduct::where('user_id', auth()->id())->get();

    $selectedClient = null;
    $selectedCorporateClient = null;

    // Support both legacy ?client_id=... and current ?client_profile_id=...
    $selectedClientId    = $request->input('client_profile_id') ?? $request->input('client_id');
    $selectedCorporateId = $request->input('corporate_client_id');

    if ($selectedClientId) {
        $selectedClient = ClientProfile::where('user_id', auth()->id())->find($selectedClientId);
    }

    if ($selectedCorporateId) {
        $selectedCorporateClient = CorporateClient::where('user_id', auth()->id())->find($selectedCorporateId);
    }

    $selectedProduct = $request->input('product_id') ? Product::find($request->input('product_id')) : null;

    return view('invoices.create', compact(
        'clients',
        'corporateClients',
        'products',
        'inventoryItems',
        'packProducts',
        'selectedClient',
        'selectedCorporateClient',
        'selectedProduct'
    ));
}



 /**
 * Stocke une nouvelle facture en base de données.
 */
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        // Billing target (exactly one)
        'client_profile_id'    => ['nullable', Rule::exists('client_profiles', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],
        'corporate_client_id'  => ['nullable', Rule::exists('corporate_clients', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],

        'invoice_date'      => ['required', 'date'],
        'due_date'          => ['nullable', 'date', 'after_or_equal:invoice_date'],
        'notes'             => ['nullable', 'string'],

        // Discounts (global)
        'global_discount_type'  => ['nullable', Rule::in(['percent', 'amount'])],
        'global_discount_value' => ['nullable', 'numeric', 'min:0'],

        // Items
        'items'                     => ['required', 'array', 'min:1'],
        'items.*.product_id'         => ['nullable', Rule::exists('products', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],
        'items.*.inventory_item_id'  => ['nullable', Rule::exists('inventory_items', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],
        'items.*.description'        => ['nullable', 'string', 'max:255'],
        'items.*.quantity'           => ['required', 'numeric', 'min:1'],
        'items.*.unit_price'         => ['required', 'numeric', 'min:0'],
        'items.*.tax_rate'           => ['required', 'numeric', 'min:0'],
        'items.*.unit_type'          => ['nullable', Rule::in(['unit', 'ml'])],
        'items.*.unit_price_ht'      => ['nullable', 'numeric', 'min:0'],

        // Packs
        'items.*.pack_product_id'    => ['nullable', Rule::exists('pack_products', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],

        // Discounts (per line)
        'items.*.line_discount_type'  => ['nullable', Rule::in(['percent', 'amount'])],
        'items.*.line_discount_value' => ['nullable', 'numeric', 'min:0'],
    ]);

    $validator->after(function ($validator) use ($request) {
        $hasClient = (bool) $request->input('client_profile_id');
        $hasCorp   = (bool) $request->input('corporate_client_id');

        if (!$hasClient && !$hasCorp) {
            $validator->errors()->add('client_profile_id', "Veuillez sélectionner un client OU une entreprise.");
        }

        if ($hasClient && $hasCorp) {
            $validator->errors()->add('client_profile_id', "Veuillez sélectionner soit un client, soit une entreprise (pas les deux).");
        }
    });

    $validatedData = $validator->validate();

    $invoice = DB::transaction(function () use ($validatedData) {
        $lastInvoice = Invoice::where('user_id', Auth::id())
            ->lockForUpdate()
            ->orderBy('invoice_number', 'desc')
            ->first();

        $nextInvoiceNumber = $lastInvoice ? $lastInvoice->invoice_number + 1 : 1;

        $invoice = Invoice::create([
            'client_profile_id'      => $validatedData['client_profile_id'] ?? null,
            'user_id'                => Auth::id(),
            'invoice_date'           => $validatedData['invoice_date'],
            'due_date'               => $validatedData['due_date'] ?? null,
            'notes'                  => $validatedData['notes'] ?? null,
            'invoice_number'         => $nextInvoiceNumber,
            'status'                 => 'En attente',
            'type'                   => 'invoice',

            // Totals will be recomputed
            'total_amount'           => 0,
            'total_tax_amount'       => 0,
            'total_amount_with_tax'  => 0,

            // Global discount fields (computed later)
            'global_discount_type'   => $validatedData['global_discount_type'] ?? null,
            'global_discount_value'  => $validatedData['global_discount_value'] ?? null,
            'global_discount_amount_ht' => 0,
        ]);

        // Optional: invoice is billed directly to a corporate client
        if (!empty($validatedData['corporate_client_id'])) {
            $invoice->corporate_client_id = $validatedData['corporate_client_id'];
            $invoice->save();
        }


        $this->recomputeInvoiceTotalsWithDiscounts(
            $invoice,
            $validatedData['items'] ?? [],
            $validatedData['global_discount_type'] ?? null,
            $validatedData['global_discount_value'] ?? null
        );

        return $invoice;
    });

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
    $corporateClients = CorporateClient::where('user_id', Auth::id())->get();
    $products       = Product::where('user_id', Auth::id())->get();
    $inventoryItems = InventoryItem::where('user_id', Auth::id())->get();

    return view('invoices.edit', compact('invoice', 'clients', 'corporateClients', 'products', 'inventoryItems'));
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

    $validator = Validator::make($request->all(), [
        // Billing target (exactly one)
        'client_profile_id'    => ['nullable', Rule::exists('client_profiles', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],
        'corporate_client_id'  => ['nullable', Rule::exists('corporate_clients', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],

        'invoice_date'      => ['required', 'date'],
        'due_date'          => ['nullable', 'date', 'after_or_equal:invoice_date'],
        'notes'             => ['nullable', 'string'],

        // Discounts (global)
        'global_discount_type'  => ['nullable', Rule::in(['percent', 'amount'])],
        'global_discount_value' => ['nullable', 'numeric', 'min:0'],

        // Items
        'items'                     => ['required', 'array', 'min:1'],
        'items.*.product_id'         => ['nullable', Rule::exists('products', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],
        'items.*.inventory_item_id'  => ['nullable', Rule::exists('inventory_items', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],
        'items.*.description'        => ['nullable', 'string', 'max:255'],
        'items.*.quantity'           => ['required', 'numeric', 'min:1'],
        'items.*.unit_price'         => ['required', 'numeric', 'min:0'],
        'items.*.tax_rate'           => ['required', 'numeric', 'min:0'],
        'items.*.unit_type'          => ['nullable', Rule::in(['unit', 'ml'])],
        'items.*.unit_price_ht'      => ['nullable', 'numeric', 'min:0'],

        // Packs
        'items.*.pack_product_id'    => ['nullable', Rule::exists('pack_products', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],

        // Discounts (per line)
        'items.*.line_discount_type'  => ['nullable', Rule::in(['percent', 'amount'])],
        'items.*.line_discount_value' => ['nullable', 'numeric', 'min:0'],
    ]);

    $validator->after(function ($validator) use ($request) {
        $hasClient = (bool) $request->input('client_profile_id');
        $hasCorp   = (bool) $request->input('corporate_client_id');

        if (!$hasClient && !$hasCorp) {
            $validator->errors()->add('client_profile_id', "Veuillez sélectionner un client OU une entreprise.");
        }

        if ($hasClient && $hasCorp) {
            $validator->errors()->add('client_profile_id', "Veuillez sélectionner soit un client, soit une entreprise (pas les deux).");
        }
    });

    $validatedData = $validator->validate();

    DB::transaction(function () use ($validatedData, $invoice) {
        // Update basic invoice fields
        $invoice->update([
            'client_profile_id'        => $validatedData['client_profile_id'] ?? null,
            'invoice_date'             => $validatedData['invoice_date'],
            'due_date'                 => $validatedData['due_date'] ?? null,
            'notes'                    => $validatedData['notes'] ?? null,

            'global_discount_type'     => $validatedData['global_discount_type'] ?? null,
            'global_discount_value'    => $validatedData['global_discount_value'] ?? null,
            'global_discount_amount_ht'=> 0,

            // Totals will be recomputed
            'total_amount'             => 0,
            'total_tax_amount'         => 0,
            'total_amount_with_tax'    => 0,
        ]);

        // Set / clear corporate billing target
        $invoice->corporate_client_id = $validatedData['corporate_client_id'] ?? null;
        $invoice->save();


        // Remove existing items then rebuild
        $invoice->items()->delete();

        $this->recomputeInvoiceTotalsWithDiscounts(
            $invoice,
            $validatedData['items'] ?? [],
            $validatedData['global_discount_type'] ?? null,
            $validatedData['global_discount_value'] ?? null
        );
    });

    return redirect()->route('invoices.show', $invoice)->with('success', 'Facture mise à jour avec succès.');
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

    // eager load everything we need (including optional corporate client)
    $invoice->load([
        'user',
        'clientProfile',
        'corporateClient',
        'items.product',
        'items.inventoryItem',
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
        'corporateClient',
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
		'corporateClient',         // ⬅️ NEW: facture directe entreprise
		'items.product',
		'items.inventoryItem',
	]);

   $client = $invoice->clientProfile;

// Entreprise possible via:
// - facture directe corporate_client_id
// - ou client rattaché à une entreprise
$companyFromClient = $client?->company;
$company = $invoice->corporateClient ?: $companyFromClient;

// Determine recipient(s)
$to = null;
$cc = null;

// 1) Si entreprise: priorité billing_email puis main_contact_email
if ($company) {
    $to = $company->billing_email ?: $company->main_contact_email;

    // CC au bénéficiaire si on a un client individuel rattaché
    if ($to && $client?->email && $client->email !== $to) {
        $cc = $client->email;
    }
}

// 2) Sinon client “classique”
if (!$to && $client) {
    $to = $client->email_billing ?: $client->email;
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
    $corporateClients = CorporateClient::where('user_id', auth()->id())->get();
    $products       = Product::where('user_id', auth()->id())->get();
    $inventoryItems = InventoryItem::where('user_id', auth()->id())->get();

    return view('invoices.create-quote', compact('clients','corporateClients','products','inventoryItems'));
}

public function storeQuote(Request $request)
{
    $validator = Validator::make($request->all(), [
        // Billing target (exactly one)
        'client_profile_id'    => ['nullable', Rule::exists('client_profiles', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],
        'corporate_client_id'  => ['nullable', Rule::exists('corporate_clients', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],

        'quote_date'                   => ['required', 'date'],
        'valid_until'                  => ['nullable', 'date', 'after_or_equal:quote_date'],

        'items'                        => ['required', 'array', 'min:1'],
        'items.*.type'                 => ['required', Rule::in(['product','inventory','custom'])],
        'items.*.product_id'           => ['nullable', Rule::exists('products', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],
        'items.*.inventory_item_id'    => ['nullable', Rule::exists('inventory_items', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],
        'items.*.quantity'             => ['required', 'numeric', 'min:0.01'],
        'items.*.unit_price'           => ['nullable', 'numeric', 'min:0'], // used for custom
        'items.*.description'          => ['nullable', 'string'],
        'items.*.line_discount_type'   => ['nullable', Rule::in(['percent','amount'])],
        'items.*.line_discount_value'  => ['nullable', 'numeric', 'min:0'],

        'global_discount_type'         => ['nullable', Rule::in(['percent','amount'])],
        'global_discount_value'        => ['nullable', 'numeric', 'min:0'],
        'notes'                        => ['nullable', 'string'],
    ]);

    $validator->after(function ($validator) use ($request) {
        $hasClient = (bool) $request->input('client_profile_id');
        $hasCorp   = (bool) $request->input('corporate_client_id');

        if (!$hasClient && !$hasCorp) {
            $validator->errors()->add('client_profile_id', "Veuillez sélectionner un client OU une entreprise.");
        }

        if ($hasClient && $hasCorp) {
            $validator->errors()->add('client_profile_id', "Veuillez sélectionner soit un client, soit une entreprise (pas les deux).");
        }
    });

    $validated = $validator->validate();

    $quote = DB::transaction(function () use ($validated) {
        $last = Invoice::where('type', 'quote')
            ->where('user_id', auth()->id())
            ->orderByDesc('id')
            ->first();

        $num = 'D-' . str_pad(($last?->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);

        /** @var \App\Models\Invoice $quote */
        $quote = Invoice::create([
            'client_profile_id'        => $validated['client_profile_id'] ?? null,
            'user_id'                  => auth()->id(),
            // Keep your existing storage mapping
            'invoice_date'             => $validated['quote_date'],
            'due_date'                 => $validated['valid_until'] ?? null,
            'notes'                    => $validated['notes'] ?? null,
            'status'                   => 'Devis',
            'type'                     => 'quote',
            'quote_number'             => $num,
            'total_amount'             => 0,
            'total_tax_amount'         => 0,
            'total_amount_with_tax'    => 0,
            'global_discount_type'     => $validated['global_discount_type'] ?? null,
            'global_discount_value'    => $validated['global_discount_value'] ?? null,
            'global_discount_amount_ht'=> 0,
        ]);

        // Optional: quote is billed directly to a corporate client
        if (!empty($validated['corporate_client_id'])) {
            $quote->corporate_client_id = $validated['corporate_client_id'];
            $quote->save();
        }


        // Recompute + persist items + totals (line discounts + global discount allocation)
        $this->recomputeInvoiceTotalsWithDiscounts(
            $quote,
            $validated['items'] ?? [],
            $validated['global_discount_type'] ?? null,
            $validated['global_discount_value'] ?? null
        );

        return $quote;
    });

    return redirect()->route('invoices.showQuote', $quote)
        ->with('success', 'Devis créé avec succès.');
}

public function editQuote(Invoice $quote)
{
    $this->authorize('update', $quote);

    $clients        = ClientProfile::where('user_id', Auth::id())->get();
    $corporateClients = CorporateClient::where('user_id', Auth::id())->get();
    $products       = Product::where('user_id', Auth::id())->get();
    $inventoryItems = InventoryItem::where('user_id', Auth::id())->get();

    return view('invoices.edit-quote', compact('quote', 'clients', 'corporateClients', 'products', 'inventoryItems'));
}
public function updateQuote(Request $request, Invoice $quote)
{
    $this->authorize('update', $quote);

    $validator = Validator::make($request->all(), [
        // Billing target (exactly one)
        'client_profile_id'    => ['nullable', Rule::exists('client_profiles', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],
        'corporate_client_id'  => ['nullable', Rule::exists('corporate_clients', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],

        'quote_date'                   => ['required', 'date'],
        'valid_until'                  => ['nullable', 'date', 'after_or_equal:quote_date'],

        'items'                        => ['required', 'array', 'min:1'],
        'items.*.type'                 => ['required', Rule::in(['product','inventory','custom'])],
        'items.*.product_id'           => ['nullable', Rule::exists('products', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],
        'items.*.inventory_item_id'    => ['nullable', Rule::exists('inventory_items', 'id')->where(fn ($q) => $q->where('user_id', Auth::id()))],
        'items.*.quantity'             => ['required', 'numeric', 'min:0.01'],
        'items.*.unit_price'           => ['nullable', 'numeric', 'min:0'], // used for custom
        'items.*.description'          => ['nullable', 'string'],
        'items.*.line_discount_type'   => ['nullable', Rule::in(['percent','amount'])],
        'items.*.line_discount_value'  => ['nullable', 'numeric', 'min:0'],

        'global_discount_type'         => ['nullable', Rule::in(['percent','amount'])],
        'global_discount_value'        => ['nullable', 'numeric', 'min:0'],
        'notes'                        => ['nullable', 'string'],
    ]);

    $validator->after(function ($validator) use ($request) {
        $hasClient = (bool) $request->input('client_profile_id');
        $hasCorp   = (bool) $request->input('corporate_client_id');

        if (!$hasClient && !$hasCorp) {
            $validator->errors()->add('client_profile_id', "Veuillez sélectionner un client OU une entreprise.");
        }

        if ($hasClient && $hasCorp) {
            $validator->errors()->add('client_profile_id', "Veuillez sélectionner soit un client, soit une entreprise (pas les deux).");
        }
    });

    $validated = $validator->validate();

    DB::transaction(function () use ($validated, $quote) {
        // Update header fields
        $quote->update([
            'client_profile_id'        => $validated['client_profile_id'] ?? null,
            'invoice_date'             => $validated['quote_date'],
            'due_date'                 => $validated['valid_until'] ?? null,
            'notes'                    => $validated['notes'] ?? null,
            'global_discount_type'     => $validated['global_discount_type'] ?? null,
            'global_discount_value'    => $validated['global_discount_value'] ?? null,
            'global_discount_amount_ht'=> 0,
        ]);

    // Set / clear corporate billing target
    $quote->corporate_client_id = $validated['corporate_client_id'] ?? null;
    $quote->save();


        // Replace items then recompute totals
        $quote->items()->delete();

        $this->recomputeInvoiceTotalsWithDiscounts(
            $quote,
            $validated['items'] ?? [],
            $validated['global_discount_type'] ?? null,
            $validated['global_discount_value'] ?? null
        );
    });

    return redirect()->route('invoices.showQuote', $quote)
        ->with('success', 'Devis mis à jour avec succès.');
}

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
        'corporateClient',
        'items.product',
        'items.inventoryItem', // <— pour les inventaires
    ]);

    $pdf = PDF::loadView('invoices.pdf_quote', ['invoice' => $invoice]);

    return $pdf->download('devis_' . ($invoice->quote_number ?? $invoice->id) . '.pdf');
}


public function sendQuoteEmail(Invoice $quote)
{
    $this->authorize('view', $quote);
$quote->load(['corporateClient', 'clientProfile.company']);

    if ($quote->type !== 'quote') {
        return redirect()->back()->with('error', 'Ce document n\'est pas un devis.');
    }
$client = $quote->clientProfile;

// Entreprise possible via:
// - devis direct corporate_client_id
// - ou client rattaché à une entreprise
$companyFromClient = $client?->company;
$company = $quote->corporateClient ?: $companyFromClient;

// 1) Email destinataire
$toEmail = null;

if ($company) {
    $toEmail = $company->billing_email ?: $company->main_contact_email;
}

if (!$toEmail && $client) {
    $toEmail = $client->email_billing ?: $client->email;
}

if (!$toEmail) {
    return redirect()->back()->with('error', 'Aucune adresse email de facturation n\'est définie (client ou entreprise).');
}

// 2) Nom du contact (pour le contenu du mail)
if ($company) {
    $contactName = trim(($company->main_contact_first_name ?? '').' '.($company->main_contact_last_name ?? ''));
    if (!$contactName) {
        $contactName = $company->trade_name ?: $company->name;
    }
} else {
    $billingFirst = $client->first_name_billing ?: $client->first_name;
    $billingLast  = $client->last_name_billing  ?: $client->last_name;
    $contactName  = trim($billingFirst.' '.$billingLast);
}

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
public function createFromPackPurchase(PackPurchase $packPurchase)
{
    // Security: pack belongs to current therapist
    if ((int) $packPurchase->user_id !== (int) Auth::id()) {
        abort(403);
    }

    // Load pack + lines
    $packPurchase->load(['pack', 'items.product', 'clientProfile']);

    if (!$packPurchase->client_profile_id) {
        return back()->with('error', 'Aucun client associé à cet achat de pack.');
    }

    // Prevent duplicates (recommended)
    $existing = Invoice::where('user_id', Auth::id())
        ->where('pack_purchase_id', $packPurchase->id)
        ->first();

    if ($existing) {
        return redirect()->route('invoices.show', $existing)
            ->with('success', 'Une facture existe déjà pour ce pack.');
    }

    $invoice = DB::transaction(function () use ($packPurchase) {

        // Get next invoice number (same logic you already use)
        $lastInvoice = Invoice::where('user_id', Auth::id())
            ->where('type', 'invoice') // keep quotes separate
            ->lockForUpdate()
            ->orderBy('invoice_number', 'desc')
            ->first();

        $nextInvoiceNumber = $lastInvoice ? $lastInvoice->invoice_number + 1 : 1;

        // Pack pricing (adapt field names if different in PackProduct)
        $pack = $packPurchase->pack; // PackProduct
        if (!$pack) {
            throw new \RuntimeException('Pack introuvable.');
        }

        // Assumptions (adjust if your PackProduct uses other fields)
        $taxRate = (float) ($pack->tax_rate ?? 0);
        // If you store TTC directly, flip the math accordingly.
        $unitPriceHt = (float) ($pack->price ?? 0);

        // Create base invoice
        $invoice = Invoice::create([
            'client_profile_id' => $packPurchase->client_profile_id,
            'user_id'           => Auth::id(),
            'invoice_date'      => now()->toDateString(),
            'due_date'          => null,
            'notes'             => 'Facture générée depuis un achat de pack.',
            'invoice_number'    => $nextInvoiceNumber,
            'total_amount'      => 0,
            'total_tax_amount'  => 0,
            'total_amount_with_tax' => 0,
            'status'            => 'En attente', // you can set Payée if you want (see below)
            'type'              => 'invoice',
            'pack_purchase_id'  => $packPurchase->id,
        ]);

        // 1) Main line: the pack
        $qty = 1;
        $taxAmount = $unitPriceHt * $qty * ($taxRate / 100);

        $invoice->items()->create([
            'type'                 => 'custom',
            'description'          => 'Pack : ' . ($pack->name ?? 'Pack'),
            'quantity'             => $qty,
            'unit_price'           => $unitPriceHt,
            'tax_rate'             => $taxRate,
            'tax_amount'           => $taxAmount,
            'total_price'          => $unitPriceHt * $qty,
            'total_price_with_tax' => ($unitPriceHt * $qty) * (1 + $taxRate / 100),
        ]);

        // 2) Optional: detail lines at 0€ for included prestations (nice on invoices)
        foreach ($packPurchase->items as $line) {
            if (!$line->product) continue;

            $invoice->items()->create([
                'type'                 => 'custom',
                'description'          => 'Inclus : ' . $line->product->name . ' × ' . (int) $line->quantity_total,
                'quantity'             => 1,
                'unit_price'           => 0,
                'tax_rate'             => 0,
                'tax_amount'           => 0,
                'total_price'          => 0,
                'total_price_with_tax' => 0,
            ]);
        }

        // Totals (only the main pack line counts)
        $totalHT  = $unitPriceHt * $qty;
        $totalTax = $taxAmount;

        $invoice->update([
            'total_amount'          => $totalHT,
            'total_tax_amount'      => $totalTax,
            'total_amount_with_tax' => $totalHT + $totalTax,
        ]);

        return $invoice;
    });

    return redirect()->route('invoices.show', $invoice)
        ->with('success', 'Facture du pack créée avec succès.');
}



/**
 * Recompute invoice totals applying:
 *  - per-line discount (percent or amount)
 *  - global discount (percent or amount) allocated proportionally across lines
 *
 * All discounts are applied on HT amounts so VAT remains correct, even with multiple VAT rates.
 *
 * @param  \App\Models\Invoice  $invoice
 * @param  array  $items
 * @param  string|null  $globalDiscountType   percent|amount|null
 * @param  float|int|string|null  $globalDiscountValue
 * @return void
 */
private function recomputeInvoiceTotalsWithDiscounts(Invoice $invoice, array $items, ?string $globalDiscountType, $globalDiscountValue): void
{
    // 1) Create items with LINE discounts applied (but not global yet)
    $lines = [];

    foreach ($items as $item) {
        $quantity = (float) ($item['quantity'] ?? 0);
        if ($quantity <= 0) {
            continue;
        }

        $desc = $item['description'] ?? '';

        // Determine line type (stay compatible with your existing payloads)
        $type = null;
        if (!empty($item['type'])) {
            $type = $item['type'];
        } elseif (!empty($item['product_id'])) {
            $type = 'product';
        } elseif (!empty($item['inventory_item_id'])) {
            $type = 'inventory';
        } else {
            $type = 'custom';
        }

        $unitPriceHt  = 0.0;
        $taxRate      = 0.0;

        $productId = null;
        $inventoryItemId = null;

        if ($type === 'product') {
            $prod = Product::where('id', $item['product_id'])
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $productId = $prod->id;
            $desc = $desc ?: $prod->name;
            $taxRate = (float) ($prod->tax_rate ?? 0);
            $unitPriceHt = (float) ($prod->price ?? 0);
        } elseif ($type === 'inventory') {
            $inv = InventoryItem::where('id', $item['inventory_item_id'])
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $inventoryItemId = $inv->id;
            $desc = $desc ?: $inv->name;

            $taxRate = (float) ($inv->vat_rate_sale ?? 0);

            // In your UI, inventory selling prices are TTC; we convert to HT for storage & computations.
            $unitPriceTtc = 0.0;
            if (($inv->unit_type ?? null) === 'ml') {
                $unitPriceTtc = (float) ($inv->selling_price_per_ml ?? 0);
            } else {
                $unitPriceTtc = (float) ($inv->selling_price ?? 0);
            }

            $unitPriceHt = $taxRate > 0 ? ($unitPriceTtc / (1 + $taxRate / 100)) : $unitPriceTtc;
        } else {
            // custom line: unit_price is assumed HT
            $unitPriceHt = (float) ($item['unit_price'] ?? 0);
            $taxRate = (float) ($item['tax_rate'] ?? 0); // if you allow custom VAT later; otherwise keep 0
            if (!$taxRate) {
                $taxRate = 0.0;
            }
        }

        $baseHt = $unitPriceHt * $quantity;

        // LINE DISCOUNT (HT)
        $lineDiscountType  = $item['line_discount_type'] ?? null; // percent|amount|null
        $lineDiscountValue = isset($item['line_discount_value']) ? (float) $item['line_discount_value'] : null;

        $lineDiscountAmountHt = 0.0;
        if ($lineDiscountType && $lineDiscountValue !== null) {
            if ($lineDiscountType === 'percent') {
                $lineDiscountAmountHt = $baseHt * ($lineDiscountValue / 100);
            } elseif ($lineDiscountType === 'amount') {
                $lineDiscountAmountHt = $lineDiscountValue;
            }
            $lineDiscountAmountHt = max(0, min($lineDiscountAmountHt, $baseHt));
        }

        $netHtAfterLine = $baseHt - $lineDiscountAmountHt;
        $taxAfterLine   = $netHtAfterLine * ($taxRate / 100);
        $ttcAfterLine   = $netHtAfterLine + $taxAfterLine;

        $created = $invoice->items()->create([
            'type' => $type,
            'product_id' => $productId,
            'inventory_item_id' => $inventoryItemId,
            'description' => $desc,
            'quantity' => $quantity,
            'unit_price' => round($unitPriceHt, 6),
            'tax_rate' => round($taxRate, 2),

            'line_discount_type' => $lineDiscountType,
            'line_discount_value' => $lineDiscountValue,
            'line_discount_amount_ht' => round($lineDiscountAmountHt, 2),
            'global_discount_amount_ht' => 0,

            'total_price_before_discount' => round($baseHt, 2),

            // temp totals (after line discount, before global discount)
            'total_price' => round($netHtAfterLine, 2),
            'tax_amount' => round($taxAfterLine, 2),
            'total_price_with_tax' => round($ttcAfterLine, 2),
        ]);

        $lines[] = [
            'id' => $created->id,
            'net_ht_after_line' => $netHtAfterLine,
        ];
    }

    // If no lines, keep totals at 0
    if (count($lines) === 0) {
        $invoice->update([
            'global_discount_amount_ht' => 0,
            'total_amount' => 0,
            'total_tax_amount' => 0,
            'total_amount_with_tax' => 0,
        ]);
        return;
    }

    $subtotalHt = array_sum(array_map(fn ($l) => $l['net_ht_after_line'], $lines));

    // 2) Compute GLOBAL DISCOUNT (HT) on subtotal after line discounts
    $globalDiscountHt = 0.0;
    if ($globalDiscountType && $globalDiscountValue !== null && $subtotalHt > 0) {
        $gv = (float) $globalDiscountValue;
        if ($globalDiscountType === 'percent') {
            $globalDiscountHt = $subtotalHt * ($gv / 100);
        } elseif ($globalDiscountType === 'amount') {
            $globalDiscountHt = $gv;
        }
        $globalDiscountHt = max(0, min($globalDiscountHt, $subtotalHt));
    }
    $globalDiscountHt = round($globalDiscountHt, 2);

    // 3) Allocate global discount proportionally across lines (fix rounding on last line)
    $allocations = [];
    $running = 0.0;
    $lastIndex = count($lines) - 1;

    foreach ($lines as $i => $l) {
        if ($subtotalHt <= 0 || $globalDiscountHt <= 0) {
            $alloc = 0.0;
        } else {
            $alloc = ($i === $lastIndex)
                ? round($globalDiscountHt - $running, 2)
                : round($globalDiscountHt * ($l['net_ht_after_line'] / $subtotalHt), 2);
        }
        $running += $alloc;
        $allocations[$l['id']] = $alloc;
    }

    // 4) Apply allocations, recompute VAT per line, sum totals
    $totalHt = 0.0;
    $totalTax = 0.0;

    foreach ($lines as $l) {
        $itemModel = $invoice->items()->find($l['id']);
        if (!$itemModel) {
            continue;
        }

        $allocHt = (float) ($allocations[$l['id']] ?? 0);
        $taxRate = (float) ($itemModel->tax_rate ?? 0);

        $htBeforeGlobal = (float) ($itemModel->total_price ?? 0); // already after line discount
        $htFinal = max(0, $htBeforeGlobal - $allocHt);

        $taxFinal = $htFinal * ($taxRate / 100);
        $ttcFinal = $htFinal + $taxFinal;

        $itemModel->update([
            'global_discount_amount_ht' => round($allocHt, 2),
            'total_price' => round($htFinal, 2),
            'tax_amount' => round($taxFinal, 2),
            'total_price_with_tax' => round($ttcFinal, 2),
        ]);

        $totalHt += $htFinal;
        $totalTax += $taxFinal;
    }

    $invoice->update([
        'global_discount_amount_ht' => $globalDiscountHt,
        'total_amount' => round($totalHt, 2),
        'total_tax_amount' => round($totalTax, 2),
        'total_amount_with_tax' => round($totalHt + $totalTax, 2),
    ]);
}

}

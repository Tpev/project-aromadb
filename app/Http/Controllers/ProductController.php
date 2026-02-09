<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Invoice;
use App\Models\BookingLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        // middleware/policies si besoin
    }

    public function index()
    {
        if (Auth::user()->license_status === 'inactive') {
            return redirect('/license-tiers/pricing');
        }

        $products = Product::where('user_id', Auth::id())
            ->orderBy('display_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'price'                => 'required|numeric|min:0',
            'tax_rate'             => 'required|numeric|min:0|max:100',
            'duration'             => 'nullable|integer|min:1',
            'mode'                 => 'required|string|in:visio,adomicile,en_entreprise,dans_le_cabinet',
            'max_per_day'          => 'nullable|integer|min:1',
            'can_be_booked_online' => 'required|boolean',
            'collect_payment'      => 'required|boolean',
            'image'                => 'nullable|image|max:5048',
            'brochure'             => 'nullable|mimes:pdf|max:10120',
            'display_order'        => 'nullable|integer|min:0',
            'requires_emargement'  => 'required|boolean',
            'visible_in_portal'    => 'required|boolean',
            'price_visible_in_portal' => 'required|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('products/images', 'public');
        }

        if ($request->hasFile('brochure')) {
            $validatedData['brochure'] = $request->file('brochure')->store('products/brochures', 'public');
        }

        $visio           = $validatedData['mode'] === 'visio';
        $adomicile       = $validatedData['mode'] === 'adomicile';
        $enEntreprise    = $validatedData['mode'] === 'en_entreprise';
        $dansLeCabinet   = $validatedData['mode'] === 'dans_le_cabinet';

        $product = Product::create([
            'user_id'               => Auth::id(),
            'name'                  => $validatedData['name'],
            'description'           => $validatedData['description'] ?? null,
            'price'                 => $validatedData['price'],
            'tax_rate'              => $validatedData['tax_rate'],
            'duration'              => $validatedData['duration'] ?? null,
            'can_be_booked_online'  => $validatedData['can_be_booked_online'],
            'collect_payment'       => $validatedData['collect_payment'],
            'visio'                 => $visio,
            'adomicile'             => $adomicile,
            'en_entreprise'         => $enEntreprise,
            'dans_le_cabinet'       => $dansLeCabinet,
            'max_per_day'           => $validatedData['max_per_day'] ?? null,
            'image'                 => $validatedData['image'] ?? null,
            'brochure'              => $validatedData['brochure'] ?? null,
            'display_order'         => $validatedData['display_order'] ?? 0,
            'requires_emargement'   => $validatedData['requires_emargement'],
            'visible_in_portal'     => $validatedData['visible_in_portal'],
            'price_visible_in_portal' => $validatedData['price_visible_in_portal'],
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Prestation créée avec succès.');
    }

    public function show(Product $product)
    {
        $invoices = Invoice::whereHas('items', function ($query) use ($product) {
            $query->where('product_id', $product->id);
        })->with('clientProfile')->get();

        // Direct booking link (active) for this product (owner-only display in the view)
        $directBookingLink = BookingLink::query()
            ->where('user_id', $product->user_id)
            ->where('is_enabled', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereJsonContains('allowed_product_ids', $product->id)
            ->orderByDesc('id')
            ->first();

        return view('products.show', compact('product', 'invoices', 'directBookingLink'));
    }

    public function edit(Product $product)
    {
        // Direct booking link (active) for this product (for the checkbox + copy link)
        $directBookingLink = BookingLink::query()
            ->where('user_id', $product->user_id)
            ->where('is_enabled', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereJsonContains('allowed_product_ids', $product->id)
            ->orderByDesc('id')
            ->first();

        return view('products.edit', compact('product', 'directBookingLink'));
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'price'                => 'required|numeric|min:0',
            'tax_rate'             => 'required|numeric|min:0|max:100',
            'duration'             => 'nullable|integer|min:1',
            'mode'                 => 'required|string|in:visio,adomicile,en_entreprise,dans_le_cabinet',
            'max_per_day'          => 'nullable|integer|min:1',
            'can_be_booked_online' => 'required|boolean',
            'collect_payment'      => 'required|boolean',

            'image'                => 'nullable|image|max:5048',
            'brochure'             => 'nullable|mimes:pdf|max:10120',
            'display_order'        => 'nullable|integer|min:0',
            'requires_emargement'  => 'required|boolean',
            'direct_booking_enabled' => 'nullable|boolean',
            'visible_in_portal'    => 'required|boolean',
            'price_visible_in_portal' => 'required|boolean',
            'remove_image'         => 'nullable|boolean',
        ]);

        // ✅ 1) If user uploads a new image: replace old
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validatedData['image'] = $request->file('image')->store('products/images', 'public');
        }
        // ✅ 2) Else if user checked remove_image: delete and null
        elseif ($request->boolean('remove_image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validatedData['image'] = null;
        }

        if ($request->hasFile('brochure')) {
            if ($product->brochure) {
                Storage::disk('public')->delete($product->brochure);
            }
            $validatedData['brochure'] = $request->file('brochure')->store('products/brochures', 'public');
        }

        $visio           = $validatedData['mode'] === 'visio';
        $adomicile       = $validatedData['mode'] === 'adomicile';
        $enEntreprise    = $validatedData['mode'] === 'en_entreprise';
        $dansLeCabinet   = $validatedData['mode'] === 'dans_le_cabinet';

        $product->update([
            'name'                 => $validatedData['name'],
            'description'          => $validatedData['description'] ?? null,
            'price'                => $validatedData['price'],
            'tax_rate'             => $validatedData['tax_rate'],
            'duration'             => $validatedData['duration'] ?? null,
            'can_be_booked_online' => $validatedData['can_be_booked_online'],
            'collect_payment'      => $validatedData['collect_payment'],

            'visio'                => $visio,
            'adomicile'            => $adomicile,
            'en_entreprise'        => $enEntreprise,
            'dans_le_cabinet'      => $dansLeCabinet,

            'max_per_day'          => $validatedData['max_per_day'] ?? null,
            'image'                => array_key_exists('image', $validatedData) ? $validatedData['image'] : $product->image,
            'brochure'             => $validatedData['brochure'] ?? $product->brochure,
            'display_order'        => $validatedData['display_order'] ?? $product->display_order,
            'requires_emargement'  => $validatedData['requires_emargement'],
            'visible_in_portal'    => $validatedData['visible_in_portal'],
            'price_visible_in_portal' => $validatedData['price_visible_in_portal'],
        ]);

        /* ---------------------- Lien réservation directe (partenaire) ---------------------- */
        $directEnabled = $request->boolean('direct_booking_enabled');

        if ($directEnabled) {
            // Find existing link (even disabled) for this product, or create a new one
            $link = BookingLink::query()
                ->where('user_id', $product->user_id)
                ->whereJsonContains('allowed_product_ids', $product->id)
                ->orderByDesc('id')
                ->first();

            if (!$link) {
                BookingLink::create([
                    'user_id' => $product->user_id,
                    'token' => BookingLink::generateToken(32),
                    'name' => 'Lien direct – ' . $product->name,
                    'allowed_product_ids' => [$product->id],
                    'is_enabled' => true,
                ]);
            } else {
                $link->update([
                    'is_enabled' => true,
                    'allowed_product_ids' => [$product->id],
                ]);
            }
        } else {
            // Disable any existing links for this product
            BookingLink::query()
                ->where('user_id', $product->user_id)
                ->whereJsonContains('allowed_product_ids', $product->id)
                ->update(['is_enabled' => false]);
        }

        return redirect()->route('products.show', $product)->with('success', 'Prestation mise à jour avec succès.');
    }

    public function destroy(Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            return redirect()->route('products.index')->with('error', 'Vous n\'êtes pas autorisé à supprimer ce produit.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produit supprimé avec succès.');
    }

    public function duplicate(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return redirect()->route('products.index')->with('error', 'Vous n\'êtes pas autorisé à dupliquer cette prestation.');
        }

        return view('products.duplicate', compact('product'));
    }

    public function storeDuplicate(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'price'                => 'required|numeric|min:0',
            'tax_rate'             => 'required|numeric|min:0|max:100',
            'duration'             => 'nullable|integer|min:1',
            'mode'                 => 'required|string|in:visio,adomicile,en_entreprise,dans_le_cabinet',
            'max_per_day'          => 'nullable|integer|min:1',
            'can_be_booked_online' => 'required|boolean',
            'collect_payment'      => 'required|boolean',
            'image'                => 'nullable|image|max:5048',
            'brochure'             => 'nullable|mimes:pdf|max:10120',
            'display_order'        => 'nullable|integer|min:0',
            'requires_emargement'  => 'required|boolean',
            'visible_in_portal'    => 'required|boolean',
            'price_visible_in_portal' => 'required|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('products/images', 'public');
        }

        if ($request->hasFile('brochure')) {
            $validatedData['brochure'] = $request->file('brochure')->store('products/brochures', 'public');
        }

        $visio           = $validatedData['mode'] === 'visio';
        $adomicile       = $validatedData['mode'] === 'adomicile';
        $enEntreprise    = $validatedData['mode'] === 'en_entreprise';
        $dansLeCabinet   = $validatedData['mode'] === 'dans_le_cabinet';

        $newProduct = $product->replicate();
        $newProduct->fill([
            'name'                 => $validatedData['name'],
            'description'          => $validatedData['description'] ?? null,
            'price'                => $validatedData['price'],
            'tax_rate'             => $validatedData['tax_rate'],
            'duration'             => $validatedData['duration'] ?? null,
            'can_be_booked_online' => $validatedData['can_be_booked_online'],
            'collect_payment'      => $validatedData['collect_payment'],
            'visio'                => $visio,
            'adomicile'            => $adomicile,
            'en_entreprise'        => $enEntreprise,
            'dans_le_cabinet'      => $dansLeCabinet,
            'max_per_day'          => $validatedData['max_per_day'] ?? null,
            'image'                => $validatedData['image'] ?? $product->image,
            'brochure'             => $validatedData['brochure'] ?? $product->brochure,
            'display_order'        => $validatedData['display_order'] ?? $product->display_order,
            'requires_emargement'  => $validatedData['requires_emargement'],
            'visible_in_portal'    => $validatedData['visible_in_portal'],
            'price_visible_in_portal' => $validatedData['price_visible_in_portal'],
        ]);

        $newProduct->save();

        return redirect()->route('products.show', $newProduct)->with('success', 'Prestation dupliquée avec succès.');
    }
}

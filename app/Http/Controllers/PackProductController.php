<?php

namespace App\Http\Controllers;

use App\Models\PackProduct;
use App\Models\PackProductItem;
use App\Models\PackPurchase;
use App\Models\PackPurchaseItem;
use App\Models\Product;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PackProductController extends Controller
{
    public function index()
    {
        if (Auth::user()->license_status === 'inactive') {
            return redirect('/license-tiers/pricing');
        }

        $packs = PackProduct::where('user_id', Auth::id())
            ->withCount('purchases')
            ->orderByDesc('id')
            ->get();

        return view('pack_products.index', compact('packs'));
    }

    public function create()
    {
        $products = Product::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('pack_products.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',

            'is_active' => 'required|boolean',
            'visible_in_portal' => 'required|boolean',
            'price_visible_in_portal' => 'required|boolean',
            'installments_enabled' => 'required|boolean',
            'allowed_installments' => 'nullable|array',
            'allowed_installments.*' => 'integer|min:2|max:12',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:999',
        ]);

        // Vérifier que les produits appartiennent au user
        $productIds = collect($validated['items'])->pluck('product_id')->unique()->values();
        $ownedCount = Product::where('user_id', Auth::id())->whereIn('id', $productIds)->count();
        if ($ownedCount !== $productIds->count()) {
            return back()->withInput()->withErrors(['items' => 'Un ou plusieurs produits ne vous appartiennent pas.']);
        }

        $allowedInstallments = collect($validated['allowed_installments'] ?? [])
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v >= 2 && $v <= 12)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ((bool) $validated['installments_enabled'] && empty($allowedInstallments)) {
            return back()->withInput()->withErrors([
                'allowed_installments' => 'Sélectionnez au moins une échéance (entre 2 et 12).',
            ]);
        }

        $pack = null;

        DB::transaction(function () use (&$pack, $validated, $allowedInstallments) {
            $pack = PackProduct::create([
                'user_id' => Auth::id(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'tax_rate' => $validated['tax_rate'],
                'is_active' => (bool) $validated['is_active'],
                'visible_in_portal' => (bool) $validated['visible_in_portal'],
                'price_visible_in_portal' => (bool) $validated['price_visible_in_portal'],
                'installments_enabled' => (bool) $validated['installments_enabled'],
                'allowed_installments' => (bool) $validated['installments_enabled'] ? $allowedInstallments : null,
            ]);

            foreach (array_values($validated['items']) as $i => $item) {
                PackProductItem::create([
                    'pack_product_id' => $pack->id,
                    'product_id' => (int) $item['product_id'],
                    'quantity' => (int) $item['quantity'],
                    'sort_order' => $i,
                ]);
            }
        });

        return redirect()->route('pack-products.show', $pack)->with('success', 'Pack créé avec succès.');
    }

    public function show(PackProduct $packProduct)
    {
        $this->ensureOwner($packProduct);

        $packProduct->load(['items.product']);

        // Pour attribuer au client depuis la page show
        $clients = ClientProfile::where('user_id', Auth::id())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $recentPurchases = $packProduct->purchases()
            ->where('user_id', Auth::id())
            ->with(['clientProfile', 'items.product'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('pack_products.show', [
            'pack' => $packProduct,
            'clients' => $clients,
            'recentPurchases' => $recentPurchases,
        ]);
    }

    public function edit(PackProduct $packProduct)
    {
        $this->ensureOwner($packProduct);

        $packProduct->load(['items']);

        $products = Product::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('pack_products.edit', [
            'pack' => $packProduct,
            'products' => $products,
        ]);
    }

    public function update(Request $request, PackProduct $packProduct)
    {
        $this->ensureOwner($packProduct);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',

            'is_active' => 'required|boolean',
            'visible_in_portal' => 'required|boolean',
            'price_visible_in_portal' => 'required|boolean',
            'installments_enabled' => 'required|boolean',
            'allowed_installments' => 'nullable|array',
            'allowed_installments.*' => 'integer|min:2|max:12',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:999',
        ]);

        $productIds = collect($validated['items'])->pluck('product_id')->unique()->values();
        $ownedCount = Product::where('user_id', Auth::id())->whereIn('id', $productIds)->count();
        if ($ownedCount !== $productIds->count()) {
            return back()->withInput()->withErrors(['items' => 'Un ou plusieurs produits ne vous appartiennent pas.']);
        }

        $allowedInstallments = collect($validated['allowed_installments'] ?? [])
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v >= 2 && $v <= 12)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ((bool) $validated['installments_enabled'] && empty($allowedInstallments)) {
            return back()->withInput()->withErrors([
                'allowed_installments' => 'Sélectionnez au moins une échéance (entre 2 et 12).',
            ]);
        }

        DB::transaction(function () use ($packProduct, $validated, $allowedInstallments) {
            $packProduct->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'tax_rate' => $validated['tax_rate'],
                'is_active' => (bool) $validated['is_active'],
                'visible_in_portal' => (bool) $validated['visible_in_portal'],
                'price_visible_in_portal' => (bool) $validated['price_visible_in_portal'],
                'installments_enabled' => (bool) $validated['installments_enabled'],
                'allowed_installments' => (bool) $validated['installments_enabled'] ? $allowedInstallments : null,
            ]);

            // Stratégie simple : on remplace tout
            $packProduct->items()->delete();

            foreach (array_values($validated['items']) as $i => $item) {
                PackProductItem::create([
                    'pack_product_id' => $packProduct->id,
                    'product_id' => (int) $item['product_id'],
                    'quantity' => (int) $item['quantity'],
                    'sort_order' => $i,
                ]);
            }
        });

        return redirect()->route('pack-products.show', $packProduct)->with('success', 'Pack mis à jour avec succès.');
    }

    public function destroy(PackProduct $packProduct)
    {
        $this->ensureOwner($packProduct);

        $packProduct->delete();

        return redirect()->route('pack-products.index')->with('success', 'Pack supprimé avec succès.');
    }

    /**
     * Attribution / achat manuel du pack pour un client
     * => crée pack_purchases + pack_purchase_items avec crédits initiaux.
     */
    public function assignToClient(Request $request, PackProduct $packProduct)
    {
        $this->ensureOwner($packProduct);

        $validated = $request->validate([
            'client_profile_id' => 'required|integer|exists:client_profiles,id',
            'purchased_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:purchased_at',
            'notes' => 'nullable|string',
        ]);

        // client doit appartenir au thérapeute
        $clientOk = ClientProfile::where('user_id', Auth::id())
            ->where('id', $validated['client_profile_id'])
            ->exists();

        if (!$clientOk) {
            return back()->withErrors(['client_profile_id' => 'Ce client ne vous appartient pas.']);
        }

        $packProduct->load('items');

        DB::transaction(function () use ($packProduct, $validated) {
            $purchase = PackPurchase::create([
                'user_id' => Auth::id(),
                'pack_product_id' => $packProduct->id,
                'client_profile_id' => (int) $validated['client_profile_id'],
                'purchased_at' => $validated['purchased_at'] ?? now(),
                'expires_at' => $validated['expires_at'] ?? null,
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($packProduct->items as $item) {
                PackPurchaseItem::create([
                    'pack_purchase_id' => $purchase->id,
                    'product_id' => $item->product_id,
                    'quantity_total' => (int) $item->quantity,
                    'quantity_remaining' => (int) $item->quantity,
                ]);
            }
        });

        return redirect()->route('pack-products.show', $packProduct)->with('success', 'Pack attribué au client avec succès.');
    }

    /**
     * Legacy-compatible assign endpoint used from client profile page.
     * Form posts {pack_product_id, purchased_at, expires_at, notes} for a fixed client profile.
     */
    public function assignFromClientProfile(Request $request, ClientProfile $clientProfile)
    {
        if ((int) $clientProfile->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'pack_product_id' => 'required|integer|exists:pack_products,id',
            'purchased_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:purchased_at',
            'notes' => 'nullable|string',
        ]);

        $packProduct = PackProduct::where('id', (int) $validated['pack_product_id'])
            ->where('user_id', Auth::id())
            ->with('items')
            ->firstOrFail();

        DB::transaction(function () use ($packProduct, $clientProfile, $validated) {
            $purchase = PackPurchase::create([
                'user_id' => Auth::id(),
                'pack_product_id' => $packProduct->id,
                'client_profile_id' => $clientProfile->id,
                'purchased_at' => $validated['purchased_at'] ?? now(),
                'expires_at' => $validated['expires_at'] ?? null,
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($packProduct->items as $item) {
                PackPurchaseItem::create([
                    'pack_purchase_id' => $purchase->id,
                    'product_id' => $item->product_id,
                    'quantity_total' => (int) $item->quantity,
                    'quantity_remaining' => (int) $item->quantity,
                ]);
            }
        });

        return redirect()->route('client_profiles.show', $clientProfile)
            ->with('success', 'Forfait attribué au client avec succès.');
    }

    private function ensureOwner(PackProduct $pack): void
    {
        if ($pack->user_id !== Auth::id()) {
            abort(403);
        }
    }
}

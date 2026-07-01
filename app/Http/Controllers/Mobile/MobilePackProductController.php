<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\PackProduct;
use App\Models\PackProductItem;
use App\Models\PackPurchase;
use App\Models\PackPurchaseItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class MobilePackProductController extends Controller
{
    public function index()
    {
        $packs = PackProduct::query()
            ->withCount(['items', 'purchases'])
            ->where('user_id', Auth::id())
            ->latest('id')
            ->get();

        return view('mobile.packs.index', compact('packs'));
    }

    public function create()
    {
        return view('mobile.packs.form', [
            'title' => 'Nouveau pack',
            'pack' => new PackProduct([
                'price' => 0,
                'tax_rate' => 0,
                'is_active' => true,
                'visible_in_portal' => true,
                'price_visible_in_portal' => true,
                'installments_enabled' => false,
            ]),
            'products' => $this->ownedProducts(),
            'action' => route('mobile.packs.store'),
            'method' => 'POST',
            'submitLabel' => 'Creer',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPackPayload($request);

        $pack = null;

        DB::transaction(function () use (&$pack, $validated) {
            $pack = PackProduct::create([
                'user_id' => Auth::id(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'tax_rate' => $validated['tax_rate'],
                'is_active' => $validated['is_active'],
                'visible_in_portal' => $validated['visible_in_portal'],
                'price_visible_in_portal' => $validated['price_visible_in_portal'],
                'installments_enabled' => $validated['installments_enabled'],
                'allowed_installments' => $validated['installments_enabled'] ? $validated['allowed_installments'] : null,
            ]);

            $this->replacePackItems($pack, $validated['items']);
        });

        return redirect()
            ->route('mobile.packs.show', $pack)
            ->with('success', 'Pack cree.');
    }

    public function show(PackProduct $packProduct)
    {
        $this->ensureOwnsPack($packProduct);

        $packProduct->load(['items.product']);
        $packProduct->loadCount(['items', 'purchases']);

        $clients = ClientProfile::query()
            ->where('user_id', Auth::id())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $recentPurchases = $packProduct->purchases()
            ->where('user_id', Auth::id())
            ->with(['clientProfile', 'items.product', 'invoice'])
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return view('mobile.packs.show', [
            'pack' => $packProduct,
            'clients' => $clients,
            'recentPurchases' => $recentPurchases,
        ]);
    }

    public function edit(PackProduct $packProduct)
    {
        $this->ensureOwnsPack($packProduct);

        $packProduct->load('items');

        return view('mobile.packs.form', [
            'title' => 'Modifier le pack',
            'pack' => $packProduct,
            'products' => $this->ownedProducts(),
            'action' => route('mobile.packs.update', $packProduct),
            'method' => 'PUT',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function update(Request $request, PackProduct $packProduct)
    {
        $this->ensureOwnsPack($packProduct);

        $validated = $this->validatedPackPayload($request);

        DB::transaction(function () use ($packProduct, $validated) {
            $packProduct->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'tax_rate' => $validated['tax_rate'],
                'is_active' => $validated['is_active'],
                'visible_in_portal' => $validated['visible_in_portal'],
                'price_visible_in_portal' => $validated['price_visible_in_portal'],
                'installments_enabled' => $validated['installments_enabled'],
                'allowed_installments' => $validated['installments_enabled'] ? $validated['allowed_installments'] : null,
            ]);

            $packProduct->items()->delete();
            $this->replacePackItems($packProduct, $validated['items']);
        });

        return redirect()
            ->route('mobile.packs.show', $packProduct)
            ->with('success', 'Pack mis a jour.');
    }

    public function destroy(PackProduct $packProduct)
    {
        $this->ensureOwnsPack($packProduct);

        $packProduct->delete();

        return redirect()
            ->route('mobile.packs.index')
            ->with('success', 'Pack supprime.');
    }

    public function assign(Request $request, PackProduct $packProduct)
    {
        $this->ensureOwnsPack($packProduct);

        $validated = $request->validate([
            'client_profile_id' => ['required', 'integer', 'exists:client_profiles,id'],
            'purchased_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:purchased_at'],
            'notes' => ['nullable', 'string'],
        ]);

        $clientOk = ClientProfile::query()
            ->where('user_id', Auth::id())
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

        return redirect()
            ->route('mobile.packs.show', $packProduct)
            ->with('success', 'Pack attribue au client.');
    }

    public function revokePurchase(PackPurchase $packPurchase)
    {
        if ((int) $packPurchase->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($packPurchase->status !== 'cancelled') {
            $data = ['status' => 'cancelled'];

            if (Schema::hasColumn('pack_purchases', 'canceled_requested_at')) {
                $data['canceled_requested_at'] = $packPurchase->canceled_requested_at ?? now();
            }

            if (Schema::hasColumn('pack_purchases', 'canceled_effective_at')) {
                $data['canceled_effective_at'] = now();
            }

            $packPurchase->update($data);
        }

        return redirect()
            ->route('mobile.packs.show', $packPurchase->pack_product_id)
            ->with('success', 'Pack client revoque.');
    }

    private function validatedPackPayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['required', 'boolean'],
            'visible_in_portal' => ['required', 'boolean'],
            'price_visible_in_portal' => ['required', 'boolean'],
            'installments_enabled' => ['required', 'boolean'],
            'allowed_installments' => ['nullable', 'array'],
            'allowed_installments.*' => ['integer', 'min:2', 'max:12'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $productIds = collect($validated['items'])->pluck('product_id')->unique()->values();
        $ownedCount = Product::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $productIds)
            ->count();

        if ($ownedCount !== $productIds->count()) {
            throw ValidationException::withMessages([
                'items' => 'Un ou plusieurs produits ne vous appartiennent pas.',
            ]);
        }

        $validated['allowed_installments'] = collect($validated['allowed_installments'] ?? [])
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value >= 2 && $value <= 12)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ((bool) $validated['installments_enabled'] && empty($validated['allowed_installments'])) {
            throw ValidationException::withMessages([
                'allowed_installments' => 'Selectionnez au moins une echeance.',
            ]);
        }

        $validated['is_active'] = (bool) $validated['is_active'];
        $validated['visible_in_portal'] = (bool) $validated['visible_in_portal'];
        $validated['price_visible_in_portal'] = (bool) $validated['price_visible_in_portal'];
        $validated['installments_enabled'] = (bool) $validated['installments_enabled'];

        return $validated;
    }

    private function replacePackItems(PackProduct $pack, array $items): void
    {
        foreach (array_values($items) as $index => $item) {
            PackProductItem::create([
                'pack_product_id' => $pack->id,
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) $item['quantity'],
                'sort_order' => $index,
            ]);
        }
    }

    private function ownedProducts()
    {
        return Product::query()
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
    }

    private function ensureOwnsPack(PackProduct $pack): void
    {
        if ((int) $pack->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}

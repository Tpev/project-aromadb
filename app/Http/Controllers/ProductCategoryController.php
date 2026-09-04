<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function index()
    {
        if (Auth::user()->license_status === 'inactive') {
            return redirect('/license-tiers/pricing');
        }

        $categories = ProductCategory::query()
            ->where('user_id', Auth::id())
            ->withCount('products')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return view('product-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedCategory($request);

        ProductCategory::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $this->ensureOwner($productCategory);

        $productCategory->update($this->validatedCategory($request, $productCategory));

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        $this->ensureOwner($productCategory);
        $productCategory->delete();

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Catégorie supprimée. Les prestations associées restent disponibles sans catégorie.');
    }

    private function validatedCategory(Request $request, ?ProductCategory $category = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('product_categories', 'name')
                    ->where(fn ($query) => $query->where('user_id', Auth::id()))
                    ->ignore($category?->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function ensureOwner(ProductCategory $category): void
    {
        abort_unless((int) $category->user_id === (int) Auth::id(), 403);
    }
}

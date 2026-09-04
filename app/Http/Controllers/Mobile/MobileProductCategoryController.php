<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MobileProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::query()
            ->where('user_id', Auth::id())
            ->withCount('products')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return view('mobile.product-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        ProductCategory::create([
            ...$this->validatedCategory($request),
            'user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('mobile.product-categories.index')
            ->with('success', 'Catégorie créée.');
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $this->ensureOwner($productCategory);
        $productCategory->update($this->validatedCategory($request, $productCategory));

        return redirect()
            ->route('mobile.product-categories.index')
            ->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        $this->ensureOwner($productCategory);
        $productCategory->delete();

        return redirect()
            ->route('mobile.product-categories.index')
            ->with('success', 'Catégorie supprimée. Les prestations restent disponibles.');
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

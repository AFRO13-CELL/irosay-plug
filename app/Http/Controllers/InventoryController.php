<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Main inventory listing — every product, filterable by category,
     * search term, status and low-stock. Serves the "Inventory" sidebar
     * link directly, and the "Apple Watches" / "Accessories" links via
     * the thin wrappers below with a preset category filter.
     */
    public function index(Request $request)
    {
        return $this->render($request);
    }

    public function watches(Request $request)
    {
        $request->merge(['category' => 'apple-watches']);

        return $this->render($request, 'Apple Watches');
    }

    public function accessories(Request $request)
    {
        // "Accessories" in the sidebar covers every non-iPhone, non-watch
        // category from the spec: AirPods, Chargers, Networking, Other.
        $slugs = ['airpods', 'chargers', 'networking', 'other-accessories'];

        return $this->render($request, 'Accessories', $slugs);
    }

    private function render(Request $request, ?string $titleOverride = null, ?array $forcedCategorySlugs = null)
    {
        $categories = Category::orderBy('name')->get();

        $query = Product::with('category');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($forcedCategorySlugs) {
            $query->whereHas('category', fn ($q) => $q->whereIn('slug', $forcedCategorySlugs));
        } elseif ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('low_stock')) {
            $query->where('tracking_type', 'quantity')->whereColumn('stock_quantity', '<=', 'min_stock_level');
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('inventory.index', [
            'products' => $products,
            'categories' => $categories,
            'title' => $titleOverride ?? 'Inventory',
            'forcedCategorySlugs' => $forcedCategorySlugs,
        ]);
    }
}

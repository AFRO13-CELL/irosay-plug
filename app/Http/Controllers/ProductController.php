<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class ProductController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $product = new Product(['tracking_type' => 'quantity', 'status' => 'active', 'min_stock_level' => 5]);

        return view('inventory.products.create', compact('categories', 'product'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $product = Product::create($data);

        AuditLog::record($request->user(), 'product.created', $product, "Created product \"{$product->name}\"");

        return redirect()->route('inventory.products.show', $product)->with('success', "Product \"{$product->name}\" created.");
    }

    public function show(Product $product)
    {
        $product->load('category', 'supplier');
        $movements = $product->stockMovements()->with('user')->latest()->paginate(15);

        return view('inventory.products.show', compact('product', 'movements'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('inventory.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);

        // stock_quantity is intentionally excluded from this form — it can
        // only change through adjustStock()/purchases/sales, each of which
        // writes a stock_movement row. Editing it here would let stock
        // drift silently, which the spec explicitly forbids.
        unset($data['stock_quantity']);

        $product->update($data);

        AuditLog::record($request->user(), 'product.updated', $product, "Updated product \"{$product->name}\"");

        return redirect()->route('inventory.products.show', $product)->with('success', "Product \"{$product->name}\" updated.");
    }

    public function destroy(Request $request, Product $product)
    {
        if ($product->phoneDevices()->exists() || $product->saleItems()->exists() || $product->purchaseItems()->exists() || $product->stockMovements()->exists()) {
            return back()->with('error', "\"{$product->name}\" has transaction history and can't be deleted — set it to Inactive instead.");
        }

        $name = $product->name;
        $product->delete();

        AuditLog::record($request->user(), 'product.deleted', null, "Deleted product \"{$name}\" (id {$product->id})");

        return redirect()->route('inventory.index')->with('success', "Product \"{$name}\" deleted.");
    }

    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'direction' => ['required', Rule::in(['increase', 'decrease'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $delta = $validated['direction'] === 'increase' ? $validated['quantity'] : -$validated['quantity'];

        try {
            $this->inventory->adjustStock(
                $product,
                $delta,
                $request->user(),
                type: 'adjustment',
                notes: $validated['notes'] ?? null,
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Stock adjusted for "' . $product->name . '".');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'sku' => [
                'nullable', 'string', 'max:255',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'description' => ['nullable', 'string'],
            'tracking_type' => ['required', Rule::in(['serialized', 'quantity'])],
            'buying_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'], // only used on create
            'min_stock_level' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['stock_quantity'] = $product ? $product->stock_quantity : ($data['stock_quantity'] ?? 0);

        return $data;
    }
}

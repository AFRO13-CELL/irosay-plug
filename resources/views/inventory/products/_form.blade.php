@if ($errors->any())
    <div class="rounded-xl bg-danger/10 border border-danger/30 text-danger px-4 py-3 text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Product Name</label>
        <input type="text" name="name" value="{{ old('name', $product->name) }}" required
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Category</label>
        <select name="category_id" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            <option value="">Select category...</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string) old('category_id', $product->category_id) === (string) $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Tracking Type</label>
        <select name="tracking_type" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm" {{ $product->exists ? 'disabled' : '' }}>
            <option value="quantity" @selected(old('tracking_type', $product->tracking_type ?? 'quantity') === 'quantity')>Quantity-tracked</option>
            <option value="serialized" @selected(old('tracking_type', $product->tracking_type ?? 'quantity') === 'serialized')>Serialized / IMEI-tracked</option>
        </select>
        @if($product->exists)
            <input type="hidden" name="tracking_type" value="{{ $product->tracking_type }}">
            <p class="text-xs text-secondary mt-1">Can't change tracking type after creation — it would orphan existing stock records.</p>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Brand</label>
        <input type="text" name="brand" value="{{ old('brand', $product->brand) }}"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Model</label>
        <input type="text" name="model" value="{{ old('model', $product->model) }}"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">SKU (optional)</label>
        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Buying Price (GH₵)</label>
        <input type="number" step="0.01" min="0" name="buying_price" value="{{ old('buying_price', $product->buying_price) }}" required
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
        @if($product->exists && $product->tracking_type === 'serialized')
            <p class="text-xs text-secondary mt-1">Reference only — each IMEI unit's actual cost is set individually when it's received via Purchases or added directly in iPhone Management.</p>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Selling Price (GH₵)</label>
        <input type="number" step="0.01" min="0" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" required
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>

    @if(!$product->exists)
        <div>
            <label class="block text-sm font-medium mb-1">Opening Stock Quantity</label>
            <input type="number" min="0" name="stock_quantity" value="{{ old('stock_quantity', 0) }}"
                   class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            <p class="text-xs text-secondary mt-1">Leave at 0 if you'll receive stock through Purchases instead.</p>
        </div>
    @endif

    <div>
        <label class="block text-sm font-medium mb-1">Minimum Stock Level</label>
        <input type="number" min="0" name="min_stock_level" value="{{ old('min_stock_level', $product->min_stock_level ?? 5) }}" required
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <select name="status" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $product->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Description (optional)</label>
        <textarea name="description" rows="3" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">{{ old('description', $product->description) }}</textarea>
    </div>
</div>

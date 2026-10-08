@if ($errors->any())
    <div class="rounded-xl bg-danger/10 border border-danger/30 text-danger px-4 py-3 text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div>
    <label class="block text-sm font-medium mb-1">Name</label>
    <input type="text" name="name" value="{{ old('name', $category->name) }}" required
           class="w-full rounded-lg border border-border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40">
</div>

<div>
    <label class="block text-sm font-medium mb-1">Tracking Type</label>
    <select name="tracking_type" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
        <option value="quantity" @selected(old('tracking_type', $category->tracking_type) === 'quantity')>Quantity-tracked (chargers, watches, etc.)</option>
        <option value="serialized" @selected(old('tracking_type', $category->tracking_type) === 'serialized')>Serialized / IMEI-tracked (iPhones)</option>
    </select>
    <p class="text-xs text-secondary mt-1">This is the default for new products in this category — each product can still override it.</p>
</div>

<div>
    <label class="block text-sm font-medium mb-1">Parent Category (optional)</label>
    <select name="parent_id" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
        <option value="">None</option>
        @foreach ($categories as $cat)
            <option value="{{ $cat->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $cat->id)>{{ $cat->name }}</option>
        @endforeach
    </select>
</div>

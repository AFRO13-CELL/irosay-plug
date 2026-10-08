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
        <label class="block text-sm font-medium mb-1">iPhone Model (product)</label>
        <select name="product_id" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            <option value="">Select model...</option>
            @foreach ($products as $p)
                <option value="{{ $p->id }}" @selected((string) old('product_id', $device->product_id) === (string) $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
        <p class="text-xs text-secondary mt-1">Only products in a serialized (IMEI-tracked) category are listed. Add a new model first in Inventory if you don't see it.</p>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Display Model Name</label>
        <input type="text" name="model" value="{{ old('model', $device->model) }}" required placeholder="e.g. iPhone 13 Pro Max 256GB"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Storage</label>
        <input type="text" name="storage" value="{{ old('storage', $device->storage) }}" placeholder="e.g. 256GB"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Color</label>
        <input type="text" name="color" value="{{ old('color', $device->color) }}"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Supplier (optional)</label>
        <select name="supplier_id" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            <option value="">None</option>
            @foreach ($suppliers as $s)
                <option value="{{ $s->id }}" @selected((string) old('supplier_id', $device->supplier_id) === (string) $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Condition</label>
        <select name="condition" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            <option value="preowned" @selected(old('condition', $device->condition) === 'preowned')>Preowned</option>
            <option value="brand_new" @selected(old('condition', $device->condition) === 'brand_new')>Brand New</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Activation Status</label>
        <select name="activation_status" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            <option value="active" @selected(old('activation_status', $device->activation_status) === 'active')>Active</option>
            <option value="non_active" @selected(old('activation_status', $device->activation_status) === 'non_active')>Non-Active</option>
            <option value="just_active" @selected(old('activation_status', $device->activation_status) === 'just_active')>Just Active</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Packaging</label>
        <select name="packaging" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            <option value="sealed" @selected(old('packaging', $device->packaging) === 'sealed')>Sealed</option>
            <option value="with_box" @selected(old('packaging', $device->packaging) === 'with_box')>With Box</option>
            <option value="without_box" @selected(old('packaging', $device->packaging) === 'without_box')>Without Box</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Battery Health (%)</label>
        <input type="number" min="0" max="100" name="battery_health" value="{{ old('battery_health', $device->battery_health) }}"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">IMEI 1</label>
        <input type="text" name="imei1" value="{{ old('imei1', $device->imei1) }}" required maxlength="20"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm font-mono">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">IMEI 2 (optional, dual-SIM)</label>
        <input type="text" name="imei2" value="{{ old('imei2', $device->imei2) }}" maxlength="20"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm font-mono">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Serial Number</label>
        <input type="text" name="serial_number" value="{{ old('serial_number', $device->serial_number) }}"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm font-mono">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Warranty</label>
        <input type="text" name="warranty" value="{{ old('warranty', $device->warranty) }}" placeholder="e.g. 3 months in-house"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Buying Price (GH₵)</label>
        <input type="number" step="0.01" min="0" name="buying_price" value="{{ old('buying_price', $device->buying_price) }}" required
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Selling Price (GH₵)</label>
        <input type="number" step="0.01" min="0" name="selling_price" value="{{ old('selling_price', $device->selling_price) }}" required
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Purchase Date</label>
        <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($device->purchase_date)->format('Y-m-d')) }}"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">{{ old('notes', $device->notes) }}</textarea>
    </div>
</div>

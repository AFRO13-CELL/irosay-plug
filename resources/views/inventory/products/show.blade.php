@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="py-6 space-y-5">
    <div class="flex items-center justify-between">
        <a href="{{ route('inventory.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Inventory</a>
        <div class="flex gap-2">
            <a href="{{ route('inventory.products.edit', $product) }}" class="px-4 py-2 rounded-lg bg-card border border-border text-sm font-semibold hover:bg-bg transition">Edit</a>
            <form method="POST" action="{{ route('inventory.products.destroy', $product) }}" onsubmit="return confirm('Delete this product? This only works if it has no transaction history.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-lg border border-danger/30 text-danger text-sm font-semibold hover:bg-danger/10 transition">Delete</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Product details --}}
        <div class="lg:col-span-2 bg-card border border-border rounded-2xl p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h1 class="text-xl font-bold">{{ $product->name }}</h1>
                    <p class="text-sm text-secondary">{{ $product->category->name }} &middot; {{ $product->brand }} {{ $product->model }}</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $product->status === 'active' ? 'bg-success/10 text-success' : 'bg-border text-secondary' }}">
                    {{ ucfirst($product->status) }}
                </span>
            </div>

            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-secondary">SKU</dt>
                    <dd class="font-medium">{{ $product->sku ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-secondary">Tracking</dt>
                    <dd class="font-medium">{{ $product->tracking_type === 'serialized' ? 'IMEI-tracked' : 'Quantity-tracked' }}</dd>
                </div>
                <div>
                    <dt class="text-secondary">Buying Price</dt>
                    <dd class="font-medium">GH₵{{ number_format($product->buying_price, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-secondary">Selling Price</dt>
                    <dd class="font-medium">GH₵{{ number_format($product->selling_price, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-secondary">Stock</dt>
                    <dd class="font-medium">
                        @if($product->tracking_type === 'serialized')
                            <a href="{{ route('iphones.index', ['product_id' => $product->id]) }}" class="text-primary hover:underline">
                                {{ $product->phoneDevices()->where('status', 'in_stock')->count() }} in stock — managed per-device
                            </a>
                        @else
                            {{ $product->stock_quantity }} @if($product->isLowStock())<span class="text-danger text-xs font-bold ml-1">LOW STOCK</span>@endif
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-secondary">Min. Stock Level</dt>
                    <dd class="font-medium">{{ $product->min_stock_level }}</dd>
                </div>
            </dl>

            @if($product->description)
                <div class="mt-4 pt-4 border-t border-border">
                    <dt class="text-secondary text-sm mb-1">Description</dt>
                    <dd class="text-sm">{{ $product->description }}</dd>
                </div>
            @endif
        </div>

        {{-- Stock adjustment --}}
        <div class="bg-card border border-border rounded-2xl p-6">
            <h2 class="font-semibold mb-4">Adjust Stock</h2>
            @if($product->tracking_type === 'serialized')
                <p class="text-sm text-secondary mb-3">Serialized products are stocked one IMEI unit at a time — manage individual devices for this model from the iPhones page.</p>
                <a href="{{ route('iphones.index', ['product_id' => $product->id]) }}" class="block text-center w-full bg-primary text-white font-semibold rounded-lg py-2 text-sm hover:bg-primarydark transition">
                    View iPhones for this Model
                </a>
            @else
                <form method="POST" action="{{ route('inventory.products.adjust-stock', $product) }}" class="space-y-3">
                    @csrf
                    <div class="flex gap-2">
                        <select name="direction" class="rounded-lg border border-border px-3 py-2 text-sm">
                            <option value="increase">Increase (+)</option>
                            <option value="decrease">Decrease (-)</option>
                        </select>
                        <input type="number" name="quantity" min="1" required placeholder="Qty"
                               class="w-24 rounded-lg border border-border px-3 py-2 text-sm">
                    </div>
                    <textarea name="notes" rows="2" placeholder="Reason (e.g. stock count correction, damaged item)"
                              class="w-full rounded-lg border border-border px-3 py-2 text-sm"></textarea>
                    <button type="submit" class="w-full bg-primary text-white font-semibold rounded-lg py-2 text-sm hover:bg-primarydark transition">
                        Record Adjustment
                    </button>
                </form>
                <p class="text-xs text-secondary mt-3">Every adjustment is logged below and in the audit trail — stock never changes silently.</p>
            @endif
        </div>
    </div>

    {{-- Movement history --}}
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">Stock Movement History</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-left px-4 py-3">Type</th>
                    <th class="text-right px-4 py-3">Change</th>
                    <th class="text-right px-4 py-3">Before &rarr; After</th>
                    <th class="text-left px-4 py-3">By</th>
                    <th class="text-left px-4 py-3">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($movements as $movement)
                    <tr>
                        <td class="px-4 py-3 text-secondary">{{ $movement->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $movement->type) }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $movement->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $movement->quantity >= 0 ? '+' : '' }}{{ $movement->quantity }}
                        </td>
                        <td class="px-4 py-3 text-right text-secondary">{{ $movement->previous_stock }} &rarr; {{ $movement->new_stock }}</td>
                        <td class="px-4 py-3">{{ $movement->user->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $movement->notes ?? $movement->reference ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-secondary">No stock movements recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $movements->links() }}
</div>
@endsection

@extends('layouts.app')

@section('title', $purchase->reference)

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('purchases.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Purchases</a>

    {{-- Header --}}
    <div class="bg-card border border-border rounded-2xl p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold">{{ $purchase->reference }}</h1>
                <p class="text-sm text-secondary">
                    <a href="{{ route('suppliers.show', $purchase->supplier) }}" class="hover:underline">{{ $purchase->supplier->name }}</a>
                    &middot; {{ $purchase->purchase_date->format('d M Y') }} &middot; recorded by {{ $purchase->user->name }}
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold
                {{ $purchase->status === 'received' ? 'bg-success/10 text-success' : ($purchase->status === 'cancelled' ? 'bg-danger/10 text-danger' : 'bg-accent/10 text-primarydark') }}">
                {{ strtoupper($purchase->status) }}
            </span>
        </div>

        <div class="grid grid-cols-3 gap-4 mt-5 text-sm">
            <div><dt class="text-secondary">Total</dt><dd class="font-medium text-base">GH₵{{ number_format($purchase->total_amount, 2) }}</dd></div>
            <div><dt class="text-secondary">Paid</dt><dd class="font-medium text-base">GH₵{{ number_format($purchase->amount_paid, 2) }}</dd></div>
            <div><dt class="text-secondary">Balance</dt><dd class="font-medium text-base {{ $purchase->balance() > 0 ? 'text-danger' : 'text-success' }}">GH₵{{ number_format($purchase->balance(), 2) }}</dd></div>
        </div>

        @if($purchase->notes)
            <p class="text-sm text-secondary mt-4 pt-4 border-t border-border">{{ $purchase->notes }}</p>
        @endif

        <div class="flex flex-wrap gap-3 mt-5 pt-5 border-t border-border">
            @if($purchase->status === 'pending')
                <form method="POST" action="{{ route('purchases.receive', $purchase) }}" onsubmit="return confirm('Mark this purchase as received? Quantity-tracked stock will update immediately.');">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg brand-gradient text-white text-sm font-semibold hover:opacity-90 transition">
                        Mark as Received
                    </button>
                </form>
                @if(!$purchase->readyToReceive())
                    <p class="text-xs text-danger self-center">Capture every IMEI device below before this can be received.</p>
                @endif

                @if($purchase->balance() > 0)
                    <form method="POST" action="{{ route('purchases.record-payment', $purchase) }}" class="flex items-center gap-2">
                        @csrf
                        <input type="number" step="0.01" min="0.01" max="{{ $purchase->balance() }}" name="amount" placeholder="Amount" required
                               class="w-32 rounded-lg border border-border px-3 py-2 text-sm">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-card border border-border text-sm font-semibold hover:bg-bg transition">Record Payment</button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    {{-- Add item --}}
    @if($purchase->status === 'pending')
        <div class="bg-card border border-border rounded-2xl p-6">
            <h2 class="font-semibold mb-3">Add Item</h2>
            <form method="POST" action="{{ route('purchases.items.store', $purchase) }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-medium text-secondary mb-1">Product</label>
                    <select name="product_id" required class="w-full rounded-lg border border-border px-3 py-2 text-sm">
                        <option value="">Select product...</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->tracking_type === 'serialized' ? 'IMEI' : 'Qty' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary mb-1">Quantity</label>
                    <input type="number" name="quantity" min="1" value="1" required class="w-24 rounded-lg border border-border px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary mb-1">Buying Price (GH₵)</label>
                    <input type="number" step="0.01" min="0" name="buying_price" required class="w-32 rounded-lg border border-border px-3 py-2 text-sm">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primarydark transition">Add</button>
            </form>
        </div>
    @endif

    {{-- Items --}}
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">Line Items</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Product</th>
                    <th class="text-right px-4 py-3">Qty</th>
                    <th class="text-right px-4 py-3">Buying Price</th>
                    <th class="text-right px-4 py-3">Line Total</th>
                    <th class="text-left px-4 py-3">Receiving Status</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($purchase->items as $item)
                    <tr class="hover:bg-bg/60 align-top">
                        <td class="px-4 py-3 font-medium">{{ $item->product->name }}</td>
                        <td class="px-4 py-3 text-right">{{ $item->quantity }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($item->buying_price, 2) }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($item->quantity * $item->buying_price, 2) }}</td>
                        <td class="px-4 py-3">
                            @if($item->product->tracking_type === 'serialized')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $item->isFullyReceived() ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                    {{ $item->devicesReceivedCount() }} of {{ $item->quantity }} IMEI captured
                                </span>
                                @if($purchase->status === 'pending' && !$item->isFullyReceived())
                                    <details class="mt-2">
                                        <summary class="text-primary text-xs cursor-pointer hover:underline">+ Capture next device</summary>
                                        <form method="POST" action="{{ route('purchases.items.receive-device', [$purchase, $item]) }}" class="mt-3 grid grid-cols-2 gap-2 max-w-md">
                                            @csrf
                                            <input type="text" name="model" value="{{ $item->product->name }}" required placeholder="Model" class="col-span-2 rounded-lg border border-border px-2 py-1.5 text-xs">
                                            <input type="text" name="storage" placeholder="Storage" class="rounded-lg border border-border px-2 py-1.5 text-xs">
                                            <input type="text" name="color" placeholder="Color" class="rounded-lg border border-border px-2 py-1.5 text-xs">
                                            <select name="condition" class="rounded-lg border border-border px-2 py-1.5 text-xs">
                                                <option value="preowned">Preowned</option>
                                                <option value="brand_new">Brand New</option>
                                            </select>
                                            <select name="activation_status" class="rounded-lg border border-border px-2 py-1.5 text-xs">
                                                <option value="active">Active</option>
                                                <option value="non_active">Non-Active</option>
                                                <option value="just_active">Just Active</option>
                                            </select>
                                            <select name="packaging" class="rounded-lg border border-border px-2 py-1.5 text-xs">
                                                <option value="sealed">Sealed</option>
                                                <option value="with_box">With Box</option>
                                                <option value="without_box">Without Box</option>
                                            </select>
                                            <input type="number" min="0" max="100" name="battery_health" placeholder="Battery %" class="rounded-lg border border-border px-2 py-1.5 text-xs">
                                            <input type="text" name="imei1" required placeholder="IMEI 1" class="col-span-2 rounded-lg border border-border px-2 py-1.5 text-xs font-mono">
                                            <input type="text" name="imei2" placeholder="IMEI 2 (optional)" class="col-span-2 rounded-lg border border-border px-2 py-1.5 text-xs font-mono">
                                            <input type="text" name="serial_number" placeholder="Serial Number" class="col-span-2 rounded-lg border border-border px-2 py-1.5 text-xs font-mono">
                                            <input type="number" step="0.01" min="0" name="selling_price" required placeholder="Selling Price (GH₵)" class="col-span-2 rounded-lg border border-border px-2 py-1.5 text-xs">
                                            <button type="submit" class="col-span-2 bg-primary text-white text-xs font-semibold rounded-lg py-1.5 hover:bg-primarydark transition">Save Device</button>
                                        </form>
                                    </details>
                                @endif
                            @else
                                <span class="text-secondary text-xs">Posts to stock on receipt</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($purchase->status === 'pending' && $item->devicesReceivedCount() === 0)
                                <form method="POST" action="{{ route('purchases.items.destroy', [$purchase, $item]) }}" onsubmit="return confirm('Remove this item?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-danger hover:underline text-xs">Remove</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-secondary">No items added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

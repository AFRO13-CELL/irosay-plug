@extends('layouts.app')

@section('title', 'Inventory Report')

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('reports.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Reports</a>
    <h1 class="text-lg font-semibold">Inventory Report</h1>

    @include('reports._filter', ['exportRoute' => 'reports.inventory.export'])
    <p class="text-xs text-secondary">Stock levels and value are a live snapshot; the date range only affects "Units Sold in Period" below.</p>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Quantity Stock Value</p><p class="text-2xl font-bold mt-1">GH₵{{ number_format($quantityStockValue, 2) }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">iPhones In Stock</p><p class="text-2xl font-bold mt-1">{{ $serializedInStockCount }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">iPhone Stock Value</p><p class="text-2xl font-bold mt-1">GH₵{{ number_format($serializedStockValue, 2) }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Units Sold in Period</p><p class="text-2xl font-bold mt-1">{{ $soldInRangeCount }}</p></div>
    </div>

    @if($lowStock->isNotEmpty())
        <div class="bg-card border border-danger/30 rounded-2xl overflow-hidden">
            <div class="px-4 py-3 border-b border-border font-semibold text-sm text-danger">Low Stock ({{ $lowStock->count() }})</div>
            <table class="w-full text-sm">
                <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                    <tr><th class="text-left px-4 py-3">Product</th><th class="text-right px-4 py-3">Stock</th><th class="text-right px-4 py-3">Minimum</th></tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($lowStock as $product)
                        <tr><td class="px-4 py-3">{{ $product->name }}</td><td class="px-4 py-3 text-right text-danger font-medium">{{ $product->stock_quantity }}</td><td class="px-4 py-3 text-right">{{ $product->min_stock_level }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">All Products</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Product</th><th class="text-left px-4 py-3">Category</th>
                    <th class="text-right px-4 py-3">Stock</th><th class="text-right px-4 py-3">Buying Price</th><th class="text-right px-4 py-3">Value</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach ($products as $product)
                    @php $serialized = $serializedByProduct->get($product->id); @endphp
                    <tr>
                        <td class="px-4 py-3">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $product->category->name }}</td>
                        <td class="px-4 py-3 text-right">
                            {{ $product->tracking_type === 'quantity' ? $product->stock_quantity : ($serialized->cnt ?? 0) }}
                        </td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($product->buying_price, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            GH₵{{ number_format($product->tracking_type === 'quantity' ? $product->stock_quantity * $product->buying_price : (float) ($serialized->val ?? 0), 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

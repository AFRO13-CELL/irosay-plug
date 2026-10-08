@extends('layouts.app')

@section('title', 'iPhone Report')

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('reports.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Reports</a>
    <h1 class="text-lg font-semibold">iPhone Report</h1>

    @include('reports._filter', ['exportRoute' => 'reports.iphones.export'])
    <p class="text-xs text-secondary">"In Stock" is a live count; the other figures apply to the selected date range.</p>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Purchased in Period</p><p class="text-2xl font-bold mt-1">{{ $summary['total_purchased'] }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Purchase Cost</p><p class="text-2xl font-bold mt-1">GH₵{{ number_format($summary['total_purchase_cost'], 2) }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Currently In Stock</p><p class="text-2xl font-bold mt-1">{{ $summary['total_in_stock'] }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Sold in Period</p><p class="text-2xl font-bold mt-1">{{ $summary['total_sold'] }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Sales Revenue</p><p class="text-2xl font-bold mt-1">GH₵{{ number_format($summary['total_revenue'], 2) }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Gross Profit</p><p class="text-2xl font-bold mt-1 text-success">GH₵{{ number_format($summary['total_gross_profit'], 2) }}</p></div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">Sold Devices</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Model</th><th class="text-left px-4 py-3">IMEI</th>
                    <th class="text-right px-4 py-3">Selling Price</th><th class="text-right px-4 py-3">Buying Price</th><th class="text-right px-4 py-3">Profit</th>
                    <th class="text-left px-4 py-3">Customer</th><th class="text-left px-4 py-3">Seller</th><th class="text-left px-4 py-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($soldItems as $item)
                    <tr>
                        <td class="px-4 py-3">{{ $item->product->name }} <span class="text-xs text-secondary">({{ $item->phoneDevice->storage }})</span></td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $item->phoneDevice->imei1 }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($item->cost_price, 2) }}</td>
                        <td class="px-4 py-3 text-right text-success">GH₵{{ number_format($item->profit(), 2) }}</td>
                        <td class="px-4 py-3">{{ $item->sale->customer->name ?? 'Walk-in' }}</td>
                        <td class="px-4 py-3">{{ $item->sale->user->name }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $item->sale->sold_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-secondary">No iPhones sold in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

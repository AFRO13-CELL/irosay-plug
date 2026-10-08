@extends('layouts.app')

@section('title', 'Sales Report')

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('reports.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Reports</a>
    <h1 class="text-lg font-semibold">Sales Report</h1>

    @include('reports._filter', ['exportRoute' => 'reports.sales.export'])

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Sales</p><p class="text-2xl font-bold mt-1">{{ $summary['count'] }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Revenue</p><p class="text-2xl font-bold mt-1">GH₵{{ number_format($summary['total'], 2) }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Discounts Given</p><p class="text-2xl font-bold mt-1">GH₵{{ number_format($summary['discount'], 2) }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Gross Profit</p><p class="text-2xl font-bold mt-1 text-success">GH₵{{ number_format($summary['profit'], 2) }}</p></div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">Daily Breakdown</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr><th class="text-left px-4 py-3">Date</th><th class="text-right px-4 py-3">Sales</th><th class="text-right px-4 py-3">Revenue</th></tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($daily as $day)
                    <tr><td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($day['date'])->format('d M Y') }}</td><td class="px-4 py-3 text-right">{{ $day['count'] }}</td><td class="px-4 py-3 text-right">GH₵{{ number_format($day['total'], 2) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-secondary">No sales in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">All Sales</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Invoice #</th><th class="text-left px-4 py-3">Customer</th><th class="text-left px-4 py-3">Date</th>
                    <th class="text-right px-4 py-3">Total</th><th class="text-right px-4 py-3">Profit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($sales as $sale)
                    <tr class="hover:bg-bg/60 cursor-pointer" onclick="window.location='{{ route('sales.show', $sale) }}'">
                        <td class="px-4 py-3 text-primary font-medium">{{ $sale->invoice_number }}</td>
                        <td class="px-4 py-3">{{ $sale->customer->name ?? 'Walk-in' }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $sale->sold_at->format('d M Y, H:i') }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($sale->total, 2) }}</td>
                        <td class="px-4 py-3 text-right text-success">GH₵{{ number_format($sale->grossProfit(), 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-secondary">No sales in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Best-Selling Products')

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('reports.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Reports</a>
    <h1 class="text-lg font-semibold">Best-Selling Products</h1>

    @include('reports._filter', ['exportRoute' => 'reports.best-selling.export'])

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr><th class="text-left px-4 py-3">#</th><th class="text-left px-4 py-3">Product</th><th class="text-right px-4 py-3">Qty Sold</th><th class="text-right px-4 py-3">Revenue</th><th class="text-right px-4 py-3">Profit</th></tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($rows as $i => $row)
                    <tr>
                        <td class="px-4 py-3 text-secondary">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-medium">{{ $row->product->name }}</td>
                        <td class="px-4 py-3 text-right">{{ $row->qty_sold }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($row->revenue, 2) }}</td>
                        <td class="px-4 py-3 text-right text-success">GH₵{{ number_format($row->profit, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-secondary">No sales in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

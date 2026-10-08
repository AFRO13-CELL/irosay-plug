@extends('layouts.app')

@section('title', 'Purchase Report')

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('reports.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Reports</a>
    <h1 class="text-lg font-semibold">Purchase Report</h1>

    @include('reports._filter', ['exportRoute' => 'reports.purchases.export'])

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Purchases</p><p class="text-2xl font-bold mt-1">{{ $summary['count'] }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Total Cost</p><p class="text-2xl font-bold mt-1">GH₵{{ number_format($summary['total_cost'], 2) }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Total Paid</p><p class="text-2xl font-bold mt-1">GH₵{{ number_format($summary['total_paid'], 2) }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Balance Owed</p><p class="text-2xl font-bold mt-1 {{ $summary['total_balance'] > 0 ? 'text-danger' : '' }}">GH₵{{ number_format($summary['total_balance'], 2) }}</p></div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Reference</th><th class="text-left px-4 py-3">Supplier</th><th class="text-left px-4 py-3">Date</th>
                    <th class="text-right px-4 py-3">Total</th><th class="text-right px-4 py-3">Balance</th><th class="text-left px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($purchases as $purchase)
                    <tr class="hover:bg-bg/60 cursor-pointer" onclick="window.location='{{ route('purchases.show', $purchase) }}'">
                        <td class="px-4 py-3 text-primary font-medium">{{ $purchase->reference }}</td>
                        <td class="px-4 py-3">{{ $purchase->supplier->name }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $purchase->purchase_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($purchase->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $purchase->balance() > 0 ? 'text-danger' : '' }}">GH₵{{ number_format($purchase->balance(), 2) }}</td>
                        <td class="px-4 py-3 capitalize">{{ $purchase->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-secondary">No purchases in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

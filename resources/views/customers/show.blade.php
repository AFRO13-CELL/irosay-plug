@extends('layouts.app')

@section('title', $customer->name)

@section('content')
<div class="py-6 space-y-5">
    <div class="flex items-center justify-between">
        <a href="{{ route('customers.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Customers</a>
        <div class="flex gap-2">
            <a href="{{ route('pos.index') }}" class="px-4 py-2 rounded-lg bg-card border border-border text-sm font-semibold hover:bg-bg transition">New Sale</a>
            <a href="{{ route('customers.edit', $customer) }}" class="px-4 py-2 rounded-lg bg-card border border-border text-sm font-semibold hover:bg-bg transition">Edit</a>
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl p-6">
        <h1 class="text-xl font-bold mb-4">{{ $customer->name }}</h1>
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div><dt class="text-secondary">Phone</dt><dd class="font-medium">{{ $customer->phone ?? '—' }}</dd></div>
            <div><dt class="text-secondary">Email</dt><dd class="font-medium">{{ $customer->email ?? '—' }}</dd></div>
            <div><dt class="text-secondary">Last Purchase</dt><dd class="font-medium">{{ $customer->lastSaleAt() ?? '—' }}</dd></div>
            <div><dt class="text-secondary">Address</dt><dd class="font-medium">{{ $customer->address ?? '—' }}</dd></div>
        </dl>
        @if($customer->notes)
            <div class="mt-4 pt-4 border-t border-border">
                <dt class="text-secondary text-sm mb-1">Notes</dt>
                <dd class="text-sm">{{ $customer->notes }}</dd>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-card border border-border rounded-2xl p-5">
            <p class="text-sm text-secondary">Total Spent</p>
            <p class="text-2xl font-bold mt-1">GH₵{{ number_format($customer->totalSpent(), 2) }}</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-5">
            <p class="text-sm text-secondary">Outstanding Balance</p>
            <p class="text-2xl font-bold mt-1 {{ $customer->outstandingBalance() > 0 ? 'text-danger' : '' }}">GH₵{{ number_format($customer->outstandingBalance(), 2) }}</p>
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">Purchase History</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Invoice #</th>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-right px-4 py-3">Items</th>
                    <th class="text-right px-4 py-3">Total</th>
                    <th class="text-right px-4 py-3">Balance</th>
                    <th class="text-left px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($sales as $sale)
                    <tr class="hover:bg-bg/60 cursor-pointer" onclick="window.location='{{ route('sales.show', $sale) }}'">
                        <td class="px-4 py-3 font-medium text-primary">{{ $sale->invoice_number }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $sale->sold_at->format('d M Y, H:i') }}</td>
                        <td class="px-4 py-3 text-right">{{ $sale->items->count() }}</td>
                        <td class="px-4 py-3 text-right font-medium">GH₵{{ number_format($sale->total, 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $sale->balance > 0 ? 'text-danger' : '' }}">GH₵{{ number_format($sale->balance, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sale->status === 'completed' ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                {{ ucfirst(str_replace('_',' ',$sale->status)) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-secondary">No purchases yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $sales->links() }}
</div>
@endsection

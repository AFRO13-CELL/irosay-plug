@extends('layouts.app')

@section('title', $supplier->name)

@section('content')
<div class="py-6 space-y-5">
    <div class="flex items-center justify-between">
        <a href="{{ route('suppliers.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Suppliers</a>
        <a href="{{ route('suppliers.edit', $supplier) }}" class="px-4 py-2 rounded-lg bg-card border border-border text-sm font-semibold hover:bg-bg transition">Edit</a>
    </div>

    <div class="bg-card border border-border rounded-2xl p-6">
        <h1 class="text-xl font-bold mb-4">{{ $supplier->name }}</h1>
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-secondary">Phone</dt><dd class="font-medium">{{ $supplier->phone ?? '—' }}</dd></div>
            <div><dt class="text-secondary">Email</dt><dd class="font-medium">{{ $supplier->email ?? '—' }}</dd></div>
            <div><dt class="text-secondary">Location</dt><dd class="font-medium">{{ $supplier->location ?? '—' }}</dd></div>
            <div><dt class="text-secondary">Amount Owed</dt><dd class="font-medium">GH₵{{ number_format($supplier->amount_owed, 2) }}</dd></div>
        </dl>
        @if($supplier->notes)
            <div class="mt-4 pt-4 border-t border-border">
                <dt class="text-secondary text-sm mb-1">Notes</dt>
                <dd class="text-sm">{{ $supplier->notes }}</dd>
            </div>
        @endif
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">Purchase History</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Reference</th>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-right px-4 py-3">Total</th>
                    <th class="text-right px-4 py-3">Paid</th>
                    <th class="text-left px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($purchases as $purchase)
                    <tr class="hover:bg-bg/60">
                        <td class="px-4 py-3"><a href="{{ route('purchases.show', $purchase) }}" class="text-primary hover:underline">{{ $purchase->reference }}</a></td>
                        <td class="px-4 py-3 text-secondary">{{ $purchase->purchase_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($purchase->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($purchase->amount_paid, 2) }}</td>
                        <td class="px-4 py-3 capitalize">{{ $purchase->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-secondary">No purchases from this supplier yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $purchases->links() }}
</div>
@endsection

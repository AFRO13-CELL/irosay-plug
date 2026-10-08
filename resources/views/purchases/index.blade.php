@extends('layouts.app')

@section('title', 'Purchases')

@section('content')
<div class="py-6 space-y-5">
    <div class="flex items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <select name="status" onchange="this.form.submit()" class="rounded-lg border border-border px-3 py-2 text-sm">
                <option value="">All statuses</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="received" @selected(request('status') === 'received')>Received</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
            </select>
        </form>
        <a href="{{ route('purchases.create') }}" class="px-4 py-2 rounded-lg brand-gradient text-white text-sm font-semibold shadow-sm hover:opacity-90 transition">+ Add Purchase</a>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Reference</th>
                    <th class="text-left px-4 py-3">Supplier</th>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-right px-4 py-3">Total</th>
                    <th class="text-right px-4 py-3">Balance</th>
                    <th class="text-left px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($purchases as $purchase)
                    <tr class="hover:bg-bg/60 cursor-pointer" onclick="window.location='{{ route('purchases.show', $purchase) }}'">
                        <td class="px-4 py-3 font-medium text-primary">{{ $purchase->reference }}</td>
                        <td class="px-4 py-3">{{ $purchase->supplier->name }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $purchase->purchase_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($purchase->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $purchase->balance() > 0 ? 'text-danger' : '' }}">GH₵{{ number_format($purchase->balance(), 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $purchase->status === 'received' ? 'bg-success/10 text-success' : ($purchase->status === 'cancelled' ? 'bg-danger/10 text-danger' : 'bg-accent/10 text-primarydark') }}">
                                {{ ucfirst($purchase->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-secondary">No purchases yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $purchases->links() }}
</div>
@endsection

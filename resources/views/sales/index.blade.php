@extends('layouts.app')

@section('title', 'Sales')

@section('content')
<div class="py-6 space-y-5">
    <div class="flex items-center justify-between gap-3">
        <form method="GET" class="flex-1 max-w-sm">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoice # or customer..."
                   class="w-full rounded-lg border border-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40">
        </form>
        <div class="flex gap-2">
            <a href="{{ route('returns.index') }}" class="px-4 py-2 rounded-lg bg-card border border-border text-sm font-semibold hover:bg-bg transition">View Returns</a>
            <a href="{{ route('pos.index') }}" class="px-4 py-2 rounded-lg brand-gradient text-white text-sm font-semibold shadow-sm hover:opacity-90 transition">+ New Sale</a>
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Invoice #</th>
                    <th class="text-left px-4 py-3">Customer</th>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-left px-4 py-3">Cashier</th>
                    <th class="text-right px-4 py-3">Total</th>
                    <th class="text-left px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($sales as $sale)
                    <tr class="hover:bg-bg/60 cursor-pointer" onclick="window.location='{{ route('sales.show', $sale) }}'">
                        <td class="px-4 py-3 font-medium text-primary">{{ $sale->invoice_number }}</td>
                        <td class="px-4 py-3">{{ $sale->customer->name ?? 'Walk-in' }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $sale->sold_at->format('d M Y, H:i') }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $sale->user->name }}</td>
                        <td class="px-4 py-3 text-right font-medium">GH₵{{ number_format($sale->total, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sale->status === 'completed' ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                {{ ucfirst(str_replace('_',' ',$sale->status)) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-secondary">No sales recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $sales->links() }}
</div>
@endsection

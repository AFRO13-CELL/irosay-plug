@extends('layouts.app')

@section('title', 'Stock Movements')

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('inventory.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Inventory</a>

    <form method="GET" class="bg-card border border-border rounded-2xl p-4 flex flex-wrap items-end gap-3">
        <div class="min-w-[180px]">
            <label class="block text-xs font-medium text-secondary mb-1">Movement Type</label>
            <select name="type" class="w-full rounded-lg border border-border px-3 py-2 text-sm">
                <option value="">All types</option>
                @foreach (['purchase','sale','return','adjustment','reservation','reservation_cancelled'] as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst(str_replace('_',' ', $type)) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primarydark transition">Filter</button>
        @if(request()->filled('type'))
            <a href="{{ route('inventory.stock-movements.index') }}" class="px-4 py-2 rounded-lg border border-border text-sm font-semibold hover:bg-bg transition">Reset</a>
        @endif
    </form>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <p class="px-4 py-3 border-b border-border text-sm text-secondary">
            This is an append-only ledger — every stock change ever made gets a row here and nothing is edited or deleted, per the spec's audit rules.
        </p>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-left px-4 py-3">Product</th>
                    <th class="text-left px-4 py-3">Type</th>
                    <th class="text-right px-4 py-3">Change</th>
                    <th class="text-right px-4 py-3">Before &rarr; After</th>
                    <th class="text-left px-4 py-3">By</th>
                    <th class="text-left px-4 py-3">Reference / Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($movements as $movement)
                    <tr>
                        <td class="px-4 py-3 text-secondary whitespace-nowrap">{{ $movement->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('inventory.products.show', $movement->product) }}" class="text-primary hover:underline">
                                {{ $movement->product->name }}
                            </a>
                            @if($movement->phoneDevice)
                                <span class="text-xs text-secondary block">IMEI {{ $movement->phoneDevice->imei1 }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $movement->type) }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $movement->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $movement->quantity >= 0 ? '+' : '' }}{{ $movement->quantity }}
                        </td>
                        <td class="px-4 py-3 text-right text-secondary">{{ $movement->previous_stock }} &rarr; {{ $movement->new_stock }}</td>
                        <td class="px-4 py-3">{{ $movement->user->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $movement->reference ?? $movement->notes ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-secondary">
                            No stock movements yet — try adjusting a product's stock, receiving a purchase, or completing a sale, and it will show up here automatically.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $movements->links() }}
</div>
@endsection

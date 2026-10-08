@extends('layouts.app')

@section('title', 'iPhones')

@section('content')
<div class="py-6 space-y-5">
    <div class="flex items-center justify-end">
        <a href="{{ route('iphones.create') }}" class="px-4 py-2 rounded-lg brand-gradient text-white text-sm font-semibold shadow-sm hover:opacity-90 transition">+ Add iPhone</a>
    </div>

    {{-- Per-model summary, per spec section 9's example --}}
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">By Model</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Model</th>
                    <th class="text-right px-4 py-3">In Stock</th>
                    <th class="text-right px-4 py-3">Reserved</th>
                    <th class="text-right px-4 py-3">Sold</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($models as $model)
                    <tr class="hover:bg-bg/60">
                        <td class="px-4 py-3 font-medium">{{ $model->name }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('iphones.index', ['product_id' => $model->id, 'status' => 'in_stock']) }}" class="text-primary hover:underline">
                                {{ $model->in_stock_count }} units
                            </a>
                        </td>
                        <td class="px-4 py-3 text-right text-secondary">{{ $model->reserved_count }}</td>
                        <td class="px-4 py-3 text-right text-secondary">{{ $model->sold_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-secondary">No iPhone models found — check the iPhones category in Inventory.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Individual devices --}}
    <form method="GET" class="bg-card border border-border rounded-2xl p-4 flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs font-medium text-secondary mb-1">Search IMEI / Serial / Color</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="w-full rounded-lg border border-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40">
        </div>
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-secondary mb-1">Status</label>
            <select name="status" class="w-full rounded-lg border border-border px-3 py-2 text-sm">
                <option value="">All</option>
                @foreach (['in_stock' => 'In Stock', 'reserved' => 'Reserved', 'sold' => 'Sold', 'returned' => 'Returned'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primarydark transition">Filter</button>
        @if(request()->anyFilled(['search','status','product_id']))
            <a href="{{ route('iphones.index') }}" class="px-4 py-2 rounded-lg border border-border text-sm font-semibold hover:bg-bg transition">Reset</a>
        @endif
    </form>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Model</th>
                    <th class="text-left px-4 py-3">IMEI 1</th>
                    <th class="text-left px-4 py-3">Color</th>
                    <th class="text-left px-4 py-3">Condition</th>
                    <th class="text-right px-4 py-3">Selling Price</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($devices as $device)
                    <tr class="hover:bg-bg/60">
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $device->product->name }}</p>
                            <p class="text-xs text-secondary">{{ $device->storage }}</p>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $device->imei1 }}</td>
                        <td class="px-4 py-3">{{ $device->color ?? '—' }}</td>
                        <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $device->condition) }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($device->selling_price, 2) }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusColor = match($device->status) {
                                    'in_stock' => 'bg-success/10 text-success',
                                    'reserved' => 'bg-accent/10 text-primarydark',
                                    'sold' => 'bg-border text-secondary',
                                    default => 'bg-danger/10 text-danger',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">{{ strtoupper(str_replace('_',' ', $device->status)) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('iphones.show', $device) }}" class="text-primary hover:underline">View</a>
                            <a href="{{ route('iphones.edit', $device) }}" class="text-primary hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-secondary">No devices match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $devices->links() }}
</div>
@endsection

@extends('layouts.app')

@section('title', $device->model)

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('iphones.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to iPhones</a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-card border border-border rounded-2xl p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h1 class="text-xl font-bold">{{ $device->model }}</h1>
                    <p class="text-sm text-secondary">{{ $device->storage }} &middot; {{ $device->color }}</p>
                </div>
                @php
                    $statusColor = match($device->status) {
                        'in_stock' => 'bg-success/10 text-success',
                        'reserved' => 'bg-accent/10 text-primarydark',
                        'sold' => 'bg-border text-secondary',
                        default => 'bg-danger/10 text-danger',
                    };
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusColor }}">{{ strtoupper(str_replace('_',' ', $device->status)) }}</span>
            </div>

            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-secondary">Condition</dt><dd class="font-medium capitalize">{{ str_replace('_',' ', $device->condition) }}</dd></div>
                <div><dt class="text-secondary">Activation</dt><dd class="font-medium capitalize">{{ str_replace('_',' ', $device->activation_status) }}</dd></div>
                <div><dt class="text-secondary">Packaging</dt><dd class="font-medium capitalize">{{ str_replace('_',' ', $device->packaging) }}</dd></div>
                <div><dt class="text-secondary">Battery Health</dt><dd class="font-medium">{{ $device->battery_health !== null ? $device->battery_health.'%' : '—' }}</dd></div>
                <div><dt class="text-secondary">IMEI 1</dt><dd class="font-mono font-medium">{{ $device->imei1 }}</dd></div>
                <div><dt class="text-secondary">IMEI 2</dt><dd class="font-mono font-medium">{{ $device->imei2 ?? '—' }}</dd></div>
                <div><dt class="text-secondary">Serial Number</dt><dd class="font-mono font-medium">{{ $device->serial_number ?? '—' }}</dd></div>
                <div><dt class="text-secondary">Warranty</dt><dd class="font-medium">{{ $device->warranty ?? '—' }}</dd></div>
                <div><dt class="text-secondary">Buying Price</dt><dd class="font-medium">GH₵{{ number_format($device->buying_price, 2) }}</dd></div>
                <div><dt class="text-secondary">Selling Price</dt><dd class="font-medium">GH₵{{ number_format($device->selling_price, 2) }}</dd></div>
                <div><dt class="text-secondary">Supplier</dt><dd class="font-medium">{{ $device->supplier->name ?? '—' }}</dd></div>
                <div><dt class="text-secondary">Purchase Date</dt><dd class="font-medium">{{ optional($device->purchase_date)->format('d M Y') ?? '—' }}</dd></div>
            </dl>

            @if($device->notes)
                <div class="mt-4 pt-4 border-t border-border">
                    <dt class="text-secondary text-sm mb-1">Notes</dt>
                    <dd class="text-sm">{{ $device->notes }}</dd>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="bg-card border border-border rounded-2xl p-6 space-y-3">
            <h2 class="font-semibold mb-2">Actions</h2>

            <a href="{{ route('iphones.edit', $device) }}" class="block text-center w-full bg-card border border-border font-semibold rounded-lg py-2 text-sm hover:bg-bg transition">
                Edit
            </a>

            @if($device->status === 'in_stock')
                <form method="POST" action="{{ route('iphones.reserve', $device) }}">
                    @csrf
                    <button type="submit" class="w-full bg-primary text-white font-semibold rounded-lg py-2 text-sm hover:bg-primarydark transition">
                        Reserve
                    </button>
                </form>
            @elseif($device->status === 'reserved')
                <form method="POST" action="{{ route('iphones.cancel-reservation', $device) }}">
                    @csrf
                    <button type="submit" class="w-full bg-card border border-border font-semibold rounded-lg py-2 text-sm hover:bg-bg transition">
                        Cancel Reservation
                    </button>
                </form>
            @endif

            @if($device->status === 'in_stock')
                <form method="POST" action="{{ route('pos.cart.add-device') }}">
                    @csrf
                    <input type="hidden" name="phone_device_id" value="{{ $device->id }}">
                    <button type="submit" class="w-full bg-primary text-white font-semibold rounded-lg py-2 text-sm hover:bg-primarydark transition">
                        Sell (add to POS cart)
                    </button>
                </form>
            @else
                <button type="button" disabled title="Only an in-stock device can be sold"
                        class="w-full bg-border text-secondary font-semibold rounded-lg py-2 text-sm cursor-not-allowed">
                    Sell
                </button>
            @endif

            @if($device->status === 'sold' && $device->saleItem && auth()->user()->hasPermission('returns.process'))
                <a href="{{ route('returns.create', $device->saleItem->sale) }}" class="block text-center w-full border border-danger/30 text-danger font-semibold rounded-lg py-2 text-sm hover:bg-danger/10 transition">
                    Process Return
                </a>
            @elseif($device->status === 'returned')
                <button type="button" disabled class="w-full bg-border text-secondary font-semibold rounded-lg py-2 text-sm cursor-not-allowed">
                    Already Returned
                </button>
            @else
                <button type="button" disabled title="Only a sold device with return permission can be returned here"
                        class="w-full bg-border text-secondary font-semibold rounded-lg py-2 text-sm cursor-not-allowed">
                    Return
                </button>
            @endif
        </div>
    </div>

    {{-- History --}}
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">Device History</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-left px-4 py-3">Event</th>
                    <th class="text-left px-4 py-3">By</th>
                    <th class="text-left px-4 py-3">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($movements as $movement)
                    <tr>
                        <td class="px-4 py-3 text-secondary whitespace-nowrap">{{ $movement->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $movement->type) }}</td>
                        <td class="px-4 py-3">{{ $movement->user->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $movement->notes ?? $movement->reference ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-secondary">No history yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

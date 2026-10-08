@extends('layouts.app')

@section('title', 'Select IMEI — ' . $product->name)

@section('content')
<div class="py-6 max-w-2xl">
    <a href="{{ route('pos.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to POS</a>

    <div class="bg-card border border-border rounded-2xl p-6 mt-4">
        <h1 class="text-lg font-semibold mb-1">{{ $product->name }}</h1>
        <p class="text-sm text-secondary mb-4">Choose the exact device to add to the cart. Only in-stock units are listed — sold, reserved, and returned devices never appear here.</p>

        <div class="space-y-2">
            @forelse ($devices as $device)
                <div class="flex items-center justify-between border border-border rounded-xl p-3 {{ in_array($device->id, $inCart) ? 'opacity-50' : '' }}">
                    <div>
                        <p class="font-medium text-sm">IMEI {{ $device->imei1 }}</p>
                        <p class="text-xs text-secondary">{{ $device->storage }} &middot; {{ $device->color }} &middot; {{ ucfirst(str_replace('_',' ',$device->condition)) }} &middot; Battery {{ $device->battery_health ?? '—' }}%</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-sm">GH₵{{ number_format($device->selling_price, 2) }}</span>
                        @if(in_array($device->id, $inCart))
                            <span class="text-xs text-secondary">Already in cart</span>
                        @else
                            <form method="POST" action="{{ route('pos.cart.add-device') }}">
                                @csrf
                                <input type="hidden" name="phone_device_id" value="{{ $device->id }}">
                                <button type="submit" class="bg-primary text-white text-xs font-semibold rounded-lg px-3 py-2 hover:bg-primarydark transition">Add to Cart</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center text-secondary py-10">No in-stock units of this model right now — receive more via Purchases.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

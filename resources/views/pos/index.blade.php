@extends('layouts.app')

@section('title', 'POS')

@section('content')
<div class="py-6 grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Product search & grid --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold">Point of Sale</h1>
            <a href="{{ route('sales.index') }}" class="text-sm text-primary hover:underline">View Sales History</a>
        </div>

        <form method="GET" class="bg-card border border-border rounded-2xl p-4 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-secondary mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Product name or brand..."
                       class="w-full rounded-lg border border-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40">
            </div>
            <div class="min-w-[180px]">
                <label class="block text-xs font-medium text-secondary mb-1">Category</label>
                <select name="category" onchange="this.form.submit()" class="w-full rounded-lg border border-border px-3 py-2 text-sm">
                    <option value="">All categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primarydark transition">Search</button>
        </form>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @forelse ($products as $product)
                <div class="bg-card border border-border rounded-xl p-3 flex flex-col">
                    <p class="font-medium text-sm leading-tight">{{ $product->name }}</p>
                    <p class="text-xs text-secondary mb-2">{{ $product->category->name }}</p>
                    <p class="font-bold text-sm mb-2">GH₵{{ number_format($product->selling_price, 2) }}</p>

                    @if($product->tracking_type === 'serialized')
                        <a href="{{ route('pos.select-device', $product) }}" class="mt-auto text-center bg-primary text-white text-xs font-semibold rounded-lg py-2 hover:bg-primarydark transition">
                            Select IMEI
                        </a>
                    @else
                        <form method="POST" action="{{ route('pos.cart.add-product') }}" class="mt-auto flex gap-1">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="number" name="quantity" value="1" min="1" max="{{ max(1, $product->stock_quantity) }}" class="w-14 rounded-lg border border-border px-2 py-1.5 text-xs">
                            <button type="submit" @if($product->stock_quantity < 1) disabled @endif
                                    class="flex-1 bg-primary text-white text-xs font-semibold rounded-lg py-1.5 hover:bg-primarydark transition disabled:opacity-40 disabled:cursor-not-allowed">
                                {{ $product->stock_quantity < 1 ? 'Out of stock' : 'Add' }}
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="col-span-full text-center text-secondary py-10">No products match this search.</p>
            @endforelse
        </div>
        {{ $products->links() }}
    </div>

    {{-- Cart --}}
    <div class="bg-card border border-border rounded-2xl p-5 h-fit sticky top-20 space-y-4">
        <h2 class="font-semibold">Cart</h2>

        {{-- Customer --}}
        <div class="border border-border rounded-xl p-3">
            @if($customer)
                <p class="text-sm">Customer: <a href="{{ route('customers.show', $customer) }}" class="font-medium text-primary hover:underline">{{ $customer->name }}</a></p>
                <form method="POST" action="{{ route('pos.customer') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="text-xs text-danger hover:underline">Remove customer (walk-in)</button>
                </form>
            @else
                <p class="text-sm text-secondary mb-2">Walk-in customer</p>
                <details>
                    <summary class="text-xs text-primary cursor-pointer hover:underline">+ Add / select customer</summary>
                    <div class="mt-2 space-y-3">
                        @if($existingCustomers->isNotEmpty())
                            <form method="POST" action="{{ route('pos.customer') }}" class="flex gap-2">
                                @csrf
                                <select name="customer_id" class="flex-1 rounded-lg border border-border px-2 py-1.5 text-xs">
                                    <option value="">Choose existing customer...</option>
                                    @foreach ($existingCustomers as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }} @if($c->phone) ({{ $c->phone }}) @endif</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="bg-primary text-white text-xs font-semibold rounded-lg px-3 hover:bg-primarydark transition">Select</button>
                            </form>
                            <p class="text-[11px] text-secondary text-center">— or add a new one —</p>
                        @endif
                        <form method="POST" action="{{ route('pos.customer') }}" class="space-y-2">
                            @csrf
                            <input type="text" name="new_name" placeholder="Customer name" class="w-full rounded-lg border border-border px-2 py-1.5 text-xs">
                            <input type="text" name="new_phone" placeholder="Phone (optional)" class="w-full rounded-lg border border-border px-2 py-1.5 text-xs">
                            <button type="submit" class="w-full bg-primary text-white text-xs font-semibold rounded-lg py-1.5 hover:bg-primarydark transition">Add &amp; Select</button>
                        </form>
                    </div>
                </details>
            @endif
        </div>

        {{-- Line items --}}
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @forelse ($cartItems as $key => $item)
                <div class="flex items-center justify-between text-sm border-b border-border pb-2">
                    <div>
                        <p class="font-medium leading-tight">{{ $item['name'] }}</p>
                        <p class="text-xs text-secondary">{{ $item['quantity'] }} &times; GH₵{{ number_format($item['unit_price'], 2) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-medium">GH₵{{ number_format($item['unit_price'] * $item['quantity'], 2) }}</span>
                        <form method="POST" action="{{ route('pos.cart.remove') }}">
                            @csrf
                            <input type="hidden" name="key" value="{{ $key }}">
                            <button type="submit" class="text-danger text-xs hover:underline">&times;</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-secondary text-center py-6">Cart is empty — add products from the left.</p>
            @endforelse
        </div>

        {{-- Discount --}}
        <form method="POST" action="{{ route('pos.discount') }}" class="flex items-center gap-2 text-sm">
            @csrf
            <label class="text-secondary">Discount GH₵</label>
            <input type="number" step="0.01" min="0" name="discount" value="{{ $discount }}" class="w-24 rounded-lg border border-border px-2 py-1 text-sm">
            <button type="submit" class="px-3 py-1 rounded-lg bg-card border border-border text-xs font-semibold hover:bg-bg transition">Apply</button>
        </form>

        {{-- Totals --}}
        <div class="text-sm space-y-1 pt-2 border-t border-border">
            <div class="flex justify-between"><span class="text-secondary">Subtotal</span><span>GH₵{{ number_format($subtotal, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-secondary">Discount</span><span>-GH₵{{ number_format($discount, 2) }}</span></div>
            <div class="flex justify-between font-bold text-base"><span>Total</span><span>GH₵{{ number_format($total, 2) }}</span></div>
        </div>

        {{-- Checkout --}}
        @if(!empty($cartItems))
            <form method="POST" action="{{ route('pos.checkout') }}" class="space-y-3 pt-2">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-secondary mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full rounded-lg border border-border px-3 py-2 text-sm">
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method }}">{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary mb-1">Amount Paid (GH₵)</label>
                    <input type="number" step="0.01" min="0" name="amount_paid" value="{{ number_format($total, 2, '.', '') }}" required
                           class="w-full rounded-lg border border-border px-3 py-2 text-sm">
                </div>
                <button type="submit" class="w-full brand-gradient text-white font-bold rounded-lg py-3 text-sm hover:opacity-90 transition">
                    Complete Sale
                </button>
            </form>
        @endif
    </div>
</div>
@endsection

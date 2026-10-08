@extends('layouts.app')

@section('title', $title ?? 'Inventory')

@section('content')
<div class="py-6 space-y-5">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex gap-2">
            <a href="{{ route('inventory.categories.index') }}" class="px-4 py-2 rounded-lg bg-card border border-border text-sm font-semibold hover:bg-bg transition">Categories</a>
            <a href="{{ route('inventory.stock-movements.index') }}" class="px-4 py-2 rounded-lg bg-card border border-border text-sm font-semibold hover:bg-bg transition">Stock Movements</a>
        </div>
        <a href="{{ route('inventory.products.create') }}" class="px-4 py-2 rounded-lg brand-gradient text-white text-sm font-semibold shadow-sm hover:opacity-90 transition">+ Add Product</a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-card border border-border rounded-2xl p-4 flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-secondary mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, brand, model, SKU..."
                   class="w-full rounded-lg border border-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40">
        </div>
        @unless($forcedCategorySlugs ?? null)
        <div class="min-w-[180px]">
            <label class="block text-xs font-medium text-secondary mb-1">Category</label>
            <select name="category" class="w-full rounded-lg border border-border px-3 py-2 text-sm">
                <option value="">All categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        @endunless
        <div class="min-w-[140px]">
            <label class="block text-xs font-medium text-secondary mb-1">Status</label>
            <select name="status" class="w-full rounded-lg border border-border px-3 py-2 text-sm">
                <option value="">All</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
        <label class="flex items-center gap-2 text-sm pb-2">
            <input type="checkbox" name="low_stock" value="1" @checked(request()->boolean('low_stock')) class="rounded border-border">
            Low stock only
        </label>
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primarydark transition">Filter</button>
        @if(request()->anyFilled(['search','category','status','low_stock']))
            <a href="{{ url()->current() }}" class="px-4 py-2 rounded-lg border border-border text-sm font-semibold hover:bg-bg transition">Reset</a>
        @endif
    </form>

    {{-- Product table --}}
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left px-4 py-3">Product</th>
                        <th class="text-left px-4 py-3">Category</th>
                        <th class="text-left px-4 py-3">Tracking</th>
                        <th class="text-right px-4 py-3">Buying</th>
                        <th class="text-right px-4 py-3">Selling</th>
                        <th class="text-right px-4 py-3">Stock</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($products as $product)
                        <tr class="hover:bg-bg/60">
                            <td class="px-4 py-3">
                                <p class="font-medium text-maintext">{{ $product->name }}</p>
                                @if($product->sku)<p class="text-xs text-secondary">SKU: {{ $product->sku }}</p>@endif
                            </td>
                            <td class="px-4 py-3 text-secondary">{{ $product->category->name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $product->tracking_type === 'serialized' ? 'bg-accent/10 text-primarydark' : 'bg-border text-secondary' }}">
                                    {{ $product->tracking_type === 'serialized' ? 'IMEI' : 'Quantity' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">GH₵{{ number_format($product->buying_price, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium">GH₵{{ number_format($product->selling_price, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($product->tracking_type === 'serialized')
                                    <a href="{{ route('iphones.index', ['product_id' => $product->id]) }}" class="text-primary hover:underline text-xs">
                                        {{ $product->phoneDevices()->where('status', 'in_stock')->count() }} in stock — manage
                                    </a>
                                @else
                                    <span class="{{ $product->isLowStock() ? 'text-danger font-semibold' : '' }}">{{ $product->stock_quantity }}</span>
                                    @if($product->isLowStock())
                                        <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-danger/10 text-danger">LOW</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $product->status === 'active' ? 'bg-success/10 text-success' : 'bg-border text-secondary' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('inventory.products.show', $product) }}" class="text-primary hover:underline">View</a>
                                <a href="{{ route('inventory.products.edit', $product) }}" class="text-primary hover:underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-secondary">No products match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $products->links() }}
</div>
@endsection

@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="py-6 space-y-5">
    <div class="flex items-center justify-between">
        <a href="{{ route('inventory.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Inventory</a>
        <a href="{{ route('inventory.categories.create') }}" class="px-4 py-2 rounded-lg brand-gradient text-white text-sm font-semibold shadow-sm hover:opacity-90 transition">+ Add Category</a>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Name</th>
                    <th class="text-left px-4 py-3">Tracking Type</th>
                    <th class="text-left px-4 py-3">Parent</th>
                    <th class="text-right px-4 py-3">Products</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach ($categories as $category)
                    <tr class="hover:bg-bg/60">
                        <td class="px-4 py-3 font-medium">{{ $category->name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $category->tracking_type === 'serialized' ? 'bg-accent/10 text-primarydark' : 'bg-border text-secondary' }}">
                                {{ $category->tracking_type === 'serialized' ? 'IMEI-tracked' : 'Quantity-tracked' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-secondary">{{ $category->parent?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('inventory.index', ['category' => $category->slug]) }}" class="text-primary hover:underline">
                                {{ $category->products_count }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('inventory.categories.edit', $category) }}" class="text-primary hover:underline">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

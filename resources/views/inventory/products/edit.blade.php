@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="py-6 max-w-3xl">
    <a href="{{ route('inventory.products.show', $product) }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to {{ $product->name }}</a>

    <div class="bg-card border border-border rounded-2xl p-6 mt-4">
        <h2 class="font-semibold text-lg mb-4">Edit Product</h2>

        <form method="POST" action="{{ route('inventory.products.update', $product) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('inventory.products._form', ['product' => $product, 'categories' => $categories])
            <button type="submit" class="w-full brand-gradient text-white font-semibold rounded-lg py-2.5 text-sm hover:opacity-90 transition">
                Save Changes
            </button>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Add Category')

@section('content')
<div class="py-6 max-w-lg">
    <a href="{{ route('inventory.categories.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Categories</a>

    <div class="bg-card border border-border rounded-2xl p-6 mt-4">
        <h2 class="font-semibold text-lg mb-4">Add Category</h2>

        <form method="POST" action="{{ route('inventory.categories.store') }}" class="space-y-4">
            @csrf
            @include('inventory.categories._form', ['category' => $category, 'categories' => $categories])
            <button type="submit" class="w-full brand-gradient text-white font-semibold rounded-lg py-2.5 text-sm hover:opacity-90 transition">
                Create Category
            </button>
        </form>
    </div>
</div>
@endsection

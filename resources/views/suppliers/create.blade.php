@extends('layouts.app')

@section('title', 'Add Supplier')

@section('content')
<div class="py-6 max-w-lg">
    <a href="{{ route('suppliers.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Suppliers</a>
    <div class="bg-card border border-border rounded-2xl p-6 mt-4">
        <h2 class="font-semibold text-lg mb-4">Add Supplier</h2>
        <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-4">
            @csrf
            @include('suppliers._form', ['supplier' => $supplier])
            <button type="submit" class="w-full brand-gradient text-white font-semibold rounded-lg py-2.5 text-sm hover:opacity-90 transition">Create Supplier</button>
        </form>
    </div>
</div>
@endsection

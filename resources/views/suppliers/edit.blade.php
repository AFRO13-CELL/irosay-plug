@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="py-6 max-w-lg">
    <a href="{{ route('suppliers.show', $supplier) }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to {{ $supplier->name }}</a>
    <div class="bg-card border border-border rounded-2xl p-6 mt-4">
        <h2 class="font-semibold text-lg mb-4">Edit Supplier</h2>
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('suppliers._form', ['supplier' => $supplier])
            <button type="submit" class="w-full brand-gradient text-white font-semibold rounded-lg py-2.5 text-sm hover:opacity-90 transition">Save Changes</button>
        </form>
    </div>
</div>
@endsection

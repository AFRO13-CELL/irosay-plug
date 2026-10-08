@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')
<div class="py-6 max-w-lg">
    <a href="{{ route('customers.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Customers</a>
    <div class="bg-card border border-border rounded-2xl p-6 mt-4">
        <h2 class="font-semibold text-lg mb-4">Add Customer</h2>
        <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
            @csrf
            @include('customers._form', ['customer' => $customer])
            <button type="submit" class="w-full brand-gradient text-white font-semibold rounded-lg py-2.5 text-sm hover:opacity-90 transition">Create Customer</button>
        </form>
    </div>
</div>
@endsection

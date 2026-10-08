@extends('layouts.app')

@section('title', 'Add Expense')

@section('content')
<div class="py-6 max-w-lg">
    <a href="{{ route('expenses.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Expenses</a>
    <div class="bg-card border border-border rounded-2xl p-6 mt-4">
        <h2 class="font-semibold text-lg mb-4">Add Expense</h2>
        <form method="POST" action="{{ route('expenses.store') }}" class="space-y-4">
            @csrf
            @include('expenses._form', ['categories' => $categories, 'paymentMethods' => $paymentMethods])
            <button type="submit" class="w-full brand-gradient text-white font-semibold rounded-lg py-2.5 text-sm hover:opacity-90 transition">Record Expense</button>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'New Purchase')

@section('content')
<div class="py-6 max-w-lg">
    <a href="{{ route('purchases.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Purchases</a>

    <div class="bg-card border border-border rounded-2xl p-6 mt-4">
        <h2 class="font-semibold text-lg mb-1">New Purchase</h2>
        <p class="text-sm text-secondary mb-4">Creates the purchase header — you'll add line items on the next screen. A reference number is generated automatically.</p>

        @if ($errors->any())
            <div class="rounded-xl bg-danger/10 border border-danger/30 text-danger px-4 py-3 text-sm mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('purchases.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Supplier</label>
                <select name="supplier_id" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
                    <option value="">Select supplier...</option>
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
                @if($suppliers->isEmpty())
                    <p class="text-xs text-danger mt-1">No suppliers yet — <a href="{{ route('suppliers.create') }}" class="underline">add one first</a>.</p>
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Purchase Date</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date', now()->format('Y-m-d')) }}" required
                       class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Initial Payment (optional, GH₵)</label>
                <input type="number" step="0.01" min="0" name="amount_paid" value="{{ old('amount_paid', 0) }}"
                       class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="w-full brand-gradient text-white font-semibold rounded-lg py-2.5 text-sm hover:opacity-90 transition">
                Create Purchase &amp; Add Items
            </button>
        </form>
    </div>
</div>
@endsection

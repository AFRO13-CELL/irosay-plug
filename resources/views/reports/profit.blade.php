@extends('layouts.app')

@section('title', 'Profit Report')

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('reports.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Reports</a>
    <h1 class="text-lg font-semibold">Profit Report</h1>

    @include('reports._filter')

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Revenue</p><p class="text-2xl font-bold mt-1">GH₵{{ number_format($revenue, 2) }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Gross Profit</p><p class="text-2xl font-bold mt-1 text-success">GH₵{{ number_format($grossProfit, 2) }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Expenses</p><p class="text-2xl font-bold mt-1 text-danger">GH₵{{ number_format($totalExpenses, 2) }}</p></div>
        <div class="bg-card border border-border rounded-2xl p-5"><p class="text-sm text-secondary">Net Profit</p><p class="text-2xl font-bold mt-1 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">GH₵{{ number_format($netProfit, 2) }}</p></div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">Expenses by Category</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr><th class="text-left px-4 py-3">Category</th><th class="text-right px-4 py-3">Amount</th></tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($expensesByCategory as $category => $amount)
                    <tr><td class="px-4 py-3">{{ $category }}</td><td class="px-4 py-3 text-right">GH₵{{ number_format($amount, 2) }}</td></tr>
                @empty
                    <tr><td colspan="2" class="px-4 py-8 text-center text-secondary">No expenses in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

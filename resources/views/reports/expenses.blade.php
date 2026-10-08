@extends('layouts.app')

@section('title', 'Expense Report')

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('reports.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Reports</a>
    <h1 class="text-lg font-semibold">Expense Report</h1>

    @include('reports._filter', ['exportRoute' => 'reports.expenses.export'])

    <div class="bg-card border border-border rounded-2xl p-5">
        <p class="text-sm text-secondary">Total Expenses</p>
        <p class="text-2xl font-bold mt-1">GH₵{{ number_format($total, 2) }}</p>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">By Category</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr><th class="text-left px-4 py-3">Category</th><th class="text-right px-4 py-3">Amount</th></tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($byCategory as $category => $amount)
                    <tr><td class="px-4 py-3">{{ $category }}</td><td class="px-4 py-3 text-right">GH₵{{ number_format($amount, 2) }}</td></tr>
                @empty
                    <tr><td colspan="2" class="px-4 py-8 text-center text-secondary">No expenses in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">All Expenses</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr><th class="text-left px-4 py-3">Date</th><th class="text-left px-4 py-3">Category</th><th class="text-left px-4 py-3">Description</th><th class="text-left px-4 py-3">Recorded By</th><th class="text-right px-4 py-3">Amount</th></tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($expenses as $expense)
                    <tr>
                        <td class="px-4 py-3 text-secondary">{{ $expense->date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $expense->category }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $expense->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $expense->user->name }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($expense->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-secondary">No expenses in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

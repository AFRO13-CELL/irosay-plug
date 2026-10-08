@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="py-6 space-y-5">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold">Expenses</h1>
        <a href="{{ route('expenses.create') }}" class="px-4 py-2 rounded-lg brand-gradient text-white text-sm font-semibold shadow-sm hover:opacity-90 transition">+ Add Expense</a>
    </div>

    <form method="GET" class="bg-card border border-border rounded-2xl p-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-secondary mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border border-border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-secondary mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border border-border px-3 py-2 text-sm">
        </div>
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-secondary mb-1">Category</label>
            <select name="category" class="w-full rounded-lg border border-border px-3 py-2 text-sm">
                <option value="">All categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primarydark transition">Filter</button>
        <a href="{{ route('expenses.index') }}" class="px-4 py-2 rounded-lg border border-border text-sm font-semibold hover:bg-bg transition">This Month</a>
    </form>

    <div class="bg-card border border-border rounded-2xl p-5">
        <p class="text-sm text-secondary">Total for {{ \Illuminate\Support\Carbon::parse($from)->format('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }}{{ request('category') ? ' ('.request('category').')' : '' }}</p>
        <p class="text-2xl font-bold mt-1">GH₵{{ number_format($totalForRange, 2) }}</p>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-left px-4 py-3">Category</th>
                    <th class="text-left px-4 py-3">Description</th>
                    <th class="text-left px-4 py-3">Payment</th>
                    <th class="text-left px-4 py-3">Recorded By</th>
                    <th class="text-right px-4 py-3">Amount</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($expenses as $expense)
                    <tr class="hover:bg-bg/60">
                        <td class="px-4 py-3 text-secondary">{{ $expense->date->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-accent/10 text-primarydark">{{ $expense->category }}</span>
                        </td>
                        <td class="px-4 py-3 text-secondary">{{ $expense->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $expense->payment_method ?? '—' }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $expense->user->name }}</td>
                        <td class="px-4 py-3 text-right font-medium">GH₵{{ number_format($expense->amount, 2) }}</td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('expenses.edit', $expense) }}" class="text-primary hover:underline">Edit</a>
                            <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline" onsubmit="return confirm('Delete this expense?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-danger hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-secondary">No expenses recorded for this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $expenses->links() }}
</div>
@endsection

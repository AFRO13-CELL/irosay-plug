@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="py-6 space-y-5">
    <div class="flex items-center justify-between gap-3">
        <form method="GET" class="flex-1 max-w-sm">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or phone..."
                   class="w-full rounded-lg border border-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40">
        </form>
        <a href="{{ route('customers.create') }}" class="px-4 py-2 rounded-lg brand-gradient text-white text-sm font-semibold shadow-sm hover:opacity-90 transition">+ Add Customer</a>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Name</th>
                    <th class="text-left px-4 py-3">Phone</th>
                    <th class="text-right px-4 py-3">Sales</th>
                    <th class="text-right px-4 py-3">Total Spent</th>
                    <th class="text-right px-4 py-3">Outstanding</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($customers as $customer)
                    <tr class="hover:bg-bg/60">
                        <td class="px-4 py-3 font-medium">{{ $customer->name }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $customer->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ $customer->sales_count }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($customer->totalSpent(), 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $customer->outstandingBalance() > 0 ? 'text-danger font-medium' : '' }}">
                            GH₵{{ number_format($customer->outstandingBalance(), 2) }}
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('customers.show', $customer) }}" class="text-primary hover:underline">View</a>
                            <a href="{{ route('customers.edit', $customer) }}" class="text-primary hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-secondary">No customers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $customers->links() }}
</div>
@endsection

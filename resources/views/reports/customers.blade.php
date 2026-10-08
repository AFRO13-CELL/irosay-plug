@extends('layouts.app')

@section('title', 'Customer Report')

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('reports.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Reports</a>
    <h1 class="text-lg font-semibold">Customer Report</h1>

    @include('reports._filter', ['exportRoute' => 'reports.customers.export'])
    <p class="text-xs text-secondary">"Spent in Period" reflects the date range above; "Total Spent" and "Outstanding" are all-time figures.</p>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Customer</th><th class="text-left px-4 py-3">Phone</th>
                    <th class="text-right px-4 py-3">Sales in Period</th><th class="text-right px-4 py-3">Spent in Period</th>
                    <th class="text-right px-4 py-3">Total Spent</th><th class="text-right px-4 py-3">Outstanding</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($customers as $customer)
                    <tr class="hover:bg-bg/60 cursor-pointer" onclick="window.location='{{ route('customers.show', $customer) }}'">
                        <td class="px-4 py-3 font-medium text-primary">{{ $customer->name }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $customer->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ $customer->period_sales_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($customer->period_spent ?? 0, 2) }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($customer->totalSpent(), 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $customer->outstandingBalance() > 0 ? 'text-danger font-medium' : '' }}">GH₵{{ number_format($customer->outstandingBalance(), 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-secondary">No customers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

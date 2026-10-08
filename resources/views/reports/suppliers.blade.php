@extends('layouts.app')

@section('title', 'Supplier Report')

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('reports.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Reports</a>
    <h1 class="text-lg font-semibold">Supplier Report</h1>

    @include('reports._filter', ['exportRoute' => 'reports.suppliers.export'])

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Supplier</th><th class="text-left px-4 py-3">Phone</th>
                    <th class="text-right px-4 py-3">Purchases in Period</th><th class="text-right px-4 py-3">Total in Period</th><th class="text-right px-4 py-3">Amount Owed</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($suppliers as $supplier)
                    <tr class="hover:bg-bg/60 cursor-pointer" onclick="window.location='{{ route('suppliers.show', $supplier) }}'">
                        <td class="px-4 py-3 font-medium text-primary">{{ $supplier->name }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $supplier->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ $supplier->period_purchases_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($supplier->period_total ?? 0, 2) }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($supplier->amount_owed, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-secondary">No suppliers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

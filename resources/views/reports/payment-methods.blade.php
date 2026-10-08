@extends('layouts.app')

@section('title', 'Payment Method Report')

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('reports.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Reports</a>
    <h1 class="text-lg font-semibold">Payment Method Report</h1>

    @include('reports._filter')

    <div class="bg-card border border-border rounded-2xl p-5">
        <p class="text-sm text-secondary">Total Payments Received</p>
        <p class="text-2xl font-bold mt-1">GH₵{{ number_format($grandTotal, 2) }}</p>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr><th class="text-left px-4 py-3">Method</th><th class="text-right px-4 py-3"># Payments</th><th class="text-right px-4 py-3">Total</th><th class="text-right px-4 py-3">% of Total</th></tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($breakdown as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $row->method }}</td>
                        <td class="px-4 py-3 text-right">{{ $row->count }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($row->total, 2) }}</td>
                        <td class="px-4 py-3 text-right text-secondary">{{ $grandTotal > 0 ? number_format(($row->total / $grandTotal) * 100, 1) : 0 }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-secondary">No payments recorded in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

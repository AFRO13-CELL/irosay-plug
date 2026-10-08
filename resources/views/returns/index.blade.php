@extends('layouts.app')

@section('title', 'Returns')

@section('content')
<div class="py-6 space-y-5">
    <h1 class="text-lg font-semibold">Returns</h1>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Sale</th><th class="text-left px-4 py-3">Customer</th>
                    <th class="text-left px-4 py-3">Reason</th><th class="text-right px-4 py-3">Refund</th>
                    <th class="text-left px-4 py-3">Processed By</th><th class="text-left px-4 py-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($returns as $return)
                    <tr class="hover:bg-bg/60 cursor-pointer" onclick="window.location='{{ route('returns.show', $return) }}'">
                        <td class="px-4 py-3 text-primary font-medium">{{ $return->sale->invoice_number }}</td>
                        <td class="px-4 py-3">{{ $return->sale->customer->name ?? 'Walk-in' }}</td>
                        <td class="px-4 py-3 text-secondary">{{ \Illuminate\Support\Str::limit($return->reason, 40) }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($return->refund_amount, 2) }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $return->user->name }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $return->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-secondary">No returns processed yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $returns->links() }}
</div>
@endsection

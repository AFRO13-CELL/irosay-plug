@extends('layouts.app')

@section('title', 'Return #' . $return->id)

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('returns.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Returns</a>

    <div class="bg-card border border-border rounded-2xl p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold">Return on <a href="{{ route('sales.show', $return->sale) }}" class="text-primary hover:underline">{{ $return->sale->invoice_number }}</a></h1>
                <p class="text-sm text-secondary">{{ $return->sale->customer->name ?? 'Walk-in' }} &middot; processed by {{ $return->user->name }} on {{ $return->created_at->format('d M Y, H:i') }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-success/10 text-success">{{ strtoupper($return->status) }}</span>
        </div>

        <div class="mt-4 pt-4 border-t border-border grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-secondary">Refund Amount</dt><dd class="font-bold text-base">GH₵{{ number_format($return->refund_amount, 2) }}</dd></div>
            <div><dt class="text-secondary">Reason</dt><dd class="font-medium">{{ $return->reason }}</dd></div>
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">Returned Items</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr><th class="text-left px-4 py-3">Product</th><th class="text-right px-4 py-3">Quantity</th></tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach ($return->items as $item)
                    <tr>
                        <td class="px-4 py-3">
                            {{ $item->product->name }}
                            @if($item->phoneDevice)<span class="text-xs text-secondary font-mono block">IMEI {{ $item->phoneDevice->imei1 }} — now marked "Returned"</span>@endif
                        </td>
                        <td class="px-4 py-3 text-right">{{ $item->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

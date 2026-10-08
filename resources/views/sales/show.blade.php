@extends('layouts.app')

@section('title', $sale->invoice_number)

@section('content')
<div class="py-6 space-y-5">
    <div class="flex items-center justify-between">
        <a href="{{ route('sales.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Sales</a>
        <div class="flex gap-2">
            @if($sale->status === 'completed' && auth()->user()->hasPermission('returns.process'))
                <a href="{{ route('returns.create', $sale) }}" class="px-4 py-2 rounded-lg border border-danger/30 text-danger text-sm font-semibold hover:bg-danger/10 transition">
                    Process Return
                </a>
            @endif
            <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="px-4 py-2 rounded-lg brand-gradient text-white text-sm font-semibold shadow-sm hover:opacity-90 transition">
                View / Print Receipt
            </a>
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold">{{ $sale->invoice_number }}</h1>
                <p class="text-sm text-secondary">
                    @if($sale->customer)
                        <a href="{{ route('customers.show', $sale->customer) }}" class="hover:underline text-primary">{{ $sale->customer->name }}</a>
                    @else
                        Walk-in customer
                    @endif
                    &middot; {{ $sale->sold_at->format('d M Y, H:i') }} &middot; sold by {{ $sale->user->name }}
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $sale->status === 'completed' ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                {{ strtoupper(str_replace('_',' ',$sale->status)) }}
            </span>
        </div>

        <div class="grid grid-cols-3 gap-4 mt-5 text-sm">
            <div><dt class="text-secondary">Subtotal</dt><dd class="font-medium">GH₵{{ number_format($sale->subtotal, 2) }}</dd></div>
            <div><dt class="text-secondary">Discount</dt><dd class="font-medium">GH₵{{ number_format($sale->discount, 2) }}</dd></div>
            <div><dt class="text-secondary">Total</dt><dd class="font-bold text-base">GH₵{{ number_format($sale->total, 2) }}</dd></div>
            <div><dt class="text-secondary">Amount Paid</dt><dd class="font-medium">GH₵{{ number_format($sale->amount_paid, 2) }}</dd></div>
            <div><dt class="text-secondary">Balance</dt><dd class="font-medium {{ $sale->balance > 0 ? 'text-danger' : '' }}">GH₵{{ number_format($sale->balance, 2) }}</dd></div>
            <div><dt class="text-secondary">Gross Profit</dt><dd class="font-medium text-success">GH₵{{ number_format($sale->grossProfit(), 2) }}</dd></div>
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border font-semibold text-sm">Items</div>
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Product</th>
                    <th class="text-right px-4 py-3">Qty</th>
                    <th class="text-right px-4 py-3">Unit Price</th>
                    <th class="text-right px-4 py-3">Subtotal</th>
                    <th class="text-right px-4 py-3">Profit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach ($sale->items as $item)
                    <tr>
                        <td class="px-4 py-3">
                            {{ $item->product->name }}
                            @if($item->phoneDevice)
                                <span class="block text-xs text-secondary font-mono">IMEI {{ $item->phoneDevice->imei1 }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">{{ $item->quantity }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-3 text-right">GH₵{{ number_format($item->subtotal, 2) }}</td>
                        <td class="px-4 py-3 text-right text-success">GH₵{{ number_format($item->profit(), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($sale->returns->isNotEmpty())
        <div class="bg-card border border-danger/30 rounded-2xl overflow-hidden">
            <div class="px-4 py-3 border-b border-border font-semibold text-sm text-danger">Returns on this Sale</div>
            <table class="w-full text-sm">
                <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                    <tr><th class="text-left px-4 py-3">Date</th><th class="text-left px-4 py-3">Reason</th><th class="text-right px-4 py-3">Refund</th></tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($sale->returns as $return)
                        <tr class="hover:bg-bg/60 cursor-pointer" onclick="window.location='{{ route('returns.show', $return) }}'">
                            <td class="px-4 py-3 text-secondary">{{ $return->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($return->reason, 50) }}</td>
                            <td class="px-4 py-3 text-right">GH₵{{ number_format($return->refund_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($sale->payments->isNotEmpty())
        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <div class="px-4 py-3 border-b border-border font-semibold text-sm">Payments</div>
            <table class="w-full text-sm">
                <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left px-4 py-3">Method</th>
                        <th class="text-right px-4 py-3">Amount</th>
                        <th class="text-left px-4 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($sale->payments as $payment)
                        <tr>
                            <td class="px-4 py-3">{{ $payment->method }}</td>
                            <td class="px-4 py-3 text-right">GH₵{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-3 text-secondary">{{ $payment->paid_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

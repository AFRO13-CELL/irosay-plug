@extends('layouts.app')

@section('title', 'Process Return — ' . $sale->invoice_number)

@section('content')
<div class="py-6 max-w-2xl">
    <a href="{{ route('sales.show', $sale) }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to {{ $sale->invoice_number }}</a>

    <div class="bg-card border border-border rounded-2xl p-6 mt-4">
        <h2 class="font-semibold text-lg mb-1">Process Return</h2>
        <p class="text-sm text-secondary mb-4">Sale {{ $sale->invoice_number }} &middot; {{ $sale->customer->name ?? 'Walk-in' }} &middot; {{ $sale->sold_at->format('d M Y') }}</p>

        @if ($errors->any())
            <div class="rounded-xl bg-danger/10 border border-danger/30 text-danger px-4 py-3 text-sm mb-4">
                <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if($returnableItems->isEmpty())
            <p class="text-secondary text-sm">Every item on this sale has already been fully returned.</p>
        @else
            <form method="POST" action="{{ route('returns.store', $sale) }}" class="space-y-4">
                @csrf
                <div class="space-y-3">
                    @foreach ($returnableItems as $item)
                        <div class="border border-border rounded-xl p-3">
                            <p class="font-medium text-sm">
                                {{ $item->product->name }}
                                @if($item->phoneDevice)<span class="text-xs text-secondary font-mono block">IMEI {{ $item->phoneDevice->imei1 }}</span>@endif
                            </p>
                            <p class="text-xs text-secondary mb-2">Sold: {{ $item->quantity }} &middot; Unit price GH₵{{ number_format($item->unit_price, 2) }} &middot; Returnable: {{ $item->returnable_qty }}</p>

                            @if($item->phoneDevice)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="items[{{ $item->id }}]" value="1" class="rounded border-border">
                                    Return this device
                                </label>
                            @else
                                <label class="text-xs text-secondary">Quantity to return</label>
                                <input type="number" name="items[{{ $item->id }}]" min="0" max="{{ $item->returnable_qty }}" value="0"
                                       class="w-24 rounded-lg border border-border px-2 py-1.5 text-sm block mt-1">
                            @endif
                        </div>
                    @endforeach
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Reason for Return</label>
                    <textarea name="reason" rows="3" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm"></textarea>
                </div>

                <p class="text-xs text-secondary">The refund amount is calculated automatically from the items you select and their original selling price.</p>

                <button type="submit" class="w-full brand-gradient text-white font-semibold rounded-lg py-2.5 text-sm hover:opacity-90 transition">
                    Process Return
                </button>
            </form>
        @endif
    </div>
</div>
@endsection

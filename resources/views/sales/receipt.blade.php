<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $sale->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', Courier, monospace; max-width: 380px; margin: 0 auto; padding: 20px; color: #111; }
        .center { text-align: center; }
        .muted { color: #555; font-size: 12px; }
        hr { border: none; border-top: 1px dashed #999; margin: 12px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        td { padding: 3px 0; vertical-align: top; }
        .right { text-align: right; }
        .totals td { padding-top: 2px; }
        .bold { font-weight: bold; }
        .imei { font-size: 10px; color: #555; }
        .no-print { margin-top: 20px; text-align: center; }
        .no-print button { padding: 10px 20px; font-size: 14px; cursor: pointer; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="center">
        <h2 style="margin-bottom:0;">{{ \App\Models\Setting::get('business_name', 'IROZAY DE PLUG') }}</h2>
        <p class="muted" style="margin-top:2px;">{{ \App\Models\Setting::get('business_type', 'Phones and Accessories') }}</p>
        <p class="muted">Ghana &middot; {{ \App\Models\Setting::get('business_phone', '') }}</p>
    </div>

    <hr>

    <table>
        <tr><td>Invoice #</td><td class="right bold">{{ $sale->invoice_number }}</td></tr>
        <tr><td>Date</td><td class="right">{{ $sale->sold_at->format('d M Y, H:i') }}</td></tr>
        <tr><td>Customer</td><td class="right">{{ $sale->customer->name ?? 'Walk-in' }}</td></tr>
        @if($sale->customer && $sale->customer->phone)
        <tr><td>Phone</td><td class="right">{{ $sale->customer->phone }}</td></tr>
        @endif
    </table>

    <hr>

    <table>
        @foreach ($sale->items as $item)
            <tr>
                <td>
                    {{ $item->product->name }} @if($item->quantity > 1) x{{ $item->quantity }} @endif
                    @if($item->phoneDevice)
                        <br><span class="imei">IMEI: {{ $item->phoneDevice->imei1 }}</span>
                    @endif
                </td>
                <td class="right">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <hr>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">GH₵{{ number_format($sale->subtotal, 2) }}</td></tr>
        <tr><td>Discount</td><td class="right">-GH₵{{ number_format($sale->discount, 2) }}</td></tr>
        <tr class="bold"><td>TOTAL</td><td class="right">GH₵{{ number_format($sale->total, 2) }}</td></tr>
        <tr><td>Paid ({{ $sale->payments->first()->method ?? '—' }})</td><td class="right">GH₵{{ number_format($sale->amount_paid, 2) }}</td></tr>
        <tr><td>Balance</td><td class="right">GH₵{{ number_format($sale->balance, 2) }}</td></tr>
    </table>

    <hr>

    <p class="center muted">
        {{ \App\Models\Setting::get('receipt_footer', 'Thank you for shopping with us!') }}
    </p>
    @if(\App\Models\Setting::get('warranty_message'))
        <p class="center muted">{{ \App\Models\Setting::get('warranty_message') }}</p>
    @endif

    <div class="no-print">
        <button onclick="window.print()">Print Receipt</button>
    </div>
</body>
</html>

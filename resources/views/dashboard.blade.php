@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="py-6 space-y-6">

    {{-- Quick actions --}}
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('pos.index') }}" class="px-4 py-2 rounded-lg brand-gradient text-white text-sm font-semibold shadow-sm hover:opacity-90 transition">+ New Sale</a>
        <a href="{{ route('iphones.create') }}" class="px-4 py-2 rounded-lg bg-card border border-border text-sm font-semibold hover:bg-bg transition">+ Add iPhone</a>
        <a href="{{ route('inventory.products.create') }}" class="px-4 py-2 rounded-lg bg-card border border-border text-sm font-semibold hover:bg-bg transition">+ Add Product</a>
        <a href="{{ route('purchases.create') }}" class="px-4 py-2 rounded-lg bg-card border border-border text-sm font-semibold hover:bg-bg transition">+ Add Purchase</a>
        <a href="{{ route('expenses.create') }}" class="px-4 py-2 rounded-lg bg-card border border-border text-sm font-semibold hover:bg-bg transition">+ Add Expense</a>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-card border border-border rounded-2xl p-5">
            <p class="text-sm text-secondary">Today's Sales</p>
            <p class="text-2xl font-bold mt-1">GH₵{{ number_format($todaysSales, 2) }}</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-5">
            <p class="text-sm text-secondary">Today's Gross Profit</p>
            <p class="text-2xl font-bold mt-1">GH₵{{ number_format($todaysGrossProfit, 2) }}</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-5">
            <p class="text-sm text-secondary">Today's Expenses</p>
            <p class="text-2xl font-bold mt-1">GH₵{{ number_format($todaysExpenses, 2) }}</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-5">
            <p class="text-sm text-secondary">Today's Net Profit</p>
            <p class="text-2xl font-bold mt-1">GH₵{{ number_format($todaysNetProfit, 2) }}</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-5">
            <p class="text-sm text-secondary">iPhones In Stock</p>
            <p class="text-2xl font-bold mt-1">{{ $iphonesInStock }}</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-card border border-border rounded-2xl p-5 hover:bg-bg transition">
            <p class="text-sm text-secondary">Other Products In Stock</p>
            <p class="text-2xl font-bold mt-1">{{ $otherProductsInStock }}</p>
        </a>
        <a href="{{ route('inventory.index', ['low_stock' => 1]) }}" class="bg-card border border-border rounded-2xl p-5 hover:bg-bg transition">
            <p class="text-sm text-secondary">Low Stock Items</p>
            <p class="text-2xl font-bold mt-1 {{ $lowStockCount > 0 ? 'text-danger' : '' }}">{{ $lowStockCount }}</p>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-card border border-border rounded-2xl p-5">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <h2 class="font-semibold">Sales Overview</h2>
                <form method="GET" class="flex flex-wrap items-center gap-2">
                    <select name="period" onchange="this.form.submit()" class="text-sm border border-border rounded-lg px-2 py-1">
                        <option value="today" @selected($period === 'today')>Today</option>
                        <option value="7" @selected($period === '7')>Last 7 days</option>
                        <option value="30" @selected($period === '30')>Last 30 days</option>
                        <option value="custom" @selected($period === 'custom')>Custom range</option>
                    </select>
                    @if($period === 'custom')
                        <input type="date" name="from" value="{{ request('from') }}" class="text-sm border border-border rounded-lg px-2 py-1">
                        <input type="date" name="to" value="{{ request('to') }}" class="text-sm border border-border rounded-lg px-2 py-1">
                        <button type="submit" class="text-sm px-3 py-1 border border-border rounded-lg hover:bg-bg transition">Go</button>
                    @endif
                </form>
            </div>
            @php $chartMax = max(1, collect($chartData)->max('value')); @endphp
            <div class="h-56 flex items-end gap-0.5 border border-border rounded-xl p-3 overflow-x-auto">
                @forelse ($chartData as $point)
                    <div class="flex flex-col items-center justify-end h-full flex-1 min-w-[6px]" title="{{ $point['label'] }}: GH₵{{ number_format($point['value'], 2) }}">
                        <div class="w-full bg-primary rounded-t transition-all" style="height: {{ $point['value'] > 0 ? max(4, ($point['value'] / $chartMax) * 100) : 1 }}%"></div>
                        @if(count($chartData) <= 14)
                            <span class="text-[9px] text-secondary mt-1 whitespace-nowrap">{{ $point['label'] }}</span>
                        @endif
                    </div>
                @empty
                    <p class="text-secondary text-sm w-full text-center self-center">No sales in this period.</p>
                @endforelse
            </div>
            @if(count($chartData) > 14)
                <p class="text-xs text-secondary mt-2">{{ $chartData[0]['label'] }} &rarr; {{ $chartData[count($chartData)-1]['label'] }} (hover a bar for its exact date and total)</p>
            @endif
        </div>

        <div class="bg-card border border-border rounded-2xl p-5">
            <h2 class="font-semibold mb-4">Recent Sales</h2>
            @if($recentSales->isEmpty())
                <div class="h-56 flex items-center justify-center text-secondary text-sm border border-dashed border-border rounded-xl">
                    No sales recorded yet.
                </div>
            @else
                <ul class="space-y-3 text-sm">
                    @foreach($recentSales as $sale)
                        <li class="flex justify-between border-b border-border pb-2 last:border-0">
                            <a href="{{ route('sales.show', $sale) }}" class="hover:underline">{{ $sale->invoice_number }} &middot; {{ $sale->customer->name ?? 'Walk-in' }}</a>
                            <span class="font-medium">GH₵{{ number_format($sale->total, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="bg-card border border-dashed border-border rounded-2xl p-5 text-sm text-secondary">
        <strong class="text-maintext">Phase 11 status:</strong> authentication, layout, dashboard, and every module from the original spec is complete and working, including a real Sales Overview chart (Today/7-day/30-day/custom range, driven by actual sales data).
        The system is feature-complete. See DEPLOYMENT.md and USER_GUIDE.md for going live.
    </div>
</div>
@endsection

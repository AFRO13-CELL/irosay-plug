@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="py-6 space-y-5">
    <h1 class="text-lg font-semibold">Reports</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @php
            $reports = [
                ['route' => 'reports.sales', 'title' => 'Sales Report', 'desc' => 'Revenue and profit by day, with a full sales list for any date range.'],
                ['route' => 'reports.profit', 'title' => 'Profit Report', 'desc' => 'Gross profit, expenses by category, and net profit for a period.'],
                ['route' => 'reports.inventory', 'title' => 'Inventory Report', 'desc' => 'Stock on hand, stock value, low-stock items, and units sold.'],
                ['route' => 'reports.iphones', 'title' => 'iPhone Report', 'desc' => 'Purchased, in-stock, and sold iPhones with per-unit profit and IMEI.'],
                ['route' => 'reports.purchases', 'title' => 'Purchase Report', 'desc' => 'Purchases by supplier for a period, with cost and balance owed.'],
                ['route' => 'reports.expenses', 'title' => 'Expense Report', 'desc' => 'Expenses broken down by category for a period.'],
                ['route' => 'reports.customers', 'title' => 'Customer Report', 'desc' => 'Spend and outstanding balance per customer.'],
                ['route' => 'reports.suppliers', 'title' => 'Supplier Report', 'desc' => 'Purchase volume per supplier for a period.'],
                ['route' => 'reports.payment-methods', 'title' => 'Payment Method Report', 'desc' => 'How customers paid — Cash, Mobile Money, Card, Other.'],
                ['route' => 'reports.best-selling', 'title' => 'Best-Selling Products', 'desc' => 'Top products by quantity sold and revenue for a period.'],
            ];
        @endphp

        @foreach ($reports as $r)
            <a href="{{ route($r['route']) }}" class="bg-card border border-border rounded-2xl p-5 hover:bg-bg transition block">
                <h2 class="font-semibold mb-1">{{ $r['title'] }}</h2>
                <p class="text-sm text-secondary">{{ $r['desc'] }}</p>
            </a>
        @endforeach
    </div>
</div>
@endsection

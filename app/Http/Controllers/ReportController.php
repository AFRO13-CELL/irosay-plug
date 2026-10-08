<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\PhoneDevice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    // ---------------------------------------------------------------
    // Sales Report
    // ---------------------------------------------------------------
    public function sales(Request $request)
    {
        [$from, $to] = $this->range($request);

        $sales = Sale::with('items', 'customer', 'user')
            ->whereBetween('sold_at', [$from, $to])
            ->where('status', 'completed')
            ->orderByDesc('sold_at')
            ->get();

        $summary = [
            'count' => $sales->count(),
            'total' => $sales->sum('total'),
            'discount' => $sales->sum('discount'),
            'profit' => $sales->sum(fn (Sale $s) => $s->grossProfit()),
        ];

        $daily = $sales->groupBy(fn (Sale $s) => $s->sold_at->format('Y-m-d'))
            ->map(fn ($group, $date) => [
                'date' => $date,
                'count' => $group->count(),
                'total' => $group->sum('total'),
            ])
            ->sortKeysDesc();

        return view('reports.sales', compact('sales', 'summary', 'daily', 'from', 'to'));
    }

    public function salesExport(Request $request)
    {
        [$from, $to] = $this->range($request);

        $sales = Sale::with('customer', 'user')
            ->whereBetween('sold_at', [$from, $to])
            ->where('status', 'completed')
            ->orderBy('sold_at')
            ->get();

        return $this->csv('sales-report.csv', ['Invoice #', 'Date', 'Customer', 'Cashier', 'Subtotal', 'Discount', 'Total', 'Paid', 'Balance'],
            $sales->map(fn (Sale $s) => [
                $s->invoice_number, $s->sold_at->format('Y-m-d H:i'), $s->customer->name ?? 'Walk-in', $s->user->name,
                $s->subtotal, $s->discount, $s->total, $s->amount_paid, $s->balance,
            ])
        );
    }

    // ---------------------------------------------------------------
    // Profit Report
    // ---------------------------------------------------------------
    public function profit(Request $request)
    {
        [$from, $to] = $this->range($request);

        $sales = Sale::with('items')->whereBetween('sold_at', [$from, $to])->where('status', 'completed')->get();
        $grossProfit = $sales->sum(fn (Sale $s) => $s->grossProfit());
        $revenue = $sales->sum('total');

        $expensesByCategory = Expense::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->pluck('total', 'category');

        $totalExpenses = $expensesByCategory->sum();
        $netProfit = $grossProfit - $totalExpenses;

        return view('reports.profit', compact('revenue', 'grossProfit', 'expensesByCategory', 'totalExpenses', 'netProfit', 'from', 'to'));
    }

    // ---------------------------------------------------------------
    // Inventory Report
    // ---------------------------------------------------------------
    public function inventory(Request $request)
    {
        [$from, $to] = $this->range($request);

        $products = Product::with('category')->where('status', 'active')->orderBy('name')->get();

        // One grouped query for every serialized product's in-stock count/value,
        // instead of a query per product row (was previously called inside the
        // Blade loop — fine at 63 products, but doesn't scale).
        $serializedByProduct = PhoneDevice::where('status', 'in_stock')
            ->selectRaw('product_id, count(*) as cnt, sum(buying_price) as val')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $quantityStockValue = $products->where('tracking_type', 'quantity')
            ->sum(fn (Product $p) => $p->stock_quantity * (float) $p->buying_price);

        $serializedInStockCount = PhoneDevice::where('status', 'in_stock')->count();
        $serializedStockValue = (float) PhoneDevice::where('status', 'in_stock')->sum('buying_price');

        $lowStock = $products->filter(fn (Product $p) => $p->isLowStock());

        $soldInRangeCount = SaleItem::whereHas('sale', fn ($q) => $q->whereBetween('sold_at', [$from, $to])->where('status', 'completed'))
            ->sum('quantity');

        return view('reports.inventory', compact(
            'products', 'quantityStockValue', 'serializedInStockCount', 'serializedStockValue',
            'lowStock', 'soldInRangeCount', 'serializedByProduct', 'from', 'to'
        ));
    }

    public function inventoryExport()
    {
        $products = Product::with('category')->orderBy('name')->get();

        $serializedByProduct = PhoneDevice::where('status', 'in_stock')
            ->selectRaw('product_id, count(*) as cnt, sum(buying_price) as val')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        return $this->csv('inventory-report.csv', ['Product', 'Category', 'Tracking', 'Stock Qty', 'Buying Price', 'Selling Price', 'Stock Value', 'Status'],
            $products->map(function (Product $p) use ($serializedByProduct) {
                $serialized = $serializedByProduct->get($p->id);

                return [
                    $p->name, $p->category->name, $p->tracking_type,
                    $p->tracking_type === 'quantity' ? $p->stock_quantity : ($serialized->cnt ?? 0),
                    $p->buying_price, $p->selling_price,
                    $p->tracking_type === 'quantity'
                        ? $p->stock_quantity * (float) $p->buying_price
                        : (float) ($serialized->val ?? 0),
                    $p->status,
                ];
            })
        );
    }

    // ---------------------------------------------------------------
    // iPhone Report
    // ---------------------------------------------------------------
    public function iphones(Request $request)
    {
        [$from, $to] = $this->range($request);

        $purchasedDevices = PhoneDevice::whereBetween('created_at', [$from, $to])->get();
        $inStockNow = PhoneDevice::where('status', 'in_stock')->count();

        $soldItems = SaleItem::whereNotNull('phone_device_id')
            ->whereHas('sale', fn ($q) => $q->whereBetween('sold_at', [$from, $to])->where('status', 'completed'))
            ->with(['phoneDevice', 'product', 'sale.customer', 'sale.user'])
            ->get();

        $summary = [
            'total_purchased' => $purchasedDevices->count(),
            'total_purchase_cost' => $purchasedDevices->sum('buying_price'),
            'total_in_stock' => $inStockNow,
            'total_sold' => $soldItems->count(),
            'total_revenue' => $soldItems->sum('unit_price'),
            'total_gross_profit' => $soldItems->sum(fn (SaleItem $i) => $i->profit()),
        ];

        return view('reports.iphones', compact('summary', 'soldItems', 'from', 'to'));
    }

    public function iphonesExport(Request $request)
    {
        [$from, $to] = $this->range($request);

        $soldItems = SaleItem::whereNotNull('phone_device_id')
            ->whereHas('sale', fn ($q) => $q->whereBetween('sold_at', [$from, $to])->where('status', 'completed'))
            ->with(['phoneDevice', 'product', 'sale.customer', 'sale.user'])
            ->get();

        return $this->csv('iphone-report.csv', ['Model', 'Storage', 'IMEI', 'Selling Price', 'Buying Price', 'Profit', 'Customer', 'Seller', 'Date'],
            $soldItems->map(fn (SaleItem $i) => [
                $i->product->name, $i->phoneDevice->storage, $i->phoneDevice->imei1,
                $i->unit_price, $i->cost_price, $i->profit(),
                $i->sale->customer->name ?? 'Walk-in', $i->sale->user->name, $i->sale->sold_at->format('Y-m-d'),
            ])
        );
    }

    // ---------------------------------------------------------------
    // Purchase Report
    // ---------------------------------------------------------------
    public function purchases(Request $request)
    {
        [$from, $to] = $this->range($request);

        $purchases = Purchase::with('supplier')
            ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('purchase_date')
            ->get();

        $summary = [
            'count' => $purchases->count(),
            'total_cost' => $purchases->sum('total_amount'),
            'total_paid' => $purchases->sum('amount_paid'),
            'total_balance' => $purchases->sum(fn (Purchase $p) => $p->balance()),
        ];

        return view('reports.purchases', compact('purchases', 'summary', 'from', 'to'));
    }

    public function purchasesExport(Request $request)
    {
        [$from, $to] = $this->range($request);

        $purchases = Purchase::with('supplier')
            ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('purchase_date')
            ->get();

        return $this->csv('purchase-report.csv', ['Reference', 'Supplier', 'Date', 'Total', 'Paid', 'Balance', 'Status'],
            $purchases->map(fn (Purchase $p) => [
                $p->reference, $p->supplier->name, $p->purchase_date->format('Y-m-d'),
                $p->total_amount, $p->amount_paid, $p->balance(), $p->status,
            ])
        );
    }

    // ---------------------------------------------------------------
    // Expense Report
    // ---------------------------------------------------------------
    public function expenses(Request $request)
    {
        [$from, $to] = $this->range($request);

        $expenses = Expense::with('user')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('date')
            ->get();

        $byCategory = $expenses->groupBy('category')->map->sum('amount')->sortDesc();
        $total = $expenses->sum('amount');

        return view('reports.expenses', compact('expenses', 'byCategory', 'total', 'from', 'to'));
    }

    public function expensesExport(Request $request)
    {
        [$from, $to] = $this->range($request);

        $expenses = Expense::with('user')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->get();

        return $this->csv('expense-report.csv', ['Date', 'Category', 'Amount', 'Payment Method', 'Description', 'Recorded By'],
            $expenses->map(fn (Expense $e) => [
                $e->date->format('Y-m-d'), $e->category, $e->amount, $e->payment_method, $e->description, $e->user->name,
            ])
        );
    }

    // ---------------------------------------------------------------
    // Customer Report
    // ---------------------------------------------------------------
    public function customers(Request $request)
    {
        [$from, $to] = $this->range($request);

        $customers = Customer::withCount(['sales as period_sales_count' => fn ($q) => $q->whereBetween('sold_at', [$from, $to])->where('status', 'completed')])
            ->withSum(['sales as period_spent' => fn ($q) => $q->whereBetween('sold_at', [$from, $to])->where('status', 'completed')], 'total')
            ->orderByDesc('period_spent')
            ->get();

        return view('reports.customers', compact('customers', 'from', 'to'));
    }

    public function customersExport(Request $request)
    {
        [$from, $to] = $this->range($request);

        $customers = Customer::withCount(['sales as period_sales_count' => fn ($q) => $q->whereBetween('sold_at', [$from, $to])->where('status', 'completed')])
            ->withSum(['sales as period_spent' => fn ($q) => $q->whereBetween('sold_at', [$from, $to])->where('status', 'completed')], 'total')
            ->orderByDesc('period_spent')
            ->get();

        return $this->csv('customer-report.csv', ['Name', 'Phone', 'Sales in Period', 'Spent in Period', 'Total Spent (all-time)', 'Outstanding Balance'],
            $customers->map(fn (Customer $c) => [
                $c->name, $c->phone, $c->period_sales_count ?? 0, $c->period_spent ?? 0, $c->totalSpent(), $c->outstandingBalance(),
            ])
        );
    }

    // ---------------------------------------------------------------
    // Supplier Report
    // ---------------------------------------------------------------
    public function suppliers(Request $request)
    {
        [$from, $to] = $this->range($request);

        $suppliers = Supplier::withCount(['purchases as period_purchases_count' => fn ($q) => $q->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])])
            ->withSum(['purchases as period_total' => fn ($q) => $q->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])], 'total_amount')
            ->orderByDesc('period_total')
            ->get();

        return view('reports.suppliers', compact('suppliers', 'from', 'to'));
    }

    public function suppliersExport(Request $request)
    {
        [$from, $to] = $this->range($request);

        $suppliers = Supplier::withCount(['purchases as period_purchases_count' => fn ($q) => $q->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])])
            ->withSum(['purchases as period_total' => fn ($q) => $q->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])], 'total_amount')
            ->orderByDesc('period_total')
            ->get();

        return $this->csv('supplier-report.csv', ['Supplier', 'Phone', 'Purchases in Period', 'Total in Period', 'Amount Owed'],
            $suppliers->map(fn (Supplier $s) => [
                $s->name, $s->phone, $s->period_purchases_count ?? 0, $s->period_total ?? 0, $s->amount_owed,
            ])
        );
    }

    // ---------------------------------------------------------------
    // Payment Method Report
    // ---------------------------------------------------------------
    public function paymentMethods(Request $request)
    {
        [$from, $to] = $this->range($request);

        $breakdown = Payment::whereBetween('paid_at', [$from, $to])
            ->selectRaw('method, count(*) as count, sum(amount) as total')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        $grandTotal = $breakdown->sum('total');

        return view('reports.payment-methods', compact('breakdown', 'grandTotal', 'from', 'to'));
    }

    // ---------------------------------------------------------------
    // Best-Selling Products
    // ---------------------------------------------------------------
    public function bestSelling(Request $request)
    {
        [$from, $to] = $this->range($request);

        $rows = SaleItem::whereHas('sale', fn ($q) => $q->whereBetween('sold_at', [$from, $to])->where('status', 'completed'))
            ->selectRaw('product_id, sum(quantity) as qty_sold, sum(subtotal) as revenue, sum((unit_price - cost_price) * quantity) as profit')
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('qty_sold')
            ->limit(50)
            ->get();

        return view('reports.best-selling', compact('rows', 'from', 'to'));
    }

    public function bestSellingExport(Request $request)
    {
        [$from, $to] = $this->range($request);

        $rows = SaleItem::whereHas('sale', fn ($q) => $q->whereBetween('sold_at', [$from, $to])->where('status', 'completed'))
            ->selectRaw('product_id, sum(quantity) as qty_sold, sum(subtotal) as revenue, sum((unit_price - cost_price) * quantity) as profit')
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('qty_sold')
            ->get();

        return $this->csv('best-selling-report.csv', ['Product', 'Quantity Sold', 'Revenue', 'Profit'],
            $rows->map(fn ($r) => [$r->product->name, $r->qty_sold, $r->revenue, $r->profit])
        );
    }

    // ---------------------------------------------------------------
    // Shared helpers
    // ---------------------------------------------------------------
    private function range(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from'))->startOfDay() : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->string('to'))->endOfDay() : now()->endOfMonth();

        return [$from, $to];
    }

    private function csv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}

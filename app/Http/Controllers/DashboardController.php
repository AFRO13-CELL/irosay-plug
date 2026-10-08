<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        $todaysSales = Sale::whereDate('sold_at', $today)->where('status', 'completed')->sum('total');
        $todaysExpenses = Expense::whereDate('date', $today)->sum('amount');

        // Gross profit sums (unit_price - cost_price) across today's sale items —
        // computed from each item's recorded cost, never the product's current price.
        $todaysGrossProfit = Sale::whereDate('sold_at', $today)
            ->where('status', 'completed')
            ->with('items')
            ->get()
            ->sum(fn (Sale $sale) => $sale->grossProfit());

        $todaysNetProfit = $todaysGrossProfit - $todaysExpenses;

        $iphonesInStock = PhoneDevice::where('status', 'in_stock')->count();
        $otherProductsInStock = (int) Product::where('tracking_type', 'quantity')->sum('stock_quantity');
        $lowStockCount = Product::where('tracking_type', 'quantity')
            ->whereColumn('stock_quantity', '<=', 'min_stock_level')
            ->count();

        $recentSales = Sale::with('customer')->latest('sold_at')->limit(5)->get();

        $period = $request->get('period', '7');
        $chartData = $this->chartData($period, $request->get('from'), $request->get('to'));

        return view('dashboard', compact(
            'todaysSales', 'todaysGrossProfit', 'todaysExpenses', 'todaysNetProfit',
            'iphonesInStock', 'otherProductsInStock', 'lowStockCount', 'recentSales',
            'period', 'chartData'
        ));
    }

    /**
     * Real Sales Chart data (spec section 6): Today (hourly), Last 7 days,
     * Last 30 days, or a Custom range — all daily except "today", which is
     * broken into hours since a single-point "chart" for one day isn't
     * useful. Every bucket is filled with 0 even when there were no sales,
     * so the chart's shape stays honest instead of skipping gaps.
     */
    private function chartData(string $period, ?string $from, ?string $to): array
    {
        if ($period === 'today') {
            $sales = Sale::whereDate('sold_at', now())->where('status', 'completed')->get();
            $byHour = $sales->groupBy(fn (Sale $s) => $s->sold_at->format('H'));

            return collect(range(0, 23))->map(fn ($h) => [
                'label' => sprintf('%02d:00', $h),
                'value' => (float) ($byHour->get(sprintf('%02d', $h), collect())->sum('total')),
            ])->all();
        }

        $start = match ($period) {
            '30' => now()->subDays(29)->startOfDay(),
            'custom' => $from ? Carbon::parse($from)->startOfDay() : now()->subDays(6)->startOfDay(),
            default => now()->subDays(6)->startOfDay(), // '7' and any unrecognized value
        };
        $end = $period === 'custom' && $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();

        // Guard against an accidentally huge custom range making the chart useless.
        if ($start->diffInDays($end) > 60) {
            $start = $end->copy()->subDays(60);
        }

        $sales = Sale::whereBetween('sold_at', [$start, $end])->where('status', 'completed')->get();
        $byDay = $sales->groupBy(fn (Sale $s) => $s->sold_at->format('Y-m-d'))->map->sum('total');

        $days = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $days[] = ['label' => $cursor->format('d M'), 'value' => (float) ($byDay[$cursor->format('Y-m-d')] ?? 0)];
            $cursor->addDay();
        }

        return $days;
    }
}

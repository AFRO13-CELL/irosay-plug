<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = Sale::with('customer', 'user')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($qq) => $qq->where('name', 'like', "%{$search}%"));
            })
            ->latest('sold_at')
            ->paginate(20)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale)
    {
        $sale->load('customer', 'user', 'items.product', 'items.phoneDevice', 'payments', 'returns');

        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale)
    {
        $sale->load('customer', 'items.product', 'items.phoneDevice', 'payments');

        return view('sales.receipt', compact('sale'));
    }
}

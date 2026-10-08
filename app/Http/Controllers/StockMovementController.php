<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    /**
     * Read-only ledger. There is intentionally no create/edit/delete here —
     * movements are only ever written by InventoryService, PurchaseService
     * (Phase 5) and SaleService (Phase 6), each alongside the change it
     * describes, inside a transaction. This page just displays history.
     */
    public function index(Request $request)
    {
        $movements = StockMovement::with(['product', 'phoneDevice', 'user'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->integer('product_id')))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('inventory.stock-movements.index', compact('movements'));
    }
}

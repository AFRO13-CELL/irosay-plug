<?php

namespace App\Http\Controllers;

use App\Models\ReturnItem;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Services\ReturnService;
use Illuminate\Http\Request;
use RuntimeException;

class ReturnController extends Controller
{
    public function __construct(private ReturnService $returns)
    {
    }

    public function index()
    {
        $returns = ReturnRecord::with('sale.customer', 'user')->latest()->paginate(20);

        return view('returns.index', compact('returns'));
    }

    public function create(Sale $sale)
    {
        $sale->load('items.product', 'items.phoneDevice');

        $returnableItems = $sale->items->map(function ($item) {
            $alreadyReturned = ReturnItem::where('sale_item_id', $item->id)->sum('quantity');
            $item->returnable_qty = $item->quantity - $alreadyReturned;

            return $item;
        })->filter(fn ($item) => $item->returnable_qty > 0);

        return view('returns.create', compact('sale', 'returnableItems'));
    }

    public function store(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $return = $this->returns->process($sale, $data['items'], $data['reason'], $request->user());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('returns.show', $return)->with('success', 'Return processed.');
    }

    public function show(ReturnRecord $return)
    {
        $return->load('sale.customer', 'user', 'items.product', 'items.phoneDevice');

        return view('returns.show', compact('return'));
    }
}

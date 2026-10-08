<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Services\InventoryService;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class PurchaseController extends Controller
{
    public function __construct(
        private PurchaseService $purchases,
        private InventoryService $inventory,
    ) {
    }

    public function index(Request $request)
    {
        $purchases = Purchase::with('supplier')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->integer('supplier_id')))
            ->latest('purchase_date')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
        ]);

        $purchase = Purchase::create([
            'supplier_id' => $data['supplier_id'],
            'user_id' => $request->user()->id,
            'reference' => $this->generateReference(),
            'purchase_date' => $data['purchase_date'],
            'notes' => $data['notes'] ?? null,
            'amount_paid' => $data['amount_paid'] ?? 0,
            'total_amount' => 0,
            'status' => 'pending',
        ]);

        AuditLog::record($request->user(), 'purchase.created', $purchase, "Created purchase {$purchase->reference}");

        return redirect()->route('purchases.show', $purchase)->with('success', "Purchase {$purchase->reference} created — now add items.");
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'user', 'items.product', 'items.phoneDevices');
        $products = Product::where('status', 'active')->orderBy('name')->get();

        return view('purchases.show', compact('purchase', 'products'));
    }

    public function addItem(Request $request, Purchase $purchase)
    {
        if ($purchase->status !== 'pending') {
            return back()->with('error', 'Items can only be added while a purchase is still pending.');
        }

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'buying_price' => ['required', 'numeric', 'min:0'],
        ]);

        PurchaseItem::create($data + ['purchase_id' => $purchase->id]);

        $this->purchases->recalculateTotal($purchase);

        return back()->with('success', 'Item added to purchase.');
    }

    public function removeItem(Request $request, Purchase $purchase, PurchaseItem $item)
    {
        if ($purchase->status !== 'pending') {
            return back()->with('error', 'Items can only be removed while a purchase is still pending.');
        }

        if ($item->phoneDevices()->exists()) {
            return back()->with('error', 'This line already has received IMEI devices linked to it and cannot be removed.');
        }

        $item->delete();
        $this->purchases->recalculateTotal($purchase);

        return back()->with('success', 'Item removed.');
    }

    /**
     * Capture one individual IMEI device against a serialized purchase
     * line. Each call creates exactly one phone_devices row and posts its
     * own stock_movement via InventoryService::receiveDevice — this is
     * what lets a purchase know a line is "3 of 5 received".
     */
    public function receiveDevice(Request $request, Purchase $purchase, PurchaseItem $item)
    {
        if ($purchase->status !== 'pending') {
            return back()->with('error', 'This purchase is no longer pending.');
        }

        if ($item->product->tracking_type !== 'serialized') {
            return back()->with('error', 'Only serialized (IMEI-tracked) lines need individual devices captured.');
        }

        if ($item->isFullyReceived()) {
            return back()->with('error', 'All units for this line have already been received.');
        }

        $data = $request->validate([
            'model' => ['required', 'string', 'max:255'],
            'storage' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:100'],
            'condition' => ['required', Rule::in(['preowned', 'brand_new'])],
            'activation_status' => ['required', Rule::in(['active', 'non_active', 'just_active'])],
            'packaging' => ['required', Rule::in(['sealed', 'with_box', 'without_box'])],
            'imei1' => ['required', 'string', 'max:20', Rule::unique('phone_devices', 'imei1'), Rule::unique('phone_devices', 'imei2')],
            'imei2' => ['nullable', 'string', 'max:20', Rule::unique('phone_devices', 'imei2'), Rule::unique('phone_devices', 'imei1')],
            'serial_number' => ['nullable', 'string', 'max:50', Rule::unique('phone_devices', 'serial_number')],
            'battery_health' => ['nullable', 'integer', 'min:0', 'max:100'],
            'selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        $device = PhoneDevice::create($data + [
            'product_id' => $item->product_id,
            'supplier_id' => $purchase->supplier_id,
            'purchase_item_id' => $item->id,
            'buying_price' => $item->buying_price,
            'purchase_date' => $purchase->purchase_date,
            'status' => 'in_stock',
        ]);

        $this->inventory->receiveDevice($device, $request->user(), reference: $purchase->reference);

        return back()->with('success', "IMEI {$device->imei1} captured ({$item->devicesReceivedCount()} of {$item->quantity} for this line).");
    }

    public function receive(Request $request, Purchase $purchase)
    {
        try {
            $this->purchases->receive($purchase, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Purchase {$purchase->reference} marked as received — stock has been updated.");
    }

    public function recordPayment(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $newPaid = (float) $purchase->amount_paid + $data['amount'];

        if ($newPaid > (float) $purchase->total_amount) {
            return back()->with('error', 'That payment would exceed the purchase total.');
        }

        $purchase->update(['amount_paid' => $newPaid]);

        AuditLog::record($request->user(), 'purchase.payment_recorded', $purchase, "GH₵{$data['amount']} recorded against {$purchase->reference}");

        return back()->with('success', 'Payment recorded.');
    }

    private function generateReference(): string
    {
        do {
            $reference = 'PUR-' . now()->format('Y') . '-' . str_pad((string) (Purchase::count() + 1), 4, '0', STR_PAD_LEFT);
        } while (Purchase::where('reference', $reference)->exists());

        return $reference;
    }
}

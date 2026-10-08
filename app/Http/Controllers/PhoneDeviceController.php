<?php

namespace App\Http\Controllers;

use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class PhoneDeviceController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    /**
     * Grouped-by-model summary cards (per spec section 9's example) plus a
     * filterable flat table of individual devices underneath.
     */
    public function index(Request $request)
    {
        $models = Product::where('tracking_type', 'serialized')
            ->withCount([
                'phoneDevices as in_stock_count' => fn ($q) => $q->where('status', 'in_stock'),
                'phoneDevices as reserved_count' => fn ($q) => $q->where('status', 'reserved'),
                'phoneDevices as sold_count' => fn ($q) => $q->where('status', 'sold'),
            ])
            ->orderBy('name')
            ->get();

        $devices = PhoneDevice::with('product')
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->integer('product_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($qq) use ($search) {
                    $qq->where('imei1', 'like', "%{$search}%")
                        ->orWhere('imei2', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhere('color', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('iphones.index', compact('models', 'devices'));
    }

    public function create()
    {
        $products = Product::where('tracking_type', 'serialized')->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $device = new PhoneDevice([
            'condition' => 'preowned',
            'activation_status' => 'active',
            'packaging' => 'without_box',
        ]);

        return view('iphones.create', compact('products', 'suppliers', 'device'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $device = PhoneDevice::create($data + ['status' => 'in_stock']);

        $this->inventory->receiveDevice($device, $request->user(), notes: 'Added directly via iPhone Management (Phase 4) — not through a Purchase yet.');

        return redirect()->route('iphones.show', $device)->with('success', "iPhone with IMEI {$device->imei1} added.");
    }

    public function show(PhoneDevice $device)
    {
        $device->load('product', 'supplier');
        $movements = $device->stockMovements()->with('user')->latest()->get();

        return view('iphones.show', compact('device', 'movements'));
    }

    public function edit(PhoneDevice $device)
    {
        $products = Product::where('tracking_type', 'serialized')->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('iphones.edit', compact('device', 'products', 'suppliers'));
    }

    public function update(Request $request, PhoneDevice $device)
    {
        $data = $this->validated($request, $device);

        $device->update($data);

        return redirect()->route('iphones.show', $device)->with('success', "iPhone with IMEI {$device->imei1} updated.");
    }

    public function reserve(Request $request, PhoneDevice $device)
    {
        if ($device->status !== 'in_stock') {
            return back()->with('error', 'Only an in-stock device can be reserved.');
        }

        try {
            $this->inventory->changeDeviceStatus($device, 'reserved', 'reservation', -1, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "IMEI {$device->imei1} reserved.");
    }

    public function cancelReservation(Request $request, PhoneDevice $device)
    {
        if ($device->status !== 'reserved') {
            return back()->with('error', 'This device is not currently reserved.');
        }

        try {
            $this->inventory->changeDeviceStatus($device, 'in_stock', 'reservation_cancelled', 1, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Reservation cancelled for IMEI {$device->imei1}.");
    }

    private function validated(Request $request, ?PhoneDevice $device = null): array
    {
        return $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'model' => ['required', 'string', 'max:255'],
            'storage' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:100'],
            'condition' => ['required', Rule::in(['preowned', 'brand_new'])],
            'activation_status' => ['required', Rule::in(['active', 'non_active', 'just_active'])],
            'packaging' => ['required', Rule::in(['sealed', 'with_box', 'without_box'])],
            'imei1' => [
                'required', 'string', 'max:20',
                Rule::unique('phone_devices', 'imei1')->ignore($device?->id),
                Rule::unique('phone_devices', 'imei2')->ignore($device?->id),
            ],
            'imei2' => [
                'nullable', 'string', 'max:20',
                Rule::unique('phone_devices', 'imei2')->ignore($device?->id),
                Rule::unique('phone_devices', 'imei1')->ignore($device?->id),
            ],
            'serial_number' => ['nullable', 'string', 'max:50', Rule::unique('phone_devices', 'serial_number')->ignore($device?->id)],
            'battery_health' => ['nullable', 'integer', 'min:0', 'max:100'],
            'buying_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'warranty' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}

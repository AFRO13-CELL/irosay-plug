<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Services\CartService;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use RuntimeException;

class PosController extends Controller
{
    public function __construct(
        private CartService $cart,
        private SaleService $sales,
    ) {
    }

    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $products = Product::with('category')
            ->where('status', 'active')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($qq) => $qq->where('slug', $request->string('category'))))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $cartItems = $this->cart->items();
        $customer = $this->cart->customerId() ? Customer::find($this->cart->customerId()) : null;
        $existingCustomers = Customer::orderBy('name')->limit(100)->get();
        $paymentMethods = ['Cash', 'Mobile Money', 'Card', 'Other'];

        return view('pos.index', [
            'categories' => $categories,
            'products' => $products,
            'cartItems' => $cartItems,
            'subtotal' => $this->cart->subtotal(),
            'discount' => $this->cart->discount(),
            'total' => $this->cart->total(),
            'customer' => $customer,
            'existingCustomers' => $existingCustomers,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function selectDevice(Product $product)
    {
        abort_unless($product->tracking_type === 'serialized', 404);

        $devices = $product->phoneDevices()->where('status', 'in_stock')->orderBy('created_at')->get();
        $inCart = collect($this->cart->items())->pluck('phone_device_id')->filter()->all();

        return view('pos.select-device', compact('product', 'devices', 'inCart'));
    }

    public function addProduct(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        try {
            $this->cart->addProduct($product, $data['quantity']);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$product->name} added to cart.");
    }

    public function addDevice(Request $request)
    {
        $data = $request->validate(['phone_device_id' => ['required', 'exists:phone_devices,id']]);

        $device = PhoneDevice::with('product')->findOrFail($data['phone_device_id']);

        try {
            $this->cart->addDevice($device);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('pos.index')->with('success', "IMEI {$device->imei1} added to cart.");
    }

    public function removeItem(Request $request)
    {
        $data = $request->validate(['key' => ['required', 'string']]);
        $this->cart->removeItem($data['key']);

        return back()->with('success', 'Item removed from cart.');
    }

    public function setCustomer(Request $request)
    {

  
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'new_name' => ['nullable', 'string', 'max:255'],
            'new_phone' => ['nullable', 'string', 'max:50'],
        ]);

        if (! empty($data['new_name'])) {
            $customer = Customer::create(['name' => $data['new_name'], 'phone' => $data['new_phone'] ?? null]);
            $this->cart->setCustomer($customer->id);

            return back()->with('success', "Customer \"{$customer->name}\" added and selected.");
        }

        $this->cart->setCustomer($data['customer_id'] ?? null);

                return back()->with('success', ($data['customer_id'] ?? null) ? 'Customer selected.' : 'Walk-in customer (no customer selected).');
    }

    public function setDiscount(Request $request)
    {
        $data = $request->validate(['discount' => ['required', 'numeric', 'min:0']]);
        $this->cart->setDiscount($data['discount']);

        return back();
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(['Cash', 'Mobile Money', 'Card', 'Other'])],
            'amount_paid' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $sale = $this->sales->checkout(
                $this->cart->items(),
                $this->cart->customerId(),
                $this->cart->discount(),
                $data['payment_method'],
                $data['amount_paid'],
                $request->user(),
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->cart->clear();

        return redirect()->route('sales.show', $sale)->with('success', "Sale {$sale->invoice_number} completed.");
    }
}

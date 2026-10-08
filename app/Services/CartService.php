<?php

namespace App\Services;

use App\Models\PhoneDevice;
use App\Models\Product;
use InvalidArgumentException;
use RuntimeException;

/**
 * The POS cart lives in the session — nothing is written to the database
 * until checkout(). Each line is keyed so a quantity product can only ever
 * have one line (re-adding just bumps the quantity) and a specific IMEI
 * device can only ever appear once (re-adding is rejected), which is what
 * satisfies the spec's "never select the same IMEI twice" rule before the
 * cart even reaches checkout.
 */
class CartService
{
    private const SESSION_KEY = 'pos_cart';

    public function addProduct(Product $product, int $quantity = 1): void
    {
        if ($product->tracking_type !== 'quantity') {
            throw new InvalidArgumentException('Serialized products must be added by specific IMEI device, not by quantity.');
        }

        $cart = $this->cart();
        $key = "product-{$product->id}";

        if (isset($cart['items'][$key])) {
            $cart['items'][$key]['quantity'] += $quantity;
        } else {
            $cart['items'][$key] = [
                'type' => 'product',
                'product_id' => $product->id,
                'phone_device_id' => null,
                'name' => $product->name,
                'unit_price' => (float) $product->selling_price,
                'cost_price' => (float) $product->buying_price,
                'quantity' => $quantity,
            ];
        }

        $this->save($cart);
    }

    public function addDevice(PhoneDevice $device): void
    {
        if ($device->status !== 'in_stock') {
            throw new RuntimeException("IMEI {$device->imei1} is not available for sale (status: {$device->status}).");
        }

        $cart = $this->cart();
        $key = "device-{$device->id}";

        if (isset($cart['items'][$key])) {
            throw new RuntimeException("IMEI {$device->imei1} is already in the cart.");
        }

        $cart['items'][$key] = [
            'type' => 'device',
            'product_id' => $device->product_id,
            'phone_device_id' => $device->id,
            'name' => $device->product->name . ' — IMEI ' . $device->imei1,
            'unit_price' => (float) $device->selling_price,
            'cost_price' => (float) $device->buying_price,
            'quantity' => 1,
        ];

        $this->save($cart);
    }

    public function removeItem(string $key): void
    {
        $cart = $this->cart();
        unset($cart['items'][$key]);
        $this->save($cart);
    }

    public function setCustomer(?int $customerId): void
    {
        $cart = $this->cart();
        $cart['customer_id'] = $customerId;
        $this->save($cart);
    }

    public function setDiscount(float $discount): void
    {
        $cart = $this->cart();
        $cart['discount'] = max(0, $discount);
        $this->save($cart);
    }

    public function items(): array
    {
        return $this->cart()['items'];
    }

    public function customerId(): ?int
    {
        return $this->cart()['customer_id'] ?? null;
    }

    public function discount(): float
    {
        return (float) ($this->cart()['discount'] ?? 0);
    }

    public function subtotal(): float
    {
        return array_sum(array_map(
            fn (array $item) => $item['unit_price'] * $item['quantity'],
            $this->items()
        ));
    }

    public function total(): float
    {
        return max(0, $this->subtotal() - $this->discount());
    }

    public function isEmpty(): bool
    {
        return empty($this->items());
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    private function cart(): array
    {
        return session(self::SESSION_KEY, ['items' => [], 'customer_id' => null, 'discount' => 0]);
    }

    private function save(array $cart): void
    {
        session([self::SESSION_KEY => $cart]);
    }
}

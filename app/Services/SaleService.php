<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\PhoneDevice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SaleService
{
    public function __construct(private InventoryService $inventory)
    {
    }

    /**
     * Completes a sale in one transaction: creates the sale + line items,
     * marks each sold IMEI device SOLD, decrements quantity-tracked stock,
     * records the payment, and writes the matching stock_movement/audit
     * rows via InventoryService — mirroring how PurchaseService::receive()
     * handles the buying side. If ANY item fails (e.g. a device was sold by
     * someone else a moment ago, or stock ran out), the whole sale rolls
     * back — nothing is left half-applied.
     */
    public function checkout(
        array $cartItems,
        ?int $customerId,
        float $discount,
        string $paymentMethod,
        float $amountPaid,
        User $user,
    ): Sale {
        if (empty($cartItems)) {
            throw new RuntimeException('The cart is empty.');
        }

        return DB::transaction(function () use ($cartItems, $customerId, $discount, $paymentMethod, $amountPaid, $user) {
            $subtotal = array_sum(array_map(fn ($i) => $i['unit_price'] * $i['quantity'], $cartItems));
            $total = max(0, $subtotal - $discount);
            $balance = $total - $amountPaid;

            $invoiceNumber = $this->generateInvoiceNumber();

            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $customerId,
                'user_id' => $user->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'balance' => $balance,
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            foreach ($cartItems as $item) {
                if ($item['type'] === 'device') {
                    // Re-fetch and lock fresh — never trust the cart's cached
                    // snapshot for availability or price at the moment of sale.
                    $device = PhoneDevice::whereKey($item['phone_device_id'])->lockForUpdate()->firstOrFail();

                    if ($device->status !== 'in_stock') {
                        throw new RuntimeException("IMEI {$device->imei1} is no longer available (status: {$device->status}). Remove it from the cart and try again.");
                    }

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $device->product_id,
                        'phone_device_id' => $device->id,
                        'quantity' => 1,
                        'unit_price' => $item['unit_price'],
                        'cost_price' => $device->buying_price, // actual recorded cost of THIS unit, never the product's current price
                        'subtotal' => $item['unit_price'],
                    ]);

                    $this->inventory->changeDeviceStatus(
                        $device, 'sold', 'sale', -1, $user, reference: $invoiceNumber
                    );
                } else {
                    $product = Product::whereKey($item['product_id'])->lockForUpdate()->firstOrFail();

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'phone_device_id' => null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'cost_price' => $product->buying_price,
                        'subtotal' => $item['unit_price'] * $item['quantity'],
                    ]);

                    // Throws (and rolls back the whole sale) if this would take stock below zero.
                    $this->inventory->adjustStock(
                        $product, -$item['quantity'], $user, type: 'sale', reference: $invoiceNumber
                    );
                }
            }

            if ($amountPaid > 0) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'method' => $paymentMethod,
                    'amount' => $amountPaid,
                    'paid_at' => now(),
                ]);
            }

            AuditLog::record($user, 'sale.created', $sale, "Sale {$invoiceNumber} completed — GH₵" . number_format($total, 2));

            return $sale;
        });
    }

    private function generateInvoiceNumber(): string
    {
        do {
            $invoice = 'INV-' . now()->format('Y') . '-' . str_pad((string) (Sale::count() + 1), 6, '0', STR_PAD_LEFT);
        } while (Sale::where('invoice_number', $invoice)->exists());

        return $invoice;
    }
}

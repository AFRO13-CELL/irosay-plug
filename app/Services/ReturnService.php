<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ReturnItem;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReturnService
{
    public function __construct(private InventoryService $inventory)
    {
    }

    /**
     * $selections: [sale_item_id => quantity_to_return]. The original Sale
     * is never edited or deleted — this always creates a new, linked
     * ReturnRecord (per the spec's "never delete historical sales" rule).
     * Quantity-tracked stock goes back up immediately; a serialized device
     * moves to 'returned' status (a distinct state from 'in_stock' — a
     * returned iPhone needs inspection before it's resellable, so it is
     * NOT auto-restocked as available).
     */
    public function process(Sale $sale, array $selections, string $reason, User $user): ReturnRecord
    {
        $selections = array_filter($selections, fn ($qty) => (int) $qty > 0);

        if (empty($selections)) {
            throw new RuntimeException('Select at least one item to return.');
        }

        return DB::transaction(function () use ($sale, $selections, $reason, $user) {
            $refundAmount = 0;
            $lines = [];

            foreach ($selections as $saleItemId => $qty) {
                $qty = (int) $qty;
                $saleItem = SaleItem::with('product', 'phoneDevice')->findOrFail($saleItemId);

                if ($saleItem->sale_id !== $sale->id) {
                    throw new RuntimeException('That item does not belong to this sale.');
                }

                $alreadyReturned = ReturnItem::where('sale_item_id', $saleItem->id)->sum('quantity');
                $available = $saleItem->quantity - $alreadyReturned;

                if ($qty > $available) {
                    throw new RuntimeException("Only {$available} unit(s) of \"{$saleItem->product->name}\" remain returnable on this sale.");
                }

                $refundAmount += $qty * (float) $saleItem->unit_price;
                $lines[] = ['saleItem' => $saleItem, 'qty' => $qty];
            }

            $return = ReturnRecord::create([
                'sale_id' => $sale->id,
                'user_id' => $user->id,
                'reason' => $reason,
                'refund_amount' => $refundAmount,
                'status' => 'approved',
            ]);

            foreach ($lines as $line) {
                /** @var SaleItem $saleItem */
                $saleItem = $line['saleItem'];
                $qty = $line['qty'];

                ReturnItem::create([
                    'return_id' => $return->id,
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'phone_device_id' => $saleItem->phone_device_id,
                    'quantity' => $qty,
                ]);

                if ($saleItem->phone_device_id) {
                    $this->inventory->changeDeviceStatus(
                        $saleItem->phoneDevice, 'returned', 'return', 0, $user,
                        reference: "RET-{$sale->invoice_number}",
                        notes: "Returned from sale {$sale->invoice_number}: {$reason}",
                    );
                } else {
                    $this->inventory->adjustStock(
                        $saleItem->product, $qty, $user, type: 'return',
                        reference: "RET-{$sale->invoice_number}",
                        notes: "Returned from sale {$sale->invoice_number}: {$reason}",
                    );
                }
            }

            // Mark the sale fully or partially refunded — never deleted, never edited otherwise.
            $totalReturnedValue = $sale->returns()->where('status', 'approved')->sum('refund_amount');
            $sale->status = $totalReturnedValue >= (float) $sale->total ? 'refunded' : 'partially_refunded';
            $sale->save();

            AuditLog::record($user, 'return.created', $return, "Return on {$sale->invoice_number}: GH₵" . number_format($refundAmount, 2) . " ({$reason})");

            return $return;
        });
    }
}
